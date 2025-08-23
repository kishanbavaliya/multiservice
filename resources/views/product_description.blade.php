<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - {{ __('Description') }}</title>
    <style>
        /* body {
 font-family: Arial, sans-serif;
 line-height: 1.6;
 margin: 0;
 padding: 20px;
 color: #333;
 } */
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
            max-width: 100%;
            margin: 20px 0;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .instagram-container,
        .tiktok-container {
            margin: 20px 0;
            width: 100%;
            overflow: hidden;
        }

        .instagram-container iframe {
            width: 100%;
            margin: 0 auto !important;
        }

        .media-link {
            margin: 20px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
            text-align: center;
        }

        .media-link a {
            color: #0066cc;
            text-decoration: none;
            font-weight: bold;
        }

        .media-link a:hover {
            text-decoration: underline;
        }
    </style>
    <!-- Add jQuery for DOM manipulation -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div>{!! $product->description !!}</div>
    <script>
        $(document).ready(function() {
            // Process all oembed tags to convert them to proper video embeds
            $('figure.media oembed').each(function() {
                var url = $(this).attr('url');
                var embedHtml = '';

                // YouTube handling
                if (url.indexOf('youtube.com') !== -1 || url.indexOf('youtu.be') !== -1) {
                    var videoId = '';
                    if (url.indexOf('youtube.com') !== -1) {
                        videoId = url.split('v=')[1];
                        var ampersandPosition = videoId.indexOf('&');
                        if (ampersandPosition !== -1) {
                            videoId = videoId.substring(0, ampersandPosition);
                        }
                    } else if (url.indexOf('youtu.be') !== -1) {
                        videoId = url.split('/').pop();
                    }

                    if (videoId) {
                        embedHtml = '<div class="video-container">' +
                            '<iframe src="https://www.youtube.com/embed/' + videoId + '" ' +
                            'frameborder="0" allow="accelerometer; autoplay; clipboard-write; ' +
                            'encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>' +
                            '</div>';
                    }
                }
                // Vimeo handling
                else if (url.indexOf('vimeo.com') !== -1) {
                    var vimeoId = url.split('/').pop();
                    embedHtml = '<div class="video-container">' +
                        '<iframe src="https://player.vimeo.com/video/' + vimeoId + '" ' +
                        'frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>' +
                        '</div>';
                }
                // Dailymotion handling
                else if (url.indexOf('dailymotion.com') !== -1 || url.indexOf('dai.ly') !== -1) {
                    var dailymotionId = '';
                    if (url.indexOf('dailymotion.com') !== -1) {
                        dailymotionId = url.split('/video/')[1];
                        if (dailymotionId && dailymotionId.indexOf('?') !== -1) {
                            dailymotionId = dailymotionId.substring(0, dailymotionId.indexOf('?'));
                        }
                    } else if (url.indexOf('dai.ly') !== -1) {
                        dailymotionId = url.split('/').pop();
                    }

                    if (dailymotionId) {
                        embedHtml = '<div class="video-container">' +
                            '<iframe src="https://www.dailymotion.com/embed/video/' + dailymotionId + '" ' +
                            'frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>' +
                            '</div>';
                    }
                }
                // For other videos, use HTML5 video tag if it's a common video format
                else {
                    var fileExtension = url.split('.').pop().toLowerCase();
                    var videoFormats = ['mp4', 'webm', 'ogg', 'mov', 'm4v'];

                    if (videoFormats.indexOf(fileExtension) !== -1) {
                        embedHtml = '<div class="video-container">' +
                            '<video width="100%" height="100%" controls>' +
                            '<source src="' + url + '" type="video/' +
                            (fileExtension === 'mov' ? 'quicktime' :
                                fileExtension === 'm4v' ? 'mp4' : fileExtension) + '">' +
                            'Your browser does not support the video tag.' +
                            '</video>' +
                            '</div>';
                    } else {
                        // For other links, create a simple link
                        var title = $(this).attr('title') || 'View Video';
                        embedHtml = '<div class="media-link"><a href="' + url +
                            '" target="_blank" rel="noopener">' + title + '</a></div>';
                    }
                }

                // Replace the oembed tag with our generated HTML
                $(this).closest('figure.media').replaceWith(embedHtml);
            });
        });
    </script>
</body>

</html>
