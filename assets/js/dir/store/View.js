Ext.define('GibsonOS.module.explorer.dir.store.View', {
    extend: 'GibsonOS.data.Store',
    alias: ['store.gosModuleExplorerDirViewStore'],
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'explorer/dir',
        method: 'GET'
    },
    model: 'GibsonOS.module.explorer.dir.model.View'
});