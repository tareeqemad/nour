@extends('layouts.admin')

@section('title', 'قوالب رسائل الجوال')

@push('styles')
<style>
.sms-template-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.sms-template-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.template-preview {
    background: var(--tblr-bg-surface-secondary);
    border: 1px solid var(--tblr-border-color);
    border-radius: 0.5rem;
    padding: 1rem;
    font-family: monospace;
    font-size: 0.875rem;
    white-space: pre-wrap;
    max-height: 200px;
    overflow-y: auto;
}
</style>
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-chat-dots me-2"></i>
                            قوالب رسائل الجوال
                        </h5>
                        <div class="general-subtitle">
                            إدارة قوالب رسائل SMS التي يتم إرسالها للمستخدمين
                        </div>
                    </div>
                </div>

                <div class="card-body pb-4">
                    <div class="row g-3">
                        @foreach($templates as $template)
                            <div class="col-md-6">
                                <div class="card sms-template-card h-100 {{ !$template->is_active ? 'border-secondary opacity-75' : '' }}">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">{{ $template->name }}</h6>
                                        <div>
                                            @if($template->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-secondary">غير نشط</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="bi bi-key me-1"></i>
                                                المفتاح: <code>{{ $template->key }}</code>
                                            </small>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="bi bi-rulers me-1"></i>
                                                الحد الأقصى: {{ $template->max_length }} حرف
                                            </small>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted d-block mb-1">معاينة القالب:</small>
                                            <div class="template-preview">
                                                {{ Str::limit($template->template, 200) }}
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="bi bi-info-circle me-1"></i>
                                                المتغيرات المتاحة: <code>{name}</code>, <code>{username}</code>, <code>{password}</code>, <code>{role}</code>, <code>{login_url}</code>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="{{ route('admin.sms-templates.edit', $template) }}" class="btn btn-sm btn-primary w-100">
                                            <i class="bi bi-pencil me-1"></i>
                                            تعديل
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($templates->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-3">لا توجد قوالب SMS</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



