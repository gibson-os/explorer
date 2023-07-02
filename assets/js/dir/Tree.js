Ext.define('GibsonOS.module.explorer.dir.Tree', {
    extend: 'GibsonOS.module.core.component.tree.Panel',
    alias: ['widget.gosModuleExplorerDirViewTree'],
    itemId: 'explorerDirTree',
    requiredPermission: {
        module: 'explorer',
        task: 'file'
    },
    header: false,
    useArrows: true,
    enableToolbar: false,
    enableDrag: true,
    getShortcuts(records) {
        let shortcuts = [];

        Ext.iterate(records, (record) => {
            shortcuts.push({
                module: 'explorer',
                task: 'index',
                action: 'index',
                text: record.get('text'),
                icon: 'icon_dir',
                parameters: {
                    dir: record.getId()
                }
            });
        });

        return shortcuts;
    },
    deleteFunction(records) {
        const me = this;

        GibsonOS.module.explorer.file.fn.delete(records[0].get('id'), [], (response) => {
            me.fireEvent('deleteDir', response, records[0]);
            records[0].remove();
        });
    },
    getFromDir(records) {
        return (typeof(records[0].getId) === 'function' ? records[0].getId() : records[0].id).replace(/[^\/]*\/$/, '');
    },
    getToDir(targetRecord) {
        return (targetRecord ? targetRecord : this.getStore().getRootNode()).getId();
    },
    isInsertAllowed(targetRecord, records, data, ctrlPressed) {
        if (targetRecord.get('id') !== data.component.getFromDir(records)) {
            return true;
        }

        return ctrlPressed;
    },
    addAfterDrop(records, targetRecord) {
        const me = this;
        let newRecords = [];

        Ext.iterate(records, (record) => {
            const recordData = typeof(record.getData) === 'function' ? record.getData() : record;

            if (recordData.type !== 'dir') {
                return false;
            }

            const text = record.text ?? record.name;

            newRecords.push({
                iconCls: 'icon16 icon_dir',
                id: me.getToDir(targetRecord) + text + '/',
                text: text
            });
        });

        if (newRecords.length === 0) {
            return;
        }

        if (!targetRecord) {
            this.getStore().getRootNode().appendChild(newRecords);

            return;
        }

        targetRecord.appendChild(newRecords);
    },
    removeAfterDrop(data) {
        Ext.iterate(data.records, (record) => {
            record.remove();
        });
    },
    initComponent() {
        let me = this;

        me.store = new GibsonOS.module.explorer.dir.store.Tree({
            gos: {
                data: {
                    extraParams: {
                        dir: this.gos.data.dir
                    }
                }
            }
        });
        me.store.on('load', (store, node) => {
            me.getSelectionModel().select(node, false, true);
            me.getView().focusRow(node);
        });
        me = GibsonOS.module.explorer.dir.decorator.Drop.init(me);

        me.callParent();

        me.addAction({
            text: 'Umbenennen',
            selectionNeeded: true,
            maxSelectionAllowed: 1,
            handler() {
                const node = me.getSelectionModel().getSelection()[0];
                const dir = node.parentNode.get('id');

                GibsonOS.MessageBox.show({
                    title: 'Ordername',
                    msg: 'Neuer Name',
                    type: GibsonOS.MessageBox.type.PROMPT,
                    promptParameter: 'newFilename',
                    okText: 'Umbenenen',
                    value: node.get('text')
                },{
                    url: baseDir + 'explorer/file/rename',
                    method: 'POST',
                    params: {
                        dir: dir,
                        oldFilename: node.get('text')
                    },
                    success(response) {
                        const data = Ext.decode(response.responseText).data;
                        const oldName = node.get('text');

                        node.set('text', data.name);
                        node.setId(dir + data.name + '/');
                        node.commit();
                        me.fireEvent('renameDir', me, response, dir, oldName, node);
                    }
                });
            }
        });
        me.addAction({
            text: 'Ordner',
            iconCls: 'icon16 icon_dir',
            // requiredPermission: {
            //     module: 'explorer',
            //     task: 'dir',
            //     action: 'save'   ,
            //     permission: GibsonOS.Permission.WRITE
            // },
            handler() {
                const node = me.getSelectionModel().getSelection()[0];
                const dir = node.get('id');

                GibsonOS.module.explorer.dir.fn.add(dir, (response) => {
                    const data = Ext.decode(response.responseText).data;

                    node.appendChild({
                        iconCls: 'icon16 icon_dir',
                        id: dir + data.name + '/',
                        text: data.name
                    });

                    me.fireEvent('addDir', me, response, dir, data.name);
                });
            }
        });
    }
});