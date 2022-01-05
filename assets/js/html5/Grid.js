Ext.define('GibsonOS.module.explorer.html5.Grid', {
    extend: 'GibsonOS.grid.Panel',
    alias: ['widget.gosModuleExplorerHtml5Grid'],
    itemId: 'explorerHtml5Grid',
    requiredPermission: {
        module: 'explorer',
        task: 'html5'
    },
    multiSelect: true,
    initComponent: function () {
        var grid = this;
        var deleteFn = function() {
            var records = grid.getSelectionModel().getSelection();

            GibsonOS.module.explorer.html5.fn.delete(records, function(response) {
                grid.getStore().remove(records);
            });
        }

        this.itemContextMenu = GibsonOS.module.explorer.html5.contextMenu.item;
        this.store = new GibsonOS.module.explorer.html5.store.Grid();
        this.columns = [{
            dataIndex: 'type',
            width: 25,
            renderer: function(value, metaData, record) {
                if (record.get('thumb')) {
                    return '<div class="icon icon16" style="' +
                        'background-image: url(data:image/png;base64,' + record.get('thumb') + '); ' +
                        'background-repeat: no-repeat; ' +
                        'background-size: contain; ' +
                        'background-position: center !important;' +
                    '"></div>';
                }

                return '<div class="icon icon16 icon_default icon_' + value + '"></div>';
            }
        },{
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
            /*},{
             header: 'Fortschritt',
             xtype: 'gosGridColumnProgressBar'*/
        },{
            header: 'Größe',
            dataIndex: 'size',
            align: 'right',
            width: 100,
            renderer: function(value) {
                return transformSize(value);
            }
        },{
            header: 'Erstellungsdatum',
            dataIndex: 'added',
            align: 'right',
            width: 150
        }];
        this.dockedItems = [{
            xtype: 'gosToolbar',
            dock: 'top',
            items: [{
                itemId: 'explorerHtml5DeleteButton',
                iconCls: 'icon_system system_delete',
                requiredPermission: {
                    action: 'delete',
                    permission: GibsonOS.Permission.DELETE
                },
                disabled: true,
                handler: function() {
                    deleteFn();
                }
            },('->'),{
                xtype: 'gosModuleExplorerFileChromecastButton',
                itemId: 'explorerHtml5ChromecastButton'
            }]
        },{
            xtype: 'gosToolbarPaging',
            itemId: 'explorerHtml5Paging',
            store: this.store,
            displayMsg: 'Medien {0} - {1} von {2}',
            emptyMsg: 'Keine Medien vorhanden'
        }];

        this.callParent();

        this.on('itemdblclick', GibsonOS.module.explorer.html5.listener.itemDblClick);
        this.on('cellkeydown', function(table, td, cellIndex, record, tr, rowIndex, event, options) {
            if (event.getKey() == Ext.EventObject.DELETE) {
                deleteFn();
            }
        });
        this.on('selectionchange', function(selection) {
            var button = grid.down('#explorerHtml5DeleteButton');

            if (selection.getCount() == 0) {
                button.disable();
            } else {
                button.enable();
            }
        });
        this.on('destroy', function(grid, options) {
            grid.getStore().gos.data.runLoadThumbnails = false;
        });

        this.getStore().on('load', function(store) {
            var size = store.getProxy().getReader().jsonData.size;
            GibsonOS.module.explorer.html5.fn.updateSize(grid.down('#explorerHtml5Paging'), size);
        });
        this.getStore().on('remove', function(store, record) {
            var size = store.getProxy().getReader().jsonData.size;
            size -= record.get('size');
            store.getProxy().getReader().jsonData.size = size;
            GibsonOS.module.explorer.html5.fn.updateSize(grid.down('#explorerHtml5Paging'), size);
        });
    }
});