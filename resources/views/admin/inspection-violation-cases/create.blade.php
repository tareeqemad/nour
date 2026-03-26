@extends('layouts.admin')

@section('title', 'إضافة قضية تفتيش وتعدي')

@php
    $breadcrumbTitle = 'إضافة قضية';
    $breadcrumbParent = 'قضايا التفتيش والتعدي';
    $breadcrumbParentUrl = route('admin.inspection-violation-cases.index');
@endphp

@section('content')
    <div class="general-page" id="inspectionCasesCreatePage">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header-form title="إضافة قضية تفتيش وتعدي" icon="bi-shield-exclamation" :backRoute="route('admin.inspection-violation-cases.index')" />
                    <div class="card-body p-4">
                    <form action="{{ route('admin.inspection-violation-cases.store') }}" method="POST">
                        @csrf

                        {{-- Section: بيانات المشترك --}}
                        <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">بيانات المشترك</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">المحافظة</label>
                                <select name="governorate_id" class="form-select @error('governorate_id') is-invalid @enderror">
                                    <option value="">اختر المحافظة</option>
                                    @foreach($governorates ?? [] as $gov)
                                        <option value="{{ $gov->value }}" {{ old('governorate_id') == $gov->value ? 'selected' : '' }}>{{ $gov->label }}</option>
                                    @endforeach
                                </select>
                                @error('governorate_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">رقم الاشتراك</label>
                                <input type="text" name="subscription_number" class="form-control @error('subscription_number') is-invalid @enderror" value="{{ old('subscription_number') }}" placeholder="رقم الاشتراك">
                                @error('subscription_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">اسم المشترك <span class="text-danger">*</span></label>
                                <input type="text" name="subscriber_name" class="form-control @error('subscriber_name') is-invalid @enderror" value="{{ old('subscriber_name') }}" required>
                                @error('subscriber_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">اسم المنتفع</label>
                                <input type="text" name="beneficiary_name" class="form-control @error('beneficiary_name') is-invalid @enderror" value="{{ old('beneficiary_name') }}">
                                @error('beneficiary_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Section: تفاصيل القضية --}}
                        <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">تفاصيل القضية</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">تاريخ القضية <span class="text-danger">*</span></label>
                                <input type="date" name="case_date" class="form-control @error('case_date') is-invalid @enderror" value="{{ old('case_date') }}" required>
                                @error('case_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">حالة القضية <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>ادخال</option>
                                    <option value="2" {{ old('status') == 2 ? 'selected' : '' }}>محكومة</option>
                                    <option value="3" {{ old('status') == 3 ? 'selected' : '' }}>منتهية</option>
                                </select>
                                @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">بيان القضية/التعدي</label>
                                <textarea name="statement" class="form-control @error('statement') is-invalid @enderror" rows="4" placeholder="تفاصيل بيان القضية أو التعدي">{{ old('statement') }}</textarea>
                                @error('statement')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Section: الربط بالمشغل --}}
                        <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">الربط بالمشغل</h6>
                        <div class="row g-3 mb-4">
                            <x-admin.operator-cascade
                                :operators="$operators ?? collect()"
                                :showGenerator="false"
                                :showGenerationUnit="true"
                                :operatorRequired="false"
                                :generationUnitRequired="false"
                                :selectedOperatorId="old('operator_id')"
                                :selectedGenerationUnitId="old('generation_unit_id')"
                                colClass="col-md-6"
                            />
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top">
                            <a href="{{ route('admin.inspection-violation-cases.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right me-1"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>
                                حفظ
                            </button>
                        </div>
                    </form>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection

