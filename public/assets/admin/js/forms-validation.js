/**
 * Forms Validation Script
 * Handles client-side validation for all admin forms
 */

(function() {
    'use strict';

    /**
     * Validate operation log form
     */
    function validateOperationLogForm(form) {
        let isValid = true;
        const errors = {};

        // Required fields
        const operatorId = form.querySelector('[name="operator_id"]');
        const generatorId = form.querySelector('[name="generator_id"]');
        const operationDate = form.querySelector('[name="operation_date"]');
        const startTime = form.querySelector('[name="start_time"]');
        const endTime = form.querySelector('[name="end_time"]');

        // Clear previous errors
        clearFormErrors(form);

        // Validate operator_id
        if (!operatorId || !operatorId.value) {
            isValid = false;
            showFieldError(operatorId, 'المشغل مطلوب');
        }

        // Validate generator_id
        if (!generatorId || !generatorId.value) {
            isValid = false;
            showFieldError(generatorId, 'المولد مطلوب');
        }

        // Validate operation_date
        if (!operationDate || !operationDate.value) {
            isValid = false;
            showFieldError(operationDate, 'تاريخ التشغيل مطلوب');
        }

        // Validate start_time
        if (!startTime || !startTime.value) {
            isValid = false;
            showFieldError(startTime, 'وقت البدء مطلوب');
        }

        // Validate end_time
        if (!endTime || !endTime.value) {
            isValid = false;
            showFieldError(endTime, 'وقت الإيقاف مطلوب');
        }

        // Validate time logic: end_time must be after start_time
        if (startTime && startTime.value && endTime && endTime.value) {
            const start = new Date('2000-01-01 ' + startTime.value);
            const end = new Date('2000-01-01 ' + endTime.value);
            if (end <= start) {
                isValid = false;
                showFieldError(endTime, 'وقت الإيقاف يجب أن يكون بعد وقت البدء');
            }
        }

        // Validate numeric fields (optional but must be valid if provided)
        const numericFields = [
            { name: 'load_percentage', min: 0, max: 100 },
            { name: 'fuel_meter_start', min: 0 },
            { name: 'fuel_meter_end', min: 0 },
            { name: 'fuel_consumed', min: 0 },
            { name: 'energy_meter_start', min: 0 },
            { name: 'energy_meter_end', min: 0 },
            { name: 'energy_produced', min: 0 }
        ];

        numericFields.forEach(field => {
            const input = form.querySelector(`[name="${field.name}"]`);
            if (input && input.value) {
                const value = parseFloat(input.value);
                if (isNaN(value) || value < (field.min || 0) || (field.max && value > field.max)) {
                    isValid = false;
                    const message = field.max 
                        ? `القيمة يجب أن تكون بين ${field.min} و ${field.max}`
                        : `القيمة يجب أن تكون أكبر من أو تساوي ${field.min}`;
                    showFieldError(input, message);
                }
            }
        });

        return isValid;
    }

    /**
     * Validate fuel efficiency form
     */
    function validateFuelEfficiencyForm(form) {
        let isValid = true;

        clearFormErrors(form);

        const generatorId = form.querySelector('[name="generator_id"]');
        const consumptionDate = form.querySelector('[name="consumption_date"]');

        if (!generatorId || !generatorId.value) {
            isValid = false;
            showFieldError(generatorId, 'المولد مطلوب');
        }

        if (!consumptionDate || !consumptionDate.value) {
            isValid = false;
            showFieldError(consumptionDate, 'تاريخ الاستهلاك مطلوب');
        }

        // Validate numeric fields
        const numericFields = [
            { name: 'operating_hours', min: 0 },
            { name: 'fuel_price_per_liter', min: 0 },
            { name: 'fuel_efficiency_percentage', min: 0, max: 100 },
            { name: 'energy_distribution_efficiency', min: 0, max: 100 },
            { name: 'total_operating_cost', min: 0 }
        ];

        numericFields.forEach(field => {
            const input = form.querySelector(`[name="${field.name}"]`);
            if (input && input.value) {
                const value = parseFloat(input.value);
                if (isNaN(value) || value < (field.min || 0) || (field.max && value > field.max)) {
                    isValid = false;
                    const message = field.max 
                        ? `القيمة يجب أن تكون بين ${field.min} و ${field.max}`
                        : `القيمة يجب أن تكون أكبر من أو تساوي ${field.min}`;
                    showFieldError(input, message);
                }
            }
        });

        return isValid;
    }

    /**
     * Validate maintenance record form
     */
    function validateMaintenanceRecordForm(form) {
        let isValid = true;

        clearFormErrors(form);

        const generatorId = form.querySelector('[name="generator_id"]');
        const maintenanceType = form.querySelector('[name="maintenance_type"]');
        const maintenanceDate = form.querySelector('[name="maintenance_date"]');

        if (!generatorId || !generatorId.value) {
            isValid = false;
            showFieldError(generatorId, 'المولد مطلوب');
        }

        if (!maintenanceType || !maintenanceType.value) {
            isValid = false;
            showFieldError(maintenanceType, 'نوع الصيانة مطلوب');
        }

        if (!maintenanceDate || !maintenanceDate.value) {
            isValid = false;
            showFieldError(maintenanceDate, 'تاريخ الصيانة مطلوب');
        }

        // Validate numeric fields
        const numericFields = [
            { name: 'downtime_hours', min: 0 },
            { name: 'maintenance_cost', min: 0 }
        ];

        numericFields.forEach(field => {
            const input = form.querySelector(`[name="${field.name}"]`);
            if (input && input.value) {
                const value = parseFloat(input.value);
                if (isNaN(value) || value < (field.min || 0)) {
                    isValid = false;
                    showFieldError(input, `القيمة يجب أن تكون أكبر من أو تساوي ${field.min}`);
                }
            }
        });

        return isValid;
    }

    /**
     * Validate compliance safety form
     */
    function validateComplianceSafetyForm(form) {
        let isValid = true;

        clearFormErrors(form);

        const operatorId = form.querySelector('[name="operator_id"]');
        const safetyCertificateStatus = form.querySelector('[name="safety_certificate_status"]');

        if (!operatorId || !operatorId.value) {
            isValid = false;
            showFieldError(operatorId, 'المشغل مطلوب');
        }

        if (!safetyCertificateStatus || !safetyCertificateStatus.value) {
            isValid = false;
            showFieldError(safetyCertificateStatus, 'حالة شهادة السلامة مطلوبة');
        }

        return isValid;
    }

    /**
     * Show error for a field
     */
    function showFieldError(field, message) {
        if (!field) return;

        field.classList.add('is-invalid');
        
        // Remove existing error message
        const existingError = field.parentElement.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }

        // Add error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.textContent = message;
        field.parentElement.appendChild(errorDiv);
    }

    /**
     * Clear all form errors
     */
    function clearFormErrors(form) {
        const invalidFields = form.querySelectorAll('.is-invalid');
        invalidFields.forEach(field => {
            field.classList.remove('is-invalid');
        });

        const errorMessages = form.querySelectorAll('.invalid-feedback');
        errorMessages.forEach(error => {
            error.remove();
        });
    }

    // Initialize form validations when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Operation Log Form
        const operationLogForm = document.getElementById('operationLogForm');
        if (operationLogForm) {
            operationLogForm.addEventListener('submit', function(e) {
                if (!validateOperationLogForm(this)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        }

        // Fuel Efficiency Form
        const fuelEfficiencyForm = document.getElementById('fuelEfficiencyForm');
        if (fuelEfficiencyForm) {
            fuelEfficiencyForm.addEventListener('submit', function(e) {
                if (!validateFuelEfficiencyForm(this)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        }

        // Maintenance Record Form
        const maintenanceRecordForm = document.getElementById('maintenanceRecordForm');
        if (maintenanceRecordForm) {
            maintenanceRecordForm.addEventListener('submit', function(e) {
                if (!validateMaintenanceRecordForm(this)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        }

        // Compliance Safety Form
        const complianceSafetyForm = document.querySelector('form[action*="compliance-safeties"]');
        if (complianceSafetyForm) {
            complianceSafetyForm.addEventListener('submit', function(e) {
                if (!validateComplianceSafetyForm(this)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        }
    });

})();



