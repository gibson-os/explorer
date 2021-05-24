GibsonOS.define('GibsonOS.module.explorer.html5.fn.delete', function(records, success) {
    let msg = 'Möchten Sie die ' + records.length + ' HTML5 Dateien wirklich löschen?';

    if (records.length === 1) {
        msg = 'Möchten Sie die HTML5 Datei von ' + records[0].get('filename') + ' wirklich löschen?';
    }

    var tokens = [];

    Ext.iterate(records, function(record) {
        tokens.push(record.get('html5MediaToken'));
    });

    GibsonOS.MessageBox.show({
        title: 'Wirklich löschen?',
        msg: msg,
        type: GibsonOS.MessageBox.type.QUESTION,
        buttons: [{
            text: 'Ja',
            sendRequest: true
        },{
            text: 'Nein'
        }]
    },{
        url: baseDir + 'explorer/html5/delete',
        params: {
            'tokens[]': tokens
        },
        success(response) {
            if (success) {
                success(response);
            }
        }
    });
});