Ext.define('GibsonOS.module.explorer.html5.model.Grid', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'filename',
        type: 'string'
    },{
        name: 'type',
        type: 'string'
    },{
        name: 'thumbAvailable',
        type: 'bool'
    },{
        name: 'thumb',
        type: 'string'
    },{
        name: 'dir',
        type: 'string'
    },{
        name: 'html5VideoToken',
        type: 'string'
    },{
        name: 'added',
        type: 'string'
    },{
        name: 'status',
        type: 'string'
    },{
        name: 'size',
        type: 'int'
    },{
        name: 'frame',
        type: 'int'
    },{
        name: 'frameCount',
        type: 'int'
    },{
        name: 'percent',
        type: 'int'
    },{
        name: 'fps',
        type: 'int'
    },{
        name: 'q',
        type: 'double'
    },{
        name: 'size',
        type: 'int'
    },{
        name: 'time',
        type: 'string'
    },{
        name: 'bitrate',
        type: 'double'
    },{
        name: 'category',
        type: 'int'
    }]
});