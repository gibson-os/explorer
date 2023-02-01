GibsonOS.define('GibsonOS.module.explorer.dir.action.AddDir', {
    init(component) {
        component.addAction(this.getConfig(component));
    },
    getConfig(component) {
        return {
            text: 'Ordner',
            iconCls: 'icon16 icon_dir',
            // requiredPermission: {
            //     module: 'explorer',
            //     task: 'dir',
            //     action: 'save'   ,
            //     permission: GibsonOS.Permission.WRITE
            // },
            handler() {
                const me = this;
                const store = component.getStore();
                const dir = store.getProxy().getReader().jsonData.dir;

                GibsonOS.module.explorer.dir.fn.add(dir, (response) => {
                    const data = Ext.decode(response.responseText).data;

                    component.fireEvent('addDir', me, response, dir, data.name);
                    store.add(data);
                });
            }
        }
    }
});