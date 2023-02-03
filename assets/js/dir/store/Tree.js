Ext.define('GibsonOS.module.explorer.dir.store.Tree', {
    extend: 'GibsonOS.data.TreeStore',
    alias: ['store.gosModuleExplorerDirTreeStore'],
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'explorer/dir/dirList'
    },
    constructor(data) {
        this.callParent(arguments);

        this.on('load', (store, node) => {
            data.gos.tree.getSelectionModel().select(node, false, true);
            data.gos.tree.getView().focusRow(node);
        });

        return this;
    }
});