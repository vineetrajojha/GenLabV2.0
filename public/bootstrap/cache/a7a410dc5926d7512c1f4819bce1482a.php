<table class="table table-hover mb-2">
    <thead>
        <tr>
            <th>#</th>
            <?php if (! ($isClient)): ?>
                <th>Client</th>
            <?php endif; ?>
            <th>Reference No</th>
            <th>Booking Date</th>
            <th>Amount</th>
            <th>Items</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($i+1); ?></td>
                <?php if (! ($isClient)): ?>
                    <td><?php echo e($booking->client->name ?? 'N/A'); ?></td>
                <?php endif; ?>
                <td><?php echo e($booking->reference_no); ?></td>
                <td><?php echo e($booking->created_at->format('d-M-Y')); ?></td>
                <td>₹<?php echo e(number_format($booking->total_amount, 2)); ?></td>
                
                <td>
                    <?php echo e($booking->items->count()); ?>

                    <?php if($booking->items->count() > 0): ?>
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-<?php echo e($booking->id); ?>">
                            <i class="fa fa-eye ms-2 text-primary"></i>
                        </a>

                        <!-- Modal -->
                        <div class="modal fade" id="itemsModal-<?php echo e($booking->id); ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl items-modal-right">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Booking Items for <?php echo e($isClient ? ($booking->marketingPerson?->name ?? 'N/A') : ($booking->client?->name ?? 'N/A')); ?></h5>
                                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span> 
                                            </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Job Order No</th>
                                                        <th>Sample Description</th>
                                                        <th>Sample Quality</th>
                                                        <th>Status</th>
                                                        <th>Particulars</th>
                                                        <th>Expected Date</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $booking->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($item->job_order_no); ?></td>
                                                        <td><?php echo e($item->sample_description); ?></td>
                                                        <td><?php echo e($item->sample_quality); ?></td>
                                                        <td><?php echo e($item->issue_date ? 'Issued' : 'Pending'); ?></td>
                                                        <td><?php echo e($item->particulars); ?></td>
                                                        <td><?php echo e(\Carbon\Carbon::parse($item->lab_expected_date)->format('d-m-Y')); ?></td>
                                                        <td>₹<?php echo e(number_format($item->amount, 2)); ?></td>
                                                    </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </td>

                <td>
                    
                    <span class="badge <?php echo e($booking->generatedInvoice?->status ? 'bg-success' : 'bg-warning'); ?>">
                        <?php echo e($booking->generatedInvoice?->status ? 'Completed' : 'Pending'); ?>

                    </span> 
                        <?php if(!$booking->generatedInvoice?->status): ?>
                         
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php if($bookings->hasPages()): ?>
    <?php echo $bookings->links('pagination::bootstrap-5'); ?>

<?php endif; ?>

<?php $__env->startPush('styles'); ?>
<style>
    .items-modal-right {
        margin-left: auto;
        margin-right: 18px;
        max-width: 1200px;
        width: 90%;
    }
    .items-modal-right .modal-content {
        margin-left: auto;
    }
    @media (max-width: 991.98px) {
        .items-modal-right {
            margin-right: 12px;
            width: auto;
            max-width: 100%;
        }
        .items-modal-right .modal-content {
            margin-left: 0;
        }
    }
</style>
<?php $__env->stopPush(); ?>
<?php /**PATH A:\GenTech\htdocs\GenlabV3.0\GenLabV3.0\resources\views/superadmin/accounts/marketingPerson/partials_bookings.blade.php ENDPATH**/ ?>