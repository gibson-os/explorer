Ext.define('GibsonOS.module.explorer.html5.toSee.Grid', {
    extend: 'GibsonOS.grid.Panel',
    alias: ['widget.gosModuleExplorerHtml5ToSeeGrid'],
    itemId: 'explorerHtml5ToSeeGrid',
    requiredPermission: {
        module: 'explorer',
        task: 'html5'
    },
    multiSelect: false,
    initComponent: function () {
        let me = this;

        me.itemContextMenu = GibsonOS.module.explorer.html5.contextMenu.item;
        me.store = new GibsonOS.module.explorer.html5.store.ToSee();
        me.columns = [{
/*            dataIndex: 'type',
            width: 25,
            renderer: function(value, metaData, record) {
                if (record.get('thumb')) {
                    return '<div class="icon icon16" style="background-image: url(data:image/png;base64,' + record.get('thumb') + ');"></div>';
                }

                return '<div class="icon icon16 icon_default icon_' + value + '"></div>';
            }
        },{*/
            header: 'Dateiname',
            dataIndex: 'filename',
            flex: 1
        },{
            header: 'Ordner',
            dataIndex: 'dir',
            flex: 1
        },{
            header: 'Status',
            dataIndex: 'status',
            align: 'right',
            width: 70
        },{
            header: 'Länge',
            dataIndex: 'duration',
            align: 'right',
            width: 100,
            renderer: function(value) {
                return transformSeconds(value);
            }
        },{
            header: 'Position',
            dataIndex: 'position',
            align: 'right',
            width: 100,
            renderer: function(value) {
                return transformSeconds(value);
            }
        },{
            header: 'Weitere Folgen',
            dataIndex: 'nextFiles',
            align: 'right',
            width: 100
        }];
        me.tbar = [('->'),{
            xtype: 'gosModuleExplorerFileChromecastButton',
            itemId: 'explorerHtml5ChromecastButton'
        }];

        me.callParent();

        me.on('itemdblclick', GibsonOS.module.explorer.html5.listener.itemDblClick);
        me.on('destroy', function(grid, options) {
            me.getStore().gos.data.runLoadThumbnails = false;
        });
    }
});