/**
 * Messages Panel - Real-time updates for messages dropdown
 */
(function() {
    'use strict';

    const MessagesPanel = {
        unreadCount: 0,
        updateInterval: null,
        updateIntervalMs: 15000, // 15 seconds (realtime)

        init: function() {
            this.loadUnreadCount();
            this.loadRecentMessages();
            this.startAutoUpdate();
            this.setupEventListeners();
        },

        loadUnreadCount: function() {
            const oldCount = this.unreadCount;
            $.ajax({
                url: '/admin/messages/unread-count',
                method: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: (data) => {
                    const newCount = data.count || 0;
                    this.unreadCount = newCount;
                    this.updateBadge();
                    this.updateSummary();
                    
                    // إشعار بصوتي إذا كانت هناك رسائل جديدة
                    if (newCount > oldCount && newCount > 0 && !document.hidden) {
                        // يمكن إضافة صوت إشعار هنا
                        // new Audio('/path/to/notification.mp3').play();
                    }
                },
                error: (xhr) => {
                    // Silently fail - لا نريد إزعاج المستخدم بأخطاء AJAX
                    console.warn('Failed to load unread messages count', xhr);
                }
            });
        },

        loadRecentMessages: function() {
            const $loading = $('#messages-loading');
            const $list = $('#messages-list');
            
            $loading.show();
            $list.hide();
            
            $.ajax({
                url: '/admin/messages/recent',
                method: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: (data) => {
                    this.renderMessages(data.messages || []);
                    $loading.hide();
                    $list.show();
                },
                error: (xhr) => {
                    // Silently fail - لا نريد إزعاج المستخدم بأخطاء AJAX
                    console.warn('Failed to load recent messages', xhr);
                    $loading.hide();
                    $list.show();
                }
            });
        },

        updateBadge: function() {
            const $badge = $('#messages-icon-badge');
            if (this.unreadCount > 0) {
                $badge.text(this.unreadCount > 99 ? '99+' : this.unreadCount).show();
            } else {
                $badge.hide();
            }
        },

        updateSummary: function() {
            const $summary = $('#messages-summary');
            if (this.unreadCount > 0) {
                $summary.text(`${this.unreadCount} ${this.unreadCount === 1 ? 'رسالة غير مقروءة' : 'رسائل غير مقروءة'}`);
            } else {
                $summary.text('لا توجد رسائل غير مقروءة');
            }
        },

        renderMessages: function(messages) {
            const $list = $('#messages-list');
            
            if (!messages || messages.length === 0) {
                $list.html(`
                    <li class="p-4 text-center text-muted">
                        <i class="bi bi-envelope-slash fs-1 d-block mb-2"></i>
                        <p class="mb-0">لا توجد رسائل</p>
                    </li>
                `);
                return;
            }

            let html = '';
            messages.forEach((msg) => {
                const senderName = msg.sender ? msg.sender.name : 'غير معروف';
                const subject = this.escapeHtml(msg.subject || '');
                const preview = this.escapeHtml((msg.body || '').substring(0, 50));
                const date = this.formatDate(msg.created_at);
                
                // تحديد ما إذا كانت الرسالة غير مقروءة
                let isUnread = false;
                if (msg.type === 'operator_to_staff' || msg.type === 'admin_to_all') {
                    // للرسائل الموجهة للجميع، نتحقق من أن المستخدم الحالي ليس المرسل
                    isUnread = !msg.is_read && msg.sender && msg.sender.id !== window.currentUserId;
                } else {
                    // للرسائل الموجهة لمستخدم محدد
                    isUnread = !msg.is_read && msg.receiver_id === window.currentUserId;
                }
                
                const unreadClass = isUnread ? 'table-warning' : '';
                const unreadBg = isUnread ? 'bg-light' : '';

                // تحديد نوع الرسالة
                let typeBadge = '';
                if (msg.type === 'admin_to_all') {
                    typeBadge = '<span class="badge bg-danger badge-sm ms-1">للكل</span>';
                } else if (msg.type === 'operator_to_staff') {
                    typeBadge = '<span class="badge bg-success badge-sm ms-1">للموظفين</span>';
                }

                html += `
                    <li class="p-3 border-bottom ${unreadBg}" style="cursor: pointer;" onclick="window.location.href='/admin/messages/${msg.id}'">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center">
                                    <div class="fw-semibold text-dark">${this.escapeHtml(senderName)}</div>
                                    ${typeBadge}
                                </div>
                                <div class="text-muted small">${date}</div>
                            </div>
                            ${isUnread ? '<span class="badge bg-primary pulse">جديد</span>' : ''}
                        </div>
                        <div class="fw-semibold mb-1 text-truncate" style="max-width: 250px;" title="${subject}">${subject}</div>
                        <div class="text-muted small text-truncate" style="max-width: 250px;">${preview}${msg.body && msg.body.length > 50 ? '...' : ''}</div>
                    </li>
                `;
            });

            $list.html(html);
        },

        formatDate: function(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'الآن';
            if (diffMins < 60) return `منذ ${diffMins} دقيقة`;
            if (diffHours < 24) return `منذ ${diffHours} ساعة`;
            if (diffDays < 7) return `منذ ${diffDays} يوم`;
            
            return date.toLocaleDateString('ar-SA', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        startAutoUpdate: function() {
            this.updateInterval = setInterval(() => {
                this.loadUnreadCount();
                this.loadRecentMessages();
            }, this.updateIntervalMs);
        },

        stopAutoUpdate: function() {
            if (this.updateInterval) {
                clearInterval(this.updateInterval);
                this.updateInterval = null;
            }
        },

        setupEventListeners: function() {
            // Reload when dropdown is opened
            $('#messagesDropdown').on('show.bs.dropdown', () => {
                this.loadUnreadCount();
                this.loadRecentMessages();
            });

            // تحديث عند إغلاق الـ dropdown (للتأكد من تحديث العدد)
            $('#messagesDropdown').on('hidden.bs.dropdown', () => {
                // تحديث العدد فقط عند الإغلاق
                this.loadUnreadCount();
            });

            // Stop auto-update when page is hidden
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.stopAutoUpdate();
                } else {
                    this.startAutoUpdate();
                    this.loadUnreadCount();
                    this.loadRecentMessages();
                }
            });

            // تحديث عند العودة للصفحة (focus)
            $(window).on('focus', () => {
                if (!document.hidden) {
                    this.loadUnreadCount();
                    this.loadRecentMessages();
                }
            });

            // تحديث عند إرسال رسالة جديدة (من خلال event)
            $(document).on('message:sent', () => {
                this.refresh();
            });

            // تحديث عند قراءة رسالة
            $(document).on('message:read', () => {
                this.refresh();
            });
        },

        // دالة لتحديث يدوي (يمكن استدعاؤها من صفحات أخرى)
        refresh: function() {
            this.loadUnreadCount();
            this.loadRecentMessages();
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Get current user ID from meta tag or global variable
        if (typeof window.currentUserId === 'undefined') {
            const $meta = $('meta[name="user-id"]');
            if ($meta.length) {
                window.currentUserId = parseInt($meta.attr('content'));
            } else {
                window.currentUserId = null;
            }
        }

        MessagesPanel.init();
    });

    // Expose to global scope
    window.MessagesPanel = MessagesPanel;
})();

