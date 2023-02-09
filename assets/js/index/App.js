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
    initComponent() {
        const me = this;

        me.items = [{
            xtype: 'gosModuleExplorerIndexPanel',
            gos: {
                data: {
                    dir: this.gos.data.dir ? this.gos.data.dir : null
                }
            }
        }];
        me.tools = [{
            type:'gear',
            itemId: 'explorerIndexSettingsButton',
            tooltip: 'Einstellungen',
            handler: function(event, toolEl, panel) {
                Ext.create('GibsonOS.module.explorer.index.settings.Window');
            }
        }];
        me.id = 'gosModuleExplorerIndexApp' + Ext.id();

        me.callParent();

        me.down('#explorerIndexView').gos.store.on('load', (store) => {
            const dir = store.getProxy().getReader().jsonData.dir;

            me.setTitle(dir);
            me.taskBarButton.setText(dir);
        });
    }
});