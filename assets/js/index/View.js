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
    initComponent: function() {
        var view = this;

        view.gos.functions.loadThumbnails = function() {
            view.gos.store.gos.data.loadThumbnailsPointer = 0;
            view.gos.store.gos.data.runLoadThumbnails = true;
            view.gos.store.fireEvent('loadThumbnails', view.gos.store);
        };

        this.gos.store = new GibsonOS.module.explorer.dir.store.View({
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
        this.gos.store.on('load', function(store, records) {
            store.gos.data.runLoadThumbnails = false;

            if (store.getProxy().getReader().jsonData.meta) {
                view.gos.data.fileSize = store.getProxy().getReader().jsonData.meta.filesize;
                view.gos.data.dirSize = store.getProxy().getReader().jsonData.meta.dirsize;
                view.gos.data.fileCount = store.getProxy().getReader().jsonData.meta.filecount;
                view.gos.data.dirFileCount = store.getProxy().getReader().jsonData.meta.dirfilecount;
                view.gos.data.dirCount = store.getProxy().getReader().jsonData.meta.dircount;
                view.gos.data.dirDirCount = store.getProxy().getReader().jsonData.meta.dirdircount;
            }

            view.gos.functions.loadThumbnails();

            Ext.iterate(records, (record) => {
                if (record.get('html5MediaStatus') === 'generated') {
                    if (!chromeCast.itemsInView[record.get('html5MediaToken')]) {
                        chromeCast.itemsInView[record.get('html5MediaToken')] = [];
                    }
                    chromeCast.itemsInView[record.get('html5MediaToken')].push(record);
                }
            });
        }, this, {
            priority: 999
        });
        this.gos.store.on('add', function(store) {
            view.gos.functions.loadThumbnails(store);
        });
        this.gos.store.on('loadThumbnails', function(store) {
            if (!store.gos.data.runLoadThumbnails) {
                return false;
            }

            var pointer = store.gos.data.loadThumbnailsPointer;
            var records = store.getRange();

            for (var i = pointer; i < records.length; i++) {
                if (
                    !records[i].get('thumbAvailable') ||
                    records[i].get('thumb')
                ) {
                    continue;
                }

                GibsonOS.Ajax.request({
                    url: baseDir + 'explorer/file/image',
                    withoutFailure: true,
                    timeout: 120000,
                    params: {
                        dir: store.getProxy().getReader().jsonData.dir,
                        filename: records[i].get('name'),
                        width: 256,
                        height: 256,
                        base64: true
                    },
                    success: function(response) {
                        if (!store.gos.data.runLoadThumbnails) {
                            return false;
                        }
console.log(response.responseText);
                        records[i].set('thumb', response.responseText);
                        store.gos.data.loadThumbnailsPointer = i+1;

                        window.setTimeout(function () {
                            store.fireEvent('loadThumbnails', store)
                        }, 100);
                    },
                    failure: function() {
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

        this.callParent();

        this.items = [{
            xtype: 'gosModuleExplorerDirGrid',
            store: this.gos.store,
            listeners: {
                itemclick: GibsonOS.module.explorer.index.listener.itemClick
            },
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
            listeners: {
                itemclick: GibsonOS.module.explorer.index.listener.itemClick
            }
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
            listeners: {
                itemclick: GibsonOS.module.explorer.index.listener.itemClick
            }
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
            listeners: {
                itemclick: GibsonOS.module.explorer.index.listener.itemClick
            }
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
            listeners: {
                itemclick: GibsonOS.module.explorer.index.listener.itemClick
            }
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
            listeners: {
                itemclick: GibsonOS.module.explorer.index.listener.itemClick
            }
        }];

        this.callParent();

        this.on('destroy', function(panel) {
            panel.gos.store.gos.data.runLoadThumbnails = false;
        });
    }
});