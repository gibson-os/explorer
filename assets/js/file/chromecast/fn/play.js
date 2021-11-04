GibsonOS.define('GibsonOS.module.explorer.file.chromecast.fn.play', function(record) {
    if (
        chromeCast.connected &&
        (
            record.get('category') === 1 ||
            record.get('category') === 2 ||
            record.get('category') === 4
        )
    ) {
        const category = record.get('category') === 4 ? 'audio' : 'video';
        const type = record.get('category') === 4 ? 'audio/mp3' : 'video/mp4';
        const mediaInfo = new chrome.cast.media.MediaInfo(record.get('html5MediaToken'), type);
        const metaInfos = record.get('metaInfos') ?? {};
        let mediaMetadata = new chrome.cast.media.MovieMediaMetadata();

        if (record.get('category') === 4) {
            mediaMetadata = new chrome.cast.media.MusicTrackMediaMetadata();
        }

        const currentSession = cast.framework.CastContext.getInstance().getCurrentSession();

        mediaMetadata.title = record.get('name');
        mediaInfo.metadata = mediaMetadata;
        mediaInfo.contentUrl = domain + baseDir + 'explorer/html5/' + category + '/token/' + record.get('html5MediaToken');
        mediaInfo.duration = metaInfos.duration ?? null;
        let isPlaying = false;

        if (currentSession.getMediaSession() && currentSession.getMediaSession().playerState !== chrome.cast.media.PlayerState.IDLE) {
            isPlaying = true;
        }

        const addToQueue = (startTime) => {
            const queueItem = new chrome.cast.media.QueueItem(mediaInfo);

            if (startTime) {
                queueItem.startTime = startTime;
            }

            currentSession.getMediaSession().queueAppendItem(
                queueItem,
                function() {
                    console.log('add to queue');
                },
                function(error) {
                    console.log('onQueueError');
                    console.log(error);
                }
            );
        };

        const loadMedia = (currentTime) => {
            const request = new chrome.cast.media.LoadRequest(mediaInfo);
            request.autoplay = true;

            if (currentTime) {
                request.currentTime = currentTime;
            }

            currentSession.loadMedia(
                request,
                onMediaDiscovered.bind(this, 'loadMedia'),
                function(error) {
                    console.log('onMediaError');
                    console.log(error);
                }
            );
        };

        const onMediaDiscovered = (how, media) => {
            chromeCast.Media = media;
            chromeCast.Media.addUpdateListener(function(isAlive) {
                console.log(isAlive);
            });
            console.log('got currentmedia');
        };

        if (record.get('position') > 0 && record.get('position') !== (metaInfos.duration ?? 0)) {
            let buttons = [{
                text: 'Neu starten',
                handler() {
                    loadMedia();
                }
            },{
                text: 'Fortsetzen',
                handler() {
                    loadMedia(record.get('position'));
                }
            }];

            if (isPlaying) {
                buttons.push({
                    text: 'Zur Playlist hinzufügen. Neu starten',
                    handler() {
                        addToQueue();
                    }
                });
                buttons.push({
                    text: 'Zur Playlist hinzufügen. Fortsetzen',
                    handler() {
                        addToQueue(record.get('position'));
                    }
                });
            }

            GibsonOS.MessageBox.show({
                title: 'Fortsetzen?',
                msg:
                    'Bereits ' + transformSeconds(record.get('position'), 0) +
                    ' von ' + transformSeconds(metaInfos.duration ?? 0, 0) + ' ' +
                    (record.get('category') === 4 ? 'gehört' : 'gesehen') + '. ' +
                    'Von Anfang an abspielen?',
                type: GibsonOS.MessageBox.type.QUESTION,
                buttons: buttons
            });
        } else if (isPlaying) {
            GibsonOS.MessageBox.show({
                title: 'Abspielen?',
                msg: 'Es läuft bereit etwas.',
                type: GibsonOS.MessageBox.type.QUESTION,
                buttons: [{
                    text: 'Abspielen',
                    handler() {
                        loadMedia();
                    }
                },{
                    text: 'Zur Playlist hinzufügen',
                    handler() {
                        addToQueue();
                    }
                }]
            });
        } else {
            loadMedia();
        }

        return true;
    }

    return false;
});