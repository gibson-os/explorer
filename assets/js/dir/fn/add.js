GibsonOS.define('GibsonOS.module.explorer.dir.fn.add', function(dir, success) {
    GibsonOS.MessageBox.show({
        title: 'Ordnername',
        msg: 'Name des neuen Ordners?',
        type: GibsonOS.MessageBox.type.PROMPT,
        promptParameter: 'dirname',
        okText: 'Anlegen'
    },{
        url: baseDir + 'explorer/dir',
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