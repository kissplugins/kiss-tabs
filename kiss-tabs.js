
(function($) {
    'use strict';
    function activateTab($wrapper, tabIndex) {
        var $navItems = $wrapper.find('.kiss-tabs-nav > .kiss-tab-nav-item');
        var $tabPanes = $wrapper.find('.kiss-tabs-content > .kiss-tab-pane');
        var $targetNavItem = $navItems.filter('[data-tab-index="' + tabIndex + '"]');
        var $targetPane = $tabPanes.filter('[data-tab-index="' + tabIndex + '"]');
        if ($targetNavItem.length && $targetPane.length) {
            $navItems.removeClass('active');
            $tabPanes.removeClass('active');
            $targetNavItem.addClass('active');
            $targetPane.addClass('active');
            window.dispatchEvent(new Event('resize'));
            $(document).trigger('kiss:tab:shown', { newTab: '#' + $targetPane.attr('id') });
            return true;
        }
        return false;
    }
    $(document).ready(function() {
        $('.kiss-tabs-wrapper').each(function() {
            var $wrapper = $(this);
            $wrapper.find('.kiss-tabs-nav > .kiss-tab-nav-item').on('click', function(e) {
                e.preventDefault();
                var $this = $(this);
                if ($this.hasClass('active')) return;
                var tabIndex = $this.data('tab-index');
                if (activateTab($wrapper, tabIndex)) {
                    if (history.pushState) { history.pushState(null, null, '#tab-' + tabIndex); }
                    else { window.location.hash = 'tab-' + tabIndex; }
                }
            });
        });
        var hash = window.location.hash;
        if (hash && hash.startsWith('#tab-')) {
            var tabIndexFromHash = parseInt(hash.substring(5), 10);
            if (!isNaN(tabIndexFromHash) && tabIndexFromHash > 0) {
                $('.kiss-tabs-wrapper').each(function() { activateTab($(this), tabIndexFromHash); });
            }
        }
    });
    $(window).on('hashchange', function() {
        var hash = window.location.hash;
        if (hash && hash.startsWith('#tab-')) {
            var tabIndexFromHash = parseInt(hash.substring(5), 10);
            if (!isNaN(tabIndexFromHash) && tabIndexFromHash > 0) {
                $('.kiss-tabs-wrapper').each(function() {
                    if ($(this).find('.kiss-tabs-nav > .kiss-tab-nav-item.active').data('tab-index') != tabIndexFromHash) {
                        activateTab($(this), tabIndexFromHash);
                    }
                });
            }
        } else if (!hash) {
             $('.kiss-tabs-wrapper').each(function() { if ($(this).find('.kiss-tabs-nav > .kiss-tab-nav-item.active').data('tab-index') != 1) { activateTab($(this), 1); } });
        }
    });
})(jQuery);
    