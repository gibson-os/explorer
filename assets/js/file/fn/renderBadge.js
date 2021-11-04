GibsonOS.define('GibsonOS.module.explorer.file.fn.renderBadge', function(values, badgeSize) {
    if (values.html5MediaStatus == 'generated') {
        return '<div class="icon icon' + badgeSize + ' icon_html5"></div>';
    }
});