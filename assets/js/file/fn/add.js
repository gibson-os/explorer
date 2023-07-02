GibsonOS.define('GibsonOS.module.explorer.file.fn.add', function(dir, success) {
    GibsonOS.MessageBox.show({
        title: 'Dateiname',
        msg: 'Name des neuen Datei?',
        type: GibsonOS.MessageBox.type.PROMPT,
        promptParameter: 'filename',
        okText: 'Anlegen'
    },{
        url: baseDir + 'explorer/file/add',
        method: 'POST',
        params: {
            dir: dir
        },
        success: function(response) {
            if (success) {
                success(response);
            }
        }
    });
});