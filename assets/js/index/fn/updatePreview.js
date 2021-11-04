GibsonOS.define('GibsonOS.module.explorer.index.fn.updatePreview', function(app, record) {
    var data = record.getData();
    var previewPanel = app.down('#explorerIndexPreview');
    
    data.width = previewPanel.getWidth();
    data.height = previewPanel.getHeight();
    data.dir = app.down('gosModuleExplorerIndexView').gos.store.gos.data.extraParams.dir;

    previewPanel.update(data);
});