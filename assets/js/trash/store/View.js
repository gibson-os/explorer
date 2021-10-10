Ext.define('GibsonOS.module.explorer.trash.store.View', {
    extend: 'GibsonOS.data.Store',
    alias: ['store.gosModuleExplorerTrashContainerStore'],
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'explorer/trash/read'
    },
    model: 'GibsonOS.module.explorer.trash.model.View'
});