Ext.define('GibsonOS.module.explorer.index.settings.Form', {
    extend: 'GibsonOS.form.Panel',
    alias: ['widget.gosModuleExplorerIndexSettingsForm'],
    itemId: 'explorerIndexSettingsForm',
    requiredPermission: {
        module: 'explorer',
        task: 'index'
    },
    initComponent: function() {
        var me = this;

        me.items = [{
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
            xtype: 'gosFormCheckbox',
            fieldLabel: 'Global setzen',
            boxLabel: 'Gilt für alle Benutzer die kein abweichendes Verzeichnis haben.',
            name: 'global',
            requiredPermission: {
                action:'saveSettings',
                permission: GibsonOS.Permission.MANAGE + GibsonOS.Permission.WRITE,
                method: 'POST'
            }
        },{
            xtype: 'gosFormCheckbox',
            fieldLabel: 'Stores erneuer',
            boxLabel: 'Beim nächsten indizieren alle Gibson Stores neu anlegen.',
            name: 'global',
            requiredPermission: {
                action:'saveSettings',
                permission: GibsonOS.Permission.MANAGE + GibsonOS.Permission.WRITE,
                method: 'POST'
            }
        }];

        this.buttons = [{
            text: 'Speichern',
            itemId: 'gosModuleExplorerIndexSettingsSaveButton',
            requiredPermission: {
                action:'saveSettings',
                permission: GibsonOS.Permission.MANAGE + GibsonOS.Permission.WRITE,
                method: 'POST'
            },
            handler: function() {
                me.getForm().submit({
                    xtype: 'gosFormActionAction',
                    url: baseDir + 'explorer/index/saveSettings',
                    method: 'POST',
                    success: function() {
                        GibsonOS.MessageBox.show({
                            title: 'Gespeichert!',
                            msg: 'Einstellungen wurde erfolgreich gespeichert!',
                            type: GibsonOS.MessageBox.type.INFO
                        });
                        me.up('window').close();
                    }
                })
            }
        }];

        this.callParent();
    }
});