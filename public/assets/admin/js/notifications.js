/**
 * Notification System for Admin Panel
 */
class AdminNotifications {
    constructor() {
        this.toastContainer = document.getElementById('toast-container');
        this.init();
    }

    init() {
        // Show flash messages as toasts
        this.showFlashMessagesAsToasts();
        
        // Auto-hide alerts after 5 seconds
        this.autoHideAlerts();
    }

    /**
     * Show flash messages as Bootstrap Toasts
     */
    showFlashMessagesAsToasts() {
        // Check for session flash messages
        const successMsg = this.getFlashMessage('success');
        const errorMsg = this.getFlashMessage('error');
        const warningMsg = this.getFlashMessage('warning');
        const infoMsg = this.getFlashMessage('info');

        // Check for validation errors
        const validationError = document.querySelector('.alert-danger[data-toast-message]');
        if (validationError && !errorMsg) {
            const validationMsg = validationError.getAttribute('data-toast-message');
            if (validationMsg) {
                this.showToast(validationMsg, 'danger');
                validationError.style.display = 'none';
            }
        }

        // Also check for validation errors in error list
        const errorList = document.querySelectorAll('.invalid-feedback');
        errorList.forEach(error => {
            const errorText = error.textContent.trim();
            if (errorText && !errorMsg) {
                this.showToast(errorText, 'danger');
            }
        });

        if (successMsg) {
            this.showToast(successMsg, 'success');
        }
        if (errorMsg) {
            this.showToast(errorMsg, 'danger');
        }
        if (warningMsg) {
            this.showToast(warningMsg, 'warning');
        }
        if (infoMsg) {
            this.showToast(infoMsg, 'info');
        }
    }

    /**
     * Get flash message from alert element
     */
    getFlashMessage(type) {
        const alert = document.querySelector(`.alert-${type}`);
        if (alert) {
            // First try to get from data attribute
            const dataMessage = alert.getAttribute('data-toast-message');
            if (dataMessage) {
                // Hide the alert after getting the message
                alert.style.display = 'none';
                return dataMessage;
            }
            // Fallback to text content
            const text = alert.querySelector('.flex-grow-1')?.textContent.trim() || alert.textContent.trim();
            const message = text.replace(new RegExp(`^${this.getTypeLabel(type)}:\\s*`, 'i'), '');
            // Hide the alert after getting the message
            alert.style.display = 'none';
            return message;
        }
        return null;
    }

    /**
     * Get type label
     */
    getTypeLabel(type) {
        const labels = {
            'success': 'نجاح',
            'danger': 'خطأ',
            'warning': 'تحذير',
            'info': 'معلومة'
        };
        return labels[type] || '';
    }

    /**
     * Show Bootstrap Toast
     */
    showToast(message, type = 'info', duration = 5000) {
        if (!this.toastContainer) {
            this.toastContainer = document.createElement('div');
            this.toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            this.toastContainer.style.zIndex = '9999';
            document.body.appendChild(this.toastContainer);
        }

        const toastId = 'toast-' + Date.now();
        const icons = {
            'success': 'bi-check-circle-fill',
            'danger': 'bi-exclamation-triangle-fill',
            'warning': 'bi-exclamation-circle-fill',
            'info': 'bi-info-circle-fill'
        };

        const bgColors = {
            'success': 'bg-success',
            'danger': 'bg-danger',
            'warning': 'bg-warning',
            'info': 'bg-info'
        };

        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgColors[type] || 'bg-primary'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center">
                        <i class="bi ${icons[type] || 'bi-info-circle-fill'} me-2 fs-5"></i>
                        <span>${message}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        this.toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        // Ensure duration is a number
        const delayValue = typeof duration === 'string' ? parseInt(duration, 10) : (typeof duration === 'number' ? duration : 5000);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: delayValue
        });

        toast.show();

        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    /**
     * Auto-hide alert messages after 5 seconds
     */
    autoHideAlerts() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    }

    /**
     * Show success notification
     */
    success(message, duration = 5000) {
        this.showToast(message, 'success', duration);
    }

    /**
     * Show error notification
     */
    error(message, duration = 5000) {
        this.showToast(message, 'danger', duration);
    }

    /**
     * Show warning notification
     */
    warning(message, duration = 5000) {
        this.showToast(message, 'warning', duration);
    }

    /**
     * Show info notification
     */
    info(message, duration = 5000) {
        this.showToast(message, 'info', duration);
    }
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    window.adminNotifications = new AdminNotifications();
});