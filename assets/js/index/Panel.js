Ext.define('GibsonOS.module.explorer.index.Panel', {
    extend: 'GibsonOS.module.core.component.Panel',
    alias: ['widget.gosModuleExplorerIndexPanel'],
    itemId: 'explorerIndexPanel',
    layout: 'border',
    initComponent: function() {
        const me = this;
        let uploadSize = 0;
        let uploadFiles = 0;
        let uploadedSize = 0;

        me.gos.data.path = [];
        me.gos.data.homePath = '/';
        me.gos.data.dirHistory = [];
        me.gos.data.dirHistoryPointer = -1;
        me.gos.data.updateBottomBar = function() {
            const view = me.down('#explorerIndexView');

            me.down('#explorerIndexSize').setText('Größe: ' + transformSize(view.gos.data.fileSize) + ' (' + transformSize(view.gos.data.dirSize) + ')');
            me.down('#explorerIndexFiles').setText('Dateien: ' + view.gos.data.fileCount + ' (' + view.gos.data.dirFileCount + ')');
            me.down('#explorerIndexDirs').setText('Ordner: ' + view.gos.data.dirCount + ' (' + view.gos.data.dirDirCount + ')');
        };

        me.items = [{
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
                            init(files) {
                                me.down('#explorerIndexUploadFile').updateProgress(0, '');
                                me.down('#explorerIndexUploadTotal').updateProgress(0, '0%');

                                me.down('#explorerIndexUploadSeparator').show();
                                me.down('#explorerIndexUploadFile').show();
                                me.down('#explorerIndexUploadTotal').show();

                                uploadFiles = files.length;

                                Ext.iterate(files, function(file) {
                                    uploadSize += file.size;
                                });
                            },
                            progress(event, file) {
                                let tmpSize = uploadedSize;
                                tmpSize += event.loaded;

                                me.down('#explorerIndexUploadFile').updateProgress(event.loaded / event.total, file.name);
                                me.down('#explorerIndexUploadTotal').updateProgress(tmpSize / uploadSize, Math.round(tmpSize / uploadSize * 100) + '%');
                            },
                            nextFile(response, file, prevFile) {
                                uploadedSize += prevFile.size;

                                if (
                                    response &&
                                    response.data
                                ) {
                                    me.down('#explorerIndexView').gos.store.add(response.data);
                                }

                                me.down('#explorerIndexUploadFile').updateProgress(0, '');
                            },
                            success(response) {
                                uploadSize = 0;
                                uploadFiles = 0;
                                uploadedSize = 0;

                                if (
                                    response &&
                                    response.data
                                ) {
                                    me.down('#explorerIndexView').gos.store.add(response.data);
                                }

                                me.down('#explorerIndexUploadSeparator').hide();
                                me.down('#explorerIndexUploadFile').hide();
                                me.down('#explorerIndexUploadTotal').hide();
                            },
                            failure() {
                                uploadSize = 0;
                                uploadFiles = 0;
                                uploadedSize = 0;

                                me.down('#explorerIndexUploadSeparator').hide();
                                me.down('#explorerIndexUploadFile').hide();
                                me.down('#explorerIndexUploadTotal').hide();
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
                itemclick(tree, record) {
                    const panel = tree.up('#explorerIndexPanel');
                    panel.gos.data.dirHistory.dirHistory = panel.gos.data.dirHistory.slice(0, panel.gos.data.dirHistoryPointer+1);

                    const viewStore = panel.down('#explorerIndexView').gos.store;
                    viewStore.getProxy().extraParams.dir = record.data.id;
                    viewStore.load();
                }
            }
        }];
        me.dockedItems = [{
            xtype: 'gosToolbar',
            dock: 'top',
            items: [{
                xtype: 'gosButton',
                itemId: 'explorerIndexBackButton',
                iconCls: 'icon_system system_back',
                requiredPermission: {
                    action: '',
                    permission: GibsonOS.Permission.READ,
                    method: 'GET'
                },
                disabled: true,
                handler() {
                    const panel = this.up('#explorerIndexPanel');

                    if (panel.gos.data.dirHistoryPointer > 0) {
                        panel.gos.data.dirHistoryPointer--;

                        const viewStore = panel.down('#explorerIndexView').gos.store;
                        viewStore.getProxy().extraParams.dir = panel.gos.data.dirHistory[panel.gos.data.dirHistoryPointer];
                        viewStore.load();
                    }
                }
            },{
                xtype: 'gosButton',
                itemId: 'explorerIndexNextButton',
                iconCls: 'icon_system system_next',
                requiredPermission: {
                    action: '',
                    permission: GibsonOS.Permission.READ,
                    method: 'GET'
                },
                disabled: true,
                handler() {
                    const panel = this.up('#explorerIndexPanel');

                    if (panel.gos.data.dirHistoryPointer < panel.gos.data.dirHistory.length-1) {
                        panel.gos.data.dirHistoryPointer++;

                        const viewStore = panel.down('#explorerIndexView').gos.store;
                        viewStore.getProxy().extraParams.dir = panel.gos.data.dirHistory[panel.gos.data.dirHistoryPointer];
                        viewStore.load();
                    }
                }
            },{
                xtype: 'gosButton',
                itemId: 'explorerIndexUpButton',
                iconCls: 'icon_system system_up',
                requiredPermission: {
                    action: '',
                    permission: GibsonOS.Permission.READ,
                    method: 'GET'
                },
                disabled: true,
                handler() {
                    const panel = this.up('#explorerIndexPanel');

                    if (panel.gos.data.path.length > 1) {
                        let pathString = '';

                        for (let i = 0; i < panel.gos.data.path.length-1; i++) {
                            pathString += panel.gos.data.path[i] + '/';
                        }

                        const viewStore = panel.down('#explorerIndexView').gos.store;
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
                    handler() {
                        const panel = this.up('#explorerIndexPanel');
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
                    handler() {
                        const panel = this.up('#explorerIndexPanel');
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
                    handler() {
                        const panel = this.up('#explorerIndexPanel');
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
                    handler() {
                        const panel = this.up('#explorerIndexPanel');
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
                    handler() {
                        const panel = this.up('#explorerIndexPanel');
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
                    handler() {
                        const panel = this.up('#explorerIndexPanel');
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

        me.callParent();

        const selectionChange = (selection, records) => {
            me.down('#explorerDirGrid').getSelectionModel().select(records, false, true);
            me.down('#explorerDirView32').getSelectionModel().select(records, false, true);
            me.down('#explorerDirView48').getSelectionModel().select(records, false, true);
            me.down('#explorerDirView64').getSelectionModel().select(records, false, true);
            me.down('#explorerDirView128').getSelectionModel().select(records, false, true);
            me.down('#explorerDirView256').getSelectionModel().select(records, false, true);
        };

        me.down('#explorerDirGrid').on('selectionchange', selectionChange);
        me.down('#explorerDirView32').on('selectionchange', selectionChange);
        me.down('#explorerDirView48').on('selectionchange', selectionChange);
        me.down('#explorerDirView64').on('selectionchange', selectionChange);
        me.down('#explorerDirView128').on('selectionchange', selectionChange);
        me.down('#explorerDirView256').on('selectionchange', selectionChange);

        const search = me.down('#explorerIndexSearch');
        search.on('keyup', function(textfield) {
            const search = textfield.getValue().toLowerCase();
            const viewStore = me.down('#explorerIndexView').gos.store;

            if (textfield.gos.data.searchActive) {
                textfield.gos.data.stopSearch = true;
            } else {
                textfield.gos.data.stopSearch = false;
                textfield.gos.data.searchActive = true;
            }

            viewStore.each((record) => {
                if (textfield.gos.data.stopSearch) {
                    textfield.gos.data.stopSearch = false;
                    textfield.gos.data.searchActive = false;
                    return false;
                }

                if (record.get('name').toLowerCase().indexOf(search) === -1) {
                    record.set('hidden', true);
                } else {
                    record.set('hidden', false);
                }
            });

            textfield.gos.data.searchActive = false;
        });
        search.on('beforeload', () => {
            me.down('#explorerIndexSearch').setValue(null);
        });

        const viewStore = me.down('#explorerIndexView').gos.store;
        viewStore.on('beforeload', () => {
            me.down('#explorerIndexSearch').setValue(null);
        });
        viewStore.on('load', (store) => {
            const dir = store.getProxy().getReader().jsonData.dir;

            me.gos.data.dir = dir;
            me.gos.data.path = store.getProxy().getReader().jsonData.path;
            me.gos.data.homePath = store.getProxy().getReader().jsonData.homePath;
            me.gos.data.updateBottomBar();

            const toolbarPath = me.down('#explorerIndexPath');
            toolbarPath.removeAll();

            for (let i = 0; i < me.gos.data.path.length; i++) {
                toolbarPath.add({
                    xtype: 'gosButton',
                    text: me.gos.data.path[i] + '/',
                    listeners: {
                        click(button) {
                            let pathString = '';

                            for (let i = 0; i < toolbarPath.items.items.length; i++) {
                                const item = toolbarPath.items.items[i];
                                pathString += item.text;

                                if (item.id === button.id) {
                                    break;
                                }
                            }

                            me.gos.data.dirHistory = me.gos.data.dirHistory.slice(0, me.gos.data.dirHistoryPointer+1);
                            store.getProxy().extraParams.dir = pathString;
                            store.load();
                        }
                    }
                });
            }

            // Dir History
            if (
                me.gos.data.dirHistory.length === 0 ||
                (
                    dir !== me.gos.data.dirHistory[me.gos.data.dirHistory.length-1] &&
                    me.gos.data.dirHistory.length-1 === me.gos.data.dirHistoryPointer
                )
            ) {
                me.gos.data.dirHistory.push(dir);
                me.gos.data.dirHistoryPointer++;
            }

            if (me.gos.data.dirHistoryPointer === me.gos.data.dirHistory.length-1) {
                me.down('#explorerIndexNextButton').disable();
            } else {
                me.down('#explorerIndexNextButton').enable();
            }

            if (me.gos.data.dirHistoryPointer === 0) {
                me.down('#explorerIndexBackButton').disable();
            } else {
                me.down('#explorerIndexBackButton').enable();
            }

            if (dir.length > me.gos.data.homePath.length) {
                me.down('#explorerIndexUpButton').enable();
            } else {
                me.down('#explorerIndexUpButton').disable();
            }

            // Tree
            const tree = me.down('#explorerDirTree');
            const node = tree.getStore().getNodeById(dir);

            if (!node) {
                tree.getStore().getProxy().extraParams.dir = dir;
                tree.getStore().load();
            } else {
                tree.getSelectionModel().select(node, false, true);
                tree.getView().focusRow(node);
            }
        });
        viewStore.on('add', (store, records) => {
            var view = me.down('#explorerIndexView');

            Ext.iterate(records, (record) => {
                if (record.get('type') === 'dir') {
                    view.gos.data.dirSize += record.get('size');
                    view.gos.data.dirCount++;
                } else {
                    view.gos.data.fileSize += record.get('size');
                    view.gos.data.fileCount++;
                }
            });

            me.gos.data.updateBottomBar();
        });
        viewStore.on('remove', function(store, record) {
            const view = me.down('#explorerIndexView');

            if (record.get('type') === 'dir') {
                view.gos.data.dirSize -= record.get('size');
                view.gos.data.dirCount--;
            } else {
                view.gos.data.fileSize -= record.get('size');
                view.gos.data.fileCount--;
            }

            me.gos.data.updateBottomBar();
        });
        viewStore.on('beforeload', (store) => {
            const tree = me.down('#explorerDirTree');
            const node = tree.getStore().getNodeById(store.getProxy().extraParams.dir);

            if (node) {
                node.expand();
            }
        });

        const view = me.down('#explorerIndexView');
        view.on('addDir', (button, response, dir, text) => {
            const tree = me.down('#explorerDirTree');
            const node = tree.getStore().getNodeById(dir);

            if (node) {
                node.appendChild({
                    iconCls: 'icon16 icon_dir',
                    id: dir + text + '/',
                    text: text
                });
            }
        });
        view.on('renameDir', (button, response, dir, oldName, record) => {
            const tree = me.down('#explorerDirTree');
            const node = tree.getStore().getNodeById(dir + '/' + oldName + '/');

            if (node) {
                node.set('text', record.get('name'));
                node.setId(dir + '/' + record.get('name') + '/');
                node.commit();
            }
        });
        view.on('deleteFile', (response, dir, records) => {
            const tree = me.down('#explorerDirTree');

            Ext.iterate(records, (record) => {
                if (record.get('type') === 'dir') {
                    const node = tree.getStore().getNodeById(dir + '/' + record.get('name') + '/');

                    if (node) {
                        node.remove(true);
                    }
                }
            });
        });

        const dirTree = me.down('#explorerDirTree');
        dirTree.on('addDir', (button, response, dir) => {
            const viewStore = me.down('#explorerIndexView').gos.store;

            if (dir === viewStore.getProxy().extraParams.dir) {
                viewStore.add(Ext.decode(response.responseText).data);
            }
        });
        dirTree.on('renameDir', (button, response, dir, oldName, node) => {
            const viewStore = me.down('#explorerIndexView').gos.store;

            if (dir === viewStore.getProxy().extraParams.dir) {
                const record = viewStore.getById(oldName);

                record.set('name', node.get('text'));
                record.commit();
            }
        });
        dirTree.on('deleteDir', (response, record) => {
            const viewStore = me.down('#explorerIndexView').gos.store;
            const parentDir = record.get('id').replace(/[^\/]*\/$/, '');

            if (record.get('id') === viewStore.getProxy().extraParams.dir) {
                me.down('#explorerIndexUpButton').handler();
            } else if (parentDir === viewStore.getProxy().extraParams.dir) {
                const dirParts = record.get('id').split('/');
                let dirName = dirParts[dirParts.length-1];

                if (!dirName) {
                    dirName = dirParts[dirParts.length-2];
                }

                viewStore.remove(viewStore.findRecord('name', dirName));
            }
        });
    }
});