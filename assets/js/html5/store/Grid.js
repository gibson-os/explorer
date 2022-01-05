Ext.define('GibsonOS.module.explorer.html5.store.Grid', {
    extend: 'GibsonOS.data.Store',
    alias: ['store.gosModuleExplorerHtml5GridStore'],
    pageSize: 100,
    model: 'GibsonOS.module.explorer.html5.model.Grid',
    constructor: function(data) {
        this.proxy = {
            type: 'gosDataProxyAjax',
            url: baseDir + 'explorer/html5/index'
        };

        this.callParent(arguments);

        this.on('load', function(store) {
            store.gos.data.loadThumbnailsPointer = 0;
            store.gos.data.runLoadThumbnails = true;
            store.fireEvent('loadThumbnails', store);
        }, this, {
            priority: 999
        });
        this.on('add', function(store) {
            store.gos.data.runLoadThumbnails = true;
            store.fireEvent('loadThumbnails', store);
        });
        this.on('loadThumbnails', function(store) {

            var pointer = store.gos.data.loadThumbnailsPointer;
            var records = store.getRange();

            for (var i = pointer; i < records.length; i++) {
                if (!store.gos.data.runLoadThumbnails) {
                    return false;
                }

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
                        dir: records[i].get('dir'),
                        filename: records[i].get('filename'),
                        width: 16,
                        height: 16,
                        base64: true
                    },
                    success: function(response) {
                        if (!store.gos.data.runLoadThumbnails) {
                            return false;
                        }

                        records[i].set('thumb', Ext.decode(response.responseText).data.thumb);
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
    }
});