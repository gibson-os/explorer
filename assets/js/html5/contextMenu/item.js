GibsonOS.define('GibsonOS.module.explorer.html5.contextMenu.item', [{

    xtype: 'gosModuleExplorerFileChromecastMenuItem',
    handler: function () {
        var menu = this.up('#contextMenu');
        var record = menu.getRecord();
        GibsonOS.module.explorer.file.chromecast.fn.play(record);
    },
    listeners: {
        beforeshowparentmenu: function (button, menu) {
            var record = menu.getRecord();
            if (record.get('status') === 'generated') {
                button.enable();
            } else {
                button.disable();
            }
        }
    }
},{
    text: 'Löschen',
    iconCls: 'icon_system system_delete',
    requiredPermission: {
        action: '',
        permission: GibsonOS.Permission.DELETE,
        method: 'DELETE'
    },
    handler: function() {
        var button = this;
        var menu = this.up('#contextMenu');
        var view = menu.getParent();
        var dir = view.getStore().getProxy().getReader().jsonData.dir;
        var records = view.getSelectionModel().getSelection();

        GibsonOS.module.explorer.html5.fn.delete(records, function(response) {
            view.getStore().remove(records);
        });
    }
}]);