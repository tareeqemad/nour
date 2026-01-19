(function ($) {
    const OPLOG = window.OPLOG || {};
    const routes = OPLOG.routes || {};

    const $listContainer = $('#operationLogsListContainer');
    const $searchBtn = $('#searchBtn');
    const $clearSearchBtn = $('#clearSearchBtn');
    const $countSpan = $('#operationLogsCount');
    const $operatorFilter = $('#operatorFilter');
    const $generatorFilter = $('#generatorFilter');
    const $generationUnitFilter = $('#generationUnitFilter');
    const $dateFromFilter = $('#dateFromFilter');
    const $dateToFilter = $('#dateToFilter');
    const $groupByGeneratorToggle = $('#groupByGeneratorToggle');
    
    // Advanced filters - select مشترك للعمليات
    const $commonOperator = $('#commonOperator');
    const $loadPercentageValue = $('#loadPercentageValue');
    const $fuelConsumedValue = $('#fuelConsumedValue');
    const $energyProducedValue = $('#energyProducedValue');
    
    // Get the container with loading overlay (the card-body)
    const $loadingContainer = $listContainer.closest('.data-table-container');

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || $('#csrfToken').val();
    }

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfToken() }
    });

    function setLoading(on) {
        if (typeof window.DataTableLoading !== 'undefined') {
            if (on) {
                window.DataTableLoading.show($loadingContainer);
            } else {
                window.DataTableLoading.hide($loadingContainer);
            }
        }
    }

    function flash(type, msg) {
        if (typeof window.showToast === 'function') {
            const toastType = type === 'danger' ? 'danger' : type === 'warning' ? 'warning' : type === 'info' ? 'info' : 'success';
            window.showToast(msg, toastType);
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

    function deleteOperationLogUrl(operationLogId) {
        return routes.delete.replace('__ID__', operationLogId);
    }

    function loadOperationLogs(params = {}) {
        setLoading(true);

        // Add AJAX header to request
        $.ajax({
            url: routes.search,
            method: 'GET',
            data: Object.assign({}, params, { ajax: 1 }),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .done(res => {
                if (res.success && res.html) {
                    $listContainer.html(res.html);
                    if (res.count !== undefined) {
                        $countSpan.text(res.count);
                    }
                } else {
                    flash('warning', 'لم يتم العثور على نتائج');
                }
            })
            .fail(xhr => {
                let errorMsg = 'حدث خطأ أثناء تحميل سجلات التشغيل';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMsg = errors.join(' | ');
                    }
                } else if (xhr.status === 403) {
                    errorMsg = 'ليس لديك صلاحية لعرض سجلات التشغيل';
                } else if (xhr.status === 500) {
                    errorMsg = 'حدث خطأ في الخادم، يرجى المحاولة لاحقاً';
                }
                flash('danger', errorMsg);
            })
            .always(() => setLoading(false));
    }

    // Search functionality - البحث فقط عند الضغط على زر البحث
    function runSearch() {
        // الحصول على operatorId - قد يكون select أو hidden input
        let operatorId = '';
        if ($operatorFilter.length && $operatorFilter.is('select')) {
            operatorId = $operatorFilter.val();
        } else {
            // للمشغل/الموظف: استخدام hidden input
            const $operatorFilterHidden = $('#operatorFilterHidden');
            if ($operatorFilterHidden.length) {
                operatorId = $operatorFilterHidden.val();
            }
        }
        
        const generatorId = $generatorFilter.length ? $generatorFilter.val() : '';
        const generationUnitId = $generationUnitFilter.length ? $generationUnitFilter.val() : '';
        const dateFrom = $dateFromFilter.length ? $dateFromFilter.val() : '';
        const dateTo = $dateToFilter.length ? $dateToFilter.val() : '';
        const groupByGenerator = $groupByGeneratorToggle.length && $groupByGeneratorToggle.is(':checked');

        // Advanced filters - استخدام select مشترك للعمليات
        const commonOperatorValue = $commonOperator.length ? $commonOperator.val() : '';
        const loadPercentageValue = $loadPercentageValue.length ? $loadPercentageValue.val() : '';
        const fuelConsumedValue = $fuelConsumedValue.length ? $fuelConsumedValue.val() : '';
        const energyProducedValue = $energyProducedValue.length ? $energyProducedValue.val() : '';

        // التحقق من وجود المشغل ووحدة التوليد على الأقل
        if (!operatorId || operatorId == '0' || !generationUnitId || generationUnitId == '0') {
            if (typeof window.showToast === 'function') {
                window.showToast('يرجى اختيار المشغل ووحدة التوليد على الأقل', 'warning');
            } else {
                alert('يرجى اختيار المشغل ووحدة التوليد على الأقل');
            }
            return;
        }

        const params = {};
        params.operator_id = operatorId;
        params.generation_unit_id = generationUnitId;
        if (generatorId) params.generator_id = generatorId;
        if (dateFrom) params.date_from = dateFrom;
        if (dateTo) params.date_to = dateTo;
        if (groupByGenerator) params.group_by_generator = 1;

        // Advanced filters
        if (loadPercentageValue && commonOperatorValue) {
            params.load_percentage_value = loadPercentageValue;
            params.load_percentage_operator = commonOperatorValue;
        }
        
        if (fuelConsumedValue && commonOperatorValue) {
            params.fuel_consumed_value = fuelConsumedValue;
            params.fuel_consumed_operator = commonOperatorValue;
        }
        
        if (energyProducedValue && commonOperatorValue) {
            params.energy_produced_value = energyProducedValue;
            params.energy_produced_operator = commonOperatorValue;
        }

        loadOperationLogs(params);
    }

    $searchBtn.on('click', runSearch);

    $clearSearchBtn.on('click', function () {
        if ($operatorFilter.length) $operatorFilter.val('');
        if ($generatorFilter.length) $generatorFilter.val('');
        if ($generationUnitFilter.length) $generationUnitFilter.val('');
        if ($dateFromFilter.length) $dateFromFilter.val('');
        if ($dateToFilter.length) $dateToFilter.val('');
        
        // Clear advanced filters
        if ($loadPercentageValue.length) $loadPercentageValue.val('');
        if ($fuelConsumedValue.length) $fuelConsumedValue.val('');
        if ($energyProducedValue.length) $energyProducedValue.val('');
        if ($commonOperator.length) $commonOperator.val('equals');
        
        if ($groupByGeneratorToggle.length) $groupByGeneratorToggle.prop('checked', false);
        
        $clearSearchBtn.addClass('d-none');
        // عند إلغاء الفلاتر، إعادة تحميل القائمة بدون فلاتر
        loadOperationLogs({});
    });

    // لا يوجد بحث تلقائي - البحث فقط عند الضغط على زر البحث

    // Function to get current filter values
    function getCurrentFilters() {
        // الحصول على operatorId - قد يكون select أو hidden input
        let operatorId = '';
        if ($operatorFilter.length && $operatorFilter.is('select')) {
            operatorId = $operatorFilter.val();
        } else {
            // للمشغل/الموظف: استخدام hidden input
            const $operatorFilterHidden = $('#operatorFilterHidden');
            if ($operatorFilterHidden.length) {
                operatorId = $operatorFilterHidden.val();
            }
        }
        
        const generatorId = $generatorFilter.length ? $generatorFilter.val() : '';
        const generationUnitId = $generationUnitFilter.length ? $generationUnitFilter.val() : '';
        const dateFrom = $dateFromFilter.length ? $dateFromFilter.val() : '';
        const dateTo = $dateToFilter.length ? $dateToFilter.val() : '';
        const groupByGenerator = $groupByGeneratorToggle.length && $groupByGeneratorToggle.is(':checked');

        // Advanced filters - استخدام select مشترك للعمليات
        const commonOperatorValue = $commonOperator.length ? $commonOperator.val() : '';
        const loadPercentageValue = $loadPercentageValue.length ? $loadPercentageValue.val() : '';
        const fuelConsumedValue = $fuelConsumedValue.length ? $fuelConsumedValue.val() : '';
        const energyProducedValue = $energyProducedValue.length ? $energyProducedValue.val() : '';

        const params = {};
        if (operatorId && operatorId != '0') params.operator_id = operatorId;
        if (generationUnitId && generationUnitId != '0') params.generation_unit_id = generationUnitId;
        if (generatorId && generatorId != '0') params.generator_id = generatorId;
        if (dateFrom) params.date_from = dateFrom;
        if (dateTo) params.date_to = dateTo;
        if (groupByGenerator) params.group_by_generator = 1;

        // Advanced filters
        if (loadPercentageValue && commonOperatorValue) {
            params.load_percentage_value = loadPercentageValue;
            params.load_percentage_operator = commonOperatorValue;
        }
        if (fuelConsumedValue && commonOperatorValue) {
            params.fuel_consumed_value = fuelConsumedValue;
            params.fuel_consumed_operator = commonOperatorValue;
        }
        if (energyProducedValue && commonOperatorValue) {
            params.energy_produced_value = energyProducedValue;
            params.energy_produced_operator = commonOperatorValue;
        }

        return params;
    }

    // Delete functionality
    $(document).on('click', '.log-delete-btn', function () {
        const $btn = $(this);
        const operationLogId = $btn.data('operation-log-id');
        const operationLogName = $btn.data('operation-log-name') || 'هذا السجل';

        if (!confirm(`هل أنت متأكد من حذف سجل التشغيل "${operationLogName}"؟\n\nهذا الإجراء لا يمكن التراجع عنه.`)) {
            return;
        }

        const deleteUrl = deleteOperationLogUrl(operationLogId);
        const currentFilters = getCurrentFilters(); // حفظ الفلاتر الحالية

        $.ajax({
            url: deleteUrl,
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .done(res => {
                if (res.success) {
                    flash('success', res.message || 'تم حذف سجل التشغيل بنجاح');
                    // إعادة تحميل النتائج مع نفس الفلاتر
                    loadOperationLogs(currentFilters);
                } else {
                    flash('danger', res.message || 'حدث خطأ أثناء حذف سجل التشغيل');
                }
            })
            .fail(xhr => {
                let errorMsg = 'حدث خطأ أثناء حذف سجل التشغيل';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status === 403) {
                    errorMsg = 'ليس لديك صلاحية لحذف سجل التشغيل';
                } else if (xhr.status === 404) {
                    errorMsg = 'سجل التشغيل غير موجود';
                }
                flash('danger', errorMsg);
            });
    });

    // Pagination links
    $(document).on('click', '.log-pagination a', function (e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (!url) return;

        const urlObj = new URL(url, window.location.origin);
        const params = {};
        urlObj.searchParams.forEach((value, key) => {
            params[key] = value;
        });

        // Preserve toggle states
        if ($groupByGeneratorToggle.length && $groupByGeneratorToggle.is(':checked')) {
            params.group_by_generator = 1;
        }

        loadOperationLogs(params);

        // Scroll to top
        $('html, body').animate({ scrollTop: $listContainer.offset().top - 100 }, 300);
    });

    // Export to window for global access
    window.OPLOG = OPLOG;
    window.OPLOG.loadOperationLogs = loadOperationLogs;

})(jQuery);




