Ext.define('GibsonOS.module.explorer.html5.TabPanel', {
    extend: 'GibsonOS.TabPanel',
    alias: ['widget.gosModuleExplorerHtml5TabPanel'],
    itemId: 'explorerHtml5Panel',
    layout: 'fit',
    initComponent: function () {
        let me = this;

        me.items = [{
            xtype: 'gosModuleExplorerHtml5ToSeeGrid',
            title: 'Zu gucken'
        },{
            xtype: 'gosModuleExplorerHtml5Grid',
            title: 'Alle'
        },{
            xtype: 'gosModuleExplorerHtml5ConnectedUserGrid',
            title: 'Verbundene Benutzer'
        }];

        me.callParent();
    }
});