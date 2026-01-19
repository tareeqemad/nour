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
        .info-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .info-item {
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
            border-right: 3px solid #3b82f6;
        }
        .info-label {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .info-value {
            font-size: 1rem;
            color: #1e293b;
            font-weight: 600;
        }
        .description-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-right: 3px solid #3b82f6;
        }
        .description-label {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }
        .description-content {
            font-size: 1rem;
            color: #1e293b;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        .status-update-form {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        .badge-type-maintenance {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-type-safety_inspection {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-status-in_progress {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-status-cancelled {
            background: #fee2e2;
            color: #991b1b;
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
                            <i class="bi bi-clipboard-check me-2"></i>
                            تفاصيل المهمة
                        </h5>
                        <div class="general-subtitle">
                            <span class="badge badge-type-{{ $task->type }} me-2">
                                {{ $task->type_label }}
                            </span>
                            <span class="badge badge-status-{{ $task->status }}">
                                {{ $task->status_label }}
                            </span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة
                        </a>
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
                    </div>
                </div>

                <div class="card-body">
                    {{-- معلومات المهمة --}}
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">المكلف</div>
                            <div class="info-value">
                                {{ $task->assignedTo->name }}
                                <br>
                                <small class="text-muted">{{ $task->assignedTo->getRoleLabel() }}</small>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">المكلف من</div>
                            <div class="info-value">{{ $task->assignedBy->name }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">تاريخ الإنشاء</div>
                            <div class="info-value">{{ $task->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                        @if($task->due_date)
                        <div class="info-item">
                            <div class="info-label">تاريخ الاستحقاق</div>
                            <div class="info-value">
                                {{ $task->due_date->format('Y-m-d') }}
                                @if($task->due_date->isPast() && $task->status !== 'completed')
                                    <br>
                                    <small class="text-danger">متأخرة</small>
                                @endif
                            </div>
                        </div>
                        @endif
                        @if($task->completed_at)
                        <div class="info-item">
                            <div class="info-label">تاريخ الإنجاز</div>
                            <div class="info-value">{{ $task->completed_at->format('Y-m-d H:i') }}</div>
                        </div>
                        @endif
                    </div>

                    {{-- موقع المهمة --}}
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">المشغل</div>
                            <div class="info-value">{{ $task->operator->name }}</div>
                        </div>
                        @if($task->generationUnit)
                        <div class="info-item">
                            <div class="info-label">وحدة التوليد</div>
                            <div class="info-value">
                                {{ $task->generationUnit->name }}
                                @if($task->generationUnit->unit_code)
                                    <br>
                                    <small class="text-muted">{{ $task->generationUnit->unit_code }}</small>
                                @endif
                            </div>
                        </div>
                        @endif
                        @if($task->generator)
                        <div class="info-item">
                            <div class="info-label">المولد</div>
                            <div class="info-value">
                                @if($task->generator)
                                    {{ $task->generator->name }}
                                    @if($task->generator->trashed())
                                        <span class="badge bg-secondary ms-1" title="مولد محذوف">محذوف</span>
                                    @endif
                                    <br>
                                    <small class="text-muted">{{ $task->generator->generator_number }}</small>
                                @else
                                    <span class="text-muted">مولد محذوف</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- وصف المهمة --}}
                    @if($task->description)
                    <div class="description-box">
                        <div class="description-label">وصف المهمة</div>
                        <div class="description-content">{{ $task->description }}</div>
                    </div>
                    @endif

                    {{-- ملاحظات --}}
                    @if($task->notes)
                    <div class="description-box" style="border-right-color: #10b981;">
                        <div class="description-label">ملاحظات</div>
                        <div class="description-content">{{ $task->notes }}</div>
                    </div>
                    @endif

                    {{-- تحديث حالة المهمة (للمكلف فقط) --}}
                    @if($isAssigned && $task->status !== 'completed' && $task->status !== 'cancelled')
                    <div class="status-update-form">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-arrow-repeat me-2"></i>
                            تحديث حالة المهمة
                        </h6>
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
                                <div class="col-md-12">
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
