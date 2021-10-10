Ext.define('GibsonOS.module.explorer.trash.Container', {
    extend: 'GibsonOS.Panel',
    alias: ['widget.gosModuleExplorerTrashContainer'],
    itemId: 'explorerTrashView',
    layout: 'fit',
    gos: {
        data: {
            fileSize: 0,
            fileCount: 0,
            dirCount: 0
        }
    },
    initComponent: function() {
        const me = this;

        me.gos.store = new GibsonOS.module.explorer.trash.store.View();

        const checkEmptyButton = function() {
            const emptyButton = me.down('#explorerTrashEmptyButton');
            const records = me.gos.store.getRange();

            if (records.length) {
                emptyButton.enable();
            } else {
                emptyButton.disable();
            }
        };

        me.gos.store.on('load', function(store) {
            const dir = store.getProxy().getReader().jsonData.dir;

            if (store.getProxy().getReader().jsonData.meta) {
                me.gos.data.fileSize = store.getProxy().getReader().jsonData.meta.fileSize;
                me.gos.data.fileCount = store.getProxy().getReader().jsonData.meta.fileCount;
                me.gos.data.dirCount = store.getProxy().getReader().jsonData.meta.dirCount;
            }

            checkEmptyButton();
        }, me, {
            priority: 999
        });
        me.gos.store.on('remove', checkEmptyButton);

        me.items = [{
            xtype: 'gosModuleExplorerTrashGrid',
            store: me.gos.store,
            listeners: {
                //itemclick: GibsonOS.module.ftp.index.itemClick
            }
        },{
            xtype: 'gosModuleExplorerTrashView',
            store: me.gos.store,
            hidden: true,
            gos: {
                data: {
                    iconSize: 32
                }
            },
            listeners: {
                //itemclick: GibsonOS.module.explorer.index.listener.itemClick
            }
        },{
            xtype: 'gosModuleExplorerTrashView',
            store: me.gos.store,
            hidden: true,
            gos: {
                data: {
                    iconSize: 48
                }
            },
            listeners: {
                //itemclick: GibsonOS.module.explorer.index.listener.itemClick
            }
        },{
            xtype: 'gosModuleExplorerTrashView',
            store: me.gos.store,
            hidden: true,
            gos: {
                data: {
                    iconSize: 64
                }
            },
            listeners: {
                //itemclick: GibsonOS.module.explorer.index.listener.itemClick
            }
        },{
            xtype: 'gosModuleExplorerTrashView',
            store: me.gos.store,
            hidden: true,
            gos: {
                data: {
                    iconSize: 128
                }
            },
            listeners: {
                //itemclick: GibsonOS.module.explorer.index.listener.itemClick
            }
        },{
            xtype: 'gosModuleExplorerTrashView',
            store: me.gos.store,
            hidden: true,
            gos: {
                data: {
                    iconSize: 256
                }
            },
            listeners: {
                //itemclick: GibsonOS.module.explorer.index.listener.itemClick
            }
        }];

        me.tbar = [{
            iconCls: 'icon_system system_refresh',
            requiredPermission: {
                action: 'read',
                permission: GibsonOS.Permission.READ
            },
            handler: function() {
                me.gos.store.load();
            }
        },{
            itemId: 'explorerTrashEmptyButton',
            iconCls: 'icon_system system_delete',
            text: 'Leeren',
            disabled: true,
            requiredPermission: {
                action: 'delete',
                permission: GibsonOS.Permission.DELETE
            },
            handler: function() {
                GibsonOS.module.explorer.trash.fn.delete(me, me.down('#explorerTrashGrid').getStore().getRange());
            }
        },{
            itemId: 'explorerTrashRestoreButton',
            text: 'Wiederherstellen',
            disabled: true,
            requiredPermission: {
                action: 'restore',
                permission: GibsonOS.Permission.WRITE
            },
            handler: function() {
                var records = me.down('#explorerTrashGrid').getSelectionModel().getSelection();
                var tokens = [];

                Ext.iterate(records, function(record) {
                    tokens.push(record.get('token'));
                });

                GibsonOS.Ajax.request({
                    url: baseDir + 'explorer/trash/restore',
                    params: {
                        'tokens[]': tokens
                    },
                    success: function(response) {
                        me.gos.store.remove(records);
                    }
                });
            }
        },{
            itemId: 'explorerTrashDeleteButton',
            iconCls: 'icon_system system_delete',
            disabled: true,
            requiredPermission: {
                action: 'delete',
                permission: GibsonOS.Permission.DELETE
            },
            handler: function() {
                GibsonOS.module.explorer.trash.fn.delete(me, me.down('#explorerTrashGrid').getSelectionModel().getSelection());
            }
        },('->'),{
            xtype: 'gosButton',
            itemId: 'explorerTrashViewButton',
            iconCls: 'icon_system system_view_details',
            menu: [{
                text: 'Sehr Kleine Symbole',
                iconCls: 'icon_system system_view_very_small_icons',
                handler: function() {
                    me.down('#explorerTrashView48').hide();
                    me.down('#explorerTrashView64').hide();
                    me.down('#explorerTrashView128').hide();
                    me.down('#explorerTrashView256').hide();
                    me.down('#explorerTrashGrid').hide();
                    me.down('#explorerTrashView32').show();
                }
            },{
                text: 'Kleine Symbole',
                iconCls: 'icon_system system_view_small_icons',
                handler: function() {
                    me.down('#explorerTrashView32').hide();
                    me.down('#explorerTrashView64').hide();
                    me.down('#explorerTrashView128').hide();
                    me.down('#explorerTrashView256').hide();
                    me.down('#explorerTrashGrid').hide();
                    me.down('#explorerTrashView48').show();
                }
            },{
                text: 'Mittlere Symbole',
                iconCls: 'icon_system system_view_middle_icons',
                handler: function() {
                    me.down('#explorerTrashView32').hide();
                    me.down('#explorerTrashView48').hide();
                    me.down('#explorerTrashView128').hide();
                    me.down('#explorerTrashView256').hide();
                    me.down('#explorerTrashGrid').hide();
                    me.down('#explorerTrashView64').show();
                }
            },{
                text: 'Große Symbole',
                iconCls: 'icon_system system_view_big_icons',
                handler: function() {
                    me.down('#explorerTrashView32').hide();
                    me.down('#explorerTrashView48').hide();
                    me.down('#explorerTrashView64').hide();
                    me.down('#explorerTrashView256').hide();
                    me.down('#explorerTrashGrid').hide();
                    me.down('#explorerTrashView128').show();
                }
            },{
                text: 'Sehr Große Symbole',
                iconCls: 'icon_system system_view_very_big_icons',
                handler: function() {
                    me.down('#explorerTrashView32').hide();
                    me.down('#explorerTrashView48').hide();
                    me.down('#explorerTrashView64').hide();
                    me.down('#explorerTrashView128').hide();
                    me.down('#explorerTrashGrid').hide();
                    me.down('#explorerTrashView256').show();
                }
            },{
                text: 'Liste',
                iconCls: 'icon_system system_view_details',
                handler: function() {
                    me.down('#explorerTrashView32').hide();
                    me.down('#explorerTrashView48').hide();
                    me.down('#explorerTrashView64').hide();
                    me.down('#explorerTrashView128').hide();
                    me.down('#explorerTrashView256').hide();
                    me.down('#explorerTrashGrid').show();
                }
            }]
        }];

        me.callParent();

        const selectionChange = function(selection, records) {
            const restoreButton = me.down('#explorerTrashRestoreButton');
            const deleteButton = me.down('#explorerTrashDeleteButton');

            if (selection.getCount() === 0) {
                restoreButton.disable();
                deleteButton.disable();
            } else {
                restoreButton.enable();
                deleteButton.enable();
            }

            me.down('#explorerTrashGrid').getSelectionModel().select(records, false, true);
            me.down('#explorerTrashView32').getSelectionModel().select(records, false, true);
            me.down('#explorerTrashView48').getSelectionModel().select(records, false, true);
            me.down('#explorerTrashView64').getSelectionModel().select(records, false, true);
            me.down('#explorerTrashView128').getSelectionModel().select(records, false, true);
            me.down('#explorerTrashView256').getSelectionModel().select(records, false, true);
        };

        me.down('#explorerTrashGrid').on('selectionchange', selectionChange);
        me.down('#explorerTrashView32').on('selectionchange', selectionChange);
        me.down('#explorerTrashView48').on('selectionchange', selectionChange);
        me.down('#explorerTrashView64').on('selectionchange', selectionChange);
        me.down('#explorerTrashView128').on('selectionchange', selectionChange);
        me.down('#explorerTrashView256').on('selectionchange', selectionChange);
    }
});