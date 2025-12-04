<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingItem;
use App\Services\GetUserActiveDepartment;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingItemsExport;

class ShowBookingByLetterController extends Controller
{
    protected $departmentService;

    public function __construct(GetUserActiveDepartment $departmentService)
    {
        $this->departmentService = $departmentService;

    }

    public function index(Request $request)
    {
        // Get search, month, year from request
        $search = $request->input('search');
        $month  = $request->input('month');
        $year   = $request->input('year');

        // Base query
        $query = BookingItem::with(['booking', 'booking.marketingPerson']);

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('job_order_no', 'like', "%{$search}%")
                  ->orWhere('sample_description', 'like', "%{$search}%")
                  ->orWhere('sample_quality', 'like', "%{$search}%")
                  ->orWhere('particulars', 'like', "%{$search}%")
                  ->orWhereHas('booking', function ($bq) use ($search) {
                      $bq->where('client_name', 'like', "%{$search}%")
                         ->orWhereHas('marketingPerson', function ($mpq) use ($search) {
                             $mpq->where('name', 'like', "%{$search}%");
                         });
                  });
            });
        }

        // Filter by month
        if (!empty($month)) {
            $query->whereMonth('lab_expected_date', $month);
        }

        // Filter by year
        if (!empty($year)) {
            $query->whereYear('lab_expected_date', $year);
        }

        // Get results (paginated)
        $perPage = (int) $request->get('perPage', 25);
        if (!in_array($perPage, [25, 50, 100])) { $perPage = 25; }
        $items = $query->latest()->paginate($perPage)->withQueryString();

        // Return view
        return view('superadmin.showbooking.bookingByLetter', compact('items', 'search', 'month', 'year'));
    } 

    public function destroy(BookingItem $bookingItem)
    {
        $bookingItem->delete();

        return redirect()->back()
                        ->with('success', 'Booking item deleted successfully.');
    }

    protected function buildQuery(Request $request)
    {
        $search = $request->input('search');
        $month  = $request->input('month');
        $year   = $request->input('year');

        $query = BookingItem::with(['booking', 'booking.marketingPerson']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('job_order_no', 'like', "%{$search}%")
                  ->orWhere('sample_description', 'like', "%{$search}%")
                  ->orWhere('sample_quality', 'like', "%{$search}%")
                  ->orWhere('particulars', 'like', "%{$search}%")
                  ->orWhereHas('booking', function ($bq) use ($search) {
                      $bq->where('client_name', 'like', "%{$search}%")
                         ->orWhereHas('marketingPerson', function ($mpq) use ($search) {
                             $mpq->where('name', 'like', "%{$search}%");
                         });
                  });
            });
        }

        if (!empty($month)) {
            $query->whereMonth('lab_expected_date', $month);
        }

        if (!empty($year)) {
            $query->whereYear('lab_expected_date', $year);
        }

        // If marketing filter is provided (user_code), limit to bookings for that marketing person
        if ($request->filled('marketing')) {
            $marketing = $request->input('marketing');
            $query->whereHas('booking', function ($bq) use ($marketing) {
                $bq->where('marketing_id', $marketing);
            });
        }

        return $query;
    }

    public function exportPdf(Request $request)
    {
        $items = $this->buildQuery($request)->latest()->get();

        $pdf = Pdf::loadView('superadmin.showbooking.bookingByLetter_pdf', [
            'items' => $items,
            'search' => $request->input('search'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('booking_items.pdf');
    }

    public function exportExcel(Request $request)
    {
        $items = $this->buildQuery($request)->latest()->get();
        return Excel::download(new BookingItemsExport($items), 'booking_items.xlsx');
    }
}
