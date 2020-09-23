GibsonOS.define('GibsonOS.module.explorer.dir.listener.dropZone', function(view) {
    return GibsonOS.dropZones.add(view.getEl(), {
        getTargetFromEvent: function(event) {
            return event.getTarget('#' + view.getId());
        },
        onNodeOver : function(target, dd, event, data) {
            if (
                data.moveData &&
                data.sourceEl !== event.getTarget().parentNode.parentNode
            ) {
                return Ext.dd.DropZone.prototype.dropAllowed;
            }

            return Ext.dd.DropZone.prototype.dropNotAllowed;
        },
        onNodeDrop: function(target, dd, event, data) {
            data = data.moveData;
            data.grid = view;
            data.to = view.getRecord(target).get('id');

            //GibsonOS.module.explorer.file.fn.move(data);
        }
    });
});