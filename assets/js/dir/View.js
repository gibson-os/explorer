Ext.define('GibsonOS.module.explorer.dir.View', {
    extend: 'GibsonOS.View',
    alias: ['widget.gosModuleExplorerDirView'],
    requiredPermission: {
        module: 'explorer',
        task: 'file'
    },
    multiSelect: true,
    style: 'background: white;',
    overflowY: 'auto',
    itemSelector: 'div.explorerViewItem',
    trackOver: true,
    overItemCls: 'explorerViewItemOver',
    selectedItemCls: 'explorerViewItemSelected',
    initComponent: function() {
        this.itemContextMenu = GibsonOS.module.explorer.dir.contextMenu.item;
        this.containerContextMenu = GibsonOS.module.explorer.dir.contextMenu.container;

        var iconSize = this.gos.data.iconSize;
        var badgeSize = iconSize/2;

        if (iconSize == 48) {
            badgeSize = 16;
        }

        this.tpl = new Ext.XTemplate(
            '<tpl for=".">',
            '<div class="explorerViewItem explorerViewItem' + iconSize + '<tpl if="hidden"> hideItem</tpl>" title="{name}">',
                '<tpl if="thumb">',
                '<div class="explorerViewItemIcon icon' + iconSize + '" style="' +
                    'background-image: url(data:image/png;base64,{thumb}); ' +
                    'background-repeat: no-repeat; ' +
                    'background-size: contain; ' +
                    'background-position: center !important;' +
                '"></div>',
                '<tpl else>',
                '<div class="explorerViewItemIcon icon_default icon' + iconSize + ' <tpl if="icon &gt; 0">customIcon{icon}<tpl else>icon_{type}</tpl>"></div>',
                '</tpl>',
                '<div class="explorerViewItemBadge">{[GibsonOS.module.explorer.file.fn.renderBadge(values, ' + badgeSize + ')]}</div>',
                '<div class="explorerViewItemName">{name}</div>',
            '</div>',
            '</tpl>'
        );
        this.itemId = 'explorerDirView' + iconSize;

        this.callParent();
        this.on('itemdblclick', GibsonOS.module.explorer.dir.listener.itemDblClick);
        this.on('itemkeydown', function(view, record, item, index, event) {
            if (event.getKey() == Ext.EventObject.DELETE) {
                var dir = view.getStore().getProxy().getReader().jsonData.dir;
                var records = view.getSelectionModel().getSelection();

                GibsonOS.module.explorer.file.fn.delete(dir, records, function(response) {
                    view.up().fireEvent('deleteFile', response, dir, records);
                    view.getStore().remove(records);
                });
            } else if (event.getKey() == Ext.EventObject.RETURN) {
                GibsonOS.module.explorer.dir.listener.itemDblClick(view, record);
            } else {
                GibsonOS.module.explorer.dir.fn.jumpToItem(view, record, index, event);
            }
        });
        this.on('render', function(view, options) {
            GibsonOS.module.explorer.file.fn.setUploadField(view, view.gos.functions && view.gos.functions.upload ? view.gos.functions.upload : {});
        });
    }
});