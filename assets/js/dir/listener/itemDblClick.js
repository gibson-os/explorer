GibsonOS.define('GibsonOS.module.explorer.dir.listener.itemDblClick', function(view, record) {
    var store = view.getStore();
    var proxy = store.getProxy();
    var dir = proxy.getReader().jsonData.dir;

    if (GibsonOS.module.explorer.file.chromecast.fn.play(record)) {
        return true;
    }

    if (record.get('type') == 'dir') {
        GibsonOS.module.explorer.dir.fn.open(store, dir + record.get('name') + '/');
    } else {
        GibsonOS.module.explorer.file.fn.download(dir + record.get('name'));
    }
});