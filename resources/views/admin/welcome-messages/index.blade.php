@extends('layouts.admin')

@section('title', 'الرسائل الترحيبية')

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="الرسائل الترحيبية" icon="bi-envelope-heart" />

                <div class="card-body">
                    @if($messages->isEmpty())
                        <div class="text-center py-5" style="color: var(--color-text-muted, #5B6780);">
                            <i class="bi bi-inbox" style="font-size: 2.5rem; opacity: .4;"></i>
                            <p class="mt-2 mb-0">لا توجد رسائل ترحيبية</p>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($messages as $message)
                                <div class="col-md-6 col-lg-4">
                                    <div style="border: 1px solid var(--color-border, #E5E7EB); border-radius: 10px; height: 100%; display: flex; flex-direction: column; {{ !$message->is_active ? 'opacity: .6;' : '' }}">
                                        {{-- Header --}}
                                        <div style="background: #FAFCFF; border-bottom: 1px solid var(--color-border-soft, #EDF1F5); padding: 0.75rem 1rem; border-radius: 10px 10px 0 0; display: flex; justify-content: space-between; align-items: center;">
                                            <div style="font-weight: 700; font-size: 0.88rem; color: var(--color-text-main, #1F2937); display: flex; align-items: center; gap: 0.4rem;">
                                                <span class="badge-role-system" style="font-size: 0.7rem; padding: 0.15rem 0.4rem;">{{ $message->order }}</span>
                                                {{ $message->title }}
                                            </div>
                                            @if($message->is_active)
                                                <span class="badge-status-active">نشط</span>
                                            @else
                                                <span class="badge-neutral">غير نشط</span>
                                            @endif
                                        </div>
                                        {{-- Body --}}
                                        <div style="padding: 1rem; flex: 1;">
                                            <div style="font-weight: 600; font-size: 0.85rem; color: var(--color-text-main, #1F2937); margin-bottom: 0.5rem;">{{ $message->subject }}</div>
                                            <p style="color: var(--color-text-muted, #5B6780); font-size: 0.82rem; line-height: 1.6; max-height: 80px; overflow: hidden; margin-bottom: 0.75rem;">
                                                {{ Str::limit($message->body, 150) }}
                                            </p>
                                            <div style="font-size: 0.78rem; color: var(--color-text-light, #98A2B3);">
                                                <i class="bi bi-key me-1"></i>
                                                <code style="background: var(--badge-primary-bg, #EEF2FF); color: var(--badge-primary-text, #24308F); padding: 0.15rem 0.4rem; border-radius: 4px; font-size: 0.75rem;">{{ $message->key }}</code>
                                            </div>
                                        </div>
                                        {{-- Footer --}}
                                        <div style="padding: 0.65rem 1rem; border-top: 1px solid var(--color-border-soft, #EDF1F5);">
                                            <a href="{{ route('admin.welcome-messages.edit', $message) }}" class="btn btn-sm btn-primary w-100">
                                                <i class="bi bi-pencil me-1"></i>
                                                تعديل
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection
