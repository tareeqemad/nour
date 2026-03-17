@php
    // تصنيف المجموعات إلى أقسام رئيسية
    $categories = [
        'system' => [
            'label' => 'إدارة النظام',
            'icon' => 'bi-gear-wide-connected',
            'color' => '#6366f1',
            'groups' => ['users', 'operators', 'roles', 'permissions', 'permission_audit_logs', 'activity_logs', 'logs'],
        ],
        'operations' => [
            'label' => 'إدارة العمليات',
            'icon' => 'bi-lightning-charge',
            'color' => '#f59e0b',
            'groups' => ['generators', 'generation_units', 'operation_logs', 'fuel_efficiencies', 'maintenance_records', 'compliance_safeties', 'electricity_tariff_prices', 'inspection_violation_cases', 'tasks'],
        ],
        'subscribers' => [
            'label' => 'المشتركين والفوترة',
            'icon' => 'bi-receipt-cutoff',
            'color' => '#10b981',
            'groups' => ['subscribers', 'meter_readings', 'invoices', 'invoice_reports', 'minimum_charge_rules', 'employee_discount_rates', 'subscriber_accounts'],
        ],
        'communication' => [
            'label' => 'التواصل والإشعارات',
            'icon' => 'bi-chat-square-dots',
            'color' => '#3b82f6',
            'groups' => ['complaints_suggestions', 'notifications', 'messages', 'authorized_phones'],
        ],
        'settings' => [
            'label' => 'الإعدادات والمحتوى',
            'icon' => 'bi-sliders',
            'color' => '#8b5cf6',
            'groups' => ['settings', 'constants', 'welcome_messages', 'sms_templates', 'guide'],
        ],
    ];

    // أيقونة لكل مجموعة
    $groupIcons = [
        'users' => 'bi-people-fill',
        'operators' => 'bi-building',
        'roles' => 'bi-person-badge-fill',
        'permissions' => 'bi-key-fill',
        'permission_audit_logs' => 'bi-clock-history',
        'activity_logs' => 'bi-activity',
        'logs' => 'bi-bug-fill',
        'generators' => 'bi-lightning-fill',
        'generation_units' => 'bi-cpu-fill',
        'operation_logs' => 'bi-journal-text',
        'fuel_efficiencies' => 'bi-fuel-pump-fill',
        'maintenance_records' => 'bi-wrench-adjustable',
        'compliance_safeties' => 'bi-shield-fill-check',
        'electricity_tariff_prices' => 'bi-currency-dollar',
        'inspection_violation_cases' => 'bi-exclamation-triangle-fill',
        'tasks' => 'bi-list-task',
        'subscribers' => 'bi-person-lines-fill',
        'meter_readings' => 'bi-speedometer2',
        'invoices' => 'bi-receipt',
        'invoice_reports' => 'bi-graph-up-arrow',
        'minimum_charge_rules' => 'bi-calculator-fill',
        'employee_discount_rates' => 'bi-percent',
        'subscriber_accounts' => 'bi-wallet2',
        'complaints_suggestions' => 'bi-chat-square-text-fill',
        'notifications' => 'bi-bell-fill',
        'messages' => 'bi-envelope-fill',
        'authorized_phones' => 'bi-phone-fill',
        'settings' => 'bi-gear-fill',
        'constants' => 'bi-database-fill',
        'welcome_messages' => 'bi-chat-dots-fill',
        'sms_templates' => 'bi-chat-left-text-fill',
        'guide' => 'bi-book-fill',
    ];

    // تجميع الصلاحيات حسب الأقسام
    $categorized = [];
    $uncategorized = [];

    foreach ($permissions as $group => $groupPerms) {
        $placed = false;
        foreach ($categories as $catKey => $cat) {
            if (in_array($group, $cat['groups'])) {
                $categorized[$catKey][$group] = $groupPerms;
                $placed = true;
                break;
            }
        }
        if (!$placed) {
            $uncategorized[$group] = $groupPerms;
        }
    }
@endphp

@if($permissions->count() > 0)
    @foreach($categories as $catKey => $cat)
        @if(isset($categorized[$catKey]) && count($categorized[$catKey]) > 0)
            @php
                $catGroups = $categorized[$catKey];
                $catTotalPerms = collect($catGroups)->flatten()->count();
                $catCollapseId = 'perm_cat_' . $catKey;
            @endphp

            <div class="perm-category" data-category="{{ $catKey }}">
                {{-- عنوان القسم --}}
                <div class="perm-category-header"
                     role="button"
                     data-bs-toggle="collapse"
                     data-bs-target="#{{ $catCollapseId }}"
                     aria-expanded="false">

                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-chevron-down perm-cat-chevron"></i>
                        <div class="perm-category-icon" style="background: {{ $cat['color'] }}20; color: {{ $cat['color'] }};">
                            <i class="bi {{ $cat['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="perm-category-title">{{ $cat['label'] }}</div>
                            <div class="perm-category-subtitle">{{ count($catGroups) }} مجموعات &middot; {{ $catTotalPerms }} صلاحية</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div class="perm-category-progress">
                            <span class="perm-cat-enabled-count" data-category="{{ $catKey }}">0</span>/<span>{{ $catTotalPerms }}</span>
                        </div>
                        <div class="perm-category-actions">
                            <button type="button" class="btn btn-sm btn-outline-success perm-cat-enable" data-category="{{ $catKey }}" onclick="event.stopPropagation();">
                                <i class="bi bi-check2-all me-1"></i>
                                تفعيل القسم
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger perm-cat-disable" data-category="{{ $catKey }}" onclick="event.stopPropagation();">
                                <i class="bi bi-x-lg me-1"></i>
                                تعطيل القسم
                            </button>
                        </div>
                    </div>
                </div>

                {{-- محتوى القسم --}}
                <div class="collapse perm-category-collapse" id="{{ $catCollapseId }}">
                    <div class="perm-category-body">
                        @foreach($catGroups as $group => $groupPermissions)
                            @php
                                $collapseId = 'perm_group_' . $catKey . '_' . $loop->index;
                                $icon = $groupIcons[$group] ?? 'bi-folder2-open';
                            @endphp

                            <div class="perm-group" data-group="{{ $group }}" data-category="{{ $catKey }}">
                                <div class="perm-group-header"
                                     role="button"
                                     data-bs-toggle="collapse"
                                     data-bs-target="#{{ $collapseId }}"
                                     aria-expanded="false">

                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-chevron-down perm-group-chevron"></i>
                                        <i class="bi {{ $icon }} perm-group-icon" style="color: {{ $cat['color'] }};"></i>
                                        <div class="fw-bold">{{ $groupPermissions->first()->group_label }}</div>
                                        <div class="perm-group-progress-badge">
                                            <span class="perm-group-enabled-count" data-group="{{ $group }}">0</span>/<span>{{ $groupPermissions->count() }}</span>
                                        </div>
                                    </div>

                                    <div class="perm-group-actions">
                                        <button type="button" class="btn btn-sm btn-outline-success perm-group-enable" data-group="{{ $group }}" onclick="event.stopPropagation();">
                                            <i class="bi bi-check2-circle me-1"></i>
                                            تفعيل الكل
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger perm-group-disable" data-group="{{ $group }}" onclick="event.stopPropagation();">
                                            <i class="bi bi-slash-circle me-1"></i>
                                            تعطيل الكل
                                        </button>
                                    </div>
                                </div>

                                <div class="collapse perm-group-collapse" id="{{ $collapseId }}">
                                    <div class="perm-list">
                                        @foreach($groupPermissions as $permission)
                                            <div class="perm-row"
                                                 data-permission-id="{{ $permission->id }}"
                                                 data-group="{{ $group }}"
                                                 data-category="{{ $catKey }}"
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
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- مجموعات غير مصنفة --}}
    @if(count($uncategorized) > 0)
        @foreach($uncategorized as $group => $groupPermissions)
            @php
                $collapseId = 'perm_group_uncat_' . $loop->index;
                $icon = $groupIcons[$group] ?? 'bi-folder2-open';
            @endphp

            <div class="perm-group" data-group="{{ $group }}">
                <div class="perm-group-header"
                     role="button"
                     data-bs-toggle="collapse"
                     data-bs-target="#{{ $collapseId }}"
                     aria-expanded="false">

                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-chevron-down perm-group-chevron"></i>
                        <i class="bi {{ $icon }} perm-group-icon"></i>
                        <div class="fw-bold">{{ $groupPermissions->first()->group_label }}</div>
                        <div class="perm-group-progress-badge">
                            <span class="perm-group-enabled-count" data-group="{{ $group }}">0</span>/<span>{{ $groupPermissions->count() }}</span>
                        </div>
                    </div>

                    <div class="perm-group-actions">
                        <button type="button" class="btn btn-sm btn-outline-success perm-group-enable" data-group="{{ $group }}" onclick="event.stopPropagation();">
                            <i class="bi bi-check2-circle me-1"></i>
                            تفعيل الكل
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger perm-group-disable" data-group="{{ $group }}" onclick="event.stopPropagation();">
                            <i class="bi bi-slash-circle me-1"></i>
                            تعطيل الكل
                        </button>
                    </div>
                </div>

                <div class="collapse perm-group-collapse" id="{{ $collapseId }}">
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
    @endif
@else
    <div class="alert alert-warning text-center mb-0">
        <i class="bi bi-exclamation-triangle me-2"></i>
        لم يتم العثور على صلاحيات تطابق البحث "{{ $search }}"
    </div>
@endif
