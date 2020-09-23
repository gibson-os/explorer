Ext.define('GibsonOS.module.explorer.html5.settings.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleExplorerHtml5SettingsWindow'],
    title: 'HTML5 Einstellungen',
    appIcon: 'icon_html5',
    width: 400,
    autoHeight: true,
    requiredPermission: {
        module: 'explorer',
        task: 'html5'
    },
    initComponent: function() {
        this.items = [{
            xtype: 'gosModuleExplorerHtml5SettingsForm'
        }];

        this.callParent();
    }
});