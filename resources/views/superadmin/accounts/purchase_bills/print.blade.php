@extends('superadmin.layouts.app')

@section('title','Purchase Bills - Print')

@section('content')
<div class="card mt-3 p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Purchase Bills</h4>
        <div>
            <button class="btn btn-sm btn-primary" onclick="window.print()">Print</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th class="text-end">Amount</th>
                    <th>Bill Date</th>
                    <th>Description</th>
                    <th>Uploaded At</th>
                    <th>File</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseBills as $i => $b)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $b['user_name'] ?? '-' }}</td>
                        <td class="text-end">{{ isset($b['amount']) ? number_format((float)$b['amount'],2) : '-' }}</td>
                        <td>{{ $b['bill_date'] ?? '-' }}</td>
                        <td>{{ $b['description'] ?? '-' }}</td>
                        <td>{{ $b['uploaded_at'] ?? '-' }}</td>
                        <td><a href="{{ $b['url'] }}" target="_blank">{{ $b['name'] ?? 'file' }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
