Ext.define('GibsonOS.module.explorer.file.chromecast.MenuItem', {
    extend: 'Ext.menu.Item',
    alias: ['widget.gosModuleExplorerFileChromecastMenuItem'],
    itemId: 'explorerFileChromecastMenuItem',
    iconCls: 'icon_system system_chromecast',
    text: 'An Chromecast senden',
    requiredPermission: {
        module: 'explorer',
        task: 'html5',
        action: 'video',
        permission: GibsonOS.Permission.READ
    },
    initComponent: function() {
        this.callParent();
        GibsonOS.module.explorer.file.chromecast.fn.init(this);
    }
});