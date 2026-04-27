@extends('layouts.app')

@section('title', 'Projects')

@php
    $pageTitle = 'Projects';
    $pageHeading = 'Projects';
@endphp

@section('content')
    <x-empty-state
        icon="📁"
        title="No projects yet"
        message="Start organizing your work by creating your first project. You can add tasks, budgets, and team members to each project."
        actionText="Create project"
        actionHref="#"
    />
@endsection
