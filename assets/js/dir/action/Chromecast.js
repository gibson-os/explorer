GibsonOS.define('GibsonOS.module.explorer.dir.action.Chromecast', {
    init(component) {
        component.addAction({
            iconCls: 'icon_system system_chromecast',
            text: 'An Chromecast senden',
            itemId: 'explorerFileChromecastMenuItem',
            selectionNeeded: true,
            maxSelectionAllowed: 1,
            requiredPermission: {
                module: 'explorer',
                task: 'html5',
                action: 'video',
                permission: GibsonOS.Permission.READ
            },
            listeners: {
                render(button) {
                    GibsonOS.module.explorer.file.chromecast.fn.init(button);
                },
                beforeshowparentmenu(button) {
                    const record = component.getSelectionModel().getSelection()[0];

                    if (record.get('html5MediaStatus') === 'generated') {
                        button.enable();
                    } else {
                        button.disable();
                    }
                },
            }
        });
    }
});