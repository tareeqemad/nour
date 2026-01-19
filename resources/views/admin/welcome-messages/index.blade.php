@extends('layouts.admin')

@section('title', 'الرسائل الترحيبية')

@push('styles')
<style>
.welcome-message-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.welcome-message-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
                            <i class="bi bi-envelope-heart me-2"></i>
                            الرسائل الترحيبية
                        </h5>
                        <div class="general-subtitle">
                            إدارة الرسائل الترحيبية التي يتم إرسالها للمستخدمين الجدد
                        </div>
                    </div>
                </div>

                <div class="card-body pb-4">
                    <div class="row g-3">
                        @foreach($messages as $message)
                            <div class="col-md-6 col-lg-4">
                                <div class="card welcome-message-card h-100 {{ !$message->is_active ? 'border-secondary opacity-75' : '' }}">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">
                                            <span class="badge bg-primary me-2">{{ $message->order }}</span>
                                            {{ $message->title }}
                                        </h6>
                                        <div>
                                            @if($message->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-secondary">غير نشط</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="fw-semibold mb-2">{{ $message->subject }}</h6>
                                        <p class="text-muted small mb-2" style="max-height: 100px; overflow: hidden; text-overflow: ellipsis;">
                                            {{ Str::limit($message->body, 150) }}
                                        </p>
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="bi bi-key me-1"></i>
                                                المفتاح: <code>{{ $message->key }}</code>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="{{ route('admin.welcome-messages.edit', $message) }}" class="btn btn-sm btn-primary w-100">
                                            <i class="bi bi-pencil me-1"></i>
                                            تعديل
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($messages->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-3">لا توجد رسائل ترحيبية</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



