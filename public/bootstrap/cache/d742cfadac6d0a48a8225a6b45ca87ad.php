<?php $i = 0; ?>
<?php $__empty_1 = true; $__currentLoopData = $approvedList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <tr data-id="<?php echo e($row->id); ?>">
        <td><?php echo e(++$i); ?></td>
        <td>
            <?php if($row->marketingPerson): ?>
                <strong><?php echo e($row->marketingPerson->name); ?></strong><br>
                <small class="text-muted"><?php echo e($row->marketing_person_code ?? ''); ?></small>
            <?php else: ?>
                <?php echo e($row->person_name ?? 'Personal'); ?>

            <?php endif; ?>
        </td>
        <td class="text-end"><?php echo e(number_format((float) $row->amount, 2)); ?></td>
        <td class="text-end"><?php echo e(number_format((float) $row->approved_amount, 2)); ?></td>
        <td><?php echo e(optional($row->from_date)->format('d M Y')); ?> - <?php echo e(optional($row->to_date)->format('d M Y')); ?></td>
        <td><?php echo e(optional($row->created_at)->format('d M Y H:i')); ?></td>
        <td><?php echo e($row->approver?->name ?? ($row->approved_by ?? '-')); ?></td>
        <td>
            <?php if($row->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($row->file_path)): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary js-preview-receipt" data-url="<?php echo e(asset('storage/'.$row->file_path)); ?>">Preview</button>
            <?php elseif($row->file_path): ?>
                <span class="text-muted">Missing</span>
            <?php else: ?>
                -
            <?php endif; ?>
        </td>
        <td><span class="badge bg-success">Approved</span></td>
    </tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <tr>
        <td colspan="9" class="text-center">No approved expenses found for this sub-section.</td>
    </tr>
<?php endif; ?>
<?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/marketing/expenses/_approved_rows.blade.php ENDPATH**/ ?>