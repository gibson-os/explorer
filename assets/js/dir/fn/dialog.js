GibsonOS.define('GibsonOS.module.explorer.dir.fn.dialog', function(dirField) {
    var dir = dirField.getValue();

    var dialog = new GibsonOS.module.explorer.dir.Dialog({
        gos: {
            data: {
                dir: dir ? dir : null
            }
        }
    });
    dialog.down('#gosModuleExplorerDirDialogOkButton').handler = function() {
        var record = dialog.down('gosModuleExplorerDirViewTree').getSelectionModel().getSelection()[0];
        dirField.setValue(record.get('id'));
        dialog.close();
    }
});