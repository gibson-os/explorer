GibsonOS.define('GibsonOS.module.explorer.dir.action.Convert', {
    init(component) {
        component.addAction({
            iconCls: 'icon_system system_chromecast',
            text: 'An Chromecast senden',
            selectionNeeded: true,
            requiredPermission: {
                task: 'html5',
                action: 'convert',
                permission: GibsonOS.Permission.MANAGE
            },
            handler() {
                const me = this;
                const records = component.getSelectionModel().getSelection();
                const dir = component.getStore().getProxy().getReader().jsonData.dir;
                let files = [];

                Ext.iterate(records, (record) => {
                    if (
                        (
                            record.get('category') === GibsonOS.module.explorer.file.data.categories.VIDEO ||
                            record.get('category') === GibsonOS.module.explorer.file.data.categories.AUDIO
                        ) &&
                        record.get('html5MediaStatus') === null
                    ) {
                        return true;
                    }

                    files.push(record.get('name'));
                });

                me.convertHandler(dir, files, records, null, null);
            },
            listeners: {
                beforeshowparentmenu: (button) => {
                    const selectionModel = component.getSelectionModel();
                    const records = selectionModel.getSelection();
                    const dir = component.getStore().getProxy().getReader().jsonData.dir;
                    let disable = true;
                    let text = 'Für HTML5 konvertieren ';

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
                        let createSubMenu = () => {
                            let metaInfos = record.get('metaInfos') ?? {};

                            if (metaInfos.audioStreams) {
                                button.menu = new Ext.menu.Menu();

                                Ext.iterate(metaInfos.audioStreams, function(streamId, audioStream) {
                                    let audioMenu = {
                                        text: (audioStream.language ? audioStream.language + ' ' : '')
                                            + (audioStream.default ? '(Standard) ' : '')
                                            + audioStream.format + ' ['
                                            + audioStream.channels + ' '
                                            + audioStream.bitrate + ']',
                                        handler() {
                                            button.convertHandler(dir, [record.get('name')], records, streamId, null);
                                        }
                                    };

                                    subtitleMenu = [{
                                        text: 'Kein Untertitel',
                                        handler() {
                                            button.convertHandler(dir, [record.get('name')], records, streamId, 'none');
                                        }
                                    },('-')];

                                    Ext.iterate(metaInfos.subtitleStreams ?? {}, function(subtitleStreamId, subtitleStream) {
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
                                success(response) {
                                    var data = Ext.decode(response.responseText).data;
                                    record.set('metaInfos', data);
                                    button.setIconCls('icon16 icon_html5');
                                    createSubMenu();
                                },
                                failure() {
                                    button.setIconCls('icon16 icon_html5');
                                }
                            });
                        } else {
                            createSubMenu();
                        }
                    }

                    Ext.iterate(records, (record) => {
                        // if (record.get('type') === 'dir') {
                        //     disable = false;
                        // }

                        if (
                            record.get('category') === GibsonOS.module.explorer.file.data.categories.VIDEO ||
                            record.get('category') === GibsonOS.module.explorer.file.data.categories.AUDIO
                        ) {
                            button.gos.data.runRefresh = true;
                            button.gos.data.refresh = () => {
                                GibsonOS.Ajax.request({
                                    url: baseDir + 'explorer/html5/convertStatus',
                                    params: {
                                        token: record.get('html5MediaToken')
                                    },
                                    success(response) {
                                        const data = Ext.decode(response.responseText).data;

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

                                        record.set('html5MediaStatus', data.status);
                                        record.commit();
                                    }
                                });
                            };

                            switch (record.get('html5MediaStatus')) {
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
                hideparentmenu(button) {
                    button.gos.data.runRefresh = false;
                    document.getElementById(button.getId() + '-arrowEl').className = null;
                }
            }
        });
    }
});