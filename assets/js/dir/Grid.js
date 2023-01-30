Ext.define('GibsonOS.module.explorer.dir.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
    alias: ['widget.gosModuleExplorerDirGrid'],
    itemId: 'explorerDirGrid',
    requiredPermission: {
        module: 'explorer',
        task: 'file'
    },
    enableToolbar: false,
    enablePagingBar: false,
    multiSelect: true,
    enableDrag: true,
    enterFunction(record) {
        const me = this;
        const dir = me.getStore().getProxy().getReader().jsonData.dir;

        if (record.get('type') === 'dir') {
            GibsonOS.module.explorer.dir.fn.open(me.getStore(), dir + record.get('name') + '/');
        } else {
            GibsonOS.module.explorer.file.fn.download(dir + record.get('name'));
        }
    },
    enterButton: {
        text: 'Öffnen',
        iconCls: '',
        // requiredPermission: {
        //     task: 'file',
        //     action: 'open',
        //     permission: GibsonOS.Permission.READ
        // }
    },
    deleteFunction(records) {
        const me = this;
        const dir = me.getStore().getProxy().getReader().jsonData.dir;

        GibsonOS.module.explorer.file.fn.delete(dir, records, (response) => {
            me.fireEvent('deleteFile', response, dir, records);
            me.getStore().remove(records);
        });
    },
    viewConfig: {
        getRowClass(record) {
            if (record.get('hidden')) {
                return 'hideItem';
            }
        }
    },
    initComponent() {
        let me = this;

        me = GibsonOS.module.explorer.dir.decorator.Drop.init(me);
        me = GibsonOS.module.explorer.dir.decorator.Shortcuts.init(me);

        me.callParent();

        GibsonOS.module.explorer.dir.decorator.Actions.add(me);
        GibsonOS.module.explorer.dir.decorator.Listeners.add(me);
    },
    getColumns() {
        return [{
            dataIndex: 'type',
            width: 25,
            renderer(value, metaData, record) {
                if (record.get('thumb')) {
                    return '<div class="icon icon16" style="' +
                        'background-image: url(data:image/png;base64,' + record.get('thumb') + '); ' +
                        'background-repeat: no-repeat; ' +
                        'background-size: contain; ' +
                        'background-position: center !important;' +
                        '"></div>';
                }

                let icon = 'icon_' + value;

                if (record.get('icon') > 0) {
                    icon = 'customIcon' + record.get('icon');
                }

                return '<div class="icon icon16 icon_default ' + icon + '"></div>';
            }
        },{
            header: 'Name',
            dataIndex: 'name',
            flex: 1
        },{
            header: 'Letzter Zugriff',
            align: 'right',
            dataIndex: 'accessed',
            width: 120,
            renderer(value) {
                const date = new Date(value * 1000);

                return Ext.Date.format(date, 'Y-m-d H:i:s');
            }
        },{
            header: 'Zuletzt bearbeitet',
            align: 'right',
            dataIndex: 'modified',
            width: 120,
            renderer(value) {
                const date = new Date(value * 1000);

                return Ext.Date.format(date, 'Y-m-d H:i:s');
            }
        },{
            header: 'Größe',
            align: 'right',
            dataIndex: 'size',
            width: 80,
            renderer(value) {
                return transformSize(value);
            }
        },{
            header: '&nbsp;',
            dataIndex: 'html5MediaStatus',
            width: 30,
            renderer(value, metaData, record) {
                return GibsonOS.module.explorer.file.fn.renderBadge(record.getData(), 16);
            }
        }]
    }
});