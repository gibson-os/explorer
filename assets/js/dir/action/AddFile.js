GibsonOS.define('GibsonOS.module.explorer.dir.action.AddFile', {
    init(component) {
        component.addAction(this.getConfig(component));
    },
    getConfig(component) {
        return {
            text: 'Datei',
            iconCls: 'icon16 icon_default',
            // requiredPermission: {
            //     module: 'explorer',
            //     task: 'file',
            //     action: 'save',
            //     permission: GibsonOS.Permission.WRITE
            // },
            handler() {
                const dir = component.getStore().getProxy().getReader().jsonData.dir;

                GibsonOS.module.explorer.file.fn.add(dir, (response) => {
                    component.getStore().add(Ext.decode(response.responseText).data);
                });
            }
        };
    }
});