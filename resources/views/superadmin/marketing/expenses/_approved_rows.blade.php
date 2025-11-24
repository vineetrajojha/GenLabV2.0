@php $i = 0; @endphp
@forelse($approvedList as $row)
    <tr data-id="{{ $row->id }}">
        <td>{{ ++$i }}</td>
        <td>
            @if($row->marketingPerson)
                <strong>{{ $row->marketingPerson->name }}</strong><br>
                <small class="text-muted">{{ $row->marketing_person_code ?? '' }}</small>
            @else
                {{ $row->person_name ?? 'Personal' }}
            @endif
        </td>
        <td class="text-end">{{ number_format((float) $row->amount, 2) }}</td>
        <td class="text-end">{{ number_format((float) $row->approved_amount, 2) }}</td>
        <td>{{ optional($row->from_date)->format('d M Y') }} - {{ optional($row->to_date)->format('d M Y') }}</td>
        <td>{{ optional($row->created_at)->format('d M Y H:i') }}</td>
        <td>{{ $row->approver?->name ?? ($row->approved_by ?? '-') }}</td>
        <td>
            @if($row->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($row->file_path))
                <button type="button" class="btn btn-sm btn-outline-secondary js-preview-receipt" data-url="{{ asset('storage/'.$row->file_path) }}">Preview</button>
            @elseif($row->file_path)
                <span class="text-muted">Missing</span>
            @else
                -
            @endif
        </td>
        <td><span class="badge bg-success">Approved</span></td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center">No approved expenses found for this sub-section.</td>
    </tr>
@endforelse
