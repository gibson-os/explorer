Ext.define('GibsonOS.module.explorer.html5.store.ConnectedUser', {
    extend: 'GibsonOS.data.Store',
    alias: ['store.gosModuleExplorerHtml5ConnectedUserStore'],
    pageSize: 100,
    model: 'GibsonOS.module.explorer.html5.model.ConnectedUser',
    constructor(data) {
        this.proxy = {
            type: 'gosDataProxyAjax',
            url: baseDir + 'explorer/html5/connectedUsers'
        };

        this.callParent(arguments);
    }
});