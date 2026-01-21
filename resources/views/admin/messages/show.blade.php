@extends('layouts.admin')

@section('title', 'عرض الرسالة')

@php
    $breadcrumbTitle = 'عرض الرسالة';
    $breadcrumbParent = 'الرسائل';
    $breadcrumbParentUrl = route('admin.messages.index');
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/messages-show.css') }}">
@endpush

@section('content')
<div class="general-page messages-show-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-envelope-open me-2"></i>
                            عرض الرسالة
                        </h5>
                        <div class="general-subtitle">
                            تفاصيل الرسالة المرسلة
                        </div>
                    </div>
                    <div class="header-actions">
                        <a href="{{ route('admin.messages.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right"></i>
                            العودة
                        </a>
                        @can('delete', $message)
                            <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" class="d-inline" id="archiveForm">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-outline-warning" onclick="if(confirm('هل أنت متأكد من أرشفة هذه الرسالة؟')) { document.getElementById('archiveForm').submit(); }">
                                    <i class="bi bi-archive"></i>
                                    أرشفة
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    {{-- معلومات الرسالة --}}
                    <div class="message-section">
                        <h6 class="section-title">
                            <i class="bi bi-info-circle"></i>
                            معلومات الرسالة
                        </h6>

                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-person"></i>
                                    المرسل
                                </div>
                                <div class="info-value">
                                    <span class="badge bg-primary">{{ $message->sender_display_name }}</span>
                                    @if(!$message->isSystemMessage() && $message->sender)
                                        <small class="text-muted">({{ $message->sender->role_name }})</small>
                                    @endif
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-tag"></i>
                                    النوع
                                </div>
                                <div class="info-value">
                                    @php
                                        $typeLabels = [
                                            'operator_to_operator' => ['label' => 'مشغل لمشغل', 'badge' => 'bg-primary'],
                                            'operator_to_staff' => ['label' => 'مشغل لموظفين', 'badge' => 'bg-success'],
                                            'admin_to_operator' => ['label' => 'أدمن لمشغل', 'badge' => 'bg-warning'],
                                            'admin_to_all' => ['label' => 'أدمن للجميع', 'badge' => 'bg-danger'],
                                        ];
                                        $typeInfo = $typeLabels[$message->type] ?? ['label' => $message->type, 'badge' => 'bg-secondary'];
                                    @endphp
                                    <span class="badge {{ $typeInfo['badge'] }}">
                                        {{ $typeInfo['label'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-calendar3"></i>
                                    تاريخ الإرسال
                                </div>
                                <div class="info-value">
                                    <span>{{ $message->created_at->format('Y-m-d') }}</span>
                                    <small class="text-muted">({{ $message->created_at->format('H:i:s') }})</small>
                                </div>
                            </div>

                            @php
                                $user = auth()->user();
                                $isReceiver = $message->receiver_id === $user->id;
                                $isBroadcastReceiver = false;
                                if ($message->type === 'operator_to_staff' && $message->operator_id) {
                                    if ($user->isCompanyOwner()) {
                                        $isBroadcastReceiver = $user->ownedOperators()->where('id', $message->operator_id)->exists();
                                    } elseif ($user->hasOperatorLinkedCustomRole()) {
                                        $isBroadcastReceiver = $user->roleModel->operator_id === $message->operator_id;
                                    }
                                }
                            @endphp
                            @if($isReceiver || $isBroadcastReceiver)
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-eye"></i>
                                        الحالة
                                    </div>
                                    <div class="info-value">
                                        @if($message->is_read)
                                            <span class="read-status read">
                                                <i class="bi bi-check-circle"></i>
                                                مقروء
                                            </span>
                                            @if($message->read_at)
                                                <small class="text-muted">({{ $message->read_at->format('Y-m-d H:i') }})</small>
                                            @endif
                                        @else
                                            <span class="read-status unread">
                                                <i class="bi bi-clock"></i>
                                                غير مقروء
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if($message->is_read && $message->read_at && ($isReceiver || $isBroadcastReceiver))
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-clock-history"></i>
                                        تاريخ القراءة
                                    </div>
                                    <div class="info-value">
                                        <span>{{ $message->read_at->format('Y-m-d') }}</span>
                                        <small class="text-muted">({{ $message->read_at->format('H:i:s') }})</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr class="section-divider">

                    {{-- الموضوع --}}
                    <div class="message-section">
                        <h6 class="section-title">
                            <i class="bi bi-tag"></i>
                            الموضوع
                        </h6>
                        <div class="subject-box">
                            <h5 class="subject-text">{{ $message->subject }}</h5>
                        </div>
                    </div>

                    <hr class="section-divider">

                    {{-- المحتوى --}}
                    <div class="message-section">
                        <h6 class="section-title">
                            <i class="bi bi-file-text"></i>
                            محتوى الرسالة
                        </h6>
                        <div class="content-box">
                            <p class="content-text">{{ $message->body }}</p>
                        </div>
                    </div>

                    {{-- الصورة المرفقة --}}
                    @if($message->hasAttachment())
                        <hr class="section-divider">
                        <div class="message-section">
                            <h6 class="section-title">
                                <i class="bi bi-image"></i>
                                الصورة المرفقة
                            </h6>
                            <div class="attachment-section">
                                <div class="attachment-image-wrapper">
                                    <a href="{{ $message->attachment_url }}" target="_blank">
                                        <img src="{{ $message->attachment_url }}" alt="الصورة المرفقة" class="attachment-image">
                                    </a>
                                </div>
                                <div class="attachment-actions">
                                    <a href="{{ $message->attachment_url }}" target="_blank" class="btn btn-primary">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                        فتح في نافذة جديدة
                                    </a>
                                    <a href="{{ $message->attachment_url }}" download class="btn btn-outline-primary">
                                        <i class="bi bi-download"></i>
                                        تحميل الصورة
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';
    
    // تحديث عدد الرسائل غير المقروءة عند قراءة رسالة
    if (window.MessagesPanel) {
        window.MessagesPanel.refresh();
    }
})();
</script>
@endpush
