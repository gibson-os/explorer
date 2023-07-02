Ext.define('GibsonOS.module.explorer.file.chromecast.Button', {
    extend: 'GibsonOS.Button',
    alias: ['widget.gosModuleExplorerFileChromecastButton'],
    itemId: 'explorerFileChromecastButton',
    iconCls: 'icon_system system_chromecast',
    requiredPermission: {
        module: 'explorer',
        task: 'html5',
        action: 'video',
        permission: GibsonOS.Permission.READ,
        method: 'GET'
    },
    initComponent: function() {
        this.callParent();
        GibsonOS.module.explorer.file.chromecast.fn.init(this);
    }
});