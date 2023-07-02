Ext.define('GibsonOS.module.explorer.html5.connectedUser.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
    alias: ['widget.gosModuleExplorerHtml5ConnectedUserGrid'],
    requiredPermission: {
        module: 'explorer',
        task: 'html5'
    },
    addFunction() {
        const me = this;
        const formWindow = new GibsonOS.module.core.component.form.Window({
            title: 'Verbindung hinzufügen',
            url: baseDir + 'explorer/html5/connectedUserForm',
            method: 'GET',
        }).show();

        formWindow.down('form').getForm().on('actioncomplete', () => {
            formWindow.close();
            me.getStore().load();
        });
    },
    deleteFunction(records) {
        const me = this;
        let ids = [];
        let msg = 'Möchten Sie die Verbindung zu dem Benutzer ' + records[0].get('connectedUser').user + ' wirklich löschen?';

        if (records.length > 1) {
            msg = 'Möchten Sie die ' + records.length + ' Verbindungen wirklich löschen?';
        }

        Ext.iterate(records, (record) => {
            ids.push({id: record.getId()});
        });

        GibsonOS.MessageBox.show({
            title: 'Wirklich löschen?',
            msg: msg,
            type: GibsonOS.MessageBox.type.QUESTION,
            buttons: [{
                text: 'Ja',
                sendRequest: true
            },{
                text: 'Nein'
            }]
        },{
            url: baseDir + 'explorer/html5/connectedUsers',
            method: 'DELETE',
            params: {
                connectedUsers: Ext.encode(ids)
            },
            success() {
                me.getStore().load();
            }
        });
    },
    initComponent() {
        let me = this;

        me.store = new GibsonOS.module.explorer.html5.store.ConnectedUser();

        me.callParent();
    },
    getColumns() {
        return [{
            header: 'Verbundener Benutzer',
            dataIndex: 'connectedUser',
            flex: 1,
            renderer(value) {
                return value.user;
            }
        }];
    }
});