GibsonOS.define('GibsonOS.module.explorer.dir.decorator.Listeners', {
    add(component) {
        component.on('cellkeydown', (table, td, cellIndex, record, tr, rowIndex, event) => {
            if (
                event.getKey() !== Ext.EventObject.DELETE &&
                event.getKey() !== Ext.EventObject.RETURN
            ) {
                GibsonOS.module.explorer.dir.fn.jumpToItem(component, record, rowIndex, event);
            }
        });
        component.on('render', (grid) => {
            GibsonOS.module.explorer.file.fn.setUploadField(grid, grid.gos.functions && grid.gos.functions.upload ? grid.gos.functions.upload : {});
        });
    }
});