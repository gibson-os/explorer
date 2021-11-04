Ext.define('GibsonOS.module.explorer.file.add.Button', {
    extend: 'Ext.menu.Item',
    alias: ['widget.gosModuleExplorerFileAddButton'],
    itemId: 'explorerFileAddButton',
    text: 'Datei',
    iconCls: 'icon16 icon_default',
    requiredPermission: {
        task: 'file',
        action: 'save',
        permission: GibsonOS.Permission.WRITE
    },
    handler: function() {
        var menu = this.up('#contextMenu');
        var view = menu.getParent();
        var store = view.getStore();
        var proxy = store.getProxy();
        var dir = proxy.getReader().jsonData.dir;

        GibsonOS.module.explorer.file.fn.add(dir, function(response) {
            var data = Ext.decode(response.responseText).data;
            view.getStore().add(data);
        });
    }
});