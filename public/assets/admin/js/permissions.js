(function ($) {
    const PERM = window.PERM || {};
    const ctx = PERM.ctx || {};
    const routes = PERM.routes || {};
    const rolesMeta = PERM.rolesMeta || {};

    const $overlay = $('#permissionsLoadingOverlay');
    const $alerts = $('#permAlerts');

    const $operatorSelect = $('#operatorSelect');
    const $roleSelect = $('#roleSelect');
    const $customRoleSelect = $('#customRoleSelect');
    const $userSelect = $('#userSelect');
    const $operatorSelectWrapper = $('#operatorSelectWrapper');
    const $customRoleSelectWrapper = $('#customRoleSelectWrapper');
    const $userSelectWrapper = $('#userSelectWrapper');

    const $saveBtn = $('#savePermissionsBtn');
    const $resetBtn = $('#resetPermissionsBtn');

    const $selectedUserName = $('#selectedUserName');
    const $selectedUserRole = $('#selectedUserRole');

    const $statRole = $('#statRole');
    const $statDirect = $('#statDirect');
    const $statRevoked = $('#statRevoked');
    const $statEffective = $('#statEffective');
    const $statDirty = $('#statDirty');

    const $treeCount = $('#treeCount');

    let currentUserId = null;
    let currentUserRole = null;

    // Sets
    let roleSet = new Set();
    let directSet = new Set();
    let revokedSet = new Set();

    // baseline
    let baselineDirect = new Set();
    let baselineRevoked = new Set();

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || $('#csrfToken').val();
    }

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfToken() }
    });

    function setLoading(on) {
        if (on) {
            $overlay.css('display', 'flex');
        } else {
            $overlay.css('display', 'none');
        }
    }

    function escapeHtml(str) {
        return String(str || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function flash(type, msg) {
        // استخدام showToast من toast.blade.php
        if (typeof window.showToast === 'function') {
            const toastType = type === 'danger' ? 'danger' : type === 'warning' ? 'warning' : type === 'info' ? 'info' : 'success';
            window.showToast(msg, toastType);
        } else {
            // Fallback للـ alerts القديمة إذا لم يكن showToast متوفر
            const html = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${escapeHtml(msg)}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
            $alerts.html(html);
        }
    }

    function debounce(fn, delay) {
        let t = null;
        return function () {
            const args = arguments;
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function userPermissionsUrl(userId) {
        return routes.userPermissions.replace('__USER__', userId);
    }

    function normalizeIds(arr) {
        return (arr || []).map(x => Number(x));
    }

    function cloneSet(s) {
        return new Set(Array.from(s));
    }

    function effectiveFor(id) {
        const hasBase = roleSet.has(id) || directSet.has(id);
        return hasBase && !revokedSet.has(id);
    }

    function baselineEffectiveFor(id) {
        const hasBase = roleSet.has(id) || baselineDirect.has(id);
        return hasBase && !baselineRevoked.has(id);
    }

    function computeEffectiveCount() {
        // (role ∪ direct) - revoked
        const tmp = new Set([...roleSet, ...directSet]);
        revokedSet.forEach(id => tmp.delete(id));
        return tmp.size;
    }

    function setRoleBadge(role) {
        const meta = rolesMeta[role] || null;
        if (!meta) {
            $selectedUserRole.attr('class', 'badge text-bg-secondary').text(role || '—');
            return;
        }
        $selectedUserRole
            .attr('class', `badge text-bg-${meta.color}`)
            .html(`<i class="bi ${meta.icon} me-1"></i>${meta.label}`);
    }

    function rowState(id) {
        if (revokedSet.has(id)) return 'revoked';
        if (directSet.has(id)) return 'direct';
        if (roleSet.has(id)) return 'role';
        return 'off';
    }

    function renderRow($row) {
        const id = Number($row.data('permission-id'));
        const state = rowState(id);
        const eff = effectiveFor(id);

        $row.removeClass('is-role is-direct is-revoked is-off is-dirty');
        $row.addClass(`is-${state}`);

        // dirty highlight
        if (currentUserId && eff !== baselineEffectiveFor(id)) {
            $row.addClass('is-dirty');
        }

        // badges
        $row.find('.perm-badge').addClass('d-none');
        if (state === 'revoked') $row.find('.perm-badge-revoked').removeClass('d-none');
        else if (state === 'direct') $row.find('.perm-badge-direct').removeClass('d-none');
        else if (state === 'role') $row.find('.perm-badge-role').removeClass('d-none');
        else $row.find('.perm-badge-off').removeClass('d-none');

        // toggle
        const $toggle = $row.find('.perm-toggle');
        $toggle.prop('disabled', !currentUserId);
        $toggle.prop('checked', eff);

        // dataset for filters
        $row.attr('data-state', state);
        $row.attr('data-effective', eff ? '1' : '0');
    }

    function renderAllRows() {
        $('.perm-row').each(function () {
            renderRow($(this));
        });
        updateStatsAndButtons();
    }

    function updateStatsAndButtons() {
        // حساب dirty count من جميع الصفوف (مرئية وغير مرئية)
        const dirtyCount = $('.perm-row.is-dirty').length;

        $statRole.text(roleSet.size);
        $statDirect.text(directSet.size);
        $statRevoked.text(revokedSet.size);
        $statEffective.text(computeEffectiveCount());
        $statDirty.text(dirtyCount);

        const hasChanges = dirtyCount > 0;
        $saveBtn.prop('disabled', !currentUserId || !hasChanges);
        $resetBtn.prop('disabled', !currentUserId || !hasChanges);
    }

    /**
     * Toggle واحد = تغيير النتيجة النهائية (Effective)
     * - إذا OFF وصلاحية من الدور => أضف revoked
     * - إذا OFF وصلاحية مش من الدور => احذف direct
     * - إذا ON => احذف revoked، وإذا مش من الدور => أضف direct
     */
    function applyEffective(id, on) {
        const isRole = roleSet.has(id);

        if (on) {
            revokedSet.delete(id);
            if (!isRole) directSet.add(id);
            else directSet.delete(id); // نظافة: لا داعي لDirect إذا الدور يكفي
        } else {
            directSet.delete(id);
            if (isRole) revokedSet.add(id);
            else revokedSet.delete(id);
        }
    }

    // ===== Select2 init =====

    function formatOperatorResult(item) {
        if (!item.id) return item.text;

        const owner = item.owner_name ? ` — <span class="text-muted">${escapeHtml(item.owner_name)}</span>` : '';
        const status = item.status ? `<span class="badge text-bg-light ms-2">${escapeHtml(item.status)}</span>` : '';
        const completed = item.profile_completed ? `<span class="badge text-bg-success ms-2">مكتمل</span>` : `<span class="badge text-bg-warning ms-2">غير مكتمل</span>`;

        return $(`
            <div>
                <div class="fw-bold">${escapeHtml(item.text)} ${status} ${completed}</div>
                <div class="small">${owner}</div>
            </div>
        `);
    }

    function formatUserResult(item) {
        if (!item.id) return item.text;

        let roleBadge = '';
        const meta = rolesMeta[item.role];
        if (meta) {
            roleBadge = `<span class="badge text-bg-${meta.color} ms-2"><i class="bi ${meta.icon} me-1"></i>${meta.label}</span>`;
        }

        return $(`
            <div>
                <div class="fw-bold">${escapeHtml(item.text)} ${roleBadge}</div>
                <div class="small text-muted">${escapeHtml(item.email || '')}</div>
            </div>
        `);
    }

    function initRoleSelect() {
        if (!$roleSelect.length) return;

        // For SuperAdmin: regular select (not select2)
        if (ctx.isSuperAdmin) {
            $roleSelect.on('change', function () {
                const roleName = $(this).val();
                currentUserId = null;
                resetUserContextUI();

                // reset sets
                roleSet = new Set();
                directSet = new Set();
                revokedSet = new Set();
                baselineDirect = new Set();
                baselineRevoked = new Set();

                // Hide operator and custom role selects
                $operatorSelectWrapper.hide();
                $customRoleSelectWrapper.hide();
                $customRoleSelect.val('').trigger('change');

                // Clear and disable user select
                if ($userSelect.length) {
                    $userSelect.val(null).trigger('change');
                    $userSelect.prop('disabled', true);
                }

                if (roleName === 'company_owner') {
                    // If company_owner selected, show operator select
                    // السوبر أدمن: إذا اختار مشغل، يظهر المشغل فقط (بدون موظفين)
                    $operatorSelectWrapper.show();
                    $userSelectWrapper.hide(); // Hide user select until operator is selected
                    if (!$operatorSelect.hasClass('select2-hidden-accessible')) {
                        initOperatorSelect();
                    }
                } else if (roleName) {
                    // Other system roles (including super_admin) - show users directly
                    $operatorSelectWrapper.hide();
                    $userSelectWrapper.show(); // Show user select wrapper
                    $userSelect.prop('disabled', false);
                    initUserSelect(roleName);
                } else {
                    // No role selected - hide both
                    $operatorSelectWrapper.hide();
                    $userSelectWrapper.hide();
                }
            });
        } else if (ctx.isAdmin) {
            // For Admin: regular select (not select2)
            $roleSelect.on('change', function () {
                const roleName = $(this).val();
                currentUserId = null;
                resetUserContextUI();

                // reset sets
                roleSet = new Set();
                directSet = new Set();
                revokedSet = new Set();
                baselineDirect = new Set();
                baselineRevoked = new Set();

                // Clear and disable user select
                if ($userSelect.length) {
                    $userSelect.val(null).trigger('change');
                    $userSelect.prop('disabled', !roleName);
                }

                if (roleName) {
                    // الأدمن: يعرض المستخدمين مباشرة (بدون مشغل)
                    $userSelect.prop('disabled', false);
                    initUserSelect(roleName);
                }
            });
        } else if (ctx.isEnergyAuthority) {
            // For Energy Authority: regular select (not select2)
            $roleSelect.on('change', function () {
                const roleName = $(this).val();
                currentUserId = null;
                resetUserContextUI();

                // reset sets
                roleSet = new Set();
                directSet = new Set();
                revokedSet = new Set();
                baselineDirect = new Set();
                baselineRevoked = new Set();

                // Hide operator select initially
                $operatorSelectWrapper.hide();

                // Clear and disable user select
                if ($userSelect.length) {
                    $userSelect.val(null).trigger('change');
                    $userSelect.prop('disabled', true);
                }

                if (roleName === 'company_owner') {
                    // إذا اختار مشغل، يظهر المشغل والمستخدمين تحت المشغل
                    $operatorSelectWrapper.show();
                    $userSelectWrapper.hide(); // Hide user select until operator is selected
                    if (!$operatorSelect.hasClass('select2-hidden-accessible')) {
                        initOperatorSelect();
                    }
                } else if (roleName) {
                    // Other system roles - show users directly
                    $operatorSelectWrapper.hide();
                    $userSelectWrapper.show(); // Show user select wrapper
                    $userSelect.prop('disabled', false);
                    initUserSelect(roleName);
                } else {
                    // No role selected - hide both
                    $operatorSelectWrapper.hide();
                    $userSelectWrapper.hide();
                }
            });
        } else if (ctx.isCompanyOwner) {
            // For CompanyOwner: regular select, no select2 needed (it's a static select)
            // الدور مختار على "مشغل" بشكل افتراضي
            $roleSelect.on('change', function () {
                const roleValue = $(this).val();
                currentUserId = null;
                resetUserContextUI();

                // reset sets
                roleSet = new Set();
                directSet = new Set();
                revokedSet = new Set();
                baselineDirect = new Set();
                baselineRevoked = new Set();

                // re-init users with selected role
                if (roleValue) {
                    $userSelect.prop('disabled', false);
                    initUserSelect(roleValue);
                }
            });

            // Initialize user select on page load (الدور مختار على "مشغل" افتراضياً)
            const initialRole = $roleSelect.val();
            if (initialRole) {
                $userSelect.prop('disabled', false);
                initUserSelect(initialRole);
            }
        }
    }

    function initOperatorSelect() {
        if (!$operatorSelect.length) return;

        $operatorSelect.select2({
            width: '100%',
            placeholder: 'ابحث عن مشغل...',
            allowClear: true,
            dir: 'rtl',
            language: 'ar',
            ajax: {
                url: routes.selectOperators,
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term || '',
                    page: params.page || 1
                }),
                processResults: data => ({
                    results: data.results || [],
                    pagination: data.pagination || { more: false }
                })
            },
            templateResult: formatOperatorResult,
            templateSelection: item => item.text || '—'
        });

        $operatorSelect.off('change').on('change', function () {
            const operatorId = $(this).val();
            currentUserId = null;
            resetUserContextUI();

            // reset sets
            roleSet = new Set();
            directSet = new Set();
            revokedSet = new Set();
            baselineDirect = new Set();
            baselineRevoked = new Set();

            // Hide custom role select
            $customRoleSelectWrapper.hide();
            $customRoleSelect.val('').trigger('change');

            // Clear and disable user select
            if ($userSelect.length) {
                $userSelect.val(null).trigger('change');
                $userSelect.prop('disabled', true);
            }

            if (operatorId) {
                if (ctx.isSuperAdmin) {
                    // السوبر أدمن: إذا اختار مشغل، يظهر المشغل فقط (بدون موظفين)
                    // لا نحتاج لتحميل الأدوار المخصصة، فقط المستخدمين (المشغل)
                    $userSelectWrapper.show(); // Show user select wrapper
                    $userSelect.prop('disabled', false);
                    initUserSelect('company_owner', operatorId);
                } else if (ctx.isEnergyAuthority) {
                    // سلطة الطاقة: إذا اختار مشغل، يظهر المشغل والمستخدمين تحت المشغل
                    $userSelectWrapper.show(); // Show user select wrapper
                    $userSelect.prop('disabled', false);
                    initUserSelect('company_owner', operatorId);
                } else {
                    // Load custom roles for this operator (CompanyOwner)
                    loadCustomRolesForOperator(operatorId);
                }
            } else {
                // No operator selected - hide user select
                $userSelectWrapper.hide();
            }
        });
    }

    function loadCustomRolesForOperator(operatorId) {
        const url = routes.selectCustomRoles.replace('__OPERATOR__', operatorId);
        
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json'
        })
        .done(function(res) {
            // Clear existing options
            $customRoleSelect.find('option:not(:first)').remove();
            
            if (res.results && res.results.length > 0) {
                // Add custom roles
                res.results.forEach(function(role) {
                    $customRoleSelect.append(new Option(role.text, role.id, false, false));
                });
                $customRoleSelectWrapper.show();
            } else {
                // No custom roles - show users directly for company_owner role
                $userSelect.prop('disabled', false);
                initUserSelect('company_owner');
            }
        })
        .fail(function() {
            flash('warning', 'فشل تحميل الأدوار المخصصة');
        });
    }

    function initCustomRoleSelect() {
        if (!$customRoleSelect.length) return;

        $customRoleSelect.on('change', function () {
            const roleId = $(this).val();
            currentUserId = null;
            resetUserContextUI();

            // reset sets
            roleSet = new Set();
            directSet = new Set();
            revokedSet = new Set();
            baselineDirect = new Set();
            baselineRevoked = new Set();

            // Clear and disable user select
            if ($userSelect.length) {
                $userSelect.val(null).trigger('change');
                $userSelect.prop('disabled', !roleId);
            }

            if (roleId) {
                // Show users for this custom role
                $userSelect.prop('disabled', false);
                initUserSelect('custom_role_' + roleId);
            }
        });
    }

    function initUserSelect(roleForSuperAdminOrCompanyOwner, operatorId = null) {
        if (!$userSelect.length) return;

        // destroy old if exists
        if ($userSelect.hasClass('select2-hidden-accessible')) {
            $userSelect.select2('destroy');
        }

        $userSelect.select2({
            width: '100%',
            placeholder: ctx.isSuperAdmin ? 'ابحث عن المستخدمين...' : (ctx.isAdmin ? 'ابحث عن المستخدمين...' : (ctx.isEnergyAuthority ? 'ابحث عن المستخدمين...' : 'ابحث عن موظف/فني...')),
            allowClear: true,
            dir: 'rtl',
            language: 'ar',
            ajax: {
                url: routes.selectUsers,
                dataType: 'json',
                delay: 250,
                data: params => {
                    const payload = {
                        q: params.term || '',
                        page: params.page || 1
                    };
                    if (ctx.isSuperAdmin) {
                        // SuperAdmin: check if it's custom role or system role
                        if (roleForSuperAdminOrCompanyOwner && roleForSuperAdminOrCompanyOwner.startsWith('custom_role_')) {
                            const roleId = roleForSuperAdminOrCompanyOwner.replace('custom_role_', '');
                            payload.role_id = parseInt(roleId, 10);
                        } else {
                            // System role
                            payload.role = roleForSuperAdminOrCompanyOwner;
                            // إذا تم تحديد operator_id، أرسله (للمشغل فقط)
                            if (operatorId && roleForSuperAdminOrCompanyOwner === 'company_owner') {
                                payload.operator_id = parseInt(operatorId, 10);
                            }
                        }
                    } else if (ctx.isAdmin) {
                        // Admin: use role value
                        payload.role = roleForSuperAdminOrCompanyOwner;
                    } else if (ctx.isEnergyAuthority) {
                        // Energy Authority: use role value
                        payload.role = roleForSuperAdminOrCompanyOwner;
                        // إذا تم تحديد operator_id، أرسله (للمشغل فقط)
                        if (operatorId && roleForSuperAdminOrCompanyOwner === 'company_owner') {
                            payload.operator_id = parseInt(operatorId, 10);
                        }
                    } else if (ctx.isCompanyOwner) {
                        // CompanyOwner: use role value (company_owner or role_id)
                        if (roleForSuperAdminOrCompanyOwner === 'company_owner') {
                            payload.role = 'company_owner';
                        } else {
                            payload.role_id = parseInt(roleForSuperAdminOrCompanyOwner, 10);
                        }
                    }
                    return payload;
                },
                processResults: data => ({
                    results: data.results || [],
                    pagination: data.pagination || { more: false }
                })
            },
            templateResult: formatUserResult,
            templateSelection: item => item.text || '—'
        });

        $userSelect.prop('disabled', false);

        $userSelect.off('select2:select').on('select2:select', function (e) {
            const item = e.params.data;
            if (!item || !item.id) {
                flash('warning', 'يرجى اختيار مستخدم صحيح');
                return;
            }

            currentUserId = Number(item.id);
            currentUserRole = item.role || null;

            $selectedUserName.text(item.text || '—');
            setRoleBadge(currentUserRole);

            loadUserPermissions(currentUserId);
        });

        $userSelect.off('select2:clear').on('select2:clear', function () {
            currentUserId = null;
            resetUserContextUI();

            roleSet = new Set();
            directSet = new Set();
            revokedSet = new Set();
            baselineDirect = new Set();
            baselineRevoked = new Set();

            renderAllRows();
        });
    }

    function resetUserContextUI() {
        $selectedUserName.text('—');
        $selectedUserRole.attr('class', 'badge text-bg-secondary').text('—');

        $statRole.text('0');
        $statDirect.text('0');
        $statRevoked.text('0');
        $statEffective.text('0');
        $statDirty.text('0');

        $saveBtn.prop('disabled', true);
        $resetBtn.prop('disabled', true);

        // disable all toggles
        $('.perm-toggle').prop('disabled', true).prop('checked', false);
        $('.perm-row').removeClass('is-role is-direct is-revoked is-off is-dirty').addClass('is-off');
        $('.perm-badge').addClass('d-none');
        $('.perm-badge-off').removeClass('d-none');
    }

    // ===== Load user perms =====
    function loadUserPermissions(userId) {
        if (!userId) {
            flash('warning', 'يرجى اختيار مستخدم أولاً');
            return;
        }

        setLoading(true);

        $.ajax({
            url: userPermissionsUrl(userId),
            method: 'GET',
            dataType: 'json'
        })
            .done(res => {
                if (!res.success) {
                    flash('danger', res.message || 'فشل تحميل الصلاحيات');
                    resetUserContextUI();
                    return;
                }

                const u = res.user || {};
                // تحميل الصلاحيات الحالية من السيرفر
                directSet = new Set(normalizeIds(u.direct_permissions));
                roleSet = new Set(normalizeIds(u.role_permissions));
                revokedSet = new Set(normalizeIds(u.revoked_permissions));

                // حفظ نسخة احتياطية (baseline) للرجوع إليها عند Reset
                baselineDirect = cloneSet(directSet);
                baselineRevoked = cloneSet(revokedSet);

                renderAllRows();
                flash('success', 'تم تحميل الصلاحيات بنجاح. يمكنك الآن تعديلها.');
            })
            .fail(xhr => {
                let errorMsg = 'حدث خطأ أثناء تحميل الصلاحيات';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMsg = errors.join(' | ');
                    }
                } else if (xhr.status === 403) {
                    errorMsg = 'ليس لديك صلاحية لعرض صلاحيات هذا المستخدم';
                } else if (xhr.status === 404) {
                    errorMsg = 'المستخدم المحدد غير موجود';
                } else if (xhr.status === 500) {
                    errorMsg = 'حدث خطأ في الخادم، يرجى المحاولة لاحقاً';
                }
                
                flash('danger', errorMsg);
                resetUserContextUI();
            })
            .always(() => setLoading(false));
    }

    // ===== Toggle permission =====
    $(document).on('change', '.perm-toggle', function () {
        if (!currentUserId) {
            this.checked = false;
            flash('warning', 'يرجى اختيار مستخدم أولاً');
            return;
        }

        const id = Number($(this).data('permission-id'));
        const wasChecked = this.checked;
        applyEffective(id, wasChecked);

        const $row = $(this).closest('.perm-row');
        renderRow($row);
        updateStatsAndButtons();
    });

    // ===== Group enable/disable =====
    $(document).on('click', '.perm-group-enable', function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (!currentUserId) {
            flash('warning', 'يرجى اختيار مستخدم أولاً');
            return;
        }

        const group = $(this).data('group');
        let count = 0;
        $(`.perm-row[data-group="${group}"]`).each(function () {
            const id = Number($(this).data('permission-id'));
            applyEffective(id, true);
            count++;
        });

        renderAllRows();
        updateStatsAndButtons();
        
        if (count > 0) {
            flash('success', `تم تفعيل ${count} صلاحية في هذه المجموعة`);
        }
    });

    $(document).on('click', '.perm-group-disable', function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (!currentUserId) {
            flash('warning', 'يرجى اختيار مستخدم أولاً');
            return;
        }

        const group = $(this).data('group');
        let count = 0;
        $(`.perm-row[data-group="${group}"]`).each(function () {
            const id = Number($(this).data('permission-id'));
            applyEffective(id, false);
            count++;
        });

        renderAllRows();
        updateStatsAndButtons();
        
        if (count > 0) {
            flash('warning', `تم تعطيل ${count} صلاحية في هذه المجموعة`);
        }
    });

    // ===== Expand/Collapse All (Bootstrap 5 API) =====
    $('#expandAllBtn').on('click', function () {
        document.querySelectorAll('.perm-group-collapse').forEach(el => {
            bootstrap.Collapse.getOrCreateInstance(el, { toggle: false }).show();
        });
    });

    $('#collapseAllBtn').on('click', function () {
        document.querySelectorAll('.perm-group-collapse').forEach(el => {
            bootstrap.Collapse.getOrCreateInstance(el, { toggle: false }).hide();
        });
    });

    // ===== Search tree (server side) =====
    const runSearch = debounce(function () {
        const term = ($('#searchInput').val() || '').trim();
        $('#clearSearchInput').toggleClass('d-none', term === '');
        
        // إذا كان البحث فارغاً، لا حاجة لـ loading overlay
        if (!term) {
            setLoading(true);
        } else {
            setLoading(true);
        }

        $.ajax({
            url: routes.searchTree,
            method: 'GET',
            data: { search: term },
            dataType: 'json'
        })
            .done(res => {
                if (!res.success) {
                    flash('warning', 'لم يتم العثور على نتائج');
                    return;
                }

                $('#permissionsTreeContainer').html(res.html);
                $treeCount.text(res.count ?? 0);

                // إعادة render جميع الصفوف حسب الحالة الحالية
                renderAllRows();

                // إعادة تطبيق الفلتر الحالي إذا كان مفعلاً
                const activeFilter = $('.perm-filter.active').data('filter');
                if (activeFilter && activeFilter !== 'all') {
                    applyFilter(activeFilter);
                }

                $('#clearSearchBtn').toggleClass('d-none', term === '');
                $('#clearSearchInput').toggleClass('d-none', term === '');
            })
            .fail(xhr => {
                const errorMsg = xhr.responseJSON?.message || 'فشل تحميل نتائج البحث';
                flash('danger', errorMsg);
            })
            .always(() => setLoading(false));
    }, 350);

    $('#searchBtn').on('click', runSearch);
    
    // Search on Enter key
    $('#searchInput').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            runSearch();
        }
    });

    // Show/hide clear button inside input
    $('#searchInput').on('input', function() {
        const val = $(this).val().trim();
        $('#clearSearchInput').toggleClass('d-none', val === '');
    });

    // Clear input button
    $('#clearSearchInput').on('click', function() {
        $('#searchInput').val('').focus();
        $(this).addClass('d-none');
    });
    
    $('#clearSearchBtn').on('click', function () {
        $('#searchInput').val('');
        $('#clearSearchInput').addClass('d-none');
        $('#clearSearchBtn').addClass('d-none');
        runSearch();
    });

    // ===== Filters (client side) =====
    function applyFilter(filter) {
        $('.perm-row').each(function () {
            const $row = $(this);
            const state = $row.attr('data-state');
            const effective = $row.attr('data-effective') === '1';

            let show = true;
            switch (filter) {
                case 'enabled': show = effective; break;
                case 'disabled': show = !effective; break;
                case 'revoked': show = (state === 'revoked'); break;
                case 'role': show = (state === 'role'); break;
                case 'direct': show = (state === 'direct'); break;
                case 'dirty': show = $row.hasClass('is-dirty'); break;
                default: show = true;
            }

            $row.toggle(show);
        });

        // hide group if no visible rows inside it (خصوصاً مع البحث/فلترة)
        $('.perm-group').each(function () {
            const $g = $(this);
            const visible = $g.find('.perm-row:visible').length;
            $g.toggle(visible > 0);
        });
    }

    $(document).on('click', '.perm-filter', function () {
        $('.perm-filter').removeClass('active');
        $(this).addClass('active');
        applyFilter($(this).data('filter'));
    });

    // ===== Reset =====
    $('#resetPermissionsBtn').on('click', function () {
        if (!currentUserId) {
            flash('warning', 'يرجى اختيار مستخدم أولاً');
            return;
        }

        // حساب dirty count من جميع الصفوف (مرئية وغير مرئية)
        let dirtyCount = 0;
        $('.perm-row').each(function() {
            if ($(this).hasClass('is-dirty')) {
                dirtyCount++;
            }
        });

        if (dirtyCount === 0) {
            flash('info', 'لا توجد تغييرات للتراجع عنها');
            return;
        }

        if (!confirm('هل أنت متأكد من التراجع عن جميع التغييرات؟')) {
            return;
        }

        // إظهار جميع الصفوف أولاً قبل reset (في حالة وجود فلتر نشط)
        $('.perm-row').show();
        $('.perm-group').show();

        // إعادة تعيين المجموعات إلى القيم الأساسية (التي تم حفظها عند التحميل)
        // هذا يلغي جميع التعديلات ويعيد الحالة الأصلية
        directSet = cloneSet(baselineDirect);
        revokedSet = cloneSet(baselineRevoked);

        // إعادة render جميع الصفوف - هذا سيحدّث حالة كل صف ويعيدها للحالة الأصلية
        renderAllRows();
        
        // إعادة تطبيق الفلتر الحالي إذا كان مفعلاً
        const activeFilter = $('.perm-filter.active').data('filter');
        if (activeFilter && activeFilter !== 'all') {
            applyFilter(activeFilter);
        }

        flash('success', 'تم التراجع عن جميع التغييرات بنجاح.');
    });

    // ===== Save =====
    $('#savePermissionsBtn').on('click', function () {
        if (!currentUserId) {
            flash('warning', 'يرجى اختيار مستخدم أولاً');
            return;
        }

        const dirtyCount = $('.perm-row.is-dirty').length;
        if (dirtyCount === 0) {
            flash('info', 'لا توجد تغييرات لحفظها');
            return;
        }

        // تأكيد قبل الحفظ
        if (!confirm(`هل أنت متأكد من حفظ ${dirtyCount} تغيير؟`)) {
            return;
        }

        const $btn = $(this);
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>جاري الحفظ...');
        setLoading(true);

        const payload = {
            user_id: currentUserId,
            permissions: Array.from(directSet),
            revoked_permissions: Array.from(revokedSet),
        };

        $.ajax({
            url: routes.assign,
            method: 'POST',
            data: payload,
            dataType: 'json'
        })
            .done(res => {
                if (!res.success) {
                    flash('danger', res.message || 'فشل حفظ الصلاحيات');
                    $btn.prop('disabled', false).html(originalText);
                    return;
                }

                const u = res.user || {};
                directSet = new Set(normalizeIds(u.direct_permissions));
                roleSet = new Set(normalizeIds(u.role_permissions));
                revokedSet = new Set(normalizeIds(u.revoked_permissions));

                baselineDirect = cloneSet(directSet);
                baselineRevoked = cloneSet(revokedSet);

                renderAllRows();
                updateStatsAndButtons();

                flash('success', res.message || 'تم حفظ الصلاحيات بنجاح.');
                $btn.prop('disabled', false).html(originalText);
            })
            .fail(xhr => {
                let errorMsg = 'حدث خطأ أثناء حفظ الصلاحيات';
                
                if (xhr.responseJSON) {
                    // معالجة validation errors
                    if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMsg = errors.join(' | ');
                    } else if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                } else if (xhr.status === 422) {
                    errorMsg = 'البيانات المرسلة غير صحيحة';
                } else if (xhr.status === 403) {
                    errorMsg = 'ليس لديك صلاحية لإجراء هذا التعديل';
                } else if (xhr.status === 404) {
                    errorMsg = 'المستخدم المحدد غير موجود';
                } else if (xhr.status === 500) {
                    errorMsg = 'حدث خطأ في الخادم، يرجى المحاولة لاحقاً';
                } else if (xhr.status === 0) {
                    errorMsg = 'فشل الاتصال بالخادم، تحقق من الاتصال بالإنترنت';
                }
                
                flash('danger', errorMsg);
                $btn.prop('disabled', false).html(originalText);
            })
            .always(() => setLoading(false));
    });

    // ===== Init =====
    $(document).ready(function () {
        // أول render (بدون مستخدم)
        resetUserContextUI();

        // التحقق من وجود user_id في الـ URL
        const urlParams = new URLSearchParams(window.location.search);
        const userIdFromUrl = urlParams.get('user_id');

        if (ctx.isSuperAdmin) {
            // SuperAdmin: init role select first
            initRoleSelect();
            initCustomRoleSelect();
            
            // إذا كان هناك user_id في الـ URL، نحتاج لتحميل المستخدم والدور أولاً
            if (userIdFromUrl) {
                loadUserFromUrl(userIdFromUrl);
            }
        } else if (ctx.isAdmin) {
            // Admin: init role select
            initRoleSelect();
            
            // إذا كان هناك user_id في الـ URL، حمّل المستخدم مباشرة
            if (userIdFromUrl) {
                loadUserFromUrl(userIdFromUrl);
            }
        } else if (ctx.isEnergyAuthority) {
            // Energy Authority: init role select
            initRoleSelect();
            initOperatorSelect();
            
            // إذا كان هناك user_id في الـ URL، حمّل المستخدم مباشرة
            if (userIdFromUrl) {
                loadUserFromUrl(userIdFromUrl);
            }
        } else if (ctx.isCompanyOwner) {
            // CompanyOwner: init role select (which will init user select)
            initRoleSelect();
            
            // إذا كان هناك user_id في الـ URL، حمّل المستخدم مباشرة
            if (userIdFromUrl) {
                loadUserFromUrl(userIdFromUrl, ctx.operatorId);
            }
        }
    });

    // ===== Helper: Set operator and load custom roles =====
    function setOperatorAndLoadCustomRoles(operatorData, userRoleId, userIdNum, userName, userRole) {
        if (!operatorData) return;
        
        // إضافة المشغل إلى select2
        const operatorOption = new Option(operatorData.text, operatorData.id, true, true);
        $operatorSelect.append(operatorOption).trigger('change');
        
        // انتظر حتى يتم تحميل الأدوار المخصصة
        setTimeout(function() {
            if (userRoleId) {
                // إذا كان المستخدم لديه custom role
                $customRoleSelect.val(userRoleId).trigger('change');
                
                // انتظر حتى يتم تهيئة select المستخدمين
                setTimeout(function() {
                    addUserToSelectAndLoad(userIdNum, userName, userRole, 'custom_role_' + userRoleId);
                }, 500);
            } else {
                // المستخدم مشغل بدون custom role
                setTimeout(function() {
                    addUserToSelectAndLoad(userIdNum, userName, userRole, 'company_owner');
                }, 500);
            }
        }, 800);
    }

    // ===== Load user from URL =====
    function loadUserFromUrl(userId, operatorIdForSuperAdmin = null) {
        const userIdNum = parseInt(userId, 10);
        if (!userIdNum || userIdNum <= 0) {
            flash('warning', 'معرف المستخدم غير صحيح');
            return;
        }

        setLoading(true);

        // جلب معلومات المستخدم مباشرة من API الصلاحيات
        $.ajax({
            url: routes.userPermissions.replace('__USER__', userIdNum),
            method: 'GET',
            dataType: 'json'
        })
            .done(function(res) {
            if (!res.success || !res.user) {
                flash('danger', 'المستخدم غير موجود أو ليس لديك صلاحية لعرضه');
                setLoading(false);
                return;
            }

            const user = res.user;
            const userRole = user.role || '';
            const userRoleId = user.role_id || null;
            const userOperatorId = user.operator_id || null;
            
            // لا نوقف loading هنا - سنوقفه في loadUserPermissions
            
            // إذا كان السوبر أدمن
            if (ctx.isSuperAdmin && userRole) {
                if (userRole === 'company_owner') {
                    // إذا كان المستخدم مشغل، حمّل المشغل والأدوار المخصصة
                    $roleSelect.val('company_owner').trigger('change');
                    
                    // انتظر حتى يتم عرض select المشغل
                    setTimeout(function() {
                        if (userOperatorId) {
                            // تحميل معلومات المشغل من API
                            $.ajax({
                                url: routes.selectOperators,
                                method: 'GET',
                                data: { q: '', page: 1, operator_id: userOperatorId }
                            })
                            .done(function(opRes) {
                                // البحث عن المشغل في النتائج
                                let operatorData = null;
                                if (opRes.results && opRes.results.length > 0) {
                                    operatorData = opRes.results.find(function(op) {
                                        return parseInt(op.id) === parseInt(userOperatorId);
                                    });
                                }
                                
                                // إذا لم نجده في النتائج، جربه مباشرة
                                if (!operatorData) {
                                    // جرب البحث بشكل مختلف
                                    $.ajax({
                                        url: routes.selectOperators,
                                        method: 'GET',
                                        data: { q: userOperatorId.toString(), page: 1 }
                                    })
                                    .done(function(searchRes) {
                                        if (searchRes.results && searchRes.results.length > 0) {
                                            operatorData = searchRes.results.find(function(op) {
                                                return parseInt(op.id) === parseInt(userOperatorId);
                                            });
                                        }
                                        if (operatorData) {
                                            setOperatorAndLoadCustomRoles(operatorData, userRoleId, userIdNum, user.name, userRole);
                                        } else {
                                            // إذا لم نجد المشغل، حمّل المستخدم مباشرة
                                            setTimeout(function() {
                                                addUserToSelectAndLoad(userIdNum, user.name, userRole, 'company_owner');
                                            }, 300);
                                        }
                                    });
                                } else {
                                    setOperatorAndLoadCustomRoles(operatorData, userRoleId, userIdNum, user.name, userRole);
                                }
                            })
                            .fail(function() {
                                // إذا فشل تحميل المشغل، حمّل المستخدم مباشرة
                                setTimeout(function() {
                                    addUserToSelectAndLoad(userIdNum, user.name, userRole, 'company_owner');
                                }, 300);
                            });
                        } else {
                            // لا يوجد مشغل - فقط دور النظام
                            setTimeout(function() {
                                addUserToSelectAndLoad(userIdNum, user.name, userRole, userRole);
                            }, 500);
                        }
                    }, 300);
                } else {
                    // دور نظامي آخر (غير مشغل)
                    $roleSelect.val(userRole).trigger('change');
                    
                    // انتظر حتى يتم تهيئة select المستخدمين
                    setTimeout(function() {
                        addUserToSelectAndLoad(userIdNum, user.name, userRole, userRole);
                    }, 500);
                }
            } else if (ctx.isCompanyOwner) {
                // Company Owner - حمّل مباشرة
                if (userRoleId) {
                    // إذا كان المستخدم لديه custom role
                    $roleSelect.val(userRoleId).trigger('change');
                    setTimeout(function() {
                        addUserToSelectAndLoad(userIdNum, user.name, userRole, userRoleId);
                    }, 300);
                } else {
                    // المستخدم مشغل
                    $roleSelect.val('company_owner').trigger('change');
                    setTimeout(function() {
                        addUserToSelectAndLoad(userIdNum, user.name, userRole, 'company_owner');
                    }, 300);
                }
            }
        })
        .fail(function(xhr) {
            let errorMsg = 'فشل تحميل معلومات المستخدم';
            if (xhr.status === 404) {
                errorMsg = 'المستخدم غير موجود';
            } else if (xhr.status === 403) {
                errorMsg = 'ليس لديك صلاحية لعرض هذا المستخدم';
            }
            flash('danger', errorMsg);
            setLoading(false);
        });
    }

    // ===== Add user to select and load permissions =====
    function addUserToSelectAndLoad(userId, userName, userRole, operatorIdForSuperAdmin = null) {
        const userIdNum = parseInt(userId, 10);
        
        // التأكد من تهيئة select2
        if (!$userSelect.hasClass('select2-hidden-accessible')) {
            if (ctx.isSuperAdmin) {
                const roleName = $roleSelect.val();
                if (roleName) {
                    initUserSelect(roleName);
                } else {
                    flash('warning', 'يرجى اختيار الدور أولاً');
                    setLoading(false);
                    return;
                }
            } else {
                const roleValue = $roleSelect.val();
                initUserSelect(roleValue || 'company_owner');
            }
        }

        // انتظر قليلاً للتأكد من تهيئة select2
        setTimeout(function() {
            // إنشاء option جديد للمستخدم
            const option = new Option(userName || `مستخدم #${userIdNum}`, userIdNum, true, true);
            
            // إضافة المستخدم للـ select
            if ($userSelect.length) {
                // إزالة أي option موجود بنفس الـ ID
                $userSelect.find('option[value="' + userIdNum + '"]').remove();
                
                // إضافة الـ option الجديد
                $userSelect.append(option);
                
                // تحديث select2
                $userSelect.val(userIdNum).trigger('change');
                
                // تحميل الصلاحيات (سيتم تلقائياً عند change event)
                // لكن للتأكد، نحمّل الصلاحيات مباشرة بعد ثانية
                setTimeout(function() {
                    if (currentUserId !== userIdNum) {
                        // إذا لم يتم تحميل الصلاحيات تلقائياً، حمّلها يدوياً
                        currentUserId = userIdNum;
                        currentUserRole = userRole || null;
                        $selectedUserName.text(userName || '—');
                        setRoleBadge(currentUserRole);
                        loadUserPermissions(userIdNum);
                    }
                    // لا نوقف loading هنا - loadUserPermissions سيتولى ذلك
                }, 1000);
            } else {
                // إذا لم يكن select موجود، حمّل الصلاحيات مباشرة
                currentUserId = userIdNum;
                currentUserRole = userRole || null;
                $selectedUserName.text(userName || '—');
                setRoleBadge(currentUserRole);
                loadUserPermissions(userIdNum);
            }
        }, 500);
    }

})(jQuery);
