Ext.define('GibsonOS.module.explorer.dir.Dialog', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleExplorerDirDialog'],
    title: 'Ordner auswählen',
    width: 300,
    height: 400,
    buttonAlign: 'center',
    requiredPermission: {
        module: 'explorer',
        task: 'dir'
    },
    initComponent: function() {
        var window = this;

        this.items = [{
            xtype: 'gosModuleExplorerDirViewTree',
            gos: {
                data: {
                    dir: this.gos.data.dir ? this.gos.data.dir : null
                }
            }
        }];
        this.buttons = [{
            text: 'OK',
            itemId: 'gosModuleExplorerDirDialogOkButton'
        }];

        this.callParent();

        this.down('gosModuleExplorerDirViewTree').getStore().load();
        this.down('gosModuleExplorerDirViewTree').on('addDir', function(button, response, dir, node) {
            var tree = window.down('gosModuleExplorerDirViewTree');
            tree.getSelectionModel().select(node);
        });
    }
});