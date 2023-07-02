Ext.define('GibsonOS.module.explorer.index.View', {
    extend: 'GibsonOS.Panel',
    alias: ['widget.gosModuleExplorerIndexView'],
    itemId: 'explorerIndexView',
    layout: 'fit',
    gos: {
        data: {
            fileSize: 0,
            dirSize: 0,
            fileCount: 0,
            dirFileCount: 0,
            dirCount: 0,
            dirDirCount: 0
        },
        functions: {
        }
    },
    initComponent() {
        const me = this;

        me.gos.functions.loadThumbnails = () => {
            me.gos.store.gos.data.loadThumbnailsPointer = 0;
            me.gos.store.gos.data.runLoadThumbnails = true;
            me.gos.store.fireEvent('loadThumbnails', me.gos.store);
        };

        me.gos.store = new GibsonOS.module.explorer.dir.store.View({
            gos: {
                data: {
                    runLoadThumbnails: true,
                    loadThumbnailsPointer: 0,
                    extraParams: {
                        dir: this.gos.data.dir
                    }
                }
            }
        });
        me.gos.store.on('load', (store, records) => {
            store.gos.data.runLoadThumbnails = false;

            if (store.getProxy().getReader().jsonData.meta) {
                me.gos.data.fileSize = store.getProxy().getReader().jsonData.meta.filesize;
                me.gos.data.dirSize = store.getProxy().getReader().jsonData.meta.dirsize;
                me.gos.data.fileCount = store.getProxy().getReader().jsonData.meta.filecount;
                me.gos.data.dirFileCount = store.getProxy().getReader().jsonData.meta.dirfilecount;
                me.gos.data.dirCount = store.getProxy().getReader().jsonData.meta.dircount;
                me.gos.data.dirDirCount = store.getProxy().getReader().jsonData.meta.dirdircount;
            }

            me.gos.functions.loadThumbnails();

            Ext.iterate(records, (record) => {
                if (record.get('html5MediaStatus') === 'generated') {
                    if (!chromeCast.itemsInView[record.get('html5MediaToken')]) {
                        chromeCast.itemsInView[record.get('html5MediaToken')] = [];
                    }

                    chromeCast.itemsInView[record.get('html5MediaToken')].push(record);
                }
            });
        }, me, {
            priority: 999
        });
        me.gos.store.on('add', (store) => {
            me.gos.functions.loadThumbnails(store);
        });
        me.gos.store.on('loadThumbnails', (store) => {
            if (!store.gos.data.runLoadThumbnails) {
                return false;
            }

            let pointer = store.gos.data.loadThumbnailsPointer;
            const records = store.getRange();

            for (var i = pointer; i < records.length; i++) {
                if (
                    !records[i].get('thumbAvailable') ||
                    records[i].get('thumb')
                ) {
                    continue;
                }

                GibsonOS.Ajax.request({
                    url: baseDir + 'explorer/file/image',
                    method: 'GET',
                    withoutFailure: true,
                    timeout: 120000,
                    params: {
                        dir: store.getProxy().getReader().jsonData.dir,
                        filename: records[i].get('name'),
                        width: 256,
                        height: 256,
                        base64: true
                    },
                    success(response) {
                        if (!store.gos.data.runLoadThumbnails) {
                            return false;
                        }

                        records[i].set('thumb', response.responseText);
                        store.gos.data.loadThumbnailsPointer = i+1;

                        window.setTimeout(() => {
                            store.fireEvent('loadThumbnails', store)
                        }, 100);
                    },
                    failure() {
                        if (!store.gos.data.runLoadThumbnails) {
                            return false;
                        }

                        store.gos.data.loadThumbnailsPointer = i+1;
                        window.setTimeout(function () {
                            store.fireEvent('loadThumbnails', store)
                        }, 100);
                    }
                });

                break;
            }
        });

        const listeners = {
            itemclick: GibsonOS.module.explorer.index.listener.itemClick,
            addDir(button, response, dir, child) {
                me.fireEvent('addDir', button, response, dir, child);
            },
            deleteFile(response, dir, records) {
                me.fireEvent('deleteFile', response, dir, records);
            },
            renameDir(button, response, dir, oldName, record) {
                me.fireEvent('renameDir', button, response, dir, oldName, record);
            }
        };

        me.items = [{
            xtype: 'gosModuleExplorerDirGrid',
            store: this.gos.store,
            listeners: listeners,
            gos: {
                functions: this.gos.functions
            }
        },{
            xtype: 'gosModuleExplorerDirView',
            store: this.gos.store,
            hidden: true,
            gos: {
                data: {
                    iconSize: 32
                },
                functions: this.gos.functions
            },
            listeners: listeners
        },{
            xtype: 'gosModuleExplorerDirView',
            store: this.gos.store,
            hidden: true,
            gos: {
                data: {
                    iconSize: 48
                },
                functions: this.gos.functions
            },
            listeners: listeners
        },{
            xtype: 'gosModuleExplorerDirView',
            store: this.gos.store,
            hidden: true,
            gos: {
                data: {
                    iconSize: 64
                },
                functions: this.gos.functions
            },
            listeners: listeners
        },{
            xtype: 'gosModuleExplorerDirView',
            store: this.gos.store,
            hidden: true,
            gos: {
                data: {
                    iconSize: 128
                },
                functions: this.gos.functions
            },
            listeners: listeners
        },{
            xtype: 'gosModuleExplorerDirView',
            store: this.gos.store,
            hidden: true,
            gos: {
                data: {
                    iconSize: 256
                },
                functions: this.gos.functions
            },
            listeners: listeners
        }];

        me.callParent();

        me.on('destroy', function(panel) {
            panel.gos.store.gos.data.runLoadThumbnails = false;
        });
    }
});