GibsonOS.define('GibsonOS.module.explorer.dir.decorator.Actions', {
    add(component) {
        component.addAction({
            text: 'Umbenennen',
            selectionNeeded: true,
            maxSelectionAllowed: 1,
            handler() {
                const record = component.getSelectionModel().getSelection()[0];

                GibsonOS.MessageBox.show({
                    title: 'Dateiname',
                    msg: 'Neuer Name',
                    type: GibsonOS.MessageBox.type.PROMPT,
                    promptParameter: 'newFilename',
                    okText: 'Umbenenen',
                    value: record.get('name')
                },{
                    url: baseDir + 'explorer/file/rename',
                    params: {
                        dir: dir,
                        oldFilename: record.get('name')
                    },
                    success(response) {
                        const data = Ext.decode(response.responseText).data;

                        record.set('name', data.filename);

                        if (data.type !== record.get('type')) {
                            record.set('type', data.type);
                            record.set('category', data.category);
                            record.set('thumbAvailable', data.thumbAvailable);
                            record.set('thumb', null);
                        }

                        record.commit();
                    }
                });
            }
        });
        GibsonOS.module.explorer.dir.action.Chromecast.init(component);
        GibsonOS.module.explorer.dir.action.Convert.init(component);
        component.addAction({
            xtype: 'menuseparator',
            addToContainerContextMenu: false,
        });
        component.addAction({
            text: 'Neu',
            menu: [
                GibsonOS.module.explorer.dir.action.AddDir.getConfig(component),
                GibsonOS.module.explorer.dir.action.AddFile.getConfig(component)
            ]
        });
    }
});