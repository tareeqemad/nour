@extends('layouts.admin')

@section('title', 'تعديل المشغل')

@php
    $breadcrumbTitle = 'تعديل المشغل';
    $breadcrumbParent = 'إدارة المشغلين';
    $breadcrumbParentUrl = route('admin.operators.index');
@endphp

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-building me-2"></i>
                        تعديل المشغل
                    </h5>
                    <a href="{{ route('admin.operators.index') }}" class="btn btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>
                        رجوع
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.operators.update', $operator) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    بيانات المشغل الأساسية
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">اسم المشغل <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $operator->name) }}">
                                
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">البريد الإلكتروني للمشغل</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $operator->email) }}">
                                
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الهاتف</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $operator->phone) }}">
                                
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">العنوان</label>
                                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $operator->address) }}">
                                
                            </div>

                            @if($operator->owner)
                                <div class="col-12 mt-4">
                                    <hr>
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-person-badge me-2"></i>
                                        تحديث بيانات تسجيل الدخول (اختياري)
                                    </h6>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        اترك الحقول فارغة إذا لم ترد تغييرها
                                    </div>
                                </div>

                                @if(auth()->user()->isSuperAdmin())
                                    <div class="col-md-6">
                                        <label class="form-label">اسم المستخدم</label>
                                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $operator->owner->username) }}">
                                        
                                    </div>
                                @else
                                    <div class="col-md-6">
                                        <label class="form-label">اسم المستخدم</label>
                                        <input type="text" class="form-control" value="{{ $operator->owner->username }}" readonly disabled>
                                        <small class="form-text">لا يمكن تغيير اسم المستخدم</small>
                                    </div>
                                @endif

                                <div class="col-md-6">
                                    <label class="form-label">البريد الإلكتروني للمستخدم</label>
                                    <input type="email" name="user_email" class="form-control @error('user_email') is-invalid @enderror" value="{{ old('user_email', $operator->owner->email) }}">
                                    
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">كلمة المرور</label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8">
                                    
                                    <small class="form-text">اتركه فارغاً إذا لم ترد تغيير كلمة المرور</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">تأكيد كلمة المرور</label>
                                    <input type="password" name="password_confirmation" class="form-control" minlength="8">
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.operators.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>
                                حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/operators.css') }}">
@endpush
