Ext.define('GibsonOS.module.explorer.dir.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
    alias: ['widget.gosModuleExplorerDirGrid'],
    itemId: 'explorerDirGrid',
    requiredPermission: {
        module: 'explorer',
        task: 'file'
    },
    enableToolbar: false,
    enablePagingBar: false,
    multiSelect: true,
    enableDrag: true,
    enterFunction(record) {
        const me = this;
        const dir = me.getStore().getProxy().getReader().jsonData.dir;

        if (record.get('type') === 'dir') {
            GibsonOS.module.explorer.dir.fn.open(me.getStore(), dir + record.get('name') + '/');
        } else {
            GibsonOS.module.explorer.file.fn.download(dir + record.get('name'));
        }
    },
    enterButton: {
        text: 'Öffnen',
        iconCls: '',
        // requiredPermission: {
        //     task: 'file',
        //     action: 'open',
        //     permission: GibsonOS.Permission.READ
        // }
    },
    deleteFunction(records) {
        const me = this;
        const dir = me.getStore().getProxy().getReader().jsonData.dir;

        GibsonOS.module.explorer.file.fn.delete(dir, records, (response) => {
            me.fireEvent('deleteFile', response, dir, records);
            me.getStore().remove(records);
        });
    },
    getShortcuts(records) {
        const me = this;
        const dir = me.getStore().getProxy().getReader().jsonData.dir;
        let shortcuts = [];

        Ext.iterate(records, (record) => {
            if (record.get('type') === 'dir') {
                shortcuts.push({
                    module: 'explorer',
                    task: 'index',
                    action: 'index',
                    text: record.get('name'),
                    icon: 'icon_dir',
                    parameters: {
                        dir: dir + record.get('name') + '/'
                    }
                });
            } else {
                shortcuts.push({
                    module: 'explorer',
                    task: 'file',
                    action: 'download',
                    text: record.get('name'),
                    icon: 'icon_' + record.get('type'),
                    parameters: {
                        path: dir + record.get('name')
                    }
                });
            }
        });

        return shortcuts;
    },
    viewConfig: {
        getRowClass(record) {
            if (record.get('hidden')) {
                return 'hideItem';
            }
        }
    },
    initComponent() {
        let me = this;

        me = GibsonOS.module.explorer.dir.decorator.Drop.init(me);

        me.callParent();

        me.addAction({
            text: 'Umbenennen',
            selectionNeeded: true,
            maxSelectionAllowed: 1,
            handler() {
                const record = me.getSelectionModel().getSelection()[0];

                GibsonOS.MessageBox.show({
                    title: 'Dateiname',
                    msg: 'Neuer Name',
                    type: GibsonOS.MessageBox.type.PROMPT,
                    promptParameter: 'newFilename',
                    okText: 'Umbenenen',
                    value: record.get('name')
                },{
                    url: baseDir + 'explorer/file/rename',
                    params: {
                        dir: dir,
                        oldFilename: record.get('name')
                    },
                    success(response) {
                        const data = Ext.decode(response.responseText).data;

                        record.set('name', data.filename);

                        if (data.type !== record.get('type')) {
                            record.set('type', data.type);
                            record.set('category', data.category);
                            record.set('thumbAvailable', data.thumbAvailable);
                            record.set('thumb', null);
                        }

                        record.commit();
                    }
                });
            }
        });
        GibsonOS.module.explorer.dir.action.Chromecast.init(me);
        GibsonOS.module.explorer.dir.action.Convert.init(me);
        me.addAction({
            xtype: 'menuseparator',
            addToContainerContextMenu: false,
        });
        me.addAction({
            text: 'Neu',
            menu: [
                GibsonOS.module.explorer.dir.action.AddDir.getConfig(me),
                GibsonOS.module.explorer.dir.action.AddFile.getConfig(me)
            ]
        });

        me.on('cellkeydown', (table, td, cellIndex, record, tr, rowIndex, event) => {
            if (
                event.getKey() !== Ext.EventObject.DELETE &&
                event.getKey() !== Ext.EventObject.RETURN
            ) {
                GibsonOS.module.explorer.dir.fn.jumpToItem(me, record, rowIndex, event);
            }
        });
        me.on('render', (grid) => {
            GibsonOS.module.explorer.file.fn.setUploadField(grid, grid.gos.functions && grid.gos.functions.upload ? grid.gos.functions.upload : {});

            // grid.dropZone = GibsonOS.module.explorer.dir.listener.dropZone(grid);
        });
    },
    getColumns() {
        return [{
            dataIndex: 'type',
            width: 25,
            renderer(value, metaData, record) {
                if (record.get('thumb')) {
                    return '<div class="icon icon16" style="' +
                        'background-image: url(data:image/png;base64,' + record.get('thumb') + '); ' +
                        'background-repeat: no-repeat; ' +
                        'background-size: contain; ' +
                        'background-position: center !important;' +
                        '"></div>';
                }

                let icon = 'icon_' + value;

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
            renderer(value) {
                const date = new Date(value * 1000);

                return Ext.Date.format(date, 'Y-m-d H:i:s');
            }
        },{
            header: 'Zuletzt bearbeitet',
            align: 'right',
            dataIndex: 'modified',
            width: 120,
            renderer(value) {
                const date = new Date(value * 1000);

                return Ext.Date.format(date, 'Y-m-d H:i:s');
            }
        },{
            header: 'Größe',
            align: 'right',
            dataIndex: 'size',
            width: 80,
            renderer(value) {
                return transformSize(value);
            }
        },{
            header: '&nbsp;',
            dataIndex: 'html5MediaStatus',
            width: 30,
            renderer(value, metaData, record) {
                return GibsonOS.module.explorer.file.fn.renderBadge(record.getData(), 16);
            }
        }]
    }
});