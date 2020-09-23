GibsonOS.define('GibsonOS.module.explorer.html5.fn.updateSize', function(pagingToolbar, size) {
    pagingToolbar.displayMsg = 'Medien {0} - {1} von {2} (' + transformSize(size) + ')';
    pagingToolbar.onLoad();
});