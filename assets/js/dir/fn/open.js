GibsonOS.define('GibsonOS.module.explorer.dir.fn.open', function(store, dir) {
    var proxy = store.getProxy();

    proxy.extraParams.dir = dir;
    store.load();
});