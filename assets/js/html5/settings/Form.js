Ext.define('GibsonOS.module.explorer.html5.settings.Form', {
    extend: 'GibsonOS.form.Panel',
    alias: ['widget.gosModuleExplorerHtml5SettingsForm'],
    itemId: 'explorerHtml5SettingsForm',
    defaults: {
        labelWidth: 260
    },
    requiredPermission: {
        module: 'explorer',
        task: 'html5'
    },
    initComponent: function() {
        var form = this;

        this.items = [{
            xtype: 'fieldcontainer',
            fieldLabel: 'Verzeichnis',
            layout: 'hbox',
            defaults: {
                hideLabel: true
            },
            requiredPermission: {
                action:'saveSettings',
                permission: GibsonOS.Permission.MANAGE + GibsonOS.Permission.WRITE,
                method: 'POST'
            },
            items: [{
                xtype: 'gosFormTextfield',
                name: 'dir',
                flex: 1,
                margins: '0 5 0 0'
            },{
                xtype: 'gosButton',
                text: '...',
                handler: function() {
                    GibsonOS.module.explorer.dir.fn.dialog(form.getForm().findField('dir'));
                }
            }]
        },{
            xtype: 'gosFormTextfield',
            fieldLabel: 'Maximale Verzeichnisgröße (0 = unendlich)',
            name: 'dirSize',
            requiredPermission: {
                action:'saveSettings',
                permission: GibsonOS.Permission.MANAGE + GibsonOS.Permission.WRITE,
                method: 'POST'
            }
        },{
            xtype: 'gosFormNumberfield',
            fieldLabel: 'Maximale Speicherzeit in Tagen (0 = unendlich)',
            name: 'lifetime',
            requiredPermission: {
                action:'saveSettings',
                permission: GibsonOS.Permission.MANAGE + GibsonOS.Permission.WRITE,
                method: 'POST'
            }
        },{
            xtype: 'gosFormNumberfield',
            fieldLabel: 'Maximale Dateianzahl (0 = unendlich)',
            name: 'mediaCount',
            requiredPermission: {
                action:'saveSettings',
                permission: GibsonOS.Permission.MANAGE + GibsonOS.Permission.WRITE,
                method: 'POST'
            }
        }];

        this.buttons = [{
            text: 'Speichern',
            itemId: 'gosModuleExplorerHtml5SettingsSaveButton',
            requiredPermission: {
                action:'saveSettings',
                permission: GibsonOS.Permission.MANAGE + GibsonOS.Permission.WRITE,
                method: 'POST'
            },
            handler: function() {
                form.getForm().submit({
                    xtype: 'gosFormActionAction',
                    url: baseDir + 'explorer/html5/saveSettings',
                    method: 'POST',
                    success: function(basicForm, action) {
                        GibsonOS.MessageBox.show({
                            title: 'Gespeichert!',
                            msg: 'Einstellungen wurde erfolgreich gespeichert!',
                            type: GibsonOS.MessageBox.type.INFO
                        });
                        form.up('window').close();
                    }
                })
            }
        }];

        this.callParent();
    }
});