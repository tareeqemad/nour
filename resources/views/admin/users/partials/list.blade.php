@php $authUser = auth()->user(); @endphp

@if($authUser->isSuperAdmin())
    {{-- مشغلون --}}
    <div class="card ui-card">
        <div class="card-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-building text-primary"></i>
                <div class="fw-bold">المشغلون</div>
                <span class="badge bg-secondary">{{ $companyOwners->total() }}</span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table users-table mb-0">
                    <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>اسم المستخدم</th>
                        <th>البريد</th>
                        <th>المشغل</th>
                        <th>عدد الموظفين</th>
                        <th>تاريخ الإنشاء</th>
                        <th>إجراءات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($companyOwners as $owner)
                        @php $op = $owner->ownedOperators->first(); @endphp
                        <tr>
                            <td class="fw-semibold">{{ $owner->name }}</td>
                            <td>{{ $owner->username }}</td>
                            <td>{{ $owner->email }}</td>
                            <td>
                                @if($op)
                                    <span class="badge bg-primary">{{ $op->name }}</span>
                                @else
                                    <span class="text-muted">غير مربوط</span>
                                @endif
                            </td>
                            <td>
                                @if($op)
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.operators.employees', $op) }}">
                                        {{ $op->employees_count ?? 0 }}
                                    </a>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td><span class="text-muted">{{ optional($owner->created_at)->format('Y-m-d') }}</span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a class="btn btn-sm btn-outline-primary js-user-edit"
                                       href="{{ route('admin.users.edit', $owner) }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">لا يوجد مشغلين.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($companyOwners->hasPages())
            <div class="card-footer bg-white">
                {{ $companyOwners->links() }}
            </div>
        @endif
    </div>

    {{-- سلطة الطاقة/النظام --}}
    <div class="card ui-card">
        <div class="card-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-lightning-charge text-primary"></i>
                <div class="fw-bold">سلطة الطاقة / النظام</div>
                <span class="badge bg-secondary">{{ $otherUsers->total() }}</span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table users-table mb-0">
                    <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>اسم المستخدم</th>
                        <th>البريد</th>
                        <th>الدور</th>
                        <th>تاريخ الإنشاء</th>
                        <th>إجراءات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($otherUsers as $u)
                        <tr>
                            <td class="fw-semibold">{{ $u->name }}</td>
                            <td>{{ $u->username }}</td>
                            <td>{{ $u->email }}</td>
                            <td><span class="badge bg-dark">{{ $u->role_name ?? '-' }}</span></td>
                            <td><span class="text-muted">{{ optional($u->created_at)->format('Y-m-d') }}</span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a class="btn btn-sm btn-outline-primary js-user-edit"
                                       href="{{ route('admin.users.edit', $u) }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger js-user-delete"
                                            data-name="{{ $u->name }}"
                                            data-url="{{ route('admin.users.destroy', $u) }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteUserModal">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">لا يوجد مستخدمين.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($otherUsers->hasPages())
            <div class="card-footer bg-white">
                {{ $otherUsers->links() }}
            </div>
        @endif
    </div>
@else
    {{-- مشغل: موظفين/فنيين --}}
    <div class="card ui-card">
        <div class="card-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-people text-primary"></i>
                <div class="fw-bold">فريق العمل</div>
                @if(isset($operator) && $operator)
                    <span class="badge bg-primary">{{ $operator->name }}</span>
                @endif
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table users-table mb-0">
                    <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>اسم المستخدم</th>
                        <th>البريد</th>
                        <th>الدور</th>
                        <th>تاريخ الإنشاء</th>
                        <th>إجراءات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse(($employees ?? collect()) as $emp)
                        <tr>
                            <td class="fw-semibold">{{ $emp->name }}</td>
                            <td>{{ $emp->username }}</td>
                            <td>{{ $emp->email }}</td>
                            <td><span class="badge bg-success">{{ $emp->role_name ?? '-' }}</span></td>
                            <td><span class="text-muted">{{ optional($emp->created_at)->format('Y-m-d') }}</span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a class="btn btn-sm btn-outline-primary js-user-edit"
                                       href="{{ route('admin.users.edit', $emp) }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger js-user-delete"
                                            data-name="{{ $emp->name }}"
                                            data-url="{{ route('admin.users.destroy', $emp) }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteUserModal">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">لا يوجد موظفين بعد.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(isset($employees) && method_exists($employees, 'hasPages') && $employees->hasPages())
            <div class="card-footer bg-white">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
@endif
