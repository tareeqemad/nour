@extends('layouts.admin')

@section('title', 'قواعد الحد الأدنى للفاتورة')
@php $breadcrumbTitle = 'قواعد الحد الأدنى'; @endphp

@section('content')
<div class="general-page">
    <div class="row g-3 justify-content-center">
        <div class="col-lg-8">

            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-table me-2"></i>قواعد الحد الأدنى للفاتورة
                        </h5>
                        <p class="text-muted small mb-0">
                            الحد الأدنى يُحسب تلقائياً بناءً على أمبير الاشتراك ونوع الفاز.
                            يُطبَّق على كل فاتورة جديدة ومسودة.
                        </p>
                    </div>
                </div>

                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- جدول القواعد --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle text-center">
                            <thead class="table-primary">
                                <tr>
                                    <th width="20%">الأمبير</th>
                                    <th width="30%">نوع الفاز</th>
                                    <th width="30%">الحد الأدنى (₪)</th>
                                    <th width="20%">إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rules as $rule)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary fs-6">{{ $rule->ampere }} A</span>
                                    </td>
                                    <td>
                                        @if($rule->phase_type === 2)
                                            <span class="badge bg-warning text-dark">ثلاثي (3 فاز)</span>
                                        @else
                                            <span class="badge bg-info text-dark">أحادي (1 فاز)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary" id="display-{{ $rule->id }}">
                                            {{ number_format($rule->minimum_charge, 2) }} ₪
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm"
                                                onclick="openEdit({{ $rule->id }}, {{ $rule->minimum_charge }}, '{{ $rule->ampere }} أمبير / {{ $rule->phase_name }}')">
                                            <i class="bi bi-pencil me-1"></i>تعديل
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        عند تعديل أي قيمة، ستُطبَّق على <strong>الفواتير الجديدة والمسودات</strong> فقط.
                        الفواتير المصدَرة لا تتأثر.
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

{{-- Modal التعديل --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>تعديل الحد الأدنى
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p class="text-muted small mb-3" id="editLabel"></p>
                    <label class="form-label fw-semibold">الحد الأدنى (₪)</label>
                    <div class="input-group">
                        <input type="number" name="minimum_charge" id="editValue"
                               class="form-control text-center fw-bold"
                               step="0.01" min="0" required>
                        <span class="input-group-text">₪</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save me-1"></i>حفظ
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
