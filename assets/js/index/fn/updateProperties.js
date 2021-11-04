GibsonOS.define('GibsonOS.module.explorer.index.fn.updateProperties', function(app, record) {
    var data = record.getData();
    var propertiesPanel = app.down('#explorerIndexProperties');

    var eastIcon = propertiesPanel.down('#explorerIndexPropertyIcon');
    propertiesPanel.down('#explorerIndexPropertyGeneral').update(data);

    if (eastIcon.data) {
        data.iconWidth = eastIcon.data.iconWidth;
    }

    propertiesPanel.down('#explorerIndexPropertyName').setValue(data.name);
    eastIcon.update(data);
});