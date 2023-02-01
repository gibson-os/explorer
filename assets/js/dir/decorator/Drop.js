GibsonOS.define('GibsonOS.module.explorer.dir.decorator.Drop', {
    init(component) {
        Ext.merge(component, Ext.merge({
            enableDrop: true,
            enableExplorerDrop: true,
            isDropAllowed(target, dd, event, data) {
                return data.component.enableExplorerDrop;
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
                const message = 'Möchten Sie ' + (records.length > 1 ? records.length + ' Elemente' : records[0]['name']) + ' ';
                let messageSuffix = 'verschieben';

                if (ctrlPressed) {
                    title = 'Kopieren';
                    messageSuffix = 'kopieren';

                    if (data.dragElementId === me.id) {
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
                        to: me.getStore().getProxy().getReader().jsonData.dir,
                        'names[]': names
                    },
                    success() {
                        me.getStore().add(records);

                        if (!ctrlPressed) {
                            data.component.getStore().remove(data.records);
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
                const message = 'Möchten Sie ' + (records.length > 1 ? records.length + ' Elemente' : records[0].get('name')) + ' ';
                let messageSuffix = 'verschieben';

                if (ctrlPressed) {
                    title = 'Kopieren';
                    messageSuffix = 'kopieren';
                }

                let names = [];

                Ext.iterate(records, (record) => {
                    names.push(record.get('name'));
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
                        to: me.getStore().getProxy().getReader().jsonData.dir + (targetIsDir ? targetRecord.get('name') : ''),
                        'names[]': names
                    },
                    success() {
                        if (!targetIsDir) {
                            me.getStore().add(records);
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