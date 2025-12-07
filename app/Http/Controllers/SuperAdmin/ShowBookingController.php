<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NewBooking;
use App\Models\Department;
use App\Services\GetUserActiveDepartment;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingsExport;


class ShowBookingController extends Controller
{
    
    protected $departmentService;
    
    public function __construct(GetUserActiveDepartment $departmentService)
    {
        $this->departmentService = $departmentService;

    }

        protected function buildQuery(Request $request, Department $department = null)
        {
            $search = $request->input('search');
            $month  = $request->input('month');
            $year   = $request->input('year');

            $query = NewBooking::with(['items', 'department', 'marketingPerson']);

            if ($department) {
                $query->where('department_id', $department->id);
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhere('reference_no', 'like', "%{$search}%")
                      ->orWhere('client_name', 'like', "%{$search}%")
                      ->orWhere('contact_email', 'like', "%{$search}%")
                      ->orWhere('contact_no', 'like', "%{$search}%")
                      ->orWhereHas('department', function ($deptQ) use ($search) {
                          $deptQ->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('items', function ($itemQ) use ($search) {
                          $itemQ->where('sample_description', 'like', "%{$search}%")
                                ->orWhere('sample_quality', 'like', "%{$search}%")
                                ->orWhere('lab_analysis_code', 'like', "%{$search}%")
                                ->orWhere('particulars', 'like', "%{$search}%");
                      })
                      ->orWhereHas('marketingPerson', function ($mpQ) use ($search) {
                          $mpQ->where('name', 'like', "%{$search}%");
                      })
                      ->orWhere('job_order_date', 'like', "%{$search}%");
                });
            }

            if (!empty($month)) {
                $query->whereMonth('job_order_date', $month);
            }

            if (!empty($year)) {
                $query->whereYear('job_order_date', $year);
            }

            // Restrict to a specific marketing person when provided (user_code is stored in marketing_id)
            if ($request->filled('marketing')) {
                $query->where('marketing_id', $request->input('marketing'));
            }

            return $query;
        }

        public function exportPdf(Request $request, Department $department = null)
        {
            $query = $this->buildQuery($request, $department);

            // Safety: avoid building extremely large PDFs that exhaust PHP memory.
            // Set max allowed rows via env `BOOKING_EXPORT_MAX_ROWS` (default 3000).
            $maxRows = (int) config('app.booking_export_max_rows', env('BOOKING_EXPORT_MAX_ROWS', 3000));
            $total = $query->count();

            if ($total > $maxRows) {
                return back()->with('error', "Too many records to export as PDF ({$total}). Please narrow the filters or set BOOKING_EXPORT_MAX_ROWS in your .env (current: {$maxRows}).");
            }

            // Optional: raise memory limit for export if configured via env BOOKING_EXPORT_MEMORY_LIMIT.
            $mem = env('BOOKING_EXPORT_MEMORY_LIMIT');
            if ($mem) {
                @ini_set('memory_limit', $mem);
            }

            $bookings = $query->latest()->get();
            $pdf = Pdf::loadView('superadmin.showbooking.showbooking_pdf', [
                'bookings' => $bookings,
                'department' => $department,
                'search' => $request->input('search'),
                'month' => $request->input('month'),
                'year' => $request->input('year'),
            ])->setPaper('a4', 'landscape');

            return $pdf->stream('bookings.pdf');
        }

        public function exportExcel(Request $request, Department $department = null)
        {
            // Use the query builder and a chunked export to avoid loading all rows into memory
            $query = $this->buildQuery($request, $department)->latest();

            // Eager load relationships used in mapping to avoid N+1 while streaming
            $query = $query->with(['items', 'department', 'marketingPerson']);

            return Excel::download(new \App\Exports\BookingsQueryExport($query), 'bookings.xlsx');
        }
    
    public function index(Request $request, Department $department = null)
    {
        $query = $this->buildQuery($request, $department);

        $perPage = (int) $request->get('perPage', 25);
        if (!in_array($perPage, [25, 50, 100])) { $perPage = 25; }
        $bookings = $query->latest()->paginate($perPage)->withQueryString();

        $departments = $this->departmentService->getDepartment();

        return view('superadmin.showbooking.showbooking', [
            'bookings' => $bookings,
            'department' => $department,
            'departments' => $departments,
            'search' => $request->input('search'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ]);
    }

    public function marketing(Request $request, Department $department = null)
    {
        $query = $this->buildQuery($request, $department);

        $perPage = (int) $request->get('perPage', 25);
        if (!in_array($perPage, [25, 50, 100])) { $perPage = 25; }
        $bookings = $query->latest()->paginate($perPage)->withQueryString();

        $departments = $this->departmentService->getDepartment();

        return view('superadmin.showbooking.marketing.showbooking', [
            'bookings' => $bookings,
            'department' => $department,
            'departments' => $departments,
            'search' => $request->input('search'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ]);
    }
    
}