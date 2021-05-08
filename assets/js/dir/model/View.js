Ext.define('GibsonOS.module.explorer.dir.model.View', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'size',
        type: 'int'
    },{
        name: 'icon',
        type: 'int'
    },{
        name: 'name',
        type: 'string'
    },{
        name: 'type',
        type: 'string'
    },{
        name: 'thumb',
        type: 'string'
    },{
        name: 'thumbAvailable',
        type: 'bool'
    },{
        name: 'html5VideoStatus',
        type: 'string'
    },{
        name: 'html5VideoToken',
        type: 'string'
    },{
        name: 'position',
        type: 'int'
    },{
        name: 'category',
        type: 'int'
    },{
        name: 'accessed',
        type: 'int'
    },{
        name: 'modified',
        type: 'int'
    },{
        name: 'files',
        type: 'int'
    },{
        name: 'dirs',
        type: 'int'
    },{
        name: 'dirFiles',
        type: 'int'
    },{
        name: 'dirDirs',
        type: 'int'
    },{
        name: 'hidden',
        type: 'bool'
    },{
        name: 'metaInfos',
        type: 'object'
    }]
});