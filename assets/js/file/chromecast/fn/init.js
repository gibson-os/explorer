GibsonOS.define('GibsonOS.module.explorer.file.chromecast.fn.init', function(item) {
    item.disabledByChromecast = false;

    if (!item.isDisabled()) {
        item.disable();
        item.disabledByChromecast = true;
    }

    item.enableFn = item.enable;
    item.enable = () => {
        if (chromeCast.available) {
            item.enableFn();
        }
    };

    item.handlerFn = item.handler;
    item.handler = () => {
        chromeCast.connect(() => {
            if (item.handlerFn) {
                item.handlerFn();
            }
        });
    };

    item.on('chromecastConnect', (listenerItem) => {
        listenerItem.setIconCls('icon_system system_chromecast_connected');
    });
    item.on('chromecastDisconnect', (listenerItem) => {
        listenerItem.setIconCls('icon_system system_chromecast');
    });
    item.on('chromecastAvailable', (listenerItem) => {
        if (listenerItem.disabledByChromecast) {
            listenerItem.enable();
        }
    });
    item.on('chromecastNotAvailable', (listenerItem) => {
        if (!listenerItem.isDisabled()) {
            listenerItem.disable();
            listenerItem.disabledByChromecast = true;
        }
    });

    item.on('disable', (listenerItem) => {
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