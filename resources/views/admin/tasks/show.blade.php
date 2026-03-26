@extends('layouts.admin')

@section('title', 'تفاصيل المهمة')

@php
    $breadcrumbTitle = 'تفاصيل المهمة';
    $isSuperAdmin = auth()->user()->isSuperAdmin();
    $isAdmin = auth()->user()->isAdmin();
    $isEnergyAuthority = auth()->user()->isEnergyAuthority();
    $canManage = $isSuperAdmin || $isAdmin || $isEnergyAuthority;
    $isAssigned = auth()->user()->id === $task->assigned_to;
@endphp

@push('styles')
    <style>
        .badge-type-maintenance {
            background: rgba(217, 119, 6, 0.08);
            color: #92400e;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .badge-type-safety_inspection {
            background: rgba(2, 132, 199, 0.08);
            color: #1e40af;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .badge-status-pending {
            background: rgba(217, 119, 6, 0.08);
            color: #92400e;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .badge-status-in_progress {
            background: rgba(2, 132, 199, 0.08);
            color: #1e40af;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .badge-status-completed {
            background: rgba(22, 163, 74, 0.08);
            color: #065f46;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .badge-status-cancelled {
            background: rgba(220, 38, 38, 0.08);
            color: #991b1b;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
    </style>
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header-form title="تفاصيل المهمة" icon="bi-clipboard-check" :backRoute="route('admin.tasks.index')" backLabel="العودة">
                    @if($canManage)
                        <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST"
                              class="d-inline task-delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger"
                                    onclick="return confirm('هل أنت متأكد من حذف هذه المهمة؟')">
                                <i class="bi bi-trash me-1"></i>
                                حذف
                            </button>
                        </form>
                    @endif
                </x-admin.card-header-form>

                <div class="card-body p-4">
                    {{-- معلومات المهمة --}}
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6 col-lg-4">
                            <x-admin.section title="معلومات التكليف" icon="bi-person-check" :boxed="true">
                                <x-admin.field label="المكلف" :value="$task->assignedTo->name . ' — ' . $task->assignedTo->getRoleLabel()" />
                                <x-admin.field label="المكلف من" :value="$task->assignedBy->name" />
                                <x-admin.field label="تاريخ الإنشاء" :value="$task->created_at->format('Y-m-d H:i')" />
                                @if($task->due_date)
                                    <div class="mb-2">
                                        <div style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780); font-weight: 600; margin-bottom: 0.15rem;">تاريخ الاستحقاق</div>
                                        <div style="font-size: 0.92rem; color: var(--color-text-main, #1F2937); font-weight: 500;">
                                            {{ $task->due_date->format('Y-m-d') }}
                                            @if($task->due_date->isPast() && $task->status !== 'completed')
                                                <span class="badge bg-danger ms-1">متأخرة</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @if($task->completed_at)
                                    <x-admin.field label="تاريخ الإنجاز" :value="$task->completed_at->format('Y-m-d H:i')" />
                                @endif
                            </x-admin.section>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <x-admin.section title="موقع المهمة" icon="bi-geo-alt" :boxed="true">
                                <x-admin.field label="المشغل" :value="$task->operator->name" />
                                @if($task->generationUnit)
                                    <x-admin.field label="وحدة التوليد" :value="$task->generationUnit->name . ($task->generationUnit->unit_code ? ' (' . $task->generationUnit->unit_code . ')' : '')" />
                                @endif
                                @if($task->generator)
                                    <div class="mb-2">
                                        <div style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780); font-weight: 600; margin-bottom: 0.15rem;">المولد</div>
                                        <div style="font-size: 0.92rem; color: var(--color-text-main, #1F2937); font-weight: 500;">
                                            {{ $task->generator->name }}
                                            @if($task->generator->trashed())
                                                <span class="badge bg-secondary ms-1">محذوف</span>
                                            @endif
                                            @if($task->generator->generator_number)
                                                <br><small class="text-muted">{{ $task->generator->generator_number }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </x-admin.section>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <x-admin.section title="تفاصيل" icon="bi-info-circle" :boxed="true">
                                <div class="mb-2">
                                    <div style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780); font-weight: 600; margin-bottom: 0.15rem;">نوع المهمة</div>
                                    <span class="badge badge-type-{{ $task->type }}">{{ $task->type === 'maintenance' ? 'صيانة' : 'فحص سلامة' }}</span>
                                </div>
                                <div class="mb-2">
                                    <div style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780); font-weight: 600; margin-bottom: 0.15rem;">الحالة</div>
                                    <span class="badge badge-status-{{ $task->status }}">{{ ['pending' => 'قيد الانتظار', 'in_progress' => 'قيد التنفيذ', 'completed' => 'مكتملة', 'cancelled' => 'ملغاة'][$task->status] ?? $task->status }}</span>
                                </div>
                            </x-admin.section>
                        </div>
                    </div>

                    {{-- وصف المهمة --}}
                    @if($task->description)
                        <x-admin.section title="وصف المهمة" icon="bi-journal-text" :boxed="true">
                            <div class="text-break" style="font-size: 0.92rem; color: var(--color-text-main, #1F2937); line-height: 1.6; white-space: pre-wrap;">{{ $task->description }}</div>
                        </x-admin.section>
                    @endif

                    {{-- ملاحظات --}}
                    @if($task->notes)
                        <x-admin.section title="ملاحظات" icon="bi-sticky" :boxed="true">
                            <div class="text-break" style="font-size: 0.92rem; color: var(--color-text-main, #1F2937); line-height: 1.6; white-space: pre-wrap;">{{ $task->notes }}</div>
                        </x-admin.section>
                    @endif

                    {{-- تحديث حالة المهمة (للمكلف فقط) --}}
                    @if($isAssigned && $task->status !== 'completed' && $task->status !== 'cancelled')
                        <div class="mt-4">
                            <x-admin.section title="تحديث حالة المهمة" icon="bi-arrow-repeat" :boxed="true">
                                <form action="{{ route('admin.tasks.update', $task) }}" method="POST" id="updateStatusForm">
                                    @csrf
                                    @method('PUT')
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">الحالة الجديدة</label>
                                            <select name="status" class="form-select" required>
                                                <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                                                <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>مكتملة</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">ملاحظات (اختياري)</label>
                                            <textarea name="notes" rows="2" class="form-control"
                                                      placeholder="أضف ملاحظات حول التحديث..."></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary" id="updateStatusBtn">
                                                <i class="bi bi-check-lg me-2"></i>
                                                <span class="btn-text">تحديث الحالة</span>
                                                <span class="btn-spinner d-none">
                                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                    جاري المعالجة...
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </x-admin.section>
                        </div>
                    @endif
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateStatusForm = document.getElementById('updateStatusForm');
    if (updateStatusForm) {
        const updateStatusBtn = document.getElementById('updateStatusBtn');
        const btnText = updateStatusBtn.querySelector('.btn-text');
        const btnSpinner = updateStatusBtn.querySelector('.btn-spinner');

        function enableUpdateButton() {
            updateStatusBtn.disabled = false;
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
        }

        function disableUpdateButton() {
            updateStatusBtn.disabled = true;
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
        }

        updateStatusForm.addEventListener('submit', function(e) {
            // تعطيل الزر وإظهار spinner
            disableUpdateButton();

            // إعادة تفعيل الزر بعد 30 ثانية كحد أقصى (في حالة حدوث خطأ في الشبكة)
            setTimeout(function() {
                if (updateStatusBtn.disabled) {
                    enableUpdateButton();
                }
            }, 30000);
        });
    }
});
</script>
@endpush
