GibsonOS.define('GibsonOS.module.explorer.file.chromecast.fn.init', function(item) {
    item.disabledByChromecast = false;

    if (!item.isDisabled()) {
        item.disable();
        item.disabledByChromecast = true;
    }

    item.enableFn = item.enable;
    item.enable = function() {
        if (chromeCast.available) {
            item.enableFn();
        }
    };

    item.handlerFn = item.handler;
    item.handler = function() {
        chromeCast.connect(function() {
            if (item.handlerFn) {
                item.handlerFn();
            }
        });
    };

    item.on('chromecastConnect', function(listenerItem) {
        listenerItem.setIconCls('icon_system system_chromecast_connected');
    });
    item.on('chromecastDisconnect', function(listenerItem) {
        listenerItem.setIconCls('icon_system system_chromecast');
    });
    item.on('chromecastAvailable', function(listenerItem) {
        if (listenerItem.disabledByChromecast) {
            listenerItem.enable();
        }
    });
    item.on('chromecastNotAvailable', function(listenerItem) {
        if (!listenerItem.isDisabled()) {
            listenerItem.disable();
            listenerItem.disabledByChromecast = true;
        }
    });

    item.on('disable', function(listenerItem) {
        listenerItem.disabledByChromecast = false;
    });

    chromeCast.buttons.push(item);

    if (chromeCast.available) {
        item.fireEvent('chromecastAvailable', item);
    }

    if (chromeCast.connected) {
        item.fireEvent('chromecastConnect', item);
    }
});