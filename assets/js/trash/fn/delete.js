GibsonOS.define('GibsonOS.module.explorer.trash.fn.delete', function(view, records) {
    let tokens = [];
    let files = [];
    let dirs = [];
    let msg = 'Möchten Sie die ' + records.length + ' Dateien endgültig entfernen?';

    if (records.length === 0) {
        return true;
    }

    if (records.length === 1) {
        msg = 'Möchten Sie die Datei ' + records[0].get('name') + ' wirklich endgültig entfernen?';
    }

    Ext.iterate(records, function(record) {
        tokens.push(record.get('token'));
        files.push(record.get('name'));

        if (record.get('type') === 'dir') {
            dirs.push(record);
        }
    });

    if (dirs.length === files.length) {
        msg = 'Möchten Sie die ' + dirs.length + ' Ordner wirklich endgültig entfernen?';

        if (records.length === 1) {
            msg = 'Möchten Sie den Ordner ' + records[0].get('name') + ' wirklich endgültig entfernen?';
        } else if (records.length === 0) {
            var dirParts = dir.split('/');
            var dirName = dirParts[dirParts.length-1];

            if (!dirName) {
                dirName = dirParts[dirParts.length-2];
            }

            msg = 'Möchten Sie den Ordner ' + dirName + ' wirklich endgültig entfernen?';
        }
    } else if (dirs.length) {
        msg = 'Möchten Sie die ' + dirs.length + ' Ordner und ' + files.length + ' Dateien wirklich endgültig entfernen?';
    }

    GibsonOS.MessageBox.show({
        title: 'Endgültig entfernen?',
        msg: msg,
        type: GibsonOS.MessageBox.type.QUESTION,
        buttons: [{
            text: 'Ja',
            sendRequest: true
        },{
            text: 'Nein'
        }]
    },{
        url: baseDir + 'explorer/trash/delete',
        params: {
            'tokens[]': tokens
        },
        success: function(response) {
            view.gos.store.remove(records);
        }
    });
});