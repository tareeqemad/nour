@extends('layouts.admin')

@section('title', 'إضافة إعلان')

@php
    $breadcrumbTitle = 'إضافة إعلان';
    $breadcrumbParent = 'الإعلانات';
    $breadcrumbParentUrl = route('admin.announcements.index');
@endphp

@section('content')
    <div class="general-page" id="announcementsCreatePage">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header-form title="إضافة إعلان" icon="bi-megaphone" :backRoute="route('admin.announcements.index')" />
                    <div class="card-body p-4">
                    <form action="{{ route('admin.announcements.store') }}" method="POST" data-prevent-double-submit>
                        @csrf
                        {{-- مفتاح Idempotency لمنع تكرار الإنشاء عند الـ double-click أو network retry --}}
                        <input type="hidden" name="_idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">

                        {{-- Section: محتوى الإعلان --}}
                        <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">محتوى الإعلان</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold">عنوان الإعلان <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title') }}" maxlength="255" required placeholder="مثلاً: انقطاع التيار في منطقة...">
                                @error('title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">نص الإعلان <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                          rows="5" required placeholder="تفاصيل الإعلان...">{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Section: التواريخ --}}
                        <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">التواريخ</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">تاريخ الإعلان <span class="text-danger">*</span></label>
                                <input type="date" name="announcement_date" class="form-control @error('announcement_date') is-invalid @enderror"
                                       value="{{ old('announcement_date', now()->toDateString()) }}" required>
                                @error('announcement_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">من تاريخ <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date', now()->toDateString()) }}" required>
                                @error('start_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">إلى تاريخ <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                       value="{{ old('end_date', now()->addDays(7)->toDateString()) }}" required>
                                @error('end_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Section: حالة الإعلان --}}
                        <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">حالة الإعلان</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-check form-switch d-flex align-items-center gap-2 p-3 border rounded" style="background:#FAFCFF;">
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured"
                                           value="1" {{ old('is_featured') ? 'checked' : '' }} style="width:2.5em;height:1.3em;">
                                    <label class="form-check-label fw-semibold" for="is_featured">
                                        <i class="bi bi-star-fill text-warning me-1"></i> إعلان مميز
                                        <div class="text-muted small mt-1" style="font-weight:normal;">يظهر بتصميم بارز في الواجهة الرئيسية</div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch d-flex align-items-center gap-2 p-3 border rounded" style="background:#FAFCFF;">
                                    <input class="form-check-input" type="checkbox" name="is_visible" id="is_visible"
                                           value="1" {{ old('is_visible', '1') ? 'checked' : '' }} style="width:2.5em;height:1.3em;">
                                    <label class="form-check-label fw-semibold" for="is_visible">
                                        <i class="bi bi-eye-fill text-success me-1"></i> ظاهر للجمهور
                                        <div class="text-muted small mt-1" style="font-weight:normal;">عند الإلغاء: الإعلان مخفي بدون حذف</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top">
                            <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right me-1"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i> حفظ ونشر
                            </button>
                        </div>
                    </form>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection
