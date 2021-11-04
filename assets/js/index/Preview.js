Ext.define('GibsonOS.module.explorer.index.Preview', {
    extend: 'GibsonOS.Panel',
    alias: ['widget.gosModuleExplorerIndexPreview'],
    itemId: 'explorerIndexPreview',
    data: {},
    initComponent: function () {
        var elementId = Ext.id();
        var currentWidth = 0;
        var currentHeight = 0;

        this.tpl = new Ext.XTemplate(
            '<tpl if="category == ' + GibsonOS.module.explorer.file.data.categories.VIDEO  + '">',
                '<tpl if="html5MediaStatus == \'generated\'">',
                    '<video width="{width}" height="{height}" id="' + elementId + '" controls>',
                    '<source src="' + baseDir + 'explorer/html5/video/token/{html5MediaToken}" type="video/mp4">',
                    '</video>',
                '</tpl>',
            '</tpl>',
            '<tpl if="category == ' + GibsonOS.module.explorer.file.data.categories.IMAGE  + '">',
                '<div style="text-align: center;"><img height="{height}" src="' + baseDir + 'explorer/file/show/{dir}{name}" /></div>',
            '</tpl>'
        );

        this.callParent();

        this.on('resize', function(panel, width, height) {
            var previewElement = Ext.getElementById(elementId);

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