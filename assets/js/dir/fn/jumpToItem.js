GibsonOS.define('GibsonOS.module.explorer.dir.fn.jumpToItem', function(view, record, index, event) {
    var key = String.fromCharCode(event.getKey()).toUpperCase();

    Ext.iterate(view.getStore().getRange(index+1), function(record) {
        if (key == record.get('name').substr(0, 1).toUpperCase()) {
            view.getSelectionModel().select(record);
            return false;
        }
    });
});