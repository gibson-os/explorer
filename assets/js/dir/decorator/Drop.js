GibsonOS.define('GibsonOS.module.explorer.dir.decorator.Drop', {
    init(component) {
        Ext.merge(component, Ext.merge({
            enableDrop: true,
            enableExplorerDrop: true,
            isDropAllowed(target, dd, event, data) {
                return data.component.enableExplorerDrop;
            },
            getFromDir() {
                return this.getStore().getProxy().getReader().jsonData.dir;
            },
            getToDir(targetRecord) {
                const me = this;
                const dir = me.getStore().getProxy().getReader().jsonData.dir;

                if (!targetRecord) {
                    return dir;
                }

                return dir + (targetRecord.get('type') === 'dir' ? targetRecord.get('name') : '')
            },
            addAfterDrop(records) {
                let newRecords = [];

                Ext.iterate(records, (record) => {
                    recordData = typeof(record.getData) === 'function' ? record.getData() : record;
                    recordData.name = recordData.name ?? recordData.text;
                    recordData.type = recordData.type ?? 'dir';
                    newRecords.push(recordData);
                });

                this.getStore().add(newRecords);
            },
            removeAfterDrop(data) {
                this.getStore().remove(data.records);
            },
            addRecords(records, ctrlPressed, data) {
                const me = this;

                if (data.dragElementId === me.id && !ctrlPressed) {
                    return;
                }

                records = [];

                Ext.iterate(data.records, (record) => {
                    records.push(record.getData());
                });

                let title = 'Verschieben';
                const message = 'Möchten Sie ' + (records.length > 1 ? records.length + ' Elemente' : (records[0]['name'] ?? records[0]['text'])) + ' ';
                let messageSuffix = 'verschieben';

                if (ctrlPressed) {
                    title = 'Kopieren';
                    messageSuffix = 'kopieren';

                    if (data.dragElementId === me.id) {
                        Ext.iterate(records, (record) => {
                            record['name'] = '(Kopie) ' + (record['name'] ?? record['text']);
                        });
                    }
                }

                let names = [];

                Ext.iterate(records, (record) => {
                    names.push(record['name'] ?? record['text']);
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
                        from: data.component.getFromDir(records),
                        to: me.getToDir(),
                        'names[]': names
                    },
                    success() {
                        me.addAfterDrop(records);

                        if (!ctrlPressed) {
                            data.component.removeAfterDrop(data)
                        }
                    }
                });
            },
            insertRecords(targetRecord, records, ctrlPressed, data) {
                const targetIsDir = targetRecord.get('type') === 'dir';
                const me = this;
                const isInsertAllowed = () => {
                    if (targetIsDir) {
                        return true;
                    }

                    if (data.dragElementId !== me.id) {
                        return true;
                    }

                    return ctrlPressed;
                }

                if (!isInsertAllowed()) {
                    return;
                }

                let title = 'Verschieben';
                const message = 'Möchten Sie ' + (records.length > 1 ? records.length + ' Elemente' : (records[0].get('name') ?? records[0].get('text'))) + ' ';
                let messageSuffix = 'verschieben';

                if (ctrlPressed) {
                    title = 'Kopieren';
                    messageSuffix = 'kopieren';
                }

                let names = [];

                Ext.iterate(records, (record) => {
                    names.push(record.get('name') ?? record.get('text'));
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
                        from: data.component.getFromDir(records),
                        to: me.getToDir(targetRecord),
                        'names[]': names
                    },
                    success() {
                        if (!targetIsDir) {
                            me.addAfterDrop(records, targetRecord);
                        }

                        if (!ctrlPressed) {
                            data.component.removeAfterDrop(data);
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