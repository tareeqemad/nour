@extends('layouts.admin')

@section('title', 'تعديل رسالة ترحيبية')

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-pencil me-2"></i>
                            تعديل رسالة ترحيبية
                        </h5>
                        <div class="general-subtitle">
                            {{ $welcomeMessage->title }}
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.welcome-messages.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>

                <div class="card-body pb-4">
                    <form action="{{ route('admin.welcome-messages.update', $welcomeMessage) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">العنوان <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title', $welcomeMessage->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">الموضوع <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" 
                                       value="{{ old('subject', $welcomeMessage->subject) }}" required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">المحتوى <span class="text-danger">*</span></label>
                                <textarea name="body" class="form-control @error('body') is-invalid @enderror" 
                                          rows="8" required>{{ old('body', $welcomeMessage->body) }}</textarea>
                                @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    يمكنك استخدام <code>{name}</code> كمتغير سيتم استبداله باسم المستخدم
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الترتيب <span class="text-danger">*</span></label>
                                <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" 
                                       value="{{ old('order', $welcomeMessage->order) }}" min="0" required>
                                @error('order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">ترتيب الرسالة عند الإرسال</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الحالة</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                           id="isActive" {{ old('is_active', $welcomeMessage->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isActive">
                                        نشط
                                    </label>
                                </div>
                                <div class="form-text">إذا كان غير نشط، لن يتم إرسال هذه الرسالة</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.welcome-messages.index') }}" class="btn btn-outline-secondary">
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



