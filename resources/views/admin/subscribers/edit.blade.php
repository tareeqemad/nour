@extends('layouts.admin')

@section('title', 'تعديل بيانات المشترك')

@php
    $breadcrumbTitle = 'تعديل بيانات المشترك';
    $breadcrumbParent = 'إدارة بيانات المشتركين';
    $breadcrumbParentUrl = route('admin.subscribers.index');
@endphp

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header-form :title="'تعديل: ' . $subscriber->subscriber_name" icon="bi-pencil" :backRoute="route('admin.subscribers.index')" />

                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ session('error') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>يرجى تصحيح الأخطاء التالية:</strong>
                                <ul class="mb-0 mt-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.subscribers.update', $subscriber) }}" method="POST" id="subscriberForm">
                            @csrf
                            @method('PUT')
                            @include('admin.subscribers.partials.form', ['subscriber' => $subscriber])
                        </form>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection
