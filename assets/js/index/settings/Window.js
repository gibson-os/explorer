Ext.define('GibsonOS.module.explorer.index.settings.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleExplorerIndexSettingsWindow'],
    title: 'Xplorer Einstellungen',
    appIcon: 'icon_dir',
    width: 500,
    autoHeight: true,
    requiredPermission: {
        module: 'explorer',
        task: 'index'
    },
    initComponent: function() {
        this.items = [{
            xtype: 'gosModuleExplorerIndexSettingsForm'
        }];

        this.callParent();
    }
});