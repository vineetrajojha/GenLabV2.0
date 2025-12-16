@php
    $amount = (float) ($expense->amount ?? 0);
    $approved = (float) ($expense->approved_amount ?? 0);
    $due = max(0, $amount - $approved);
    $receiptUrl = $expense->file_path ? asset('storage/'.$expense->file_path) : null;
    $receiptExists = $expense->file_path ? \Illuminate\Support\Facades\Storage::disk('public')->exists($expense->file_path) : false;
    $updateUrl = route('superadmin.personal.expenses.update', $expense);
    $deleteUrl = route('superadmin.personal.expenses.destroy', $expense);
@endphp
<tr
    data-id="{{ $expense->id }}"
    data-amount="{{ $amount }}"
    data-approved="{{ $approved }}"
    data-due="{{ $due }}"
    data-date="{{ optional($expense->from_date)->format('Y-m-d') }}"
    data-description="{{ e($expense->description ?? '') }}"
    data-update-url="{{ $updateUrl }}"
    data-delete-url="{{ $deleteUrl }}"
    data-receipt-url="{{ $receiptUrl }}"
>
    <td>{{ $serial ?? '—' }}</td>
    <td>{{ $expense->description ? \Illuminate\Support\Str::limit($expense->description, 60) : '-' }}</td>
    <td class="text-end">{{ number_format($amount, 2) }}</td>
    <td class="text-end">
        @if($approved > 0)
            {{ number_format($approved, 2) }}
        @else
            -
        @endif
    </td>
    <td>{{ optional($expense->from_date)->format('d M Y') }}</td>
    <td>
        @if($receiptUrl && $receiptExists)
            <button type="button" class="btn btn-sm btn-outline-secondary js-preview-receipt" data-url="{{ $receiptUrl }}">Preview</button>
        @elseif($receiptUrl)
            <span class="text-muted">Missing</span>
        @else
            -
        @endif
    </td>
    <td>
        @php
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
        @endphp
        {{ $approverName ?? '-' }}
    </td>
    <td>
        @php
            $status = strtolower($expense->status ?? 'pending');
        @endphp
        @if($status === 'approved')
            <span class="badge bg-success">Approved</span>
        @elseif($status === 'rejected')
            <span class="badge bg-danger">Rejected</span>
        @else
            <span class="badge bg-warning text-dark">Pending</span>
        @endif
    </td>
    <td>
        @php
            $currentStatus = strtolower($expense->status ?? 'pending');
            // Only consider 'approved' as final — allow actions for pending and rejected
            $isFinal = ($currentStatus === 'approved');
        @endphp
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-primary js-edit-expense" @if($isFinal) disabled @endif>Edit</button>
            <button type="button" class="btn btn-outline-danger js-delete-expense" @if($isFinal) disabled @endif>Delete</button>
        </div>
    </td>
</tr>
