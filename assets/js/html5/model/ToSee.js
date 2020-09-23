Ext.define('GibsonOS.module.explorer.html5.model.ToSee', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'token',
        type: 'string'
    },{
        name: 'filename',
        type: 'string'
    },{
        name: 'dir',
        type: 'string'
    },{
        name: 'duration',
        type: 'int'
    },{
        name: 'position',
        type: 'int'
    },{
        name: 'nextFiles',
        type: 'int'
    },{
        name: 'status',
        type: 'string'
    },{
        name: 'category',
        type: 'int'
    }]
});