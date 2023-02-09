Ext.define('GibsonOS.module.explorer.index.Properties', {
    extend: 'GibsonOS.Panel',
    alias: ['widget.gosModuleExplorerIndexProperties'],
    itemId: 'explorerIndexProperties',
    initComponent: function() {
        const me = this;

        me.callParent();
        me.on('resize', function(panel, width) {
            const eastIcon = panel.down('#explorerIndexPropertyIcon');
            let data = eastIcon.data;

            if (!data) {
                data = {};
            }

            if (width < 270) {
                data.iconWidth = 128;
            } else {
                data.iconWidth = 256;
            }

            eastIcon.update(data);
        });
    },
    items: [{
        xtype: 'gosPanel',
        itemId: 'explorerIndexPropertyIcon',
        data: {
            iconWidth: 128
        },
        tpl: new Ext.XTemplate(
            '<tpl if="thumb">',
            '<div class="explorerPropertyThumb icon{iconWidth}" style="background-image: url(data:image/png;base64,{thumb});"></div>',
            '<tpl else>',
            '<div class="explorerPropertyThumb icon_default icon{iconWidth} <tpl if="icon &gt; 0">customIcon{icon}<tpl else>icon_{type}</tpl>"></div>',
            '</tpl>'
        )
    },{
        itemId: 'explorerIndexPropertyName',
        xtype: 'gosFormTextfield',
        hideLabel: true,
        width: '100%'
    },{
        xtype: 'gosTabPanel',
        itemId: 'explorerIndexPropertyTabPanel',
        items: [{
            xtype: 'gosPanel',
            itemId: 'explorerIndexPropertyGeneral',
            title: 'Allgemein',
            data: {},
            tpl: new Ext.XTemplate(
                '<table class="gibson_status_table">',
                '<tr>',
                '<th>Typ:</th>',
                '<td>{type}</td>',
                '</tr>',
                '<tr>',
                '<th>Größe:</th>',
                '<td>{[transformSize(values.size)]}</td>',
                '</tr>',
                '<tr>',
                '<th>Letzter Zugriff:</th>',
                '<td><tpl if="accessed">{[Ext.Date.format(new Date(values.accessed*1000), \'Y-m-d H:i:s\')]}</tpl></td>',
                '</tr>',
                '<tr>',
                '<th>Zuletzt bearbeitet:</th>',
                '<td><tpl if="modified">{[Ext.Date.format(new Date(values.modified*1000), \'Y-m-d H:i:s\')]}</tpl></td>',
                '</tr>',
                '<tpl if="type==\'dir\'">',
                '<tr>',
                '<th>Ordner:</th>',
                '<td>{dirs} ({dirDirs})</td>',
                '</tr>',
                '<tr>',
                '<th>Dateien:</th>',
                '<td>{files} ({dirFiles})</td>',
                '</tr>',
                '</tpl>',
                '</table>'
            )
        }]
    }]
});