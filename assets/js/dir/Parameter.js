Ext.define('GibsonOS.module.explorer.dir.Parameter', {
    extend: 'GibsonOS.module.core.component.form.FieldContainer',
    alias: ['widget.gosModuleExplorerDirParameter'],
    initComponent() {
        const me = this;

        me.items = [{
            xtype: 'gosFormTextfield',
            name: me.name,
            margins: '0 5 0 0'
        },{
            xtype: 'gosButton',
            text: '...',
            flex: 0,
            handler: function() {
                GibsonOS.module.explorer.dir.fn.dialog(me.down('gosFormTextfield'));
            }
        }];

        me.callParent();
    },
    getName() {
        return this.name;
    },
    getValue() {
        const me = this;

        return me.down('gosFormTextfield').getValue();
    }
});