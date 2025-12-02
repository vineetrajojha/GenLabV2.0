<?php
    $amount = (float) ($expense->amount ?? 0);
    $approved = (float) ($expense->approved_amount ?? 0);
    $due = max(0, $amount - $approved);
    $receiptUrl = $expense->file_path ? asset('storage/'.$expense->file_path) : null;
    $receiptExists = $expense->file_path ? \Illuminate\Support\Facades\Storage::disk('public')->exists($expense->file_path) : false;
    $updateUrl = route('superadmin.personal.expenses.update', $expense);
    $deleteUrl = route('superadmin.personal.expenses.destroy', $expense);
?>
<tr
    data-id="<?php echo e($expense->id); ?>"
    data-amount="<?php echo e($amount); ?>"
    data-approved="<?php echo e($approved); ?>"
    data-due="<?php echo e($due); ?>"
    data-date="<?php echo e(optional($expense->from_date)->format('Y-m-d')); ?>"
    data-description="<?php echo e(e($expense->description ?? '')); ?>"
    data-update-url="<?php echo e($updateUrl); ?>"
    data-delete-url="<?php echo e($deleteUrl); ?>"
    data-receipt-url="<?php echo e($receiptUrl); ?>"
>
    <td><?php echo e($serial ?? '—'); ?></td>
    <td><?php echo e($expense->description ? \Illuminate\Support\Str::limit($expense->description, 60) : '-'); ?></td>
    <td><?php echo e(number_format($amount, 2)); ?></td>
    <td><?php echo e(optional($expense->from_date)->format('d M Y')); ?></td>
    <td>
        <?php if($receiptUrl && $receiptExists): ?>
            <button type="button" class="btn btn-sm btn-outline-secondary js-preview-receipt" data-url="<?php echo e($receiptUrl); ?>">Preview</button>
        <?php elseif($receiptUrl): ?>
            <span class="text-muted">Missing</span>
        <?php else: ?>
            -
        <?php endif; ?>
    </td>
    <td>
        <?php
            $approverName = null;
            if(!empty($expense->approver) && !empty($expense->approver->name)){
                $approverName = $expense->approver->name;
            } elseif(!empty($expense->approved_by)){
                // If approved_by is numeric (id), try resolving against Admin then User
                if(is_numeric($expense->approved_by)){
                    $ap = \App\Models\Admin::find($expense->approved_by) ?? \App\Models\User::find($expense->approved_by);
                    $approverName = $ap?->name ?? $expense->approved_by;
                } else {
                    // may already be a name string
                    $approverName = $expense->approved_by;
                }
            }
        ?>
        <?php echo e($approverName ?? '-'); ?>

    </td>
    <td>
        <?php
            $status = strtolower($expense->status ?? 'pending');
        ?>
        <?php if($status === 'approved'): ?>
            <span class="badge bg-success">Approved</span>
        <?php elseif($status === 'rejected'): ?>
            <span class="badge bg-danger">Rejected</span>
        <?php else: ?>
            <span class="badge bg-warning text-dark">Pending</span>
        <?php endif; ?>
    </td>
    <td>
        <?php
            $currentStatus = strtolower($expense->status ?? 'pending');
            // Only consider 'approved' as final — allow actions for pending and rejected
            $isFinal = ($currentStatus === 'approved');
        ?>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-primary js-edit-expense" <?php if($isFinal): ?> disabled <?php endif; ?>>Edit</button>
            <button type="button" class="btn btn-outline-danger js-delete-expense" <?php if($isFinal): ?> disabled <?php endif; ?>>Delete</button>
        </div>
    </td>
</tr>
<?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/personal/expenses/_daily_row.blade.php ENDPATH**/ ?>