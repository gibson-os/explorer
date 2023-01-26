Ext.define('GibsonOS.module.explorer.html5.model.ConnectedUser', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'user',
        type: 'object'
    },{
        name: 'connectedUser',
        type: 'object'
    }]
});