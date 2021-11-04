Ext.define('GibsonOS.module.explorer.index.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleExplorerIndexApp'],
    title: 'Xplorer',
    appIcon: 'icon_dir',
    width: 800,
    height: 400,
    requiredPermission: {
        module: 'explorer',
        task: 'dir'
    },
    initComponent: function() {
        var app = this;

        this.items = [{
            xtype: 'gosModuleExplorerIndexPanel',
            gos: {
                data: {
                    dir: this.gos.data.dir ? this.gos.data.dir : null
                }
            }
        }];
        this.tools = [{
            type:'gear',
            itemId: 'explorerIndexSettingsButton',
            tooltip: 'Einstellungen',
            handler: function(event, toolEl, panel) {
                Ext.create('GibsonOS.module.explorer.index.settings.Window');
            }
        }];
        this.id = 'gosModuleExplorerIndexApp' + Ext.id();

        this.callParent();

        this.down('#explorerIndexView').gos.store.on('load', function(store, records, successful, operation, options) {
            var dir = store.getProxy().getReader().jsonData.dir;

            app.setTitle(dir);
            app.taskBarButton.setText(dir);
        });
    }
});