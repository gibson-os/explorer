GibsonOS.define('GibsonOS.module.explorer.html5.fn.convert', function(dir, files, audioStream, subtitleStream, success) {
    GibsonOS.Ajax.request({
        url: baseDir + 'explorer/html5/convert',
        params: {
            dir: dir,
            'files[]': files,
            audioStream: audioStream,
            subtitleStream: subtitleStream
        },
        success: function(response) {
            if (typeof(success) == 'function') {
                success(response);
            }
        }
    });
});