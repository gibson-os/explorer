/* @todo Total oldschool! Funktioniert ehh nicht mehr */
GibsonOS.define('GibsonOS.module.explorer.file.fn.move', function(data) {
    GibsonOS.MessageBox.show({
        title: 'Wirklich verschieben?',
        msg: 'Möchten Sie die Datei wirklich verschieben?',
        type: GibsonOS.MessageBox.type.QUESTION,
        buttons: [{
            text: 'Ja',
            sendRequest: true
        },{
            text: 'Nein'
        }]
    },{
        url: baseDir + 'explorer/file/move',
        params: {
            from: data.grid.getStore().getProxy().extraParams.dir,
            to: data.to,
            name: data.record.get('name')
        },
        success: function(response) {
            if (data.type != 'file') {
                data.tree.getStore().load();
            }
        }
    });
});