GibsonOS.define('GibsonOS.module.explorer.dir.decorator.Actions', {
    add(component) {
        component.addAction({
            text: 'Umbenennen',
            selectionNeeded: true,
            maxSelectionAllowed: 1,
            handler() {
                const me = this;
                const record = me.component.getSelectionModel().getSelection()[0];
                const dir = me.component.getStore().getProxy().getReader().jsonData.dir;

                GibsonOS.MessageBox.show({
                    title: 'Dateiname',
                    msg: 'Neuer Name',
                    type: GibsonOS.MessageBox.type.PROMPT,
                    promptParameter: 'newFilename',
                    okText: 'Umbenenen',
                    value: record.get('name')
                },{
                    url: baseDir + 'explorer/file/rename',
                    method: 'POST',
                    params: {
                        dir: dir,
                        oldFilename: record.get('name')
                    },
                    success(response) {
                        const data = Ext.decode(response.responseText).data;
                        const oldName = record.get('name');

                        record.set('name', data.name);

                        if (data.type !== record.get('type')) {
                            record.set('type', data.type);
                            record.set('category', data.category);
                            record.set('thumbAvailable', data.thumbAvailable);
                            record.set('thumb', null);
                        }

                        if (data.type === 'dir') {
                            me.component.fireEvent('renameDir', me, response, dir, oldName, record);
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