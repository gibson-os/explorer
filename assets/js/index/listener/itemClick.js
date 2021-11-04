GibsonOS.define('GibsonOS.module.explorer.index.listener.itemClick', function(view, record, item, index, event, options) {
    var store = view.getStore();
    var proxy = store.getProxy();
    var app = view.up('#app');

    GibsonOS.module.explorer.index.fn.updatePreview(app, record);
    GibsonOS.module.explorer.index.fn.updateProperties(app, record);
});