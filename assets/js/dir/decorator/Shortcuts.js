GibsonOS.define('GibsonOS.module.explorer.dir.decorator.Shortcuts', {
    init(component) {
        Ext.merge(component, Ext.merge({
            getShortcuts(records) {
                const me = this;
                const dir = me.getStore().getProxy().getReader().jsonData.dir;
                let shortcuts = [];

                Ext.iterate(records, (record) => {
                    if (record.get('type') === 'dir') {
                        shortcuts.push({
                            module: 'explorer',
                            task: 'index',
                            action: 'index',
                            text: record.get('name'),
                            icon: 'icon_dir',
                            parameters: {
                                dir: dir + '/' + record.get('name') + '/'
                            }
                        });
                    } else {
                        shortcuts.push({
                            module: 'explorer',
                            task: 'file',
                            action: 'download',
                            text: record.get('name'),
                            icon: 'icon_' + record.get('type'),
                            parameters: {
                                path: dir + '/' + record.get('name')
                            }
                        });
                    }
                });

                return shortcuts;
            }
        }, component));

        return component;
    }
});