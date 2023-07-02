GibsonOS.define('GibsonOS.module.explorer.file.fn.delete', function(dir, records, success) {
    let msg = 'Möchten Sie die ' + records.length + ' Dateien wirklich löschen?';

    if (records.length === 1) {
        msg = 'Möchten Sie die Datei ' + records[0].get('name') + ' wirklich löschen?';
    }

    let files = [];
    let dirs = [];

    Ext.iterate(records, function(record) {
        files.push(record.get('name'));

        if (record.get('type') === 'dir') {
            dirs.push(record);
        }
    });

    if (dirs.length === files.length) {
        msg = 'Möchten Sie die ' + dirs.length + ' Ordner wirklich löschen?';

        if (records.length === 1) {
            msg = 'Möchten Sie den Ordner ' + records[0].get('name') + ' wirklich löschen?';
        } else if (records.length === 0) {
            var dirParts = dir.split('/');
            var dirName = dirParts[dirParts.length-1];

            if (!dirName) {
                dirName = dirParts[dirParts.length-2];
            }

            msg = 'Möchten Sie den Ordner ' + dirName + ' wirklich löschen?';
        }
    } else if (dirs.length) {
        msg = 'Möchten Sie die ' + dirs.length + ' Ordner und ' + (files.length-dirs.length) + ' Dateien wirklich löschen?';
    }

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
        url: baseDir + 'explorer/file',
        method: 'DELETE',
        params: {
            dir: dir,
            'files[]': files
        },
        success: function(response) {
            if (success) {
                success(response);
            }
        }
    });
});