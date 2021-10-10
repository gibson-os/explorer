Ext.define('GibsonOS.module.explorer.trash.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleExplorerTrashApp'],
    title: 'Papierkorb',
    appIcon: 'icon_trash',
    width: 600,
    height: 400,
    requiredPermission: {
        module: 'explorer',
        task: 'trash'
    },
    initComponent: function() {
        const me = this;

        me.items = [{
            xtype: 'gosModuleExplorerTrashContainer'
        }];
        me.id = 'gosModuleExplorerTrashApp';

        me.callParent();
    }
});