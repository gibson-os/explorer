Ext.define('GibsonOS.module.explorer.html5.connectedUser.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
    alias: ['widget.gosModuleExplorerHtml5ConnectedUserGrid'],
    requiredPermission: {
        module: 'explorer',
        task: 'html5'
    },
    addFunction() {
        new GibsonOS.module.core.component.form.Window({
            title: 'Verbindung hinzufügen',
            url: baseDir + 'explorer/html5/connectedUserForm'
        }).show();
    },
    deleteFunction(records) {

    },
    initComponent() {
        let me = this;

        me.store = new GibsonOS.module.explorer.html5.store.ConnectedUser();
        me.columns = [{
            header: 'Verbundener Benutzer',
            dataIndex: 'connectedUser',
            flex: 1
        }];

        me.callParent();
    }
});