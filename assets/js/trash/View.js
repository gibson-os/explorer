Ext.define('GibsonOS.module.explorer.trash.View', {
    extend: 'GibsonOS.View',
    alias: ['widget.gosModuleExplorerTrashView'],
    requiredPermission: {
        module: 'explorer',
        task: 'trash'
    },
    multiSelect: true,
    style: 'background: white;',
    overflowY: 'auto',
    itemSelector: 'div.explorerViewItem',
    trackOver: true,
    overItemCls: 'explorerViewItemOver',
    selectedItemCls: 'explorerViewItemSelected',
    initComponent: function() {
        const me = this;

        me.itemContextMenu = GibsonOS.module.explorer.trash.itemContextMenu;
        me.containerContextMenu = GibsonOS.module.explorer.trash.containerContextMenu;

        const iconSize = me.gos.data.iconSize;
        let badgeSize = iconSize/2;

        if (iconSize === 48) {
            badgeSize = 16;
        }

        me.tpl = new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="explorerViewItem explorerViewItem' + iconSize + '" title="{name}">',
                    '<tpl if="thumb">',
                        '<div class="explorerViewItemIcon icon' + iconSize + '" style="background-image: url(data:image/png;base64,{thumb});"></div>',
                    '<tpl else>',
                        '<div class="explorerViewItemIcon icon_default icon' + iconSize + ' <tpl if="icon &gt; 0">customIcon{icon}<tpl else>icon_{type}</tpl>"></div>',
                    '</tpl>',
                    '<div class="explorerViewItemBadge">{[GibsonOS.module.explorer.file.fn.renderBadge(values, ' + badgeSize + ')]}</div>',
                    '<div class="explorerViewItemName">{dir}{filename}</div>',
                '</div>',
            '</tpl>'
        );
        me.itemId = 'explorerTrashView' + iconSize;

        me.callParent();
        me.on('itemkeydown', function(view, record, item, index, event) {
            if (event.getKey() === Ext.EventObject.DELETE) {
                GibsonOS.module.explorer.trash.fn.delete(view, view.getSelectionModel().getSelection());
            } else {
                //GibsonOS.module.explorer.trash.jumpToItem(view, record, index, event);
            }
        });
    }
});