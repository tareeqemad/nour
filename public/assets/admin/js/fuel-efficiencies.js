(function ($) {
    const FUEL_EFF = window.FUEL_EFF || {};
    const routes = FUEL_EFF.routes || {};

    const $listContainer = $('#fuelEfficienciesListContainer');
    const $searchBtn = $('#searchBtn');
    const $clearSearchBtn = $('#clearSearchBtn');
    const $countSpan = $('#fuelEfficienciesCount');
    const $operatorFilter = $('#operatorFilter');
    const $operatorFilterHidden = $('#operatorFilterHidden');
    const $generationUnitFilter = $('#generationUnitFilter');
    const $generatorFilter = $('#generatorFilter');
    const $dateFromFilter = $('#dateFromFilter');
    const $dateToFilter = $('#dateToFilter');
    const $groupByGeneratorToggle = $('#groupByGeneratorToggle');
    
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

    function deleteFuelEfficiencyUrl(fuelEfficiencyId) {
        return routes.delete.replace('__ID__', fuelEfficiencyId);
    }

    function loadFuelEfficiencies(params = {}) {
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
                let errorMsg = 'حدث خطأ أثناء تحميل سجلات كفاءة الوقود';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMsg = errors.join(' | ');
                    }
                } else if (xhr.status === 403) {
                    errorMsg = 'ليس لديك صلاحية لعرض سجلات كفاءة الوقود';
                } else if (xhr.status === 500) {
                    errorMsg = 'حدث خطأ في الخادم، يرجى المحاولة لاحقاً';
                }
                flash('danger', errorMsg);
            })
            .always(() => setLoading(false));
    }

    // Search functionality
    const runSearch = debounce(function () {
        // الحصول على operatorId - قد يكون select أو hidden input
        let operatorId = '';
        if ($operatorFilter.length && $operatorFilter.is('select')) {
            operatorId = $operatorFilter.val();
        } else {
            // للمشغل/الموظف: استخدام hidden input
            if ($operatorFilterHidden.length) {
                operatorId = $operatorFilterHidden.val();
            }
        }
        
        const generationUnitId = $generationUnitFilter.length ? $generationUnitFilter.val() : '';
        const generatorId = $generatorFilter.length ? $generatorFilter.val() : '';
        const dateFrom = $dateFromFilter.length ? $dateFromFilter.val() : '';
        const dateTo = $dateToFilter.length ? $dateToFilter.val() : '';
        const groupByGenerator = $groupByGeneratorToggle.length && $groupByGeneratorToggle.is(':checked');

        const params = {};
        if (operatorId && operatorId != '0') params.operator_id = operatorId;
        if (generationUnitId && generationUnitId != '0') params.generation_unit_id = generationUnitId;
        if (generatorId && generatorId != '0') params.generator_id = generatorId;
        if (dateFrom) params.date_from = dateFrom;
        if (dateTo) params.date_to = dateTo;
        if (groupByGenerator) params.group_by_generator = 1;

        loadFuelEfficiencies(params);
    }, 300);

    $searchBtn.on('click', runSearch);

    $clearSearchBtn.on('click', function () {
        if ($operatorFilter.length && $operatorFilter.is('select')) $operatorFilter.val('0');
        if ($generationUnitFilter.length) $generationUnitFilter.val('0');
        if ($generatorFilter.length) $generatorFilter.val('0');
        if ($dateFromFilter.length) $dateFromFilter.val('');
        if ($dateToFilter.length) $dateToFilter.val('');
        if ($groupByGeneratorToggle.length) $groupByGeneratorToggle.prop('checked', false);
        loadFuelEfficiencies({});
    });

    // Operator filter change
    if ($operatorFilter.length) {
        $operatorFilter.on('change', function() {
            runSearch();
        });
    }

    // Generator filter change
    if ($generatorFilter.length) {
        $generatorFilter.on('change', runSearch);
    }

    // Group by generator toggle
    if ($groupByGeneratorToggle.length) {
        $groupByGeneratorToggle.on('change', runSearch);
    }

    // Date filters change
    if ($dateFromFilter.length) {
        $dateFromFilter.on('change', runSearch);
    }
    if ($dateToFilter.length) {
        $dateToFilter.on('change', runSearch);
    }

    // Function to get current filter values
    function getCurrentFilters() {
        // الحصول على operatorId - قد يكون select أو hidden input
        let operatorId = '';
        if ($operatorFilter.length && $operatorFilter.is('select')) {
            operatorId = $operatorFilter.val();
        } else {
            // للمشغل/الموظف: استخدام hidden input
            if ($operatorFilterHidden.length) {
                operatorId = $operatorFilterHidden.val();
            }
        }
        
        const generationUnitId = $generationUnitFilter.length ? $generationUnitFilter.val() : '';
        const generatorId = $generatorFilter.length ? $generatorFilter.val() : '';
        const dateFrom = $dateFromFilter.length ? $dateFromFilter.val() : '';
        const dateTo = $dateToFilter.length ? $dateToFilter.val() : '';
        const groupByGenerator = $groupByGeneratorToggle.length && $groupByGeneratorToggle.is(':checked');

        const params = {};
        if (operatorId && operatorId != '0') params.operator_id = operatorId;
        if (generationUnitId && generationUnitId != '0') params.generation_unit_id = generationUnitId;
        if (generatorId && generatorId != '0') params.generator_id = generatorId;
        if (dateFrom) params.date_from = dateFrom;
        if (dateTo) params.date_to = dateTo;
        if (groupByGenerator) params.group_by_generator = 1;

        return params;
    }

    // Delete functionality
    $(document).on('click', '.fuel-efficiency-delete-btn', function () {
        const $btn = $(this);
        const fuelEfficiencyId = $btn.data('fuel-efficiency-id');
        const fuelEfficiencyName = $btn.data('fuel-efficiency-name') || 'هذا السجل';

        if (!confirm(`هل أنت متأكد من حذف سجل كفاءة الوقود "${fuelEfficiencyName}"؟\n\nهذا الإجراء لا يمكن التراجع عنه.`)) {
            return;
        }

        const deleteUrl = deleteFuelEfficiencyUrl(fuelEfficiencyId);
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
                    flash('success', res.message || 'تم حذف سجل كفاءة الوقود بنجاح');
                    // إعادة تحميل النتائج مع نفس الفلاتر
                    loadFuelEfficiencies(currentFilters);
                } else {
                    flash('danger', res.message || 'حدث خطأ أثناء حذف سجل كفاءة الوقود');
                }
            })
            .fail(xhr => {
                let errorMsg = 'حدث خطأ أثناء حذف سجل كفاءة الوقود';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status === 403) {
                    errorMsg = 'ليس لديك صلاحية لحذف سجل كفاءة الوقود';
                } else if (xhr.status === 404) {
                    errorMsg = 'سجل كفاءة الوقود غير موجود';
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

        // Preserve group_by_generator toggle state
        if ($groupByGeneratorToggle.length && $groupByGeneratorToggle.is(':checked')) {
            params.group_by_generator = 1;
        }

        loadFuelEfficiencies(params);

        // Scroll to top
        $('html, body').animate({ scrollTop: $listContainer.offset().top - 100 }, 300);
    });

    // Initialize Select2 for all selects
    $('.select2').select2({
        dir: 'rtl',
        language: 'ar',
        allowClear: true,
        width: '100%'
    });

    // Export to window for global access
    window.FUEL_EFF = FUEL_EFF;
    window.FUEL_EFF.loadFuelEfficiencies = loadFuelEfficiencies;

})(jQuery);


