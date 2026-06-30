@extends('madmoun::layouts.app')

@section('title', 'بوالص الضمان')

@section('content')
    <div class="row">
        {{-- نموذج إضافة بوليصة --}}
        <div class="col-xl-4">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">إضافة بوليصة ضمان</div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('madmoun.policies.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">رقم البوليصة</label>
                            <input type="text" name="policy_number" class="form-control @error('policy_number') is-invalid @enderror" value="{{ old('policy_number') }}" placeholder="مثال: PL-1001">
                            @error('policy_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">المستفيد</label>
                            <input type="text" name="beneficiary" class="form-control @error('beneficiary') is-invalid @enderror" value="{{ old('beneficiary') }}" placeholder="اسم المستفيد">
                            @error('beneficiary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">قيمة الضمان (₪)</label>
                            <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" placeholder="0.00">
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg me-1"></i> حفظ البوليصة
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- قائمة البوالص --}}
        <div class="col-xl-8">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">بوالص الضمان</div>
                    <span class="badge bg-primary-transparent">{{ $policies->count() }} بوليصة</span>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table text-nowrap table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>رقم البوليصة</th>
                                    <th>المستفيد</th>
                                    <th>المشغّل</th>
                                    <th>القيمة</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($policies as $policy)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-semibold">{{ $policy->policy_number }}</td>
                                        <td>{{ $policy->beneficiary }}</td>
                                        <td>{{ $policy->operator?->name ?? '—' }}</td>
                                        <td>{{ number_format($policy->amount, 2) }} ₪</td>
                                        <td><span class="badge bg-success-transparent">نشطة</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            لا توجد بوالص بعد — أضف أول بوليصة من النموذج.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
