/**
 * Generators Form - Shared JavaScript
 * Used by both create.blade.php and edit.blade.php
 *
 * Requires: jQuery, Bootstrap 5, Select2, flatpickr
 * Config via window.generatorFormConfig = { isEdit, csrfRefreshUrl, redirectUrl, generateNumberUrl, isSuperAdmin }
 */
(function () {
    'use strict';

    const config = window.generatorFormConfig || {};

    document.addEventListener('DOMContentLoaded', function () {
        // ─── Select2 ──────────────────────────────────
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('.select2').select2({
                dir: 'rtl',
                language: 'ar',
                allowClear: true,
                width: '100%'
            });
        }

        // ─── Tab navigation state ─────────────────────
        let currentTab = 0;
        const tabs = ['basic', 'specs', 'fuel', 'technical', 'control'];

        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');

            if (prevBtn) prevBtn.style.display = currentTab === 0 ? 'none' : 'inline-block';
            if (nextBtn) nextBtn.style.display = currentTab === tabs.length - 1 ? 'none' : 'inline-block';
            if (submitBtn) submitBtn.style.display = currentTab === tabs.length - 1 ? 'inline-block' : 'none';
        }

        window.navigateTabs = function (direction) {
            currentTab += direction;
            if (currentTab < 0) currentTab = 0;
            if (currentTab >= tabs.length) currentTab = tabs.length - 1;

            const tabButton = document.getElementById(tabs[currentTab] + '-tab');
            if (tabButton) {
                const tab = new bootstrap.Tab(tabButton);
                tab.show();
            }
            updateNavigationButtons();
        };

        document.querySelectorAll('#generatorTabs button[data-bs-toggle="tab"]').forEach(function (tabButton, index) {
            tabButton.addEventListener('shown.bs.tab', function () {
                currentTab = index;
                updateNavigationButtons();
            });
            tabButton.addEventListener('click', function () {
                var tabId = this.getAttribute('data-bs-target');
                if (tabId) {
                    var tabName = tabId.replace('#', '');
                    var tabIndex = tabs.indexOf(tabName);
                    if (tabIndex !== -1) currentTab = tabIndex;
                }
            });
        });

        updateNavigationButtons();

        // ─── Session timer & CSRF refresh ─────────────
        let lastRefreshTime = Date.now();
        const refreshInterval = 10 * 60 * 1000;

        function updateSessionTimer() {
            var timePassed = Date.now() - lastRefreshTime;
            var timeRemaining = Math.max(0, refreshInterval - timePassed);
            var minutesRemaining = Math.ceil(timeRemaining / 60000);

            var timerBadge = document.getElementById('sessionTimer');
            if (!timerBadge) return;

            if (minutesRemaining > 5) {
                timerBadge.className = 'badge bg-success ms-2';
                timerBadge.textContent = '\u0646\u0634\u0637 - \u0627\u0644\u062A\u062D\u062F\u064A\u062B \u0628\u0639\u062F ' + minutesRemaining + ' \u062F\u0642\u064A\u0642\u0629';
            } else if (minutesRemaining > 2) {
                timerBadge.className = 'badge bg-warning ms-2';
                timerBadge.textContent = '\u0627\u0644\u062A\u062D\u062F\u064A\u062B \u0628\u0639\u062F ' + minutesRemaining + ' \u062F\u0642\u0627\u0626\u0642';
            } else if (minutesRemaining > 0) {
                timerBadge.className = 'badge bg-danger ms-2';
                timerBadge.textContent = '\u0627\u0644\u062A\u062D\u062F\u064A\u062B \u0642\u0631\u064A\u0628\u0627\u064B (' + minutesRemaining + ' \u062F\u0642\u064A\u0642\u0629)';
            }
        }

        setInterval(updateSessionTimer, 30000);
        updateSessionTimer();

        function refreshCSRFToken() {
            if (!config.csrfRefreshUrl) return;
            fetch(config.csrfRefreshUrl, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (response) {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var newToken = doc.querySelector('meta[name="csrf-token"]') || doc.querySelector('input[name="_token"]');
                if (newToken) {
                    var tokenValue = newToken.getAttribute('content') || newToken.value;
                    var tokenInput = document.querySelector('input[name="_token"]');
                    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    if (tokenInput) tokenInput.value = tokenValue;
                    if (tokenMeta) tokenMeta.setAttribute('content', tokenValue);
                    lastRefreshTime = Date.now();
                    updateSessionTimer();

                    var timerBadge = document.getElementById('sessionTimer');
                    if (timerBadge) {
                        timerBadge.className = 'badge bg-success ms-2';
                        timerBadge.textContent = '\u2713 \u062A\u0645 \u0627\u0644\u062A\u062D\u062F\u064A\u062B';
                        setTimeout(updateSessionTimer, 2000);
                    }
                }
            })
            .catch(function (error) {
                console.error('Error refreshing CSRF token:', error);
                var timerBadge = document.getElementById('sessionTimer');
                if (timerBadge) {
                    timerBadge.className = 'badge bg-danger ms-2';
                    timerBadge.textContent = '\u26A0 \u062E\u0637\u0623 \u0641\u064A \u0627\u0644\u062A\u062D\u062F\u064A\u062B';
                }
            });
        }

        setInterval(refreshCSRFToken, refreshInterval);

        // ─── Form modification warning ────────────────
        let formModified = false;
        document.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.addEventListener('change', function () { formModified = true; });
        });
        window.addEventListener('beforeunload', function (e) {
            if (formModified) { e.preventDefault(); e.returnValue = ''; }
        });

        // ─── AJAX form submission ─────────────────────
        var $form = $('#generatorForm');
        var $submitBtn = $('#submitBtn');

        var fieldLabels = {
            'name': '\u0627\u0633\u0645 \u0627\u0644\u0645\u0648\u0644\u062F',
            'generator_number': '\u0631\u0642\u0645 \u0627\u0644\u0645\u0648\u0644\u062F',
            'operator_id': '\u0627\u0644\u0645\u0634\u063A\u0644',
            'generation_unit_id': '\u0648\u062D\u062F\u0629 \u0627\u0644\u062A\u0648\u0644\u064A\u062F',
            'status': '\u062D\u0627\u0644\u0629 \u0627\u0644\u0645\u0648\u0644\u062F',
            'description': '\u0627\u0644\u0648\u0635\u0641',
            'capacity_kva': '\u0642\u062F\u0631\u0629 \u0627\u0644\u0645\u0648\u0644\u062F (KVA)',
            'power_factor': '\u0645\u0639\u0627\u0645\u0644 \u0627\u0644\u0642\u062F\u0631\u0629 (P.F)',
            'voltage': '\u0627\u0644\u062C\u0647\u062F \u0627\u0644\u0646\u0627\u062A\u062C (V)',
            'frequency': '\u0627\u0644\u062A\u0631\u062F\u062F (Hz)',
            'engine_type': '\u0646\u0648\u0639 \u0627\u0644\u0645\u062D\u0631\u0643',
            'manufacturing_year': '\u0633\u0646\u0629 \u0627\u0644\u062A\u0635\u0646\u064A\u0639',
            'injection_system': '\u0646\u0638\u0627\u0645 \u0627\u0644\u062D\u0642\u0646',
            'fuel_consumption_rate': '\u0645\u0639\u062F\u0644 \u0627\u0633\u062A\u0647\u0644\u0627\u0643 \u0627\u0644\u0648\u0642\u0648\u062F',
            'ideal_fuel_efficiency': '\u0643\u0641\u0627\u0621\u0629 \u0627\u0644\u0648\u0642\u0648\u062F \u0627\u0644\u0645\u062B\u0627\u0644\u064A\u0629',
            'internal_tank_capacity': '\u0633\u0639\u0629 \u062E\u0632\u0627\u0646 \u0627\u0644\u0648\u0642\u0648\u062F \u0627\u0644\u062F\u0627\u062E\u0644\u064A',
            'measurement_indicator': '\u0645\u0624\u0634\u0631 \u0627\u0644\u0642\u064A\u0627\u0633',
            'technical_condition': '\u0627\u0644\u062D\u0627\u0644\u0629 \u0627\u0644\u0641\u0646\u064A\u0629',
            'last_major_maintenance_date': '\u062A\u0627\u0631\u064A\u062E \u0622\u062E\u0631 \u0635\u064A\u0627\u0646\u0629 \u0643\u0628\u0631\u0649',
            'engine_data_plate_image': '\u0635\u0648\u0631\u0629 \u0644\u0648\u062D\u0629 \u0627\u0644\u0628\u064A\u0627\u0646\u0627\u062A \u0644\u0644\u0645\u062D\u0631\u0643',
            'generator_data_plate_image': '\u0635\u0648\u0631\u0629 \u0644\u0648\u062D\u0629 \u0627\u0644\u0628\u064A\u0627\u0646\u0627\u062A \u0644\u0644\u0645\u0648\u0644\u062F',
            'control_panel_available': '\u0644\u0648\u062D\u0629 \u0627\u0644\u062A\u062D\u0643\u0645',
            'control_panel_type': '\u0646\u0648\u0639 \u0644\u0648\u062D\u0629 \u0627\u0644\u062A\u062D\u0643\u0645',
            'control_panel_status': '\u062D\u0627\u0644\u0629 \u0644\u0648\u062D\u0629 \u0627\u0644\u062A\u062D\u0643\u0645',
            'control_panel_image': '\u0635\u0648\u0631\u0629 \u0644\u0648\u062D\u0629 \u0627\u0644\u062A\u062D\u0643\u0645',
            'operating_hours': '\u0642\u0631\u0627\u0621\u0629 \u0633\u0627\u0639\u0627\u062A \u0627\u0644\u062A\u0634\u063A\u064A\u0644 \u0627\u0644\u062D\u0627\u0644\u064A\u0629'
        };

        $form.on('submit', function (e) {
            e.preventDefault();
            formModified = false;

            // Remove HTML5 validation from hidden tabs to avoid "not focusable" issue
            var hiddenTabs = document.querySelectorAll('.tab-pane:not(.show)');
            var hiddenInputs = [];

            hiddenTabs.forEach(function (tab) {
                tab.querySelectorAll('input[type="number"], input[type="text"], input[type="date"]').forEach(function (input) {
                    var constraints = {
                        min: input.getAttribute('min'),
                        max: input.getAttribute('max'),
                        required: input.hasAttribute('required')
                    };
                    if (constraints.min || constraints.max || constraints.required) {
                        hiddenInputs.push({ input: input, constraints: constraints });
                        if (constraints.min) input.removeAttribute('min');
                        if (constraints.max) input.removeAttribute('max');
                        if (constraints.required) input.removeAttribute('required');
                    }
                });
            });

            if (!$form[0].checkValidity()) {
                hiddenInputs.forEach(function (item) {
                    if (item.constraints.min) item.input.setAttribute('min', item.constraints.min);
                    if (item.constraints.max) item.input.setAttribute('max', item.constraints.max);
                    if (item.constraints.required) item.input.setAttribute('required', 'required');
                });
                $form[0].reportValidity();
                return false;
            }

            hiddenInputs.forEach(function (item) {
                if (item.constraints.min) item.input.setAttribute('min', item.constraints.min);
                if (item.constraints.max) item.input.setAttribute('max', item.constraints.max);
                if (item.constraints.required) item.input.setAttribute('required', 'required');
            });

            $submitBtn.prop('disabled', true);
            var originalText = $submitBtn.html();
            $submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>\u062C\u0627\u0631\u064A \u0627\u0644\u062D\u0641\u0638...');

            var formData = new FormData(this);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function (response) {
                    if (response.success) {
                        var msg = response.message || (config.isEdit ? '\u062A\u0645 \u062A\u062D\u062F\u064A\u062B \u0627\u0644\u0645\u0648\u0644\u062F \u0628\u0646\u062C\u0627\u062D' : '\u062A\u0645 \u0625\u0646\u0634\u0627\u0621 \u0627\u0644\u0645\u0648\u0644\u062F \u0628\u0646\u062C\u0627\u062D');
                        if (typeof window.showToast === 'function') {
                            window.showToast(msg, 'success');
                        } else {
                            alert(msg);
                        }
                        setTimeout(function () {
                            window.location.href = config.redirectUrl;
                        }, 500);
                    }
                },
                error: function (xhr) {
                    $submitBtn.prop('disabled', false);
                    $submitBtn.html(originalText);

                    if (xhr.status === 422) {
                        var errors = (xhr.responseJSON && xhr.responseJSON.errors) || {};
                        var errorMessages = [];

                        $form.find('.is-invalid').removeClass('is-invalid');
                        $form.find('.invalid-feedback').remove();

                        $.each(errors, function (field, messages) {
                            var errorMsg = Array.isArray(messages) ? messages[0] : messages;
                            var fieldLabel = fieldLabels[field] || field;
                            errorMessages.push(fieldLabel + ': ' + errorMsg);

                            var $field = $form.find('[name="' + field + '"]');
                            if ($field.length) {
                                $field.addClass('is-invalid');
                                $field.closest('.col-md-6, .col-md-4, .col-md-12, .col-md-3').find('.invalid-feedback').remove();
                                $field.after('<div class="invalid-feedback d-block">' + errorMsg + '</div>');
                            }
                        });

                        var $firstError = $form.find('.is-invalid').first();
                        if ($firstError.length) {
                            $('html, body').animate({ scrollTop: $firstError.offset().top - 100 }, 500);
                        }

                        var errorMessage = '\u064A\u0631\u062C\u0649 \u062A\u0635\u062D\u064A\u062D \u0627\u0644\u0623\u062E\u0637\u0627\u0621 \u0627\u0644\u062A\u0627\u0644\u064A\u0629:\n\n' + errorMessages.join('\n');
                        if (typeof window.showToast === 'function') {
                            window.showToast(errorMessage, 'error', '\u062A\u062D\u0642\u0642 \u0645\u0646 \u0627\u0644\u0623\u062E\u0637\u0627\u0621');
                        } else if (window.adminNotifications && typeof window.adminNotifications.error === 'function') {
                            window.adminNotifications.error(errorMessage, '\u062A\u062D\u0642\u0642 \u0645\u0646 \u0627\u0644\u0623\u062E\u0637\u0627\u0621');
                        } else {
                            console.error('Validation errors:', errorMessages);
                        }
                    } else {
                        var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) || '\u062D\u062F\u062B \u062E\u0637\u0623 \u0623\u062B\u0646\u0627\u0621 \u062D\u0641\u0638 \u0627\u0644\u0628\u064A\u0627\u0646\u0627\u062A';
                        if (typeof window.showToast === 'function') {
                            window.showToast(errorMsg, 'error');
                        } else if (window.adminNotifications && typeof window.adminNotifications.error === 'function') {
                            window.adminNotifications.error(errorMsg);
                        } else {
                            console.error('Error:', errorMsg);
                        }
                    }
                }
            });
        });

        // ─── Restore saved form data ──────────────────
        var savedFormData = localStorage.getItem('generator_form_backup');
        if (savedFormData) {
            try {
                var data = JSON.parse(savedFormData);
                Object.keys(data).forEach(function (key) {
                    var field = document.querySelector('[name="' + key + '"]');
                    if (field && !field.value) field.value = data[key];
                });

                var alertEl = document.createElement('div');
                alertEl.className = 'alert alert-success alert-dismissible fade show';
                alertEl.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i><strong>\u062A\u0645 \u0627\u0633\u062A\u0639\u0627\u062F\u0629 \u0627\u0644\u0628\u064A\u0627\u0646\u0627\u062A \u0628\u0646\u062C\u0627\u062D!</strong> \u0627\u0644\u0628\u064A\u0627\u0646\u0627\u062A \u0627\u0644\u062A\u064A \u0623\u062F\u062E\u0644\u062A\u0647\u0627 \u0633\u0627\u0628\u0642\u0627\u064B \u062A\u0645 \u0627\u0633\u062A\u0639\u0627\u062F\u062A\u0647\u0627.<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                var formEl = document.querySelector('form');
                if (formEl) formEl.insertBefore(alertEl, formEl.firstChild);

                localStorage.removeItem('generator_form_backup');
            } catch (ex) {
                console.error('Error restoring form data:', ex);
                localStorage.removeItem('generator_form_backup');
            }
        }

        // ─── Image preview ────────────────────────────
        document.querySelectorAll('.image-input').forEach(function (input) {
            input.addEventListener('change', function () {
                var previewId = this.getAttribute('data-preview');
                var previewContainer = document.getElementById(previewId);
                var file = this.files[0];

                if (file) {
                    if (!file.type.startsWith('image/')) {
                        if (window.adminNotifications) window.adminNotifications.error('\u064A\u0631\u062C\u0649 \u0627\u062E\u062A\u064A\u0627\u0631 \u0645\u0644\u0641 \u0635\u0648\u0631\u0629 \u0635\u0627\u0644\u062D', '\u062E\u0637\u0623');
                        this.value = '';
                        previewContainer.style.display = 'none';
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        if (window.adminNotifications) window.adminNotifications.error('\u062D\u062C\u0645 \u0627\u0644\u0635\u0648\u0631\u0629 \u0643\u0628\u064A\u0631 \u062C\u062F\u0627\u064B. \u064A\u0631\u062C\u0649 \u0627\u062E\u062A\u064A\u0627\u0631 \u0635\u0648\u0631\u0629 \u0623\u0642\u0644 \u0645\u0646 5 \u0645\u064A\u062C\u0627\u0628\u0627\u064A\u062A', '\u062E\u0637\u0623');
                        this.value = '';
                        previewContainer.style.display = 'none';
                        return;
                    }

                    var reader = new FileReader();
                    reader.onload = function (e) {
                        var img = previewContainer.querySelector('.image-preview');
                        img.src = e.target.result;
                        previewContainer.style.display = 'block';
                        previewContainer.style.opacity = '0';
                        setTimeout(function () {
                            previewContainer.style.transition = 'opacity 0.3s ease';
                            previewContainer.style.opacity = '1';
                        }, 10);
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.style.display = 'none';
                }
            });
        });

        window.removeImagePreview = function (inputName, previewId) {
            var input = document.querySelector('input[name="' + inputName + '"]');
            var previewContainer = document.getElementById(previewId);

            if (input) input.value = '';
            if (previewContainer) {
                previewContainer.style.transition = 'opacity 0.3s ease';
                previewContainer.style.opacity = '0';
                setTimeout(function () {
                    previewContainer.style.display = 'none';
                    var img = previewContainer.querySelector('.image-preview');
                    if (img) img.src = '';
                    previewContainer.style.opacity = '1';
                }, 300);
            }
        };

        // ─── Year picker ──────────────────────────────
        var manufacturingYearInput = document.getElementById('manufacturing_year');
        if (manufacturingYearInput) {
            (function initYearPicker() {
                if (typeof flatpickr === 'undefined') { setTimeout(initYearPicker, 100); return; }
                var currentYear = new Date().getFullYear();
                var defaultYear = manufacturingYearInput.value || null;

                flatpickr(manufacturingYearInput, {
                    dateFormat: 'Y',
                    mode: 'single',
                    minDate: '1900-01-01',
                    maxDate: new Date(),
                    locale: 'ar',
                    allowInput: true,
                    defaultDate: defaultYear ? defaultYear + '-01-01' : null,
                    onChange: function (selectedDates, dateStr) {
                        if (dateStr) {
                            var year = parseInt(dateStr.split('-')[0]);
                            manufacturingYearInput.value = (year >= 1900 && year <= currentYear) ? year : '';
                        }
                    },
                    onReady: function (selectedDates, dateStr, instance) {
                        var monthNav = instance.calendarContainer.querySelector('.flatpickr-month');
                        if (monthNav) monthNav.style.cursor = 'pointer';
                    }
                });
            })();
        }

        // ─── Cascading: operator → generation units (create only, super admin) ───
        var operatorSelect = document.getElementById('operator_id');
        var generationUnitSelect = document.getElementById('generation_unit_id');
        var generationUnitHelp = document.getElementById('generation_unit_help');
        var generatorNumberInput = document.getElementById('generator_number');

        if (operatorSelect && generationUnitSelect) {
            $(operatorSelect).on('change', async function () {
                var operatorId = $(this).val();
                generationUnitSelect.innerHTML = '<option value="">\u0627\u062E\u062A\u0631 \u0648\u062D\u062F\u0629 \u0627\u0644\u062A\u0648\u0644\u064A\u062F</option>';
                generationUnitSelect.disabled = !operatorId;
                if (!config.isEdit) generatorNumberInput.value = '';

                if (!operatorId) {
                    if (generationUnitHelp) generationUnitHelp.textContent = '\u064A\u062C\u0628 \u0627\u062E\u062A\u064A\u0627\u0631 \u0627\u0644\u0645\u0634\u063A\u0644 \u0623\u0648\u0644\u0627\u064B \u0644\u0639\u0631\u0636 \u0648\u062D\u062F\u0627\u062A \u0627\u0644\u062A\u0648\u0644\u064A\u062F';
                    return;
                }

                try {
                    var response = await fetch('/admin/operators/' + operatorId + '/generation-units', {
                        method: 'GET',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    var data = await response.json();

                    if (data.success && data.generation_units && data.generation_units.length > 0) {
                        var urlParams = new URLSearchParams(window.location.search);
                        var generationUnitIdFromUrl = urlParams.get('generation_unit_id');
                        var availableCount = 0, fullCount = 0, urlUnitFull = false;

                        data.generation_units.forEach(function (unit) {
                            var option = document.createElement('option');
                            option.value = unit.id;
                            option.textContent = unit.label;
                            option.dataset.available = unit.available;
                            if (!unit.available) {
                                option.disabled = true;
                                option.textContent += ' (\u0645\u0645\u062A\u0644\u0626\u0629)';
                                fullCount++;
                            } else {
                                availableCount++;
                            }
                            if (generationUnitIdFromUrl && generationUnitIdFromUrl == unit.id) {
                                if (unit.available) option.selected = true;
                                else urlUnitFull = true;
                            }
                            generationUnitSelect.appendChild(option);
                        });

                        if (window.adminNotifications) {
                            if (availableCount === 0 && fullCount > 0) {
                                window.adminNotifications.error('\u062C\u0645\u064A\u0639 \u0648\u062D\u062F\u0627\u062A \u0627\u0644\u062A\u0648\u0644\u064A\u062F \u0645\u0645\u062A\u0644\u0626\u0629. \u064A\u062C\u0628 \u0632\u064A\u0627\u062F\u0629 \u0639\u062F\u062F \u0627\u0644\u0645\u0648\u0644\u062F\u0627\u062A \u0627\u0644\u0645\u0633\u0645\u0648\u062D \u0645\u0646 \u0625\u0639\u062F\u0627\u062F\u0627\u062A \u0627\u0644\u0648\u062D\u062F\u0629.');
                            } else if (urlUnitFull && availableCount > 0) {
                                window.adminNotifications.warning('\u0627\u0644\u0648\u062D\u062F\u0629 \u0627\u0644\u0645\u062D\u062F\u062F\u0629 \u0645\u0645\u062A\u0644\u0626\u0629. \u0627\u062E\u062A\u0631 \u0648\u062D\u062F\u0629 \u0623\u062E\u0631\u0649 \u0645\u062A\u0627\u062D\u0629.');
                            }
                        }

                        if ($.fn.select2 && $(generationUnitSelect).data('select2')) {
                            $(generationUnitSelect).trigger('change.select2');
                        }

                        if (generationUnitIdFromUrl && $(generationUnitSelect).val() == generationUnitIdFromUrl) {
                            $(generationUnitSelect).trigger('change');
                        }

                        if (generationUnitHelp) {
                            generationUnitHelp.textContent = availableCount > 0
                                ? (availableCount + ' \u0648\u062D\u062F\u0629 \u0645\u062A\u0627\u062D\u0629' + (fullCount > 0 ? ' \u00B7 ' + fullCount + ' \u0645\u0645\u062A\u0644\u0626\u0629' : ''))
                                : '\u062C\u0645\u064A\u0639 \u0627\u0644\u0648\u062D\u062F\u0627\u062A \u0645\u0645\u062A\u0644\u0626\u0629';
                        }
                    } else {
                        if (generationUnitHelp) generationUnitHelp.textContent = '\u0644\u0627 \u062A\u0648\u062C\u062F \u0648\u062D\u062F\u0627\u062A \u062A\u0648\u0644\u064A\u062F \u0644\u0647\u0630\u0627 \u0627\u0644\u0645\u0634\u063A\u0644';
                        if (window.adminNotifications) window.adminNotifications.info('\u0647\u0630\u0627 \u0627\u0644\u0645\u0634\u063A\u0644 \u0644\u064A\u0633 \u0644\u062F\u064A\u0647 \u0648\u062D\u062F\u0627\u062A \u062A\u0648\u0644\u064A\u062F. \u0623\u0636\u0641 \u0648\u062D\u062F\u0629 \u062A\u0648\u0644\u064A\u062F \u0623\u0648\u0644\u0627\u064B.');
                    }
                } catch (error) {
                    console.error('Error loading generation units:', error);
                    if (generationUnitHelp) generationUnitHelp.textContent = '\u062D\u062F\u062B \u062E\u0637\u0623 \u0623\u062B\u0646\u0627\u0621 \u062A\u062D\u0645\u064A\u0644 \u0648\u062D\u062F\u0627\u062A \u0627\u0644\u062A\u0648\u0644\u064A\u062F';
                }
            });

            if ($(operatorSelect).val() && !config.isEdit) $(operatorSelect).trigger('change');
        }

        // ─── Auto-generate generator number on unit selection ─────
        if (generationUnitSelect && generatorNumberInput && config.generateNumberUrl) {
            $(generationUnitSelect).on('change', async function () {
                // في وضع التعديل لا نعيد توليد الرقم تلقائياً
                if (config.isEdit && generatorNumberInput.value) return;

                var generationUnitId = $(this).val();
                if (!generationUnitId) { if (!config.isEdit) generatorNumberInput.value = ''; return; }

                var selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.dataset.available === 'false' && !config.isEdit) {
                    generatorNumberInput.value = '';
                    alert('\u0647\u0630\u0647 \u0627\u0644\u0648\u062D\u062F\u0629 \u0645\u0645\u062A\u0644\u0626\u0629. \u064A\u0631\u062C\u0649 \u0627\u062E\u062A\u064A\u0627\u0631 \u0648\u062D\u062F\u0629 \u0623\u062E\u0631\u0649.');
                    return;
                }

                try {
                    var csrfToken = document.querySelector('meta[name="csrf-token"]');
                    var response = await fetch(config.generateNumberUrl + '/' + generationUnitId, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });
                    var data = await response.json();
                    generatorNumberInput.value = (data.success && data.generator_number) ? data.generator_number : '';
                    if (!data.success && data.message) console.warn(data.message);
                } catch (error) {
                    console.error('Error generating generator number:', error);
                }
            });

            // Auto-trigger if pre-selected
            if (!config.isEdit) {
                var urlParams = new URLSearchParams(window.location.search);
                var guFromUrl = urlParams.get('generation_unit_id');
                if (generationUnitSelect.value || guFromUrl) {
                    if (guFromUrl && !generationUnitSelect.value) {
                        setTimeout(function () {
                            if (generationUnitSelect.querySelector('option[value="' + guFromUrl + '"]')) {
                                generationUnitSelect.value = guFromUrl;
                                generationUnitSelect.dispatchEvent(new Event('change'));
                            }
                        }, 100);
                    } else if (generationUnitSelect.value) {
                        generationUnitSelect.dispatchEvent(new Event('change'));
                    }
                }
            }
        }

        // ─── Non-super-admin: auto-select from URL ───
        if (!config.isSuperAdmin && !config.isEdit) {
            var urlParamsCompany = new URLSearchParams(window.location.search);
            var guIdFromUrl = urlParamsCompany.get('generation_unit_id');
            var guSelectCompany = document.getElementById('generation_unit_id');
            var gnInputCompany = document.getElementById('generator_number');

            if (guIdFromUrl && guSelectCompany) {
                if (guSelectCompany.querySelector('option[value="' + guIdFromUrl + '"]')) {
                    guSelectCompany.value = guIdFromUrl;
                    if (guSelectCompany.value) guSelectCompany.dispatchEvent(new Event('change'));
                }
            } else if (guSelectCompany && guSelectCompany.value && gnInputCompany && !gnInputCompany.value) {
                guSelectCompany.dispatchEvent(new Event('change'));
            }
        }

        // ─── Calculate ideal fuel efficiency ──────────
        function calculateIdealFuelEfficiency() {
            var capacityKva = parseFloat((document.querySelector('input[name="capacity_kva"]') || {}).value) || 0;
            var powerFactor = parseFloat((document.querySelector('input[name="power_factor"]') || {}).value) || 0;
            var fuelConsumptionRate = parseFloat((document.querySelector('input[name="fuel_consumption_rate"]') || {}).value) || 0;
            var idealEfficiencyInput = document.getElementById('ideal_fuel_efficiency');

            if (idealEfficiencyInput && capacityKva > 0 && powerFactor > 0 && fuelConsumptionRate > 0) {
                idealEfficiencyInput.value = ((capacityKva * powerFactor) / fuelConsumptionRate).toFixed(2);
            } else if (idealEfficiencyInput) {
                idealEfficiencyInput.value = '0.5';
            }
        }

        ['capacity_kva', 'power_factor', 'fuel_consumption_rate'].forEach(function (name) {
            var input = document.querySelector('input[name="' + name + '"]');
            if (input) {
                input.addEventListener('input', calculateIdealFuelEfficiency);
                input.addEventListener('change', calculateIdealFuelEfficiency);
            }
        });

        calculateIdealFuelEfficiency();
    });
})();
