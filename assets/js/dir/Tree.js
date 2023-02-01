Ext.define('GibsonOS.module.explorer.dir.Tree', {
    extend: 'GibsonOS.module.core.component.tree.Panel',
    alias: ['widget.gosModuleExplorerDirViewTree'],
    itemId: 'explorerDirTree',
    requiredPermission: {
        module: 'explorer',
        task: 'file'
    },
    header: false,
    useArrows: true,
    deleteFunction(records) {
        const me = this;

        GibsonOS.module.explorer.file.fn.delete(records[0].get('id'), [], (response) => {
            me.fireEvent('deleteDir', response, record);
            record.remove();
        });
    },
    initComponent() {
        const me = this;

        me.store = new GibsonOS.module.explorer.dir.store.Tree({
            gos: {
                tree: this,
                data: {
                    extraParams: {
                        dir: this.gos.data.dir
                    }
                }
            }
        });

        me.callParent();

        me.addAction({
            text: 'Umbenennen',
            selectionNeeded: true,
            maxSelectionAllowed: 1,
            handler() {
            }
        });
        me.addAction({
            text: 'Ordner',
            iconCls: 'icon16 icon_dir',
            // requiredPermission: {
            //     module: 'explorer',
            //     task: 'dir',
            //     action: 'save'   ,
            //     permission: GibsonOS.Permission.WRITE
            // },
            handler() {
                const node = me.getSelectionModel().getSelection()[0];
                const dir = node.get('id');

                GibsonOS.module.explorer.dir.fn.add(dir, (response) => {
                    const data = Ext.decode(response.responseText).data;

                    node.appendChild({
                        iconCls: 'icon16 icon_dir',
                        id: dir + data.name + '/',
                        text: data.name
                    });

                    me.fireEvent('addDir', me, response, dir, data.name);
                });
            }
        });
    },
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