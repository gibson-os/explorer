GibsonOS.define('GibsonOS.module.explorer.dir.contextMenu.item', [{
    text: 'Neu',
    menu: [{
        xtype: 'gosModuleExplorerDirAddButton'
    },{
        xtype: 'gosModuleExplorerFileAddButton'
    }]
},('-'),{
    text: 'Öffnen',
    requiredPermission: {
        task: 'file',
        action: 'open',
        permission: GibsonOS.Permission.READ
    },
    handler: function() {
        var menu = this.up('#contextMenu');
        var view = menu.getParent();
        var record = menu.getRecord();
        var store = view.getStore();
        var proxy = store.getProxy();
        var dir = proxy.getReader().jsonData.dir;

        if (record.get('type') === 'dir') {
            GibsonOS.module.explorer.dir.fn.open(store, dir + record.get('name') + '/');
        } else {
            // Datei vorschau falls typ passt. Sonst Download
        }
    }
},{
    text: 'Umbennen',
    requiredPermission: {
        task: 'file',
        action: 'rename',
        permission: GibsonOS.Permission.WRITE
    },
    handler: function() {
        var menu = this.up('#contextMenu');
        var view = menu.getParent();
        var dir = view.getStore().getProxy().getReader().jsonData.dir;
        var record = view.getSelectionModel().getSelection()[0];

        GibsonOS.module.explorer.file.fn.rename(dir, record, function() {
            view.gos.functions.loadThumbnails();
        });
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
        var menu = button.up('#contextMenu');
        var view = menu.getParent();
        var dir = view.getStore().getProxy().getReader().jsonData.dir;
        var records = view.getSelectionModel().getSelection();

        GibsonOS.module.explorer.file.fn.delete(dir, records, function(response) {
            view.up().fireEvent('deleteFile', response, dir, records);
            view.getStore().remove(records);
        });
    }
},{
    text: 'Download',
    iconCls: 'icon_system system_down',
    requiredPermission: {
        task: 'file',
        action: 'download',
        permission: GibsonOS.Permission.READ
    },
    handler: function() {
        var menu = this.up('#contextMenu');
        var record = menu.getRecord();
        var dir = menu.getParent().getStore().getProxy().getReader().jsonData.dir;

        GibsonOS.module.explorer.file.fn.download(dir + record.get('name'));
    },
    listeners: {
        beforeshowparentmenu: function(button, menu) {
            var records = menu.getParent().getSelectionModel().getSelection();

            button.show();

            if (records.length > 1) {
                button.hide();
                return true;
            }

            Ext.iterate(records, function(record) {
                if (record.get('type') === 'dir') {
                    button.hide();
                    return true;
                }
            });
        }
    }
},{
    text: 'Download (zip)',
    iconCls: 'icon_system system_down',
    requiredPermission: {
        task: 'dir',
        action: 'download',
        permission: GibsonOS.Permission.READ
    },
    handler: function() {
        var menu = this.up('#contextMenu');
        var record = menu.getRecord();
        var dir = menu.getParent().getStore().getProxy().getReader().jsonData.dir;

        GibsonOS.module.explorer.dir.fn.download(dir + record.get('name'));
    },
    listeners: {
        beforeshowparentmenu: function(button, menu) {
            var records = menu.getParent().getSelectionModel().getSelection();

            button.show();

            if (
                records.length === 1 &&
                records[0].get('type') !== 'dir'
            ) {
                button.hide();
            }
        }
    }
},{
    xtype: 'gosModuleExplorerFileChromecastMenuItem',
    handler: function() {
        var menu = this.up('#contextMenu');
        var record = menu.getRecord();
        GibsonOS.module.explorer.file.chromecast.fn.play(record);
    },
    listeners: {
        beforeshowparentmenu: function(button, menu) {
            var record = menu.getRecord();

            if (record.get('html5VideoStatus') === 'generated') {
                button.enable();
            } else {
                button.disable();
            }
        }
    }
},{
    iconCls: 'icon16 icon_html5',
    requiredPermission: {
        task: 'html5',
        action: 'convert',
        permission: GibsonOS.Permission.MANAGE
    },
    handler: function() {
        var button = this;
        var menu = this.up('menu');
        var dir = menu.getParent().getStore().getProxy().getReader().jsonData.dir;
        var records = menu.getParent().getSelectionModel().getSelection();
        var files = [];

        Ext.iterate(records, function(record) {
            if (
                record.get('category') === GibsonOS.module.explorer.file.data.categories.VIDEO &&
                record.get('html5VideoStatus') === null
            ) {
                return true;
            }

            files.push(record.get('name'));
        });

        button.convertHandler(dir, files, records, null, null);
    },
    convertHandler: function(dir, files, records, audioStreamId, subtitleStreamId) {
        let me = this;

        GibsonOS.module.explorer.html5.fn.convert(dir, files, audioStreamId, subtitleStreamId, function(response) {
            let data = Ext.decode(response.responseText).data;
            me.disable();

            Ext.iterate(records, function(record) {
                if (!data[dir + record.get('name')]) {
                    return true;
                }

                record.set('html5VideoStatus', 'wait');
                record.set('html5VideoToken', data[dir + record.get('name')]);
                record.commit();
            });

            GibsonOS.MessageBox.show({
                title: 'Konvertieren!',
                msg: 'Dateien wurden zum konvertieren eingereiht!',
                type: GibsonOS.MessageBox.type.INFO
            });
        });
    },
    listeners: {
        beforeshowparentmenu: function(button, menu) {
            var selectionModel = menu.getParent().getSelectionModel();
            var records = selectionModel.getSelection();
            var dir = menu.getParent().getStore().getProxy().getReader().jsonData.dir;
            var disable = true;
            var text = 'Für HTML5 konvertieren ';

            button.menu = null;
            button.setText(text);
            button.setIconCls('icon16 icon_html5');

            if (!button.gos) {
                button.gos = {};
            }

            if (!button.gos.data) {
                button.gos.data = {};
            }

            if (
                records.length === 1 &&
                (
                    records[0].get('category') === GibsonOS.module.explorer.file.data.categories.VIDEO ||
                    records[0].get('category') === GibsonOS.module.explorer.file.data.categories.AUDIO
                )
            ) {
                let record = records[0];
                let createSubMenu = function() {
                    let metaInfos = record.get('metaInfos');

                    if (metaInfos.audioStreams) {
                        button.menu = new Ext.menu.Menu();

                        Ext.iterate(metaInfos.audioStreams, function(streamId, audioStream) {
                            let audioMenu = {
                                text: (audioStream.language ? audioStream.language + ' ' : '')
                                    + (audioStream.default ? '(Standard) ' : '')
                                    + audioStream.format + ' ['
                                    + audioStream.channels + ' '
                                    + audioStream.bitrate + ']',
                                handler: function() {
                                    button.convertHandler(dir, [record.get('name')], records, streamId, null);
                                }
                            };

                            subtitleMenu = [{
                                text: 'Kein Untertitel',
                                handler: function() {
                                    button.convertHandler(dir, [record.get('name')], records, streamId, 'none');
                                }
                            },('-')];

                            Ext.iterate(metaInfos.subtitleStreams, function(subtitleStreamId, subtitleStream) {
                                subtitleMenu.push({
                                    text: (subtitleStream.language ? subtitleStream.language + ' ' : '')
                                        + (subtitleStream.forced ? '(Forced) ' : '')
                                        + (subtitleStream.default ? '(Standard) ' : ''),
                                    handler: function() {
                                        button.convertHandler(dir, [record.get('name')], records, streamId, subtitleStreamId);
                                    }
                                });
                            });

                            if (subtitleMenu.length > 2) {
                                audioMenu.menu = subtitleMenu;
                            }

                            button.menu.add(audioMenu);
                        });

                        document.getElementById(button.getId() + '-arrowEl').className = 'x-menu-item-arrow';
                    }
                };

                if (!record.get('metaInfos') || !record.get('metaInfos').length) {
                    button.setIconCls('icon_loading');

                    GibsonOS.Ajax.request({
                        url: baseDir + 'explorer/file/metaInfos',
                        params: {
                            path: dir + record.get('name')
                        },
                        success: function(response) {
                            var data = Ext.decode(response.responseText).data;
                            record.set('metaInfos', data);
                            button.setIconCls('icon16 icon_html5');
                            createSubMenu();
                        },
                        failure: function() {
                            button.setIconCls('icon16 icon_html5');
                        }
                    });
                } else {
                    createSubMenu();
                }
            }

            Ext.iterate(records, function(record) {
                if (record.get('type') === 'dir') {
                    disable = false;
                }

                if (
                    record.get('category') === GibsonOS.module.explorer.file.data.categories.VIDEO ||
                    record.get('category') === GibsonOS.module.explorer.file.data.categories.AUDIO
                ) {
                    button.gos.data.runRefresh = true;
                    button.gos.data.refresh = function() {
                        GibsonOS.Ajax.request({
                            url: baseDir + 'explorer/html5/convertStatus',
                            params: {
                                token: record.get('html5VideoToken')
                            },
                            success: function(response) {
                                var data = Ext.decode(response.responseText).data;

                                if (selectionModel.getCount() === 1) {
                                    switch (data.status) {
                                        case 'error':
                                            button.setText(text + '(Fehler)');
                                            button.gos.data.runRefresh = false;
                                            break;
                                        case 'wait':
                                            if (button.gos.data.runRefresh) {
                                                button.setText(text + '(Warte)');
                                                window.setTimeout(button.gos.data.refresh, 900);
                                            }
                                            break;
                                        case 'generate':
                                            if (button.gos.data.runRefresh) {
                                                button.setText(text + '(' + data.percent + '% [' + data.timeRemaining + '])');
                                                window.setTimeout(button.gos.data.refresh, 900);
                                            }
                                            break;
                                        case 'generated':
                                            button.setText(text + '(Generiert)');
                                            button.gos.data.runRefresh = false;
                                            button.up('menu').down('#explorerFileChromecastMenuItem').enable();
                                            break;
                                    }
                                }

                                record.set('html5VideoStatus', data.status);
                                record.commit();
                            }
                        });
                    };

                    switch (record.get('html5VideoStatus')) {
                        case 'error':
                            if (selectionModel.getCount() === 1) {
                                button.setText(text + '(Fehler)');
                            }
                            break;
                        case 'wait':
                            if (selectionModel.getCount() === 1) {
                                button.setText(text + '(Warte)');
                                window.setTimeout(button.gos.data.refresh, 900);
                            }
                            break;
                        case 'generate':
                            if (selectionModel.getCount() === 1) {
                                button.setText(text + '(Generiere)');
                                window.setTimeout(button.gos.data.refresh, 900);
                            }
                            break;
                        case 'generated':
                            if (selectionModel.getCount() === 1) {
                                button.setText(text + '(Generiert)');
                            }
                            break;
                        default:
                            disable = false;
                            break;
                    }
                }
            });

            if (disable) {
                button.disable();
            } else {
                button.enable();
            }
        },
        hideparentmenu: function(button) {
            button.gos.data.runRefresh = false;
            document.getElementById(button.getId() + '-arrowEl').className = null;
        }
    }
},{
    requiredPermission: {
        action: 'properties',
        permission: GibsonOS.Permission.READ
    },
    text: 'Eigenschaften',
    handler: function() {
    }
}]);