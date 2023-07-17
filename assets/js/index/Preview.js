Ext.define('GibsonOS.module.explorer.index.Preview', {
    extend: 'GibsonOS.Panel',
    alias: ['widget.gosModuleExplorerIndexPreview'],
    itemId: 'explorerIndexPreview',
    data: {},
    initComponent() {
        const me = this;
        const elementId = Ext.id();
        let currentWidth = 0;
        let currentHeight = 0;

        me.tpl = new Ext.XTemplate(
            '<tpl if="category == ' + GibsonOS.module.explorer.file.data.categories.VIDEO  + '">',
                '<tpl if="html5MediaStatus == \'generated\'">',
                    '<video width="{width}" height="{height}" id="' + elementId + '" controls>',
                    '<source src="' + baseDir + 'explorer/html5/stream/token/{html5MediaToken}" type="video/mp4">',
                    '</video>',
                '</tpl>',
            '</tpl>',
            '<tpl if="category == ' + GibsonOS.module.explorer.file.data.categories.IMAGE  + '">',
                '<div style="text-align: center;"><img height="{height}" src="' + baseDir + 'explorer/file/show/{dir}{name}" /></div>',
            '</tpl>'
        );

        me.callParent();

        me.on('resize', (panel, width, height) => {
            const previewElement = Ext.getElementById(elementId);

            if (previewElement) {
                previewElement.width = width;
                previewElement.height = height;
                currentWidth = width;
                currentHeight = height;
            }
        });

        //var videoElement = Ext.get(elementId).dom;

        /*videoElement.onplay = function() {
            console.log('play');
        };*/
    }
});