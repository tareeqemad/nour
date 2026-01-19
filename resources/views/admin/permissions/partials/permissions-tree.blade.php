@if($permissions->count() > 0)
    @foreach($permissions as $group => $groupPermissions)
        @php $collapseId = 'perm_group_' . $loop->index; @endphp

        <div class="perm-group" data-group="{{ $group }}">
            <div class="perm-group-header"
                 role="button"
                 data-bs-toggle="collapse"
                 data-bs-target="#{{ $collapseId }}"
                 aria-expanded="true">

                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-chevron-down perm-group-chevron"></i>
                    <i class="bi bi-folder2-open"></i>
                    <div class="fw-bold">{{ $groupPermissions->first()->group_label }}</div>
                    <span class="badge text-bg-secondary">{{ $groupPermissions->count() }}</span>
                </div>

                <div class="perm-group-actions">
                    <button type="button" class="btn btn-sm btn-outline-success perm-group-enable" data-group="{{ $group }}">
                        <i class="bi bi-check2-circle me-1"></i>
                        تفعيل الكل
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger perm-group-disable" data-group="{{ $group }}">
                        <i class="bi bi-slash-circle me-1"></i>
                        تعطيل الكل
                    </button>
                </div>
            </div>

            <div class="collapse show perm-group-collapse" id="{{ $collapseId }}">
                <div class="perm-list">
                    @foreach($groupPermissions as $permission)
                        <div class="perm-row"
                             data-permission-id="{{ $permission->id }}"
                             data-group="{{ $group }}"
                             data-permission-name="{{ $permission->name }}">

                            <div class="perm-row-main">
                                <div class="perm-row-text">
                                    <div class="perm-row-title">{{ $permission->label }}</div>
                                    @if($permission->description)
                                        <div class="perm-row-desc">{{ $permission->description }}</div>
                                    @endif
                                    <div class="perm-row-code">
                                        <code>{{ $permission->name }}</code>
                                    </div>
                                </div>

                                <div class="perm-row-side">
                                    <div class="perm-badges">
                                        <span class="badge bg-danger perm-badge perm-badge-revoked d-none">ممنوعة</span>
                                        <span class="badge bg-success perm-badge perm-badge-direct d-none">مباشرة</span>
                                        <span class="badge bg-info perm-badge perm-badge-role d-none">من الدور</span>
                                        <span class="badge bg-secondary perm-badge perm-badge-off d-none">غير مفعلة</span>
                                    </div>

                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input perm-toggle"
                                               type="checkbox"
                                               role="switch"
                                               id="perm_toggle_{{ $permission->id }}"
                                               data-permission-id="{{ $permission->id }}"
                                               disabled>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="alert alert-warning text-center mb-0">
        <i class="bi bi-exclamation-triangle me-2"></i>
        لم يتم العثور على صلاحيات تطابق البحث "{{ $search }}"
    </div>
@endif
