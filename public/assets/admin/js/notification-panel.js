/**
 * Notification Panel System
 */
class NotificationPanel {
    constructor() {
        this.notificationList = document.getElementById('notification-list');
        this.notificationBadge = document.getElementById('notification-icon-badge');
        this.notificationCount = document.getElementById('notification-count');
        this.markAllReadBtn = document.getElementById('mark-all-read');
        this.notificationsUrl = '/admin/notifications';
        this.pollInterval = 15000; // تحديث كل 15 ثانية (realtime)
        this.pollTimer = null;
        
        this.init();
    }

    init() {
        // جلب الإشعارات عند تحميل الصفحة
        this.loadNotifications();
        
        // إعداد الأحداث
        this.setupEvents();
        
        // بدء التحديث التلقائي
        this.startPolling();
    }

    setupEvents() {
        // زر تعليم الكل كمقروء
        if (this.markAllReadBtn) {
            this.markAllReadBtn.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }

        // عند فتح القائمة المنسدلة، تحديث الإشعارات
        const dropdown = document.getElementById('notificationDropdown');
        if (dropdown) {
            dropdown.addEventListener('shown.bs.dropdown', () => {
                this.loadNotifications();
            });
        }
    }

    async loadNotifications() {
        try {
            const response = await fetch(this.notificationsUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error('فشل في جلب الإشعارات');
            }

            const data = await response.json();
            this.renderNotifications(data.notifications);
            this.updateBadge(data.unread_count);
        } catch (error) {
            console.error('خطأ في جلب الإشعارات:', error);
        }
    }

    renderNotifications(notifications) {
        if (!this.notificationList) return;

        if (notifications.length === 0) {
            this.notificationList.innerHTML = `
                <li class="nour-dropdown-empty">
                    <i class="bi bi-bell-slash"></i>
                    <p>لا توجد إشعارات</p>
                </li>
            `;
            if (this.markAllReadBtn) {
                this.markAllReadBtn.style.display = 'none';
            }
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            const unreadBg = notification.read ? '' : 'background:#EEF2FF;border-right:2.5px solid #24308F;';
            const iconColor = notification.color || 'primary';

            html += `
                <li class="notification-item" data-notification-id="${notification.id}" style="${unreadBg}">
                    <div class="d-flex align-items-start gap-2" style="font-size:0.82rem;">
                        <div style="width:32px;height:32px;border-radius:8px;background:var(--primary-soft,#EEF2FF);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi ${notification.icon}" style="color:var(--primary,#24308F);font-size:0.95rem;"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div class="d-flex justify-content-between align-items-start">
                                <span style="font-weight:700;color:#1F2937;font-size:0.8rem;line-height:1.3;">${this.escapeHtml(notification.title)}</span>
                                <div class="d-flex gap-1" style="flex-shrink:0;">
                                    ${!notification.read ? `
                                        <button class="mark-read-btn" data-notification-id="${notification.id}" title="تعليم كمقروء"
                                                style="background:none;border:none;color:#24308F;cursor:pointer;padding:2px;font-size:0.85rem;">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    ` : ''}
                                    <button class="delete-notification-btn" data-notification-id="${notification.id}" title="حذف"
                                            style="background:none;border:none;color:#EF4444;cursor:pointer;padding:2px;font-size:0.85rem;">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                            </div>
                            <p style="margin:0.15rem 0 0.25rem;color:#5B6780;font-size:0.76rem;line-height:1.4;">${this.escapeHtml(notification.message)}</p>
                            <span style="color:#98A2B3;font-size:0.7rem;">
                                <i class="bi bi-clock me-1"></i>${notification.created_at}
                            </span>
                            ${notification.link ? `
                                <div style="margin-top:0.35rem;">
                                    <a href="${notification.link}" style="font-size:0.72rem;color:#24308F;font-weight:600;text-decoration:none;">
                                        عرض التفاصيل <i class="bi bi-arrow-left"></i>
                                    </a>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </li>
            `;
        });

        this.notificationList.innerHTML = html;

        // إضافة مستمعي الأحداث للأزرار
        this.attachEventListeners();

        // إظهار/إخفاء زر تعليم الكل كمقروء
        const unreadCount = notifications.filter(n => !n.read).length;
        if (this.markAllReadBtn) {
            this.markAllReadBtn.style.display = unreadCount > 0 ? 'block' : 'none';
        }
    }

    attachEventListeners() {
        // أزرار تعليم كمقروء
        document.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const notificationId = btn.getAttribute('data-notification-id');
                this.markAsRead(notificationId);
            });
        });

        // أزرار الحذف
        document.querySelectorAll('.delete-notification-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const notificationId = btn.getAttribute('data-notification-id');
                this.deleteNotification(notificationId);
            });
        });

        // النقر على الإشعار لتعليمه كمقروء
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', (e) => {
                if (e.target.closest('.btn')) return; // تجاهل النقر على الأزرار
                const notificationId = item.getAttribute('data-notification-id');
                const notification = item.querySelector('.notification-unread');
                if (notification) {
                    this.markAsRead(notificationId);
                }
            });
        });
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/admin/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (!response.ok) {
                throw new Error('فشل في تحديث الإشعار');
            }

            // تحديث الواجهة
            const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (item) {
                item.classList.remove('notification-unread');
                const markReadBtn = item.querySelector('.mark-read-btn');
                if (markReadBtn) {
                    markReadBtn.remove();
                }
            }

            // إعادة تحميل الإشعارات لتحديث العدد
            this.loadNotifications();
        } catch (error) {
            console.error('خطأ في تحديث الإشعار:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/admin/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (!response.ok) {
                throw new Error('فشل في تحديث الإشعارات');
            }

            // إعادة تحميل الإشعارات
            this.loadNotifications();
        } catch (error) {
            console.error('خطأ في تحديث الإشعارات:', error);
        }
    }

    async deleteNotification(notificationId) {
        if (!confirm('هل أنت متأكد من حذف هذا الإشعار؟')) {
            return;
        }

        try {
            const response = await fetch(`/admin/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (!response.ok) {
                throw new Error('فشل في حذف الإشعار');
            }

            // إزالة العنصر من الواجهة
            const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (item) {
                item.remove();
            }

            // إعادة تحميل الإشعارات لتحديث العدد
            this.loadNotifications();
        } catch (error) {
            console.error('خطأ في حذف الإشعار:', error);
        }
    }

    updateBadge(count) {
        if (this.notificationBadge) {
            if (count > 0) {
                this.notificationBadge.textContent = count > 99 ? '99+' : count;
                this.notificationBadge.style.display = 'block';
            } else {
                this.notificationBadge.style.display = 'none';
            }
        }

        if (this.notificationCount) {
            this.notificationCount.textContent = `${count} غير مقروء`;
        }
    }

    startPolling() {
        this.pollTimer = setInterval(() => {
            this.loadNotifications();
        }, this.pollInterval);
    }

    stopPolling() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// تهيئة النظام عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    window.notificationPanel = new NotificationPanel();
});


