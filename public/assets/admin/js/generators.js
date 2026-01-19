(function ($) {
    const GEN = window.GEN || {};
    const routes = GEN.routes || {};

    const $loadingOverlay = $('#generatorsLoadingOverlay');
    const $listContainer = $('#generatorsListContainer');
    const $searchInput = $('#searchInput');
    const $searchBtn = $('#searchBtn');
    const $clearSearchBtn = $('#clearSearchBtn');
    const $countSpan = $('#generatorsCount');
    const $operatorFilter = $('#operatorFilter');
    const $statusFilter = $('#statusFilter');

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || $('#csrfToken').val();
    }

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfToken() }
    });

    function setLoading(on) {
        $loadingOverlay.toggle(!!on);
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

    function deleteGeneratorUrl(generatorId) {
        return routes.delete.replace('__ID__', generatorId);
    }

    function loadGenerators(params = {}) {
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
                let errorMsg = 'حدث خطأ أثناء تحميل المولدات';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMsg = errors.join(' | ');
                    }
                } else if (xhr.status === 403) {
                    errorMsg = 'ليس لديك صلاحية لعرض المولدات';
                } else if (xhr.status === 500) {
                    errorMsg = 'حدث خطأ في الخادم، يرجى المحاولة لاحقاً';
                }
                flash('danger', errorMsg);
            })
            .always(() => setLoading(false));
    }

    // Search functionality
    const runSearch = debounce(function () {
        const term = ($searchInput.val() || '').trim();
        const statusId = $statusFilter.length ? $statusFilter.val() : '';
        const operatorId = $operatorFilter.length ? $operatorFilter.val() : '';

        const params = {};
        if (term) params.q = term;
        if (statusId) params.status_id = statusId;
        if (operatorId) params.operator_id = operatorId;

        loadGenerators(params);
    }, 350);

    $searchBtn.on('click', runSearch);
    
    // Search on Enter key
    $searchInput.on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            runSearch();
        }
    });

    $clearSearchBtn.on('click', function () {
        $searchInput.val('');
        if ($statusFilter.length) $statusFilter.val('');
        if ($operatorFilter.length) $operatorFilter.val('');
        // Update clear button visibility
        $clearSearchBtn.addClass('d-none');
        runSearch();
    });

    // Update clear button visibility based on filters
    function updateClearButtonVisibility() {
        const hasFilters = ($searchInput.val() || '').trim() || 
                          ($statusFilter.length && $statusFilter.val()) || 
                          ($operatorFilter.length && $operatorFilter.val());
        $clearSearchBtn.toggleClass('d-none', !hasFilters);
    }

    // Watch for changes in filters
    $searchInput.on('input', updateClearButtonVisibility);
    
    // Status filter change
    if ($statusFilter.length) {
        $statusFilter.on('change', function() {
            updateClearButtonVisibility();
            runSearch();
        });
    }

    // Operator filter change
    if ($operatorFilter.length) {
        $operatorFilter.on('change', function() {
            updateClearButtonVisibility();
            runSearch();
        });
    }

    // Delete generator
    $(document).on('click', '.gen-delete-btn', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const generatorId = $btn.data('generator-id');
        const generatorName = $btn.data('generator-name');

        if (!confirm(`هل أنت متأكد من حذف المولد "${generatorName}"؟\nهذا الإجراء لا يمكن التراجع عنه.`)) {
            return;
        }

        const $row = $btn.closest('.gen-row');
        $row.fadeOut(200);

        $.ajax({
            url: deleteGeneratorUrl(generatorId),
            method: 'DELETE',
            dataType: 'json'
        })
            .done(res => {
                flash('success', res.message || 'تم حذف المولد بنجاح');
                
                // Reload the list
                const term = ($searchInput.val() || '').trim();
                const statusId = $statusFilter.length ? $statusFilter.val() : '';
                const operatorId = $operatorFilter.length ? $operatorFilter.val() : '';

                const params = {};
                if (term) params.q = term;
                if (statusId) params.status_id = statusId;
                if (operatorId) params.operator_id = operatorId;

                loadGenerators(params);
            })
            .fail(xhr => {
                $row.fadeIn(200);
                let errorMsg = 'حدث خطأ أثناء حذف المولد';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMsg = errors.join(' | ');
                    }
                } else if (xhr.status === 403) {
                    errorMsg = 'ليس لديك صلاحية لحذف هذا المولد';
                } else if (xhr.status === 404) {
                    errorMsg = 'المولد غير موجود';
                } else if (xhr.status === 422) {
                    errorMsg = xhr.responseJSON?.message || 'لا يمكن حذف المولد لأنه يحتوي على سجلات مرتبطة';
                } else if (xhr.status === 500) {
                    errorMsg = 'حدث خطأ في الخادم، يرجى المحاولة لاحقاً';
                }
                flash('danger', errorMsg);
            });
    });

    // Pagination links
    $(document).on('click', '.pagination a', function (e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (!url) return;

        const urlObj = new URL(url);
        const params = {};
        urlObj.searchParams.forEach((value, key) => {
            if (key === 'page') {
                params.page = value;
            } else {
                params[key] = value;
            }
        });

        loadGenerators(params);

        // Scroll to top
        $('html, body').animate({ scrollTop: $listContainer.offset().top - 100 }, 300);
    });

})(jQuery);

