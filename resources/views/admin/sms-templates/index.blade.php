@extends('layouts.admin')

@section('title', 'قوالب رسائل الجوال')

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="قوالب رسائل الجوال" icon="bi-chat-dots" />

                <div class="card-body">
                    @if($templates->isEmpty())
                        <div class="text-center py-5" style="color: var(--color-text-muted, #5B6780);">
                            <i class="bi bi-chat-dots" style="font-size: 2.5rem; opacity: .4;"></i>
                            <p class="mt-2 mb-0">لا توجد قوالب SMS</p>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($templates as $template)
                                <div class="col-md-6">
                                    <div style="border: 1px solid var(--color-border, #E5E7EB); border-radius: 10px; height: 100%; display: flex; flex-direction: column; {{ !$template->is_active ? 'opacity: .6;' : '' }}">
                                        {{-- Header --}}
                                        <div style="background: #FAFCFF; border-bottom: 1px solid var(--color-border-soft, #EDF1F5); padding: 0.75rem 1rem; border-radius: 10px 10px 0 0; display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-weight: 700; font-size: 0.88rem; color: var(--color-text-main, #1F2937);">
                                                {{ $template->name }}
                                            </span>
                                            @if($template->is_active)
                                                <span class="badge-status-active">نشط</span>
                                            @else
                                                <span class="badge-neutral">غير نشط</span>
                                            @endif
                                        </div>
                                        {{-- Body --}}
                                        <div style="padding: 1rem; flex: 1;">
                                            <div class="d-flex flex-wrap gap-3 mb-3" style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780);">
                                                <span>
                                                    <i class="bi bi-key me-1"></i>
                                                    <code style="background: var(--badge-primary-bg, #EEF2FF); color: var(--badge-primary-text, #24308F); padding: 0.1rem 0.35rem; border-radius: 4px; font-size: 0.75rem;">{{ $template->key }}</code>
                                                </span>
                                                <span>
                                                    <i class="bi bi-rulers me-1"></i>
                                                    {{ $template->max_length }} حرف
                                                </span>
                                            </div>

                                            <div style="background: var(--color-bg-card-header, #F8FBFD); border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 8px; padding: 0.75rem; font-family: monospace; font-size: 0.8rem; white-space: pre-wrap; max-height: 120px; overflow-y: auto; color: var(--color-text-secondary, #3B4863); line-height: 1.6; margin-bottom: 0.75rem;">{{ Str::limit($template->template, 200) }}</div>

                                            @php $tvars = \App\Models\SmsTemplate::placeholdersFor($template->key); @endphp
                                            @if(count($tvars))
                                            <div style="font-size: 0.75rem; color: var(--color-text-light, #98A2B3);">
                                                <i class="bi bi-info-circle me-1"></i>
                                                المتغيرات:
                                                @foreach($tvars as $ph => $ex)
                                                    <code style="background: var(--badge-neutral-bg, #F3F4F6); color: var(--badge-neutral-text, #3B4863); padding: 0.1rem 0.3rem; border-radius: 3px; font-size: 0.7rem;">{{ '{'.$ph.'}' }}</code>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                        {{-- Footer --}}
                                        <div style="padding: 0.65rem 1rem; border-top: 1px solid var(--color-border-soft, #EDF1F5);">
                                            <a href="{{ route('admin.sms-templates.edit', $template) }}" class="btn btn-sm btn-primary w-100">
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
