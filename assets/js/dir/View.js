Ext.define('GibsonOS.module.explorer.dir.View', {
    extend: 'GibsonOS.module.core.component.view.View',
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
    enableKeyEvents: true,
    enableClickEvents: true,
    enableContextMenu: true,
    initComponent() {
        let me = this;

        me = GibsonOS.decorator.Drag.init(me);
        me = GibsonOS.decorator.Drop.init(me);
        me = GibsonOS.decorator.ActionManager.init(me);
        me = GibsonOS.decorator.action.Add.init(me);
        me = GibsonOS.decorator.action.Enter.init(me);
        me = GibsonOS.decorator.action.Delete.init(me);
        me = GibsonOS.module.explorer.dir.decorator.Drop.init(me);
        me = GibsonOS.module.explorer.dir.decorator.Shortcuts.init(me);

        let iconSize = this.gos.data.iconSize;
        let badgeSize = iconSize/2;

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
        me.itemId = 'explorerDirView' + iconSize;

        me.callParent();

        GibsonOS.decorator.ActionManager.addListeners(me);
        GibsonOS.decorator.Drag.addListeners(me);
        GibsonOS.decorator.Drop.addListeners(me);
        GibsonOS.module.explorer.dir.decorator.Actions.add(me);
        GibsonOS.module.explorer.dir.decorator.Listeners.add(me);
    }
});