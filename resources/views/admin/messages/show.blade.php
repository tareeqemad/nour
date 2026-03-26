@extends('layouts.admin')

@section('title', 'عرض الرسالة')

@php
    $breadcrumbTitle = 'عرض الرسالة';
    $breadcrumbParent = 'الرسائل';
    $breadcrumbParentUrl = route('admin.messages.index');

    $typeLabels = [
        'operator_to_operator' => ['label' => 'مشغل لمشغل', 'badge' => 'bg-primary'],
        'operator_to_staff' => ['label' => 'مشغل لموظفين', 'badge' => 'bg-success'],
        'admin_to_operator' => ['label' => 'أدمن لمشغل', 'badge' => 'bg-warning'],
        'admin_to_all' => ['label' => 'أدمن للجميع', 'badge' => 'bg-danger'],
    ];
    $typeInfo = $typeLabels[$message->type] ?? ['label' => $message->type, 'badge' => 'bg-secondary'];

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

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header-form title="عرض الرسالة" icon="bi-envelope-open" :backRoute="route('admin.messages.index')">
                    @can('delete', $message)
                        <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" class="d-inline" id="archiveForm">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="if(confirm('هل أنت متأكد من أرشفة هذه الرسالة؟')) { document.getElementById('archiveForm').submit(); }">
                                <i class="bi bi-archive me-1"></i>أرشفة
                            </button>
                        </form>
                    @endcan
                </x-admin.card-header-form>

                <div class="card-body p-4">
                    {{-- معلومات الرسالة --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 col-lg-3">
                            <x-admin.field label="المرسل" :value="false" />
                            <div>
                                <span class="badge bg-primary">{{ $message->sender_display_name }}</span>
                                @if(!$message->isSystemMessage() && $message->sender)
                                    <small class="text-muted ms-1">({{ $message->sender->role_name }})</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <x-admin.field label="النوع" :value="false" />
                            <span class="badge {{ $typeInfo['badge'] }}">{{ $typeInfo['label'] }}</span>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <x-admin.field label="تاريخ الإرسال" :value="$message->created_at->format('Y-m-d H:i')" />
                        </div>
                        @if($isReceiver || $isBroadcastReceiver)
                            <div class="col-md-6 col-lg-3">
                                <x-admin.field label="الحالة" :value="false" />
                                @if($message->is_read)
                                    <span class="text-success fw-semibold" style="font-size: 0.92rem;"><i class="bi bi-check-circle me-1"></i>مقروء</span>
                                    @if($message->read_at)
                                        <small class="text-muted d-block">{{ $message->read_at->format('Y-m-d H:i') }}</small>
                                    @endif
                                @else
                                    <span class="text-warning fw-semibold" style="font-size: 0.92rem;"><i class="bi bi-clock me-1"></i>غير مقروء</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- الموضوع --}}
                    <x-admin.section title="الموضوع" icon="bi-tag" :boxed="true">
                        <h5 class="mb-0 fw-bold" style="color: var(--color-text-main, #1F2937);">{{ $message->subject }}</h5>
                    </x-admin.section>

                    {{-- المحتوى --}}
                    <x-admin.section title="محتوى الرسالة" icon="bi-file-text" :boxed="true">
                        <div class="text-break" style="white-space: pre-wrap; line-height: 1.7; font-size: 0.95rem; color: var(--color-text-main, #1F2937);">{{ $message->body }}</div>
                    </x-admin.section>

                    {{-- الصورة المرفقة --}}
                    @if($message->hasAttachment())
                        <x-admin.section title="الصورة المرفقة" icon="bi-image" :boxed="true">
                            <div class="text-center mb-3">
                                <a href="{{ $message->attachment_url }}" target="_blank">
                                    <img src="{{ $message->attachment_url }}" alt="الصورة المرفقة" class="img-fluid rounded" style="max-height: 400px;">
                                </a>
                            </div>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ $message->attachment_url }}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>فتح
                                </a>
                                <a href="{{ $message->attachment_url }}" download class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i>تحميل
                                </a>
                            </div>
                        </x-admin.section>
                    @endif
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    if (window.MessagesPanel) window.MessagesPanel.refresh();
})();
</script>
@endpush
