@extends('superadmin.layouts.app')
@section('title', 'Report By Job Order')
@section('content')

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@php
    // Safe defaults to avoid errors if controller data not passed.
    $items = $items ?? collect();
    $paginator = $items instanceof \Illuminate\Contracts\Pagination\Paginator ? $items : null;
@endphp

<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Reports</h4>
                <h6>Show All Reports</h6>
            </div>
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                @php $q = http_build_query(array_filter(request()->only(['search','month','year','marketing']))); @endphp
                <a href="{{ route('superadmin.bookings.bookingByLetter.exportPdf') }}{{ $q ? ('?'.$q) : '' }}" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                @php $q = http_build_query(array_filter(request()->only(['search','month','year','marketing']))); @endphp
                <a href="{{ route('superadmin.bookings.bookingByLetter.exportExcel') }}{{ $q ? ('?'.$q) : '' }}" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <li><a data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
    </div>

    <div class="card">

        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

            <!-- Search Form -->
            <div class="search-set">
                <form method="GET" action="{{ route('superadmin.reporting.viewByJobOrder') }}" class="d-flex input-group">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                    @if(request()->filled('month'))
                        <input type="hidden" name="month" value="{{ request('month') }}">
                    @endif
                    @if(request()->filled('year'))
                        <input type="hidden" name="year" value="{{ request('year') }}">
                    @endif
                    @if(request()->filled('marketing'))
                        <input type="hidden" name="marketing" value="{{ request('marketing') }}">
                    @endif
                    <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
            </div>

            <!-- Month & Year Filter Form -->
            <div class="search-set">
                <form method="GET" action="{{ route('superadmin.reporting.viewByJobOrder') }}" class="d-flex input-group">
                    <select name="month" class="form-control">
                        <option value="">Select Month</option>
                        @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                        @endforeach
                    </select>

                    <select name="year" class="form-control">
                        <option value="">Select Year</option>
                        @foreach(range(date('Y'), date('Y') - 10) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                        @endforeach
                    </select>

                    @if(request()->filled('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(request()->filled('marketing'))
                        <input type="hidden" name="marketing" value="{{ request('marketing') }}">
                    @endif
                    <button class="btn btn-outline-secondary" type="submit">Filter</button>
                </form>
            </div>

        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                            <th>Job Order No</th>
                            <th style="width:180px;">Client Name</th>
                            <th style="width:240px;">Sample Description</th>
                            <th>Sample Quality</th>
                            <th style="width:240px;">Particulars</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                            <td class="job-order-cell" data-bs-toggle="tooltip" title="{{ $item->job_order_no }}">{{ $item->job_order_no }}</td>
                            <td class="truncate-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->booking?->client_name ?? '-' }}">{{ $item->booking?->client_name ?? '-' }}</div>
                            </td>
                            <td class="truncate-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_description }}">{{ $item->sample_description }}</div>
                            </td>
                            <td>
                                <div class="cell-inner">{{ $item->sample_quality }}</div>
                            </td>
                            <td class="truncate-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->particulars }}">{{ $item->particulars }}</div>
                            </td>
                            <td class="d-flex">
                                @php
                                    $report = $item->reports->first(function ($report) {
                                        return filled($report->pivot->pdf_path ?? null);
                                    });
                                @endphp

                                @if($report && $report->pivot->pdf_path)
                                    <a href="{{ route('viewPdf', basename($report->pivot->pdf_path)) }}"
                                       target="_blank"
                                       class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                       data-bs-toggle="tooltip" title="View Report">
                                        <i data-feather="file-text" class="feather-file-text"></i>
                                    </a>
                                @endif

                                <a href="{{ route('superadmin.bookings.cards.single', [$item->booking->id, $item->id]) }}"
                                    target="_blank"
                                    class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                    data-bs-toggle="tooltip" title="View Booking Card">
                                        <i data-feather="eye" class="feather-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No items found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                        <form method="GET" action="{{ route('superadmin.reporting.viewByJobOrder') }}" class="d-flex align-items-center gap-2">
                            @foreach(request()->except(['perPage','page']) as $key => $val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endforeach
                            <label for="perPageSelect" class="me-1 mb-0 small">Rows per page:</label>
                            <select name="perPage" id="perPageSelect" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                @foreach([25,50,100] as $size)
                                    <option value="{{ $size }}" {{ request('perPage',25)==$size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </form>
                        <div>
                            @if($paginator)
                                {{ $paginator->appends(request()->all())->links('pagination::bootstrap-5') }}
                            @endif
                        </div>
                    </div>
                </div>
               
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Show full content for client/sample/particulars (no truncation) */
    .truncate-cell { max-width: 320px; }
    .truncate-cell .cell-inner{
        display: block;
        white-space: normal;
        word-break: break-word;
        overflow: visible;
    }
    @media (max-width: 992px){ .truncate-cell { max-width: 220px; } }

    /* Allow full job order text to wrap */
    .job-order-cell{ max-width:320px; white-space:normal; word-break:break-word; overflow:visible; }
</style>
@endpush

@endsection
