@extends('layouts.app')

@section('title', 'Tasks')

@php
    $pageTitle = 'Tasks';
    $pageHeading = 'Task Management';
@endphp

@section('content')
    <x-empty-state
        icon="✅"
        title="No tasks yet"
        message="Keep track of your work by creating tasks. Assign them to team members, set deadlines, and monitor progress."
        actionText="Add task"
        actionHref="#"
    />
@endsection
