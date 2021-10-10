Ext.define('GibsonOS.module.explorer.trash.model.View', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'token',
        type: 'string'
    },{
        name: 'dir',
        type: 'string'
    },{
        name: 'filename',
        type: 'string'
    },{
        name: 'type',
        type: 'string'
    },{
        name: 'added',
        type: 'int'
    }]
});