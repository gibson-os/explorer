Ext.define('GibsonOS.module.explorer.dir.Grid', {
    extend: 'GibsonOS.grid.Panel',
    alias: ['widget.gosModuleExplorerDirGrid'],
    itemId: 'explorerDirGrid',
    requiredPermission: {
        module: 'explorer',
        task: 'file'
    },
    multiSelect: true,
    columns: [{
        dataIndex: 'type',
        width: 25,
        renderer: function(value, metaData, record) {
            if (record.get('thumb')) {
                return '<div class="icon icon16" style="background-image: url(data:image/png;base64,' + record.get('thumb') + ');"></div>';
            }

            var icon = 'icon_' + value;

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
        renderer: function(value) {
            var date = new Date(value * 1000);
            return Ext.Date.format(date, 'Y-m-d H:i:s');
        }
    },{
        header: 'Zuletzt bearbeitet',
        align: 'right',
        dataIndex: 'modified',
        width: 120,
        renderer: function(value) {
            var date = new Date(value * 1000);
            return Ext.Date.format(date, 'Y-m-d H:i:s');
        }
    },{
        header: 'Größe',
        align: 'right',
        dataIndex: 'size',
        width: 80,
        renderer: function(value) {
            return transformSize(value);
        }
    },{
        header: '&nbsp;',
        dataIndex: 'html5MediaStatus',
        width: 30,
        renderer: function(value, metaData, record) {
            return GibsonOS.module.explorer.file.fn.renderBadge(record.getData(), 16);
        }
    }],
    viewConfig: {
        getRowClass: function(record) {
            if (record.get('hidden')) {
                return 'hideItem';
            }
        },
        listeners: {
            render: function(view) {
                var grid = view.up('gridpanel');

                grid.dragZone = Ext.create('Ext.dd.DragZone', view.getEl(), {
                    getDragData: function(event) {
                        var proxy = grid.getStore().getProxy();
                        var dir = proxy.getReader().jsonData.dir;
                        var sourceElement = event.getTarget().parentNode.parentNode;
                        var record = view.getRecord(sourceElement);

                        if (
                            record &&
                            sourceElement
                        ) {
                            let clone = sourceElement.cloneNode(true);
                            let moveData = {
                                grid: grid,
                                record: record
                            };
                            let data;

                            if (record.get('type') === 'dir') {
                                data = {
                                    module: 'explorer',
                                    task: 'index',
                                    action: 'index',
                                    text: record.get('name'),
                                    icon: 'icon_dir',
                                    customIcon: record.get('icon'),
                                    params: {
                                        dir: dir + record.get('name') + '/'
                                    }
                                };

                                moveData.type = 'dir';
                            } else {
                                data = {
                                    module: 'explorer',
                                    task: 'file',
                                    action: 'download',
                                    text: record.get('name'),
                                    icon: 'icon_' + record.get('type'),
                                    thumb: record.get('thumb'),
                                    params: {
                                        path: dir + record.get('name')
                                    }
                                };

                                moveData.type = 'file';
                            }

                            return grid.dragData = {
                                sourceEl: sourceElement,
                                repairXY: Ext.fly(sourceElement).getXY(),
                                ddel: clone,
                                shortcut: data,
                                moveData: moveData
                            };
                        }
                    },
                    getRepairXY: function() {
                        return this.dragData.repairXY;
                    }
                });
            }
        }
    },
    initComponent: function() {
        var grid = this;

        this.itemContextMenu = GibsonOS.module.explorer.dir.contextMenu.item;
        this.containerContextMenu = GibsonOS.module.explorer.dir.contextMenu.container;

        this.callParent();

        this.on('itemdblclick', GibsonOS.module.explorer.dir.listener.itemDblClick);
        this.on('cellkeydown', function(table, td, cellIndex, record, tr, rowIndex, event) {
            if (event.getKey() === Ext.EventObject.DELETE) {
                var dir = grid.getStore().getProxy().getReader().jsonData.dir;
                var records = grid.getSelectionModel().getSelection();

                GibsonOS.module.explorer.file.fn.delete(dir, records, function(response) {
                    grid.up().fireEvent('deleteFile', response, dir, records);
                    grid.getStore().remove(records);
                });
            } else if (event.getKey() === Ext.EventObject.RETURN) {
                GibsonOS.module.explorer.dir.listener.itemDblClick(grid, record);
            } else {
                GibsonOS.module.explorer.dir.fn.jumpToItem(grid, record, rowIndex, event);
            }
        });
        this.on('render', function(grid) {
            GibsonOS.module.explorer.file.fn.setUploadField(grid, grid.gos.functions && grid.gos.functions.upload ? grid.gos.functions.upload : {});

            grid.dropZone = GibsonOS.module.explorer.dir.listener.dropZone(grid);
        });
    }
});