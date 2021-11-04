GibsonOS.define('GibsonOS.module.explorer.file.fn.setUploadField', function(view, options) {
    var element = view.getEl().dom;

    var stopEvents = function(event) {
        event.stopPropagation();
        event.preventDefault();
    };

    element.ondragover = stopEvents;
    element.ondrageleave = stopEvents;
    element.ondrop = function(event) {
        stopEvents(event);

        var files = event.dataTransfer.files;
        var i = 0;
        var overwriteAll = false;
        var ignoreAll = false;

        if (options.init) {
            options.init(files);
        }

        var uploadFile = function() {
            var dir = view.getStore().getProxy().getReader().jsonData.dir;
            var overwrite = false;
            var ignore = false;
            var upload = function (data) {
                var xhr = new XMLHttpRequest();
                var formData = new FormData();
                formData.append('file', files[i]);
                formData.append('dir', dir);
                formData.append('overwrite', data && data.overwrite ? true : false);
                formData.append('ignore', data && data.ignore ? true : false);

                xhr.open('POST', baseDir + 'explorer/file/upload');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.upload.onprogress = function(uploadEvent) {
                    if (options.progress) {
                        options.progress(uploadEvent, files[i]);
                    }
                };
                xhr.onreadystatechange = function() {
                    if (xhr.readyState != 4) {
                        return false;
                    }

                    if (
                        xhr.status != 200 &&
                        options.failure
                    ) {
                        options.failure(null);
                        return false;
                    }

                    i++;
                    var response = Ext.decode(xhr.responseText);

                    if (response.failure) {
                        GibsonOS.MessageBox.show({msg: 'Datei konnte nicht hochgeladen werden!'});

                        if (options.failure) {
                            options.failure(response);
                        }
                    } else if (i < files.length) {
                        if (options.nextFile) {
                            options.nextFile(response, files[i], files[i-1]);
                        }

                        uploadFile();
                    } else if (options.success) {
                        options.success(response);
                    }
                };

                xhr.send(formData);
            };

            GibsonOS.Ajax.request({
                url: baseDir + 'explorer/file/upload',
                params: {
                    dir: dir,
                    filename: files[i]['name'],
                    overwrite: overwriteAll,
                    ignore: ignoreAll
                },
                messageBox: {
                    sendRequest: false,
                    buttonHandler: function (button, request) {
                        switch (button.parameter) {
                            case 'overwrite':
                                overwriteAll = true;
                            case 'overwrite[]':
                                upload({overwrite: true});
                                break;
                            case 'ignore':
                                ignoreAll = true;
                            case 'ignore[]':
                                i++;

                                if (i < files.length) {
                                    if (options.nextFile) {
                                        options.nextFile(null, files[i], files[i - 1]);
                                    }

                                    uploadFile();
                                } else if (options.success) {
                                    options.success();
                                }
                                break;
                            default:
                                if (options.success) {
                                    options.success();
                                }
                        }
                    }
                },
                success: function (response) {
                    upload();
                }
            });
        };

        uploadFile();
    };
});