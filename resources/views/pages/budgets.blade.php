@extends('layouts.app')

@section('title', 'Budgets')

@php
    $pageTitle = 'Budgets';
    $pageHeading = 'Budget Planning';
@endphp

@section('content')
    <x-empty-state
        icon="💰"
        title="No budgets yet"
        message="Track expenses, allocate resources, and monitor spending across all your projects. Create your first budget plan to get started."
        actionText="Create budget"
        actionHref="#"
    />
@endsection
