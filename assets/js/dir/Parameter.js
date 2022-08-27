Ext.define('GibsonOS.module.explorer.dir.Parameter', {
    extend: 'GibsonOS.module.core.component.form.FieldContainer',
    alias: ['widget.gosModuleExplorerDirParameter'],
    isFormField: true,
    initComponent() {
        const me = this;

        me.items = [{
            xtype: 'gosFormTextfield',
            margins: '0 5 0 0',
            isFormField: false,
            name: me.name,
            value: me.value
        },{
            xtype: 'gosButton',
            text: '...',
            flex: 0,
            isFormField: false,
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
    },
    isValid() {
        const me = this;

        return me.down('gosFormTextfield').isValid();
    }
});