Ext.define('GibsonOS.module.explorer.dir.model.View', {
    extend: 'GibsonOS.data.Model',
    idProperty: 'name',
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
        name: 'html5MediaStatus',
        type: 'string'
    },{
        name: 'html5MediaToken',
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