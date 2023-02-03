Ext.define('GibsonOS.module.explorer.index.Panel', {
    extend: 'GibsonOS.Panel',
    alias: ['widget.gosModuleExplorerIndexPanel'],
    itemId: 'explorerIndexPanel',
    layout: 'border',
    initComponent: function() {
        var panel = this;
        var uploadSize = 0;
        var uploadFiles = 0;
        var uploadedSize = 0;

        this.gos.data.path = [];
        this.gos.data.homePath = '/';
        this.gos.data.dirHistory = [];
        this.gos.data.dirHistoryPointer = -1;
        this.gos.data.updateBottomBar = function() {
            var view = panel.down('#explorerIndexView');

            panel.down('#explorerIndexSize').setText('Größe: ' + transformSize(view.gos.data.fileSize) + ' (' + transformSize(view.gos.data.dirSize) + ')');
            panel.down('#explorerIndexFiles').setText('Dateien: ' + view.gos.data.fileCount + ' (' + view.gos.data.dirFileCount + ')');
            panel.down('#explorerIndexDirs').setText('Ordner: ' + view.gos.data.dirCount + ' (' + view.gos.data.dirDirCount + ')');
        };

        this.items = [{
            xtype: 'gosPanel',
            region: 'center',
            layout: 'border',
            items: [{
                xtype: 'gosModuleExplorerIndexView',
                region: 'center',
                flex: 0,
                gos: {
                    data: {
                        dir: this.gos.data.dir
                    },
                    functions: {
                        upload: {
                            init: function(files) {
                                panel.down('#explorerIndexUploadFile').updateProgress(0, '');
                                panel.down('#explorerIndexUploadTotal').updateProgress(0, '0%');

                                panel.down('#explorerIndexUploadSeparator').show();
                                panel.down('#explorerIndexUploadFile').show();
                                panel.down('#explorerIndexUploadTotal').show();

                                uploadFiles = files.length;

                                Ext.iterate(files, function(file) {
                                    uploadSize += file.size;
                                });
                            },
                            progress: function(event, file) {
                                var tmpSize = uploadedSize;
                                tmpSize += event.loaded;

                                panel.down('#explorerIndexUploadFile').updateProgress(event.loaded / event.total, file.name);
                                panel.down('#explorerIndexUploadTotal').updateProgress(tmpSize / uploadSize, Math.round(tmpSize / uploadSize * 100) + '%');
                            },
                            nextFile: function(response, file, prevFile) {
                                uploadedSize += prevFile.size;

                                if (
                                    response &&
                                    response.data
                                ) {
                                    panel.down('#explorerIndexView').gos.store.add(response.data);
                                }

                                panel.down('#explorerIndexUploadFile').updateProgress(0, '');
                            },
                            success: function(response) {
                                uploadSize = 0;
                                uploadFiles = 0;
                                uploadedSize = 0;

                                if (
                                    response &&
                                    response.data
                                ) {
                                    panel.down('#explorerIndexView').gos.store.add(response.data);
                                }

                                panel.down('#explorerIndexUploadSeparator').hide();
                                panel.down('#explorerIndexUploadFile').hide();
                                panel.down('#explorerIndexUploadTotal').hide();
                            },
                            failure: function(response) {
                                uploadSize = 0;
                                uploadFiles = 0;
                                uploadedSize = 0;

                                panel.down('#explorerIndexUploadSeparator').hide();
                                panel.down('#explorerIndexUploadFile').hide();
                                panel.down('#explorerIndexUploadTotal').hide();
                            }
                        }
                    }
                }
            },{
                xtype: 'gosModuleExplorerIndexProperties',
                region: 'east',
                flex: 0,
                collapsible: true,
                collapsed: true,
                split: true,
                width: 210,
                minWidth: 140,
                hideCollapseTool: true,
                header: false
            },{
                xtype: 'gosModuleExplorerIndexPreview',
                region: 'south',
                flex: 0,
                collapsible: true,
                collapsed: true,
                split: true,
                height: '50%',
                hideCollapseTool: true,
                header: false
            }]
        },{
            xtype: 'gosModuleExplorerDirViewTree',
            flex: 0,
            gos: {
                data: {
                    dir: this.gos.data.dir
                }
            },
            region: 'west',
            collapsible: true,
            split: true,
            width: 250,
            hideCollapseTool: true,
            listeners: {
                itemclick: function(tree, record, item, index, event, options) {
                    var panel = tree.up('#explorerIndexPanel');
                    panel.gos.data.dirHistory.dirHistory = panel.gos.data.dirHistory.slice(0, panel.gos.data.dirHistoryPointer+1);

                    var viewStore = panel.down('#explorerIndexView').gos.store;
                    viewStore.getProxy().extraParams.dir = record.data.id;
                    viewStore.load();
                }
            }
        }];
        this.dockedItems = [{
            xtype: 'gosToolbar',
            dock: 'top',
            items: [{
                xtype: 'gosButton',
                itemId: 'explorerIndexBackButton',
                iconCls: 'icon_system system_back',
                requiredPermission: {
                    action:'read',
                    permission: GibsonOS.Permission.READ
                },
                disabled: true,
                handler: function() {
                    var panel = this.up('#explorerIndexPanel');

                    if (panel.gos.data.dirHistoryPointer > 0) {
                        panel.gos.data.dirHistoryPointer--;

                        var viewStore = panel.down('#explorerIndexView').gos.store;
                        viewStore.getProxy().extraParams.dir = panel.gos.data.dirHistory[panel.gos.data.dirHistoryPointer];
                        viewStore.load();
                    }
                }
            },{
                xtype: 'gosButton',
                itemId: 'explorerIndexNextButton',
                iconCls: 'icon_system system_next',
                requiredPermission: {
                    action:'read',
                    permission: GibsonOS.Permission.READ
                },
                disabled: true,
                handler: function() {
                    var panel = this.up('#explorerIndexPanel');

                    if (panel.gos.data.dirHistoryPointer < panel.gos.data.dirHistory.length-1) {
                        panel.gos.data.dirHistoryPointer++;

                        var viewStore = panel.down('#explorerIndexView').gos.store;
                        viewStore.getProxy().extraParams.dir = panel.gos.data.dirHistory[panel.gos.data.dirHistoryPointer];
                        viewStore.load();
                    }
                }
            },{
                xtype: 'gosButton',
                itemId: 'explorerIndexUpButton',
                iconCls: 'icon_system system_up',
                requiredPermission: {
                    action:'read',
                    permission: GibsonOS.Permission.READ
                },
                disabled: true,
                handler: function() {
                    var panel = this.up('#explorerIndexPanel');

                    if (panel.gos.data.path.length > 1) {
                        var pathString = '';

                        for (var i = 0; i < panel.gos.data.path.length-1; i++) {
                            pathString += panel.gos.data.path[i] + '/';
                        }

                        var viewStore = panel.down('#explorerIndexView').gos.store;
                        viewStore.getProxy().extraParams.dir = pathString;
                        viewStore.load();
                    }
                }
            },('-'),{
                xtype: 'gosPanel',
                itemId: 'explorerIndexPath',
                frame: false,
                plain: false,
                flex: 0
            },('->'),{
                xtype: 'gosFormTextfield',
                itemId: 'explorerIndexSearch',
                enableKeyEvents: true,
                hideLabel: true,
                gos: {
                    data: {
                        searchActive: false,
                        stopSearch: false
                    }
                }
            },{
                xtype: 'gosButton',
                itemId: 'explorerIndexViewButton',
                iconCls: 'icon_system system_view_details',
                menu: [{
                    text: 'Sehr Kleine Symbole',
                    iconCls: 'icon_system system_view_very_small_icons',
                    handler: function() {
                        var panel = this.up('#explorerIndexPanel');
                        panel.down('#explorerDirView48').hide();
                        panel.down('#explorerDirView64').hide();
                        panel.down('#explorerDirView128').hide();
                        panel.down('#explorerDirView256').hide();
                        panel.down('#explorerDirGrid').hide();
                        panel.down('#explorerDirView32').show();
                    }
                },{
                    text: 'Kleine Symbole',
                    iconCls: 'icon_system system_view_small_icons',
                    handler: function() {
                        var panel = this.up('#explorerIndexPanel');
                        panel.down('#explorerDirView32').hide();
                        panel.down('#explorerDirView64').hide();
                        panel.down('#explorerDirView128').hide();
                        panel.down('#explorerDirView256').hide();
                        panel.down('#explorerDirGrid').hide();
                        panel.down('#explorerDirView48').show();
                    }
                },{
                    text: 'Mittlere Symbole',
                    iconCls: 'icon_system system_view_middle_icons',
                    handler: function() {
                        var panel = this.up('#explorerIndexPanel');
                        panel.down('#explorerDirView32').hide();
                        panel.down('#explorerDirView48').hide();
                        panel.down('#explorerDirView128').hide();
                        panel.down('#explorerDirView256').hide();
                        panel.down('#explorerDirGrid').hide();
                        panel.down('#explorerDirView64').show();
                    }
                },{
                    text: 'Große Symbole',
                    iconCls: 'icon_system system_view_big_icons',
                    handler: function() {
                        var panel = this.up('#explorerIndexPanel');
                        panel.down('#explorerDirView32').hide();
                        panel.down('#explorerDirView48').hide();
                        panel.down('#explorerDirView64').hide();
                        panel.down('#explorerDirView256').hide();
                        panel.down('#explorerDirGrid').hide();
                        panel.down('#explorerDirView128').show();
                    }
                },{
                    text: 'Sehr Große Symbole',
                    iconCls: 'icon_system system_view_very_big_icons',
                    handler: function() {
                        var panel = this.up('#explorerIndexPanel');
                        panel.down('#explorerDirView32').hide();
                        panel.down('#explorerDirView48').hide();
                        panel.down('#explorerDirView64').hide();
                        panel.down('#explorerDirView128').hide();
                        panel.down('#explorerDirGrid').hide();
                        panel.down('#explorerDirView256').show();
                    }
                },{
                    text: 'Liste',
                    iconCls: 'icon_system system_view_details',
                    handler: function() {
                        var panel = this.up('#explorerIndexPanel');
                        panel.down('#explorerDirView32').hide();
                        panel.down('#explorerDirView48').hide();
                        panel.down('#explorerDirView64').hide();
                        panel.down('#explorerDirView128').hide();
                        panel.down('#explorerDirView256').hide();
                        panel.down('#explorerDirGrid').show();
                    }
                }]
            },{
                xtype: 'gosModuleExplorerFileChromecastButton',
                itemId: 'explorerIndexChromecastButton'
            }]
        },{
            xtype: 'gosToolbar',
            dock: 'bottom',
            items: [{
                itemId: 'explorerIndexSize',
                xtype: 'gosToolbarTextItem',
                text: 'Größe: 0 ' + sizeUnits[0]
            },('-'),{
                itemId: 'explorerIndexFiles',
                xtype: 'gosToolbarTextItem',
                text: 'Dateien: 0'
            },('-'),{
                itemId: 'explorerIndexDirs',
                xtype: 'gosToolbarTextItem',
                text: 'Ordner: 0'
            },{
                xtype: 'tbseparator',
                itemId: 'explorerIndexUploadSeparator',
                hidden: true
            },{
                xtype: 'progressbar',
                itemId: 'explorerIndexUploadFile',
                hidden: true,
                width: 250
            },{
                xtype: 'progressbar',
                itemId: 'explorerIndexUploadTotal',
                hidden: true,
                width: 150
            }]
        }];

        this.callParent();

        var selectionChange = function(selection, records) {
            panel.down('#explorerDirGrid').getSelectionModel().select(records, false, true);
            panel.down('#explorerDirView32').getSelectionModel().select(records, false, true);
            panel.down('#explorerDirView48').getSelectionModel().select(records, false, true);
            panel.down('#explorerDirView64').getSelectionModel().select(records, false, true);
            panel.down('#explorerDirView128').getSelectionModel().select(records, false, true);
            panel.down('#explorerDirView256').getSelectionModel().select(records, false, true);
        };

        this.down('#explorerDirGrid').on('selectionchange', selectionChange);
        this.down('#explorerDirView32').on('selectionchange', selectionChange);
        this.down('#explorerDirView48').on('selectionchange', selectionChange);
        this.down('#explorerDirView64').on('selectionchange', selectionChange);
        this.down('#explorerDirView128').on('selectionchange', selectionChange);
        this.down('#explorerDirView256').on('selectionchange', selectionChange);

        this.down('#explorerIndexSearch').on('keyup', function(textfield) {
            var search = textfield.getValue().toLowerCase();
            var viewStore = panel.down('#explorerIndexView').gos.store;

            if (textfield.gos.data.searchActive) {
                textfield.gos.data.stopSearch = true;
            } else {
                textfield.gos.data.stopSearch = false;
                textfield.gos.data.searchActive = true;
            }

            viewStore.each(function(record) {
                if (textfield.gos.data.stopSearch) {
                    textfield.gos.data.stopSearch = false;
                    textfield.gos.data.searchActive = false;
                    return false;
                }

                if (record.get('name').toLowerCase().indexOf(search) == -1) {
                    record.set('hidden', true);
                } else {
                    record.set('hidden', false);
                }
            });

            textfield.gos.data.searchActive = false;
        });
        this.down('#explorerIndexSearch').on('beforeload', function(store, operation, options) {
            panel.down('#explorerIndexSearch').setValue(null);
        });
        this.down('#explorerIndexView').gos.store.on('beforeload', function(store, operation, options) {
            panel.down('#explorerIndexSearch').setValue(null);
        });
        this.down('#explorerIndexView').gos.store.on('load', function(store, records, successful, operation, options) {
            var dir = store.getProxy().getReader().jsonData.dir;

            panel.gos.data.dir = dir;
            panel.gos.data.path = store.getProxy().getReader().jsonData.path;
            panel.gos.data.homePath = store.getProxy().getReader().jsonData.homePath;
            panel.gos.data.updateBottomBar();

            var toolbarPath = panel.down('#explorerIndexPath');
            toolbarPath.removeAll();

            for (var i = 0; i < panel.gos.data.path.length; i++) {
                toolbarPath.add({
                    xtype: 'gosButton',
                    text: panel.gos.data.path[i] + '/',
                    listeners: {
                        click: function(button, event, options) {
                            var pathString = '';

                            for (var i = 0; i < toolbarPath.items.items.length; i++) {
                                var item = toolbarPath.items.items[i];
                                pathString += item.text;

                                if (item.id == button.id) {
                                    break;
                                }
                            }

                            panel.gos.data.dirHistory = panel.gos.data.dirHistory.slice(0, panel.gos.data.dirHistoryPointer+1);
                            store.getProxy().extraParams.dir = pathString;
                            store.load();
                        }
                    }
                });
            }

            // Dir History
            if (
                panel.gos.data.dirHistory.length == 0 ||
                (
                dir != panel.gos.data.dirHistory[panel.gos.data.dirHistory.length-1] &&
                panel.gos.data.dirHistory.length-1 == panel.gos.data.dirHistoryPointer
                )
            ) {
                panel.gos.data.dirHistory.push(dir);
                panel.gos.data.dirHistoryPointer++;
            }

            if (panel.gos.data.dirHistoryPointer == panel.gos.data.dirHistory.length-1) {
                panel.down('#explorerIndexNextButton').disable();
            } else {
                panel.down('#explorerIndexNextButton').enable();
            }

            if (panel.gos.data.dirHistoryPointer == 0) {
                panel.down('#explorerIndexBackButton').disable();
            } else {
                panel.down('#explorerIndexBackButton').enable();
            }

            if (dir.length > panel.gos.data.homePath.length) {
                panel.down('#explorerIndexUpButton').enable();
            } else {
                panel.down('#explorerIndexUpButton').disable();
            }

            // Tree
            var tree = panel.down('#explorerDirTree');
            var node = tree.getStore().getNodeById(dir);

            if (!node) {
                tree.getStore().getProxy().extraParams.dir = dir;
                tree.getStore().load();
            } else {
                tree.getSelectionModel().select(node, false, true);
                tree.getView().focusRow(node);
            }
        });
        this.down('#explorerIndexView').gos.store.on('add', function(store, records, index, options) {
            var view = panel.down('#explorerIndexView');

            Ext.iterate(records, function(record) {
                if (record.get('type') == 'dir') {
                    view.gos.data.dirSize += record.get('size');
                    view.gos.data.dirCount++;
                } else {
                    view.gos.data.fileSize += record.get('size');
                    view.gos.data.fileCount++;
                }
            });

            panel.gos.data.updateBottomBar();
        });
        this.down('#explorerIndexView').gos.store.on('remove', function(store, record, index, isMove, options) {
            var view = panel.down('#explorerIndexView');

            if (record.get('type') == 'dir') {
                view.gos.data.dirSize -= record.get('size');
                view.gos.data.dirCount--;
            } else {
                view.gos.data.fileSize -= record.get('size');
                view.gos.data.fileCount--;
            }

            panel.gos.data.updateBottomBar();
        });
        this.down('#explorerIndexView').gos.store.on('beforeload', function(store, operations, options) {
            var tree = panel.down('#explorerDirTree');
            var node = tree.getStore().getNodeById(store.getProxy().extraParams.dir);

            if (node) {
                node.expand();
            }
        });

        this.down('#explorerIndexView').on('addDir', (button, response, dir, text) => {
            const tree = panel.down('#explorerDirTree');
            const node = tree.getStore().getNodeById(dir);

            if (node) {
                node.appendChild({
                    iconCls: 'icon16 icon_dir',
                    id: dir + text + '/',
                    text: text
                });
            }
        });
        this.down('#explorerIndexView').on('renameDir', (button, response, dir, oldName, record) => {
            const tree = panel.down('#explorerDirTree');
            const node = tree.getStore().getNodeById(dir + oldName + '/');

            if (node) {
                node.set('text', record.get('name'));
                node.setId(dir + record.get('name') + '/');
                node.commit();
            }
        });
        this.down('#explorerIndexView').on('deleteFile', (response, dir, records) => {
            const tree = panel.down('#explorerDirTree');

            Ext.iterate(records, function(record) {
                if (record.get('type') === 'dir') {
                    const node = tree.getStore().getNodeById(dir + record.get('name') + '/');

                    if (node) {
                        node.remove(true);
                    }
                }
            });
        });

        this.down('#explorerDirTree').on('addDir', (button, response, dir) => {
            const viewStore = panel.down('#explorerIndexView').gos.store;

            if (dir === viewStore.getProxy().extraParams.dir) {
                viewStore.add(Ext.decode(response.responseText).data);
            }
        });
        this.down('#explorerDirTree').on('renameDir', (button, response, dir, oldName, node) => {
            const viewStore = panel.down('#explorerIndexView').gos.store;

            if (dir === viewStore.getProxy().extraParams.dir) {
                const record = viewStore.getById(oldName);

                record.set('name', node.get('text'));
                record.commit();
            }
        });
        this.down('#explorerDirTree').on('deleteDir', (response, record) => {
            const viewStore = panel.down('#explorerIndexView').gos.store;
            const parentDir = record.get('id').replace(/[^\/]*\/$/, '');

            if (record.get('id') === viewStore.getProxy().extraParams.dir) {
                panel.down('#explorerIndexUpButton').handler();
            } else if (parentDir === viewStore.getProxy().extraParams.dir) {
                var dirParts = record.get('id').split('/');
                var dirName = dirParts[dirParts.length-1];

                if (!dirName) {
                    dirName = dirParts[dirParts.length-2];
                }

                viewStore.remove(viewStore.findRecord('name', dirName));
            }
        });
        this.down('#explorerDirTree').on('convertVideoSuccess', function(button, response) {
            var data = Ext.decode(response.responseText).data;
            var menu = button.up('menu');
            var parent = menu.getParent();
            var viewStore = parent.up('#app').down('#explorerIndexView').gos.store;

            if (menu.getRecord().get('id') == viewStore.getProxy().extraParams.dir) {
                viewStore.each(function(record) {
                    if (!data[record.get('name')]) {
                        return true;
                    }

                    record.set('html5MediaStatus', 'wait');
                    record.set('html5MediaToken', data[record.get('name')]);
                    record.commit();
                });
            }

            GibsonOS.MessageBox.show({
                title: 'Konvertieren!',
                msg: 'Dateien wurden zum konvertieren eingereiht!',
                type: GibsonOS.MessageBox.type.INFO
            });
        });
    }
});