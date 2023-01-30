GibsonOS.define('GibsonOS.module.explorer.dir.decorator.Drop', {
    init(component) {
        Ext.merge(component, Ext.merge({
            enableDrop: true,
            enableExplorerDrop: true,
            isDropAllowed(target, dd, event, data) {
                return data.component.enableExplorerDrop;
            },
            addRecords(records, ctrlPressed, data) {
                if (data.dragElementId === component.id && !ctrlPressed) {
                    return;
                }

                let title = 'Verschieben';
                const message = 'Möchten Sie ' + (records.length > 1 ? records.length + ' Elemente' : records[0]['name']) + ' ';
                let messageSuffix = 'verschieben';

                if (ctrlPressed) {
                    title = 'Kopieren';
                    messageSuffix = 'kopieren';

                    if (data.dragElementId === component.id) {
                        Ext.iterate(records, (record) => {
                            record['name'] = '(Kopie) ' + record['name'];
                        });
                    }
                }

                let names = [];

                Ext.iterate(records, (record) => {
                    names.push(record['name']);
                });

                GibsonOS.MessageBox.show({
                    title: title,
                    msg: message + messageSuffix + '?',
                    type: GibsonOS.MessageBox.type.QUESTION,
                    buttons: [{
                        text: 'Ja',
                        sendRequest: true
                    },{
                        text: 'Nein'
                    }]
                },{
                    url: baseDir + 'explorer/file/' + (ctrlPressed ? 'copy' : 'move'),
                    params: {
                        from: data.component.getStore().getProxy().getReader().jsonData.dir,
                        to: component.getStore().getProxy().getReader().jsonData.dir,
                        'names[]': names
                    },
                    success() {
                        component.getStore().add(records);

                        if (!ctrlPressed) {
                            data.component.getStore().remove(data.records);
                        }
                    }
                });
            },
            insertRecords(targetRecord, records, ctrlPressed, data) {
                const targetIsDir = targetRecord.get('type') === 'dir';

                if (data.dragElementId === component.id && !(ctrlPressed || targetIsDir)) {
                    return;
                }

                let title = 'Verschieben';
                const message = 'Möchten Sie ' + (records.length > 1 ? records.length + ' Elemente' : records[0]['name']) + ' ';
                let messageSuffix = 'verschieben';

                if (ctrlPressed) {
                    title = 'Kopieren';
                    messageSuffix = 'kopieren';
                }

                let names = [];

                Ext.iterate(records, (record) => {
                    names.push(record['name']);
                });

                GibsonOS.MessageBox.show({
                    title: title,
                    msg: message + messageSuffix + '?',
                    type: GibsonOS.MessageBox.type.QUESTION,
                    buttons: [{
                        text: 'Ja',
                        sendRequest: true
                    },{
                        text: 'Nein'
                    }]
                },{
                    url: baseDir + 'explorer/file/' + (ctrlPressed ? 'copy' : 'move'),
                    params: {
                        from: data.component.getStore().getProxy().getReader().jsonData.dir,
                        to: component.getStore().getProxy().getReader().jsonData.dir,
                        names: names
                    },
                    success() {
                        if (!targetIsDir) {
                            component.getStore().add(records);
                        }

                        if (!ctrlPressed) {
                            data.component.getStore().remove(data.records);
                        }
                    }
                });
            },
            deleteRecords() {
            },
        }, component));

        return component;
    }
});