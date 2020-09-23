Ext.define('GibsonOS.module.explorer.html5.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleExplorerHtml5App'],
    title: 'HTML5 Medien',
    appIcon: 'icon_html5',
    width: 800,
    height: 400,
    requiredPermission: {
        module: 'explorer',
        task: 'html5'
    },
    initComponent: function() {
        var app = this;
        var settingsWindow = new GibsonOS.module.explorer.html5.settings.Window({
            autoShow: false,
            closeAction: 'hide'
        });

        this.items = [{
            xtype: 'gosModuleExplorerHtml5TabPanel'
        }];
        this.tools = [{
            type:'gear',
            itemId: 'explorerHtml5SettingsButton',
            disabled: true,
            tooltip: 'Einstellungen',
            handler: function(event, toolEl, panel) {
                settingsWindow.show();
            }
        }];

        this.callParent();

        this.down('#explorerHtml5Grid').getStore().on('load', function(store) {
            var settings = store.getProxy().getReader().jsonData.settings;
            var form = settingsWindow.down('#explorerHtml5SettingsForm').getForm();

            form.findField('dir').setValue(settings.html5_media_path);
            form.findField('dirSize').setValue(settings.html5_media_size);
            form.findField('lifetime').setValue(settings.html5_media_lifetime);
            form.findField('mediaCount').setValue(settings.html5_media_count);

            app.down('#explorerHtml5SettingsButton').enable();
        });
    }
});