<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.8.2/video-js.min.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.8.2/alt/video.core.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
        <script
            src="https://cdn.jsdelivr.net/npm/videojs-contrib-quality-levels@2.0.9/dist/videojs-contrib-quality-levels.min.js">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/videojs-hls-quality-selector@1.1.1/dist/videojs-hls-quality-selector.min.js">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/cdnbye@latest/dist/videojs-hlsjs-plugin.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@swarmcloud/hls/p2p-engine.min.js"></script>
        <script src="{{ url('js/watermark.min.js') }}"></script>

        <style>
            html,
            body {
                margin: 0;
                padding: 0;
                height: 100%;
                overflow: hidden;
            }

            .video-container {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }

            .video-js {
                width: 100% !important;
                height: 100% !important;
            }

            .video-js .vjs-big-play-button {
                position: absolute;
                /* Position it absolutely */
                left: 50%;
                /* Move to the center */
                top: 50%;
                /* Move to the vertical center */
                transform: translate(-50%, -50%);
                /* Adjust to truly center */
                z-index: 10;
                /* Ensure it's above other controls */
            }
        </style>
    </head>

    <body>
        <div class="video-container">
            <video id=video class="video-js" controls preload="auto" width="640" height="264">

            </video>
        </div>

        @php
            $src = url('/video/playlist.m3u8');
            $src = 'http://192.168.254.120:8081/video/playlist.m3u8';
        @endphp

        <script>
            var p2pConfig = {
                logLevel: 'error',
                swFile: "{{ url('js/sw.js') }}",
                live: true,
                trackerZone: 'hk',
                useHttpRange: true,
            };
            var options = {
                autoplay: true,
                controls: true,
                preload: 'auto',
                liveui: true,
                playsinline: true, // Allow inline playback on mobile
                controlBar: {
                    fullscreenToggle: false, // Disable fullscreen button
                    pictureInPictureToggle: false, // Disable picture-in-picture button
                    playToggle: true, // Keep play button
                    volumePanel: true, // Keep volume control
                    qualitySelector: false, // Disable quality selector (if using quality plugin)
                },
                sources: [{
                    src: '{!! $src !!}',
                }, ],
                html5: {
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
                }
            };
            const initP2pEngine = (videojsPlayer, hlsjsInstance) => {
                if (P2PEngineHls.isSupported()) {
                    new P2PEngineHls({
                        hlsjsInstance,
                        ...p2pConfig
                    });
                }
            }
            if (videojs.Html5Hlsjs) {
                videojs.Html5Hlsjs.addHook('beforeinitialize', initP2pEngine);
                // videojs.Html5Hlsjs.removeHook('beforeinitialize', initP2pEngine);  // remove the hook function when leave page
            } else {
                // use ServiceWorker based p2p engine if hls.js is not supported, need additional file sw.js
                new P2PEngineHls.ServiceWorkerEngine(p2pConfig);
            }

            P2PEngineHls.tryRegisterServiceWorker(p2pConfig).then(() => {
                var player = videojs('video', options);

            })

            //document read
        </script>
    </body>


    <script>
        setTimeout(function() {
            location.reload();
        }, 5400000);
    </script>

</html>
