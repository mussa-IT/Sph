@extends('layouts.app')

@section('title', 'Resources')

@php
    $pageTitle = 'Resources';
    $pageHeading = 'Resources';
@endphp

@section('content')
    <x-empty-state
        icon="📚"
        title="No resources yet"
        message="Store documents, links, and reference materials for your team. Add your first resource to build a shared knowledge base."
        actionText="Add resource"
        actionHref="#"
    />
@endsection
