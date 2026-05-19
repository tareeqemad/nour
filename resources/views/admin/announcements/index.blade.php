@extends('layouts.admin')

@section('title', 'الإعلانات')

@php
    $breadcrumbTitle = 'الإعلانات';
    $breadcrumbParent = 'خدمات';
    $today = now()->startOfDay();
@endphp

@section('content')
    <div class="general-page" id="announcementsPage">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header title="الإعلانات" icon="bi-megaphone">
                        <x-slot:actions>
                            @can('create', App\Models\Announcement::class)
                                <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    إضافة إعلان
                                </a>
                            @endcan
                        </x-slot:actions>
                    </x-admin.card-header>

                    <div class="card-body pb-4">
                        {{-- فلاتر --}}
                        <form method="GET" action="{{ route('admin.announcements.index') }}" class="row g-3 align-items-end mb-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">بحث</label>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="عنوان الإعلان أو نصه">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label fw-semibold">الحالة</label>
                                <select name="status" class="form-select">
                                    @php $st = $status ?? 'all'; @endphp
                                    <option value="all"      {{ $st === 'all'      ? 'selected' : '' }}>الكل</option>
                                    <option value="active"   {{ $st === 'active'   ? 'selected' : '' }}>نشط</option>
                                    <option value="upcoming" {{ $st === 'upcoming' ? 'selected' : '' }}>قادم</option>
                                    <option value="expired"  {{ $st === 'expired'  ? 'selected' : '' }}>منتهي</option>
                                    <option value="hidden"   {{ $st === 'hidden'   ? 'selected' : '' }}>مخفي</option>
                                    <option value="featured" {{ $st === 'featured' ? 'selected' : '' }}>مميز</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="bi bi-search me-1"></i> بحث
                                </button>
                                <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary" title="إعادة تعيين">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </form>

                        {{-- الجدول --}}
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>العنوان</th>
                                        <th class="text-center">التاريخ</th>
                                        <th class="text-center">الفترة</th>
                                        <th class="text-center">الحالة</th>
                                        <th class="text-center">المنشئ</th>
                                        <th class="text-center" style="width:160px;">إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($announcements as $a)
                                        @php
                                            $isActive   = $a->is_visible && $a->start_date <= $today && $a->end_date >= $today;
                                            $isExpired  = $a->end_date < $today;
                                            $isUpcoming = $a->start_date > $today;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-start gap-2">
                                                    @if($a->is_featured)
                                                        <span class="badge bg-warning text-dark" title="مميز"><i class="bi bi-star-fill"></i></span>
                                                    @endif
                                                    <div>
                                                        <a href="{{ route('admin.announcements.show', $a) }}" class="fw-semibold text-decoration-none">{{ $a->title }}</a>
                                                        <div class="text-muted small mt-1">{{ \Illuminate\Support\Str::limit(strip_tags($a->description), 80) }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center text-nowrap">{{ $a->announcement_date?->format('Y-m-d') }}</td>
                                            <td class="text-center text-nowrap">
                                                <small class="text-muted">من</small> {{ $a->start_date?->format('Y-m-d') }}<br>
                                                <small class="text-muted">إلى</small> {{ $a->end_date?->format('Y-m-d') }}
                                            </td>
                                            <td class="text-center">
                                                @if(!$a->is_visible)
                                                    <span class="badge bg-secondary">مخفي</span>
                                                @elseif($isExpired)
                                                    <span class="badge bg-danger">منتهي</span>
                                                @elseif($isUpcoming)
                                                    <span class="badge bg-info">قادم</span>
                                                @elseif($isActive)
                                                    <span class="badge bg-success">نشط</span>
                                                @endif
                                            </td>
                                            <td class="text-center small text-muted">
                                                {{ $a->creator?->name ?? '—' }}<br>
                                                <small>{{ $a->created_at?->format('Y-m-d') }}</small>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    @can('update', $a)
                                                        <form action="{{ route('admin.announcements.toggle-featured', $a) }}" method="POST" class="d-inline">
                                                            @csrf @method('PATCH')
                                                            <button type="submit" class="btn btn-sm {{ $a->is_featured ? 'btn-warning' : 'btn-outline-warning' }}"
                                                                    title="{{ $a->is_featured ? 'إلغاء التمييز' : 'تمييز' }}">
                                                                <i class="bi bi-star{{ $a->is_featured ? '-fill' : '' }}"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.announcements.toggle-visibility', $a) }}" method="POST" class="d-inline">
                                                            @csrf @method('PATCH')
                                                            <button type="submit" class="btn btn-sm {{ $a->is_visible ? 'btn-success' : 'btn-outline-secondary' }}"
                                                                    title="{{ $a->is_visible ? 'إخفاء' : 'إظهار' }}">
                                                                <i class="bi bi-eye{{ $a->is_visible ? '-fill' : '-slash' }}"></i>
                                                            </button>
                                                        </form>
                                                        <a href="{{ route('admin.announcements.edit', $a) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete', $a)
                                                        <form action="{{ route('admin.announcements.destroy', $a) }}" method="POST" class="d-inline"
                                                              onsubmit="return confirm('تأكيد حذف الإعلان؟');">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="bi bi-megaphone" style="font-size:2.5rem;opacity:.4;"></i>
                                                <div class="mt-2">لا توجد إعلانات</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $announcements->links() }}
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection
