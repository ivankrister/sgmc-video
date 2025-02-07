<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Video</title>
        <!-- Clappr Builds -->
        <script src="//cdn.jsdelivr.net/npm/@clappr/player@0.8/dist/clappr.min.js"></script>
        <!-- P2PEngine -->
        <script src="//cdn.jsdelivr.net/npm/@swarmcloud/hls/p2p-engine.min.js"></script>
    </head>
    <style type="text/css">
        html,
        body {
            width: 100%;
            height: 100%;
            margin: auto;
            overflow: hidden;
        }

        body {
            display: flex;
        }

        #player {
            flex: auto;
        }
    </style>
    <script type="text/javascript">
        window.addEventListener('resize', function() {
            document.getElementById('player').style.height = window.innerHeight + 'px';
        });
    </script>

    <body>
        <div id="player"></div>
        <script>
            var sources = [
                'https://ac1.blodiab.com/sgmc/live.m3u8',
                'https://ac2.blodiab.com/sgmc/live.m3u8',
                'https://ac3.blodiab.com/sgmc/live.m3u8',
                'https://ac4.blodiab.com/sgmc/live.m3u8',
                'https://ac5.blodiab.com/sgmc/live.m3u8',
                'https://ac6.blodiab.com/sgmc/live.m3u8',
                'https://ac7.blodiab.com/sgmc/live.m3u8',
                'https://ac8.blodiab.com/sgmc/live.m3u8',
                'https://ac9.blodiab.com/sgmc/live.m3u8',
                'https://ac10.blodiab.com/sgmc/live.m3u8',
                'https://ac11.blodiab.com/sgmc/live.m3u8',
                'https://ac12.blodiab.com/sgmc/live.m3u8',
                'https://ac13.blodiab.com/sgmc/live.m3u8',

            ];
            var source = "{{ url('api/video/playlist.m3u8') }}";
            var p2pConfig = {
                swFile: './sw.js',
                live: true,
                trackerZone: 'hk',

            }
            var player = new Clappr.Player({
                parentId: "#player",
                width: '100%',
                height: '100%',
                mute: false,
                autoPlay: true,
                mediacontrol: {
                    buttons: "#FF2400"
                },
                mimeType: "application/x-mpegURL",
                playback: {
                    playInline: true,
                    hlsjsConfig: {
                        enableWorker: true,
                        lowLatencyMode: true,
                        liveSyncDurationCount: 2,
                        liveMaxLatencyDurationCount: 10,
                        maxBufferLength: 8,
                        maxMaxBufferLength: 10,
                        maxLiveSyncPlaybackRate: 1,
                        liveDurationInfinity: true
                    }
                },
            });

            P2PEngineHls.tryRegisterServiceWorker(p2pConfig).then(() => {
                player.load({
                    source: source
                });
                p2pConfig.hlsjsInstance = player.core.getCurrentPlayback()?._hls;
                var engine = new P2PEngineHls(p2pConfig);
            })
        </script>
    </body>

</html>
