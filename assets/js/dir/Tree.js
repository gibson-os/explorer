Ext.define('GibsonOS.module.explorer.dir.Tree', {
    extend: 'GibsonOS.tree.Panel',
    alias: ['widget.gosModuleExplorerDirViewTree'],
    itemId: 'explorerDirTree',
    requiredPermission: {
        module: 'explorer',
        task: 'file'
    },
    header: false,
    useArrows: true,
    initComponent: function() {
        var tree = this;

        this.store = new GibsonOS.module.explorer.dir.store.Tree({
            gos: {
                tree: this,
                data: {
                    extraParams: {
                        dir: this.gos.data.dir
                    }
                }
            }
        });

        this.callParent();

        this.on('cellkeydown', function(view, td, cellIndex, record, tr, rowIndex, event) {
            if (event.getKey() == Ext.EventObject.DELETE) {
                GibsonOS.module.explorer.file.fn.delete(record.get('id'), [], function(response) {
                    tree.fireEvent('deleteDir', response, record);
                    record.remove();
                });
            }
        });
    },
    itemContextMenu: [{
        text: 'Neuer Ordner',
        iconCls: 'icon16 icon_dir',
        requiredPermission: {
            action: 'save',
            permission: GibsonOS.Permission.WRITE
        },
        handler: function() {
            var button = this;
            var menu = this.up('#contextMenu');
            var parent = menu.getParent();
            var store = parent.getStore();
            var dir = menu.getRecord().get('id');

            GibsonOS.module.explorer.dir.fn.add(dir, function(response) {
                var data = Ext.decode(response.responseText).data;

                var node = parent.getSelectionModel().getLastSelected();
                var child = node.appendChild({
                    iconCls: 'icon16 icon_dir',
                    id: dir + data.name + '/',
                    text: data.name
                });

                parent.fireEvent('addDir', button, response, dir, child);
            });
        }
    },('-'),{
        text: 'Umbennen',
        requiredPermission: {
            action: 'rename',
            permission: GibsonOS.Permission.WRITE
        },
        handler: function() {
            /*Ext.MessageBox.prompt('Neuer Name', 'Neuer Name', function(btn, text) {
             if (btn == 'ok') {
             record.set('text', text);
             saveDesktop();
             }
             }, window, false, record.get('text'));*/
        }
    },{
        text: 'Löschen',
        iconCls: 'icon_system system_delete',
        requiredPermission: {
            action: 'delete',
            permission: GibsonOS.Permission.DELETE
        },
        handler: function() {
            var button = this;
            var menu = this.up('#contextMenu');
            var parent = menu.getParent();
            var dir = menu.getRecord().get('id');

            GibsonOS.module.explorer.file.fn.delete(dir, [], function(response) {
                var node = parent.getSelectionModel().getLastSelected();
                parent.fireEvent('deleteDir', response, node);
                node.remove();
            });
        }
    },{
        text: 'Download (zip)',
        iconCls: 'icon_system system_down',
        requiredPermission: {
            action: 'download',
            permission: GibsonOS.Permission.READ
        },
        handler: function() {
        }
    },{
        text: 'Für HTML5 konvertieren',
        iconCls: 'icon16 icon_html5',
        requiredPermission: {
            task: 'html5',
            action: 'convert',
            permission: GibsonOS.Permission.MANAGE
        },
        handler: function() {
            var button = this;
            var menu = this.up('#contextMenu');
            var parent = menu.getParent();
            var record = menu.getRecord();

            GibsonOS.Ajax.request({
                url: baseDir + 'explorer/html5/convert',
                params: {
                    dir: record.get('id')
                },
                success: function(response) {
                    parent.fireEvent('convertVideoSuccess', button, response);
                }
            });
        }
    },{
        requiredPermission: {
            action: 'properties',
            permission: GibsonOS.Permission.READ
        },
        text: 'Eigenschaften',
        handler: function() {
        }
    }],
    viewConfig: {
        listeners: {
            render: function(view) {
                var tree = view.up('treepanel');

                tree.dragZone = Ext.create('Ext.dd.DragZone', tree.getEl(), {
                    getDragData: function(event) {
                        var tree = view.up('treepanel');
                        var proxy = tree.getStore().getProxy();
                        var dir = proxy.getReader().jsonData.dir;
                        var sourceElement = event.getTarget().parentNode.parentNode.parentNode;

                        if (sourceElement) {
                            var record = view.getRecord(sourceElement);
                            var clone = sourceElement.getElementsByTagName('span')[0].cloneNode(true);
                            var moveData = {
                                grid: tree,
                                record: record,
                                type: 'dir'
                            };
                            var data = {
                                module: 'explorer',
                                task: 'index',
                                action: 'index',
                                text: record.get('text'),
                                icon: 'icon_dir',
                                params: {
                                    dir: record.get('id')
                                }
                            };

                            return tree.dragData = {
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
                tree.dropZone = GibsonOS.dropZones.add(tree.getEl(), {
                    getTargetFromEvent: function(event) {
                        return event.getTarget('#' + tree.getId());
                        //return event.getTarget('.x-grid-row');
                    },
                    onNodeOver : function(target, dd, event, data) {
                        if (data.moveData) {
                            return Ext.dd.DropZone.prototype.dropAllowed;
                        }

                        return Ext.dd.DropZone.prototype.dropNotAllowed;
                    },
                    onNodeDrop: function(target, dd, event, data) {
                        data = data.moveData;
                        data.grid = tree;
                        data.to = view.getRecord(target).get('id');

                        //GibsonOS.module.explorer.file.fn.move(data);
                    }
                });
            }
        }
    }
});