@extends('layouts.admin')

@section('title', 'تعديل رسالة ترحيبية')

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header-form :title="'تعديل: ' . $welcomeMessage->title" icon="bi-pencil" :backRoute="route('admin.welcome-messages.index')" />

                <div class="card-body">
                    <form action="{{ route('admin.welcome-messages.update', $welcomeMessage) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">العنوان <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title', $welcomeMessage->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">الموضوع <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                                       value="{{ old('subject', $welcomeMessage->subject) }}" required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">المحتوى <span class="text-danger">*</span></label>
                                <textarea name="body" class="form-control @error('body') is-invalid @enderror"
                                          rows="8" required>{{ old('body', $welcomeMessage->body) }}</textarea>
                                @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    استخدم <code style="background: var(--badge-primary-bg, #EEF2FF); color: var(--badge-primary-text, #24308F); padding: 0.1rem 0.35rem; border-radius: 4px;">{name}</code> كمتغير لاسم المستخدم
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الترتيب <span class="text-danger">*</span></label>
                                <input type="number" name="order" class="form-control @error('order') is-invalid @enderror"
                                       value="{{ old('order', $welcomeMessage->order) }}" min="0" required>
                                @error('order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الحالة</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                           id="isActive" {{ old('is_active', $welcomeMessage->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isActive">نشط</label>
                                </div>
                                <small class="form-text text-muted">إذا كان غير نشط لن يتم إرسال الرسالة</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid var(--color-border-soft, #EDF1F5);">
                            <a href="{{ route('admin.welcome-messages.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i>
                                حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection
