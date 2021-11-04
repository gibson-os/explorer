GibsonOS.define('GibsonOS.module.explorer.file.fn.rename', function(dir, record, success) {
    GibsonOS.MessageBox.show({
        title: 'Dateiname',
        msg: 'Neuer Name',
        type: GibsonOS.MessageBox.type.PROMPT,
        promptParameter: 'newFilename',
        okText: 'Umbenenen',
        value: record.get('name'),
    }, {
        url: baseDir + 'explorer/file/rename',
        params: {
            dir: dir,
            oldFilename: record.get('name')
        },
        success: function (response) {
            var data = Ext.decode(response.responseText).data;

            record.set('name', data.filename);

            if (data.type != record.get('type')) {
                record.set('type', data.type);
                record.set('category', data.category);
                record.set('thumbAvailable', data.thumbAvailable);
                record.set('thumb', null);
            }

            record.commit();

            if (success) {
                success(response);
            }
        }
    });
});