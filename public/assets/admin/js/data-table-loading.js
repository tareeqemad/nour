/**
 * Data Table Loading Overlay - Public/Global
 * 
 * Usage:
 * 1. Wrap your table/content area with class "data-table-container"
 * 2. Include this script
 * 3. Use window.DataTableLoading.show(container) and window.DataTableLoading.hide(container)
 * 
 * Example for Table:
 * <div class="table-responsive data-table-container">
 *     <table class="table">
 *         <thead>...</thead>
 *         <tbody>
 *             <!-- overlay will be on tbody only -->
 *         </tbody>
 *     </table>
 * </div>
 * 
 * Example for Div-based list:
 * <div class="data-table-container">
 *     <div class="list-content">
 *         <!-- overlay will be on this div -->
 *     </div>
 * </div>
 */

(function($) {
    'use strict';

    window.DataTableLoading = {
        /**
         * Show loading overlay - only on tbody or content area
         * @param {jQuery|HTMLElement|string} container - Container element or selector
         */
        show: function(container) {
            const $container = $(container);
            let $targetArea = null;
            let $overlay = null;
            
            // Find the target area (tbody for tables, or the content div)
            const $tbody = $container.find('tbody').first();
            if ($tbody.length > 0) {
                // Table structure: overlay on tbody
                $targetArea = $tbody;
                // Make tbody position relative if not already
                if ($targetArea.css('position') === 'static') {
                    $targetArea.css('position', 'relative');
                }
            } else {
                // Div-based structure: overlay on the main content area
                // Look for common content containers - prioritize ListContainer/ContentContainer over .log-list
                $targetArea = $container.find('[id$="ListContainer"], [id$="ContentContainer"]').first();
                if ($targetArea.length === 0) {
                    // Fallback to .log-list or other content areas
                    $targetArea = $container.find('.log-list, .list-content').first();
                }
                if ($targetArea.length === 0) {
                    // If no specific container, use the container itself
                    $targetArea = $container;
                }
                // Make target area position relative if not already
                if ($targetArea.css('position') === 'static') {
                    $targetArea.css('position', 'relative');
                }
                // Ensure min-height for proper overlay display if content is empty
                if (!$targetArea.css('min-height') || $targetArea.css('min-height') === '0px') {
                    $targetArea.css('min-height', '100px');
                }
            }
            
            // Check if overlay already exists within target area
            $overlay = $targetArea.find('.data-table-loading-overlay').first();
            if ($overlay.length === 0) {
                // Create overlay and append to target area
                const overlayHtml = `
                    <div class="data-table-loading-overlay">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
                            </div>
                            <div class="mt-3 text-muted fw-semibold">جاري التحميل...</div>
                        </div>
                    </div>
                `;
                $targetArea.append(overlayHtml);
                $overlay = $targetArea.find('.data-table-loading-overlay').first();
            }

            $overlay.css({
                'display': 'flex',
                'visibility': 'visible'
            }).stop(true, true).animate({
                'opacity': '1'
            }, 200);
        },

        /**
         * Hide loading overlay
         * @param {jQuery|HTMLElement|string} container - Container element or selector
         */
        hide: function(container) {
            const $container = $(container);
            let $targetArea = null;
            
            // Find the same target area as in show() method
            const $tbody = $container.find('tbody').first();
            if ($tbody.length > 0) {
                $targetArea = $tbody;
            } else {
                // Div-based structure: overlay on the main content area
                // Look for common content containers - prioritize ListContainer/ContentContainer over .log-list
                $targetArea = $container.find('[id$="ListContainer"], [id$="ContentContainer"]').first();
                if ($targetArea.length === 0) {
                    // Fallback to .log-list or other content areas
                    $targetArea = $container.find('.log-list, .list-content').first();
                }
                if ($targetArea.length === 0) {
                    $targetArea = $container;
                }
            }
            
            const $overlay = $targetArea.find('.data-table-loading-overlay').first();
            
            if ($overlay.length === 0) return;

            $overlay.stop(true, true).animate({
                'opacity': '0'
            }, 200, function() {
                $(this).css({
                    'display': 'none',
                    'visibility': 'hidden'
                });
            });
        },

        /**
         * Check if loading is visible
         * @param {jQuery|HTMLElement|string} container - Container element or selector
         * @returns {boolean}
         */
        isVisible: function(container) {
            const $container = $(container);
            let $targetArea = null;
            
            // Find the same target area as in show() method
            const $tbody = $container.find('tbody').first();
            if ($tbody.length > 0) {
                $targetArea = $tbody;
            } else {
                // Div-based structure: overlay on the main content area
                // Look for common content containers - prioritize ListContainer/ContentContainer over .log-list
                $targetArea = $container.find('[id$="ListContainer"], [id$="ContentContainer"]').first();
                if ($targetArea.length === 0) {
                    // Fallback to .log-list or other content areas
                    $targetArea = $container.find('.log-list, .list-content').first();
                }
                if ($targetArea.length === 0) {
                    $targetArea = $container;
                }
            }
            
            const $overlay = $targetArea.find('.data-table-loading-overlay').first();
            return $overlay.length > 0 && $overlay.is(':visible') && $overlay.css('opacity') !== '0';
        }
    };

})(jQuery);

