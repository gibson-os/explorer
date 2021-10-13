Ext.define('GibsonOS.module.explorer.trash.Grid', {
    extend: 'GibsonOS.grid.Panel',
    alias: ['widget.gosModuleExplorerTrashGrid'],
    itemId: 'explorerTrashGrid',
    requiredPermission: {
        module: 'explorer',
        task: 'trash'
    },
    multiSelect: true,
    columns: [{
        dataIndex: 'type',
        width: 25,
        renderer(value) {
            return '<div class="icon icon16 icon_default icon_' + value + '"></div>';
        }
    },{
        header: 'Name',
        dataIndex: 'dir',
        flex: 1,
        renderer(value, metaData, record) {
            return value + record.get('filename');
        }
    },{
        header: 'Gelöscht am',
        align: 'right',
        dataIndex: 'added',
        width: 120,
        renderer(value) {
            return Ext.Date.format(value, 'Y-m-d H:i:s');
        }
    }],
    initComponent: function() {
        const me = this;

        me.itemContextMenu = GibsonOS.module.explorer.trash.itemContextMenu;
        me.containerContextMenu = GibsonOS.module.explorer.trash.containerContextMenu;

        me.callParent();

        me.on('itemdblclick', GibsonOS.module.explorer.trash.itemDblClick);
        me.on('cellkeydown', function(table, td, cellIndex, record, tr, rowIndex, event) {
            if (event.getKey() === Ext.EventObject.DELETE) {
                GibsonOS.module.explorer.trash.fn.delete(me, me.getSelectionModel().getSelection());
            } else {
                //GibsonOS.module.explorer.trash.jumpToItem(me, record, rowIndex, event);
            }
        });
    }
});