Ext.define('GibsonOS.module.explorer.dir.add.Button', {
    extend: 'Ext.menu.Item',
    alias: ['widget.gosModuleExplorerDirAddButton'],
    itemId: 'explorerDirAddButton',
    text: 'Ordner',
    iconCls: 'icon16 icon_dir',
    requiredPermission: {
        task: 'dir',
        action: 'save',
        permission: GibsonOS.Permission.WRITE
    },
    handler: function() {
        var button = this;
        var menu = button.up('#contextMenu');
        var view = menu.getParent();
        var store = view.getStore();
        var proxy = store.getProxy();
        var dir = proxy.getReader().jsonData.dir;

        GibsonOS.module.explorer.dir.fn.add(dir, function(response) {
            var data = Ext.decode(response.responseText).data;

            view.up().fireEvent('addDir', button, response, dir, data.name);
            view.getStore().add(data);
        });
    }
});