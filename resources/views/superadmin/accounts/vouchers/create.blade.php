@extends('superadmin.layouts.app')

@section('title', 'Generate Vouchers')

@section('content')
<div class="card mt-3">
    <div class="page-header">
        <div class="add-item d-flex ms-4 mt-4">
            <div class="page-title">
                <h4>Generate Voucher</h4>
                <h6>Create vouchers and send them for approval</h6>
            </div>
        </div>
        <ul class="table-top-head list-inline d-flex gap-3 align-items-center">
            <li class="list-inline-item">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createVoucherModal" title="Create Voucher">
                    Create Voucher
                </button>
            </li>
            <li class="list-inline-item">
                <a href="#" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="#" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <li><a data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
    </div>

    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="search-set d-flex align-items-center gap-2">
            <!-- Search form -->
            <form method="GET" action="{{ route('superadmin.vouchers.index') }}" class="d-flex input-group input-group-sm m-0">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </form>
        </div>

        

        <!-- Month & Year Filter Form -->
        <div class="search-set d-flex gap-2">
            <form method="GET" action="{{ route('superadmin.vouchers.index') }}" class="d-flex input-group">
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
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>

                <button class="btn btn-outline-secondary" type="submit">Filter</button>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="row g-0">
            <div class="col-12 p-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="vouchersTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Person</th>
                                <th class="text-end">Amount</th>
                                <th>Purpose</th>
                                   <th>Attachment</th>
                                <th>Payment</th>
                                <th>Created At</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vouchers as $i => $v)
                                <tr>
                                    <td>{{ $v->id }}</td>
                                    <td><strong>{{ optional($v->user)->name }}</strong></td>
                                    <td class="text-end">{{ number_format((float)$v->amount, 2) }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($v->purpose, 80) }}</td>
                                       <td>
                                           @if($v->attachment)
                                               <a href="{{ asset('storage/' . $v->attachment) }}" target="_blank" title="View attachment"><i class="fa fa-paperclip"></i></a>
                                           @else
                                               -
                                           @endif
                                       </td>
                                       <td>
                                           @if(isset($v->payment_status) && $v->payment_status === 'paid')
                                               <span class="badge bg-success">Paid</span>
                                           @else
                                               <span class="badge bg-secondary">Unpaid</span>
                                           @endif
                                       </td>
                                    <td>{{ optional($v->created_at)->format('d M Y') }}</td>
                                    <td>
                                        @if($v->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($v->status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($v->status == 'pending')
                                            <a href="{{ route('superadmin.vouchers.edit', $v->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                                            <form method="POST" action="{{ route('superadmin.vouchers.destroy', $v->id) }}" class="d-inline-block js-delete-voucher">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        @elseif($v->status == 'approved')
                                            <a href="{{ route('superadmin.vouchers.generate', $v->id) }}" class="btn btn-sm btn-primary" target="_blank">Generate Voucher</a>
                                        @else
                                            <small>Processed</small>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No vouchers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">Grand Total:</td>
                                <td class="text-end">{{ number_format($vouchers->sum('amount'), 2) }}</td>
                                <td colspan="6"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <div class="card-footer">
        {{-- pagination placeholder if you later paginate --}}
    </div>
</div>

<!-- Create Voucher Modal -->
<div class="modal fade" id="createVoucherModal" tabindex="-1" aria-labelledby="createVoucherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createVoucherModalLabel">Create Voucher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(isset($voucher))
            <form method="POST" action="{{ route('superadmin.vouchers.update', $voucher->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
        @else
            <form method="POST" action="{{ route('superadmin.vouchers.store') }}" enctype="multipart/form-data">
                @csrf
        @endif

            <div class="mb-3">
                <label class="form-label">Select User</label>
                <select name="user_id" class="form-control" required>
                    <option value="">-- Select --</option>
                    @if(!empty($users))
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ (old('user_id') == $u->id) || (isset($voucher) && $voucher->user_id == $u->id) ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Amount</label>
                <input type="number" step="0.01" name="amount" class="form-control" required value="{{ old('amount', isset($voucher) ? $voucher->amount : '') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Purpose</label>
                <textarea name="purpose" class="form-control">{{ old('purpose', isset($voucher) ? $voucher->purpose : '') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Attachment (optional)</label>
                <input type="file" name="attachment" class="form-control">
                @if(isset($voucher) && $voucher->attachment)
                    <div class="mt-1"><a href="{{ asset('storage/' . $voucher->attachment) }}" target="_blank">View current attachment</a></div>
                @endif
            </div>

            <div class="d-grid">
                <button class="btn btn-primary">{{ isset($voucher) ? 'Update & Save' : 'Create & Send For Approval' }}</button>
            </div>
        </form>

        @if(isset($voucher))
            <script>
                document.addEventListener('DOMContentLoaded', function(){
                    var modalEl = document.getElementById('createVoucherModal');
                    if(modalEl){
                        var modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                });
            </script>
        @endif

<script>
    document.addEventListener('DOMContentLoaded', function(){
        if (!window.Swal) return;

        document.querySelectorAll('.js-delete-voucher').forEach(function(frm){
            frm.addEventListener('submit', function(e){
                e.preventDefault();
                const form = this;
                Swal.fire({
                    title: 'Delete voucher?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel'
                }).then(function(result){
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
      </div>
    </div>
  </div>
</div>
@endsection
