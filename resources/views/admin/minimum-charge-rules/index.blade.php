@extends('layouts.admin')

@section('title', 'قواعد الحد الأدنى للفاتورة')
@php $breadcrumbTitle = 'قواعد الحد الأدنى'; @endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="قواعد الحد الأدنى للفاتورة" icon="bi-table" />

                <div class="card-body">
                    <div style="background: var(--color-info-bg, #F0F9FF); border: 1px solid var(--color-info-border, #BAE6FD); border-radius: 8px; padding: 0.65rem 1rem; margin-bottom: 1rem; font-size: 0.85rem; color: var(--color-info-text, #0369A1);">
                        <i class="bi bi-info-circle me-1"></i>
                        الحد الأدنى يُحسب تلقائياً بناءً على أمبير الاشتراك ونوع الفاز. يُطبَّق على الفواتير الجديدة والمسودات فقط.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 general-table text-center">
                            <thead>
                                <tr>
                                    <th>الأمبير</th>
                                    <th>نوع الفاز</th>
                                    <th>الحد الأدنى (₪)</th>
                                    <th>إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rules as $rule)
                                <tr>
                                    <td>
                                        <span class="badge-role-system" style="font-size: 0.85rem;">{{ $rule->ampere }} A</span>
                                    </td>
                                    <td>
                                        @if($rule->phase_type === 2)
                                            <span class="badge-warning">ثلاثي (3 فاز)</span>
                                        @else
                                            <span class="badge-info">أحادي (1 فاز)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold" style="color: var(--color-primary, #24308F);" id="display-{{ $rule->id }}">
                                            {{ number_format($rule->minimum_charge, 2) }} ₪
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="openEdit({{ $rule->id }}, {{ $rule->minimum_charge }}, '{{ $rule->ampere }} أمبير / {{ $rule->phase_name }}')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-admin.card>
        </div>
    </div>
</div>

{{-- Modal التعديل --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">
                    <i class="bi bi-pencil me-1"></i>تعديل الحد الأدنى
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p style="font-size: 0.85rem; color: var(--color-text-muted, #5B6780); margin-bottom: 0.75rem;" id="editLabel"></p>
                    <label class="form-label fw-semibold">الحد الأدنى (₪)</label>
                    <div class="input-group">
                        <input type="number" name="minimum_charge" id="editValue"
                               class="form-control text-center fw-bold"
                               step="0.01" min="0" required>
                        <span class="input-group-text">₪</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i>حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEdit(id, currentValue, label) {
    const baseUrl = '{{ url("admin/minimum-charge-rules") }}';
    document.getElementById('editForm').action = `${baseUrl}/${id}`;
    document.getElementById('editValue').value = currentValue;
    document.getElementById('editLabel').textContent = label;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endpush
