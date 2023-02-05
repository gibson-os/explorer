Ext.define('GibsonOS.module.explorer.dir.store.Tree', {
    extend: 'GibsonOS.data.TreeStore',
    alias: ['store.gosModuleExplorerDirTreeStore'],
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'explorer/dir/dirList'
    }
});