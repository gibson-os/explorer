function explorerDialogTree(buttonProperities, dir)
{
    var properties = Ext.merge({
        xtype: 'gosButton',
        text: 'OK'
    }, buttonProperities);
    
    if (typeof(dir) == 'undefined') {
        dir = '/';
    }
    
    var treeStore = new GibsonOS.data.TreeStore({
        proxy: {
            type: 'gosDataProxyAjax',
            url: baseDir + 'explorer/dir/list',
            method: 'GET',
            extraParams: {
                dir: dir
            },
            listeners: {
                load: function(store, node, records, successful) {
                    Ext.getCmp('explorerDialogTreeTree').selectPath(dir);
                }
            }
        }
    });
    
    new GibsonOS.Window({
        id: 'explorerDialogTree',
        width: 350,
        height: 350,
        modal: true,
        title: 'Ordner Dialog',
        items: [{
            xtype: 'gosModuleExplorerDirViewTree',
            id: 'explorerDialogTreeTree',
            store: treeStore,
            listeners: {
                itemclick: function(tree, record, item, index, event, options) {
                    Ext.getCmp('explorerDialogTreeDir').setValue(record.data.id);
                }
            }
        },{
            xtype: 'gosFormHidden',
            id: 'explorerDialogTreeDir'
        }],
        buttons: [{
            xtype: 'gosButton',
            text: 'Neuen Ordner erstellen',
            disabled: true
        },properties,{
            xtype: 'gosButton',
            text: 'Abbrechen',
            handler: function() {
                Ext.getCmp('explorerDialogTree').close();
            }
        }]
    }).show();
}