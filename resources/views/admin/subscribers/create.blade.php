@extends('layouts.admin')

@section('title', 'إضافة مشترك جديد')

@php
    $breadcrumbTitle = 'إضافة مشترك جديد';
    $breadcrumbParent = 'إدارة بيانات المشتركين';
    $breadcrumbParentUrl = route('admin.subscribers.index');
@endphp

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header-form title="إضافة مشترك جديد" icon="bi-person-plus" :backRoute="route('admin.subscribers.index')" />

                    <div class="card-body">
                        <form action="{{ route('admin.subscribers.store') }}" method="POST" id="subscriberForm">
                            @csrf
                            @include('admin.subscribers.partials.form')
                        </form>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection
