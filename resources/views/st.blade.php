<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Video.js HLS with Different Base URLs</title>
  <link href="https://vjs.zencdn.net/7.13.3/video-js.css" rel="stylesheet">
  <script src="https://vjs.zencdn.net/7.13.3/video.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/hls.js@1.0.0/dist/hls.min.js"></script>
</head>
<body>

  <h1>Video.js with Custom HLS Base URL</h1>
  <video id="my-video" class="video-js vjs-default-skin" controls preload="auto" width="640" height="360"></video>

  <script>
    var video = document.getElementById('my-video');
    var videoJsPlayer = videojs(video);

    if (Hls.isSupported()) {
      var hls = new Hls({
        xhrSetup: function(xhr, url) {
          // Log all requests to see if .ts requests are being captured
          console.log('XHR Request:', url);

          // Only rewrite .ts URLs
          if (url.includes('.ts')) {
            const tsBaseUrl = '{!! $src !!}';
            var newUrl = tsBaseUrl + url.split('/').pop();
            console.log('Rewriting URL:', url, 'to', newUrl);
            xhr.open('GET', newUrl, true);
          }
        }
      });

      @php
        $src = url('api/video/playlist.m3u8');
        //$src = 'http://192.168.254.120:8081/video/playlist.m3u8';
    @endphp


      // Load the M3U8 file
      hls.loadSource('{!! $src !!}');
      hls.attachMedia(video);

      hls.on(Hls.Events.MANIFEST_PARSED, function() {
        console.log('Manifest parsed successfully');
        videoJsPlayer.play();
      });

      hls.on(Hls.Events.ERROR, function(event, data) {
        console.error('HLS.js error:', data);
      });
    } else {
      console.error("HLS.js is not supported in your browser.");
    }
  </script>

</body>
</html>
