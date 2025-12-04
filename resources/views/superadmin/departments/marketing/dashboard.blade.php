@extends('superadmin.layouts.app')

@php
    $pageTitle = 'Marketing Dashboard';
    $metricLookup = collect($payload['metrics'] ?? [])->pluck('value', 'label');
    $insightMessage = $payload['insights']['message'] ?? 'Keep campaigns aligned with approved budgets and booking targets.';
@endphp

@section('title', $pageTitle)

@section('content')
    {{-- Use the marketing person profile dashboard layout so each marketing person sees personal data --}}
    @php
        // `marketingPerson` is the current marketing user by default; controllers may pass a different model
        // Prefer the web guard (marketing user), fall back to payload or generic auth user
        $marketingPerson = $payload['marketingPerson'] ?? (Auth::guard('web')->check() ? Auth::guard('web')->user() : (Auth::check() ? Auth::user() : ($user ?? null)));
        $stats = $payload['stats'] ?? [];
    @endphp

    @include('superadmin.accounts.marketingPerson.profile', ['marketingPerson' => $marketingPerson, 'stats' => $stats])
@endsection
