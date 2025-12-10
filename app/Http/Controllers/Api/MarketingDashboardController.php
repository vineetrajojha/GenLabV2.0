<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MarketingPersonStatsService;
use App\Models\NewBooking;
use App\Models\Invoice;
use App\Models\InvoiceTransaction;
use App\Models\CashLetterPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MarketingDashboardController extends Controller
{
    protected MarketingPersonStatsService $statsService;

    public function __construct(MarketingPersonStatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    /**
     * Return overview KPIs for a marketing person.
     * GET /api/marketing-dashboard/{user_code}/overview
     */
    public function overview(Request $request, $user_code)
    {
        $filters = $request->only(['month', 'year']);

        $data = $this->statsService->calculate($user_code, $filters);

        return response()->json([
            'status' => true,
            'message' => 'Marketing overview fetched',
            'data' => $data,
        ], 200);
    }

    /**
     * Provide a compact summary suitable for dashboard widgets (subset of overview).
     * GET /api/marketing-dashboard/{user_code}/summary
     */
    public function summary(Request $request, $user_code)
    {
        $filters = $request->only(['month', 'year']);
        $data = $this->statsService->calculate($user_code, $filters);

        $summary = [
            'totalBookings' => $data['totalBookings'] ?? 0,
            'totalBookingAmount' => $data['totalBookingAmount'] ?? 0,
            'totalInvoiceAmount' => $data['totalInvoiceAmount'] ?? 0,
            'totalUnpaidInvoiceAmount' => $data['totalUnpaidInvoiceAmount'] ?? 0,
            'tdsAmount' => $data['tdsAmount'] ?? 0,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Summary fetched',
            'data' => $summary,
        ], 200);
    }

    /**
     * Return monthly series data for charts.
     * GET /api/marketing-dashboard/{user_code}/series?type=bookings|invoices|transactions|cash&months=12
     */
    public function series(Request $request, $user_code)
    {
        $type = $request->get('type', 'bookings');
        $months = max(1, (int) $request->get('months', 12));

        $end = Carbon::now()->endOfMonth();
        $start = Carbon::now()->subMonths($months - 1)->startOfMonth();

        switch ($type) {
            case 'bookings':
                $rows = NewBooking::query()
                    ->where('marketing_id', $user_code)
                    ->whereBetween('created_at', [$start, $end])
                    ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as value')
                    ->groupBy('year', 'month')
                    ->get();
                break;
            case 'invoices':
                $rows = Invoice::query()
                    ->where('marketing_user_code', $user_code)
                    ->whereBetween('created_at', [$start, $end])
                    ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COALESCE(SUM(total_amount),0) as value')
                    ->groupBy('year', 'month')
                    ->get();
                break;
            case 'transactions':
                $rows = InvoiceTransaction::query()
                    ->where('marketing_person_id', $user_code)
                    ->whereBetween('created_at', [$start, $end])
                    ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COALESCE(SUM(amount_received),0) as value')
                    ->groupBy('year', 'month')
                    ->get();
                break;
            case 'cash':
                $rows = CashLetterPayment::query()
                    ->where('marketing_person_id', $user_code)
                    ->whereBetween('created_at', [$start, $end])
                    ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COALESCE(SUM(total_amount),0) as value')
                    ->groupBy('year', 'month')
                    ->get();
                break;
            default:
                return response()->json([ 'status' => false, 'message' => 'Invalid series type' ], 400);
        }

        $map = [];
        foreach ($rows as $r) {
            $key = sprintf('%04d-%02d', $r->year, $r->month);
            $map[$key] = (float) ($r->value ?? 0);
        }

        $labels = [];
        $series = [];
        $cursor = $start->copy();
        for ($i = 0; $i < $months; $i++) {
            $key = $cursor->format('Y-m');
            $labels[] = $cursor->format('M Y');
            $series[] = $map[$key] ?? 0;
            $cursor->addMonth();
        }

        return response()->json([
            'status' => true,
            'message' => 'Series fetched',
            'data' => [
                'labels' => $labels,
                'series' => $series,
            ]
        ], 200);
    }
}
