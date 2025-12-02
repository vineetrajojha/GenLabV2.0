<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Approve Vouchers</h5>
            <ul class="table-top-head list-inline d-flex gap-3 mb-0">
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.vouchers.export.pdf', request()->query())); ?>" class="js-export-pdf" data-bs-toggle="tooltip" title="PDF"><i class="fa fa-file-pdf"></i></a>
            </li>
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.vouchers.export.excel', request()->query())); ?>" class="js-export-excel" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <li><a href="<?php echo e(request()->fullUrl()); ?>" data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
    </div>
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="search-set d-flex align-items-center gap-2">
                <form method="GET" action="<?php echo e(route('superadmin.vouchers.approve')); ?>" class="d-flex input-group input-group-sm m-0">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search by user, purpose...">
                    <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
            </div>

            <div class="search-set d-flex gap-2">
                <form method="GET" action="<?php echo e(route('superadmin.vouchers.approve')); ?>" class="d-flex input-group m-0">
                    <select name="month" class="form-control">
                        <option value="">Select Month</option>
                        <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m); ?>" <?php echo e(request('month') == $m ? 'selected' : ''); ?>><?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select name="year" class="form-control">
                        <option value="">Select Year</option>
                        <?php $__currentLoopData = range(date('Y'), date('Y') - 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button class="btn btn-outline-secondary" type="submit">Filter</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
            <?php endif; ?>

            <ul class="nav nav-tabs mb-3" id="voucherTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">Pending</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="processed-tab" data-bs-toggle="tab" data-bs-target="#processed" type="button" role="tab">Processed</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="pending" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Purpose</th>
                                    <th>Attachment</th>
                                    <th>Payment</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $pending; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($v->id); ?></td>
                                        <td><?php echo e(optional($v->user)->name); ?></td>
                                        <td><?php echo e(number_format($v->amount,2)); ?></td>
                                        <td><?php echo e(\Illuminate\Support\Str::limit($v->purpose,80)); ?></td>
                                        <td>
                                            <?php if($v->attachment): ?>
                                                <a href="<?php echo e(asset('storage/' . $v->attachment)); ?>" target="_blank" title="View attachment"><i class="fa fa-paperclip"></i></a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($v->payment_status == 'paid'): ?>
                                                <span class="badge bg-success">Paid</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Unpaid</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($v->created_at->format('Y-m-d H:i')); ?></td>
                                        <td>
                                            <form method="POST" action="<?php echo e(route('superadmin.vouchers.approve.action', $v->id)); ?>" style="display:inline-block">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button class="btn btn-sm btn-success">Approve</button>
                                            </form>

                                            <form method="POST" action="<?php echo e(route('superadmin.vouchers.reject.action', $v->id)); ?>" style="display:inline-block; margin-left:6px;">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="8">No pending vouchers.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="processed" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Purpose</th>
                                    <th>Attachment</th>
                                    <th>Payment</th>
                                    <th>Created At</th>
                                    <th>Status</th>
                                    <th>Approved By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $processed; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($v->id); ?></td>
                                        <td><?php echo e(optional($v->user)->name); ?></td>
                                        <td><?php echo e(number_format($v->amount,2)); ?></td>
                                        <td><?php echo e(\Illuminate\Support\Str::limit($v->purpose,80)); ?></td>
                                        <td>
                                            <?php if($v->attachment): ?>
                                                <a href="<?php echo e(asset('storage/' . $v->attachment)); ?>" target="_blank" title="View attachment"><i class="fa fa-paperclip"></i></a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" action="<?php echo e(route('superadmin.vouchers.payment', $v->id)); ?>" class="d-flex gap-2 align-items-center">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <select name="payment_status" class="form-select form-select-sm">
                                                    <option value="unpaid" <?php echo e($v->payment_status !== 'paid' ? 'selected' : ''); ?>>Unpaid</option>
                                                    <option value="paid" <?php echo e($v->payment_status === 'paid' ? 'selected' : ''); ?>>Paid</option>
                                                </select>
                                                <button class="btn btn-sm btn-outline-primary">Save</button>
                                            </form>
                                        </td>
                                        <td><?php echo e($v->created_at->format('Y-m-d H:i')); ?></td>
                                        <td>
                                            <?php if($v->status == 'approved'): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e(optional($v->approver)->name ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="8">No processed vouchers.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        <?php $__env->startPush('scripts'); ?>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                async function downloadBlob(url, fallbackName) {
                    const resp = await fetch(url, { credentials: 'same-origin' });
                    if(!resp.ok) throw new Error('Network response was not ok');
                    const blob = await resp.blob();
                    // Try extract filename
                    const disp = resp.headers.get('content-disposition') || '';
                    let filename = fallbackName || 'download';
                    const m = /filename\*?=(?:UTF-8'')?"?([^";]+)"?/.exec(disp);
                    if(m && m[1]) filename = m[1];
                    // create download
                    const urlObj = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = urlObj;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    URL.revokeObjectURL(urlObj);
                }

                document.addEventListener('DOMContentLoaded', function(){
                    document.querySelectorAll('.js-export-pdf, .js-export-excel').forEach(el => {
                        el.addEventListener('click', async function(e){
                            e.preventDefault();
                            const url = this.href;
                            const isPdf = this.classList.contains('js-export-pdf');
                            const fallback = isPdf ? 'vouchers.pdf' : 'vouchers.csv';

                            const swal = Swal.fire({
                                title: 'Preparing download',
                                html: 'Please wait while the file is being generated...',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });

                            try {
                                await downloadBlob(url, fallback);
                                Swal.close();
                            } catch(err) {
                                Swal.close();
                                Swal.fire({icon:'error', title:'Download failed', text: err.message || 'Unable to download file.'});
                            }
                        });
                    });
                });
            </script>
        <?php $__env->stopPush(); ?>

        <?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/accounts/vouchers/approve.blade.php ENDPATH**/ ?>