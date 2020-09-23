Ext.define('GibsonOS.module.explorer.dir.store.Tree', {
    extend: 'GibsonOS.data.TreeStore',
    alias: ['store.gosModuleExplorerDirTreeStore'],
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'explorer/dir/dirList'
    },
    constructor: function(data) {
        this.callParent(arguments);

        this.on('load', function(store, node, records, successful) {
            if (node.isRoot()) {
                var node = store.getNodeById(store.getProxy().extraParams.dir);
            }

            data.gos.tree.getSelectionModel().select(node, false, true);
            data.gos.tree.getView().focusRow(node);
        });

        return this;
    }
});