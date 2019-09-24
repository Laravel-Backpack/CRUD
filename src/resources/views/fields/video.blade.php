<!-- text input -->
<?php

$value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '';

// if attribute casting is used, convert to JSON
if (is_array($value)) {
    $value = json_encode((object) $value);
} elseif (is_object($value)) {
    $value = json_encode($value);
} else {
    $value = $value;
}

?>


<div data-video @include('crud::inc.field_wrapper_attributes') >
    <label for="{{ $field['name'] }}_link">{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <input class="video-json" type="hidden" name="{{ $field['name'] }}" value="{{ $value }}">
    <div class="input-group">
        <input @include('crud::inc.field_attributes', ['default_class' => 'video-link form-control']) type="text" id="{{ $field['name'] }}_link">
        <div class="input-group-addon video-previewSuffix video-noPadding">
            <div class="video-preview">
                <span class="video-previewImage"></span>
                <a class="video-previewLink hidden" target="_blank" href="">
                    <i class="fa video-previewIcon"></i>
                </a>
            </div>
            <div class="video-dummy">
                <a class="video-previewLink youtube dummy" target="_blank" href="javascript:;">
                    <i class="fa fa-youtube video-previewIcon dummy"></i>
                </a>
                <a class="video-previewLink vimeo dummy" target="_blank" href="javascript:;">
                    <i class="fa fa-vimeo video-previewIcon dummy"></i>
                </a>
                <a class="video-previewLink facebook dummy" target="_blank" href="javascript:;">
                    <i class="fa fa-facebook video-previewIcon dummy"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    {{-- @push('crud_fields_styles')
        {{-- YOUR CSS HERE --}}
        <style media="screen">
            .video-previewSuffix {
                border: 0;
                min-width: 102px; }
            .video-noPadding {
                padding: 0; }
            .video-preview {
                margin-left: 34px;
                display: none; }
            .video-previewLink {
                 color: #fff;
                 display: block;
                 width: 34px; height: 34px;
                 text-align: center;
                 float: left; }
            .video-previewLink.youtube {
                background: #DA2724; }
            .video-previewLink.vimeo {
                background: #00ADEF; }
            .video-previewLink.facebook {
                background: #3b5998; }
            .video-previewIcon {
                transform: translateY(10px); }
            .video-previewImage {
                float: left;
                display: block;
                width: 34px; height: 34px;
                background-size: cover;
                background-position: center center; }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        {{-- YOUR JS HERE --}}
        <script>
            jQuery(document).ready(function($) {

                var tryYouTube = function( link ){

                    var id = null;

                    // RegExps for YouTube link forms
                    var youtubeStandardExpr = /^https?:\/\/(www\.)?youtube.com\/watch\?v=([^?&]+)/i; // Group 2 is video ID
                    var youtubeAlternateExpr = /^https?:\/\/(www\.)?youtube.com\/v\/([^\/\?]+)/i; // Group 2 is video ID
                    var youtubeShortExpr = /^https?:\/\/youtu.be\/([^\/]+)/i; // Group 1 is video ID
                    var youtubeEmbedExpr = /^https?:\/\/(www\.)?youtube.com\/embed\/([^\/]+)/i; // Group 2 is video ID

                    var match = link.match(youtubeStandardExpr);

                    if (match != null){
                        id = match[2];
                    }
                    else {
                        match = link.match(youtubeAlternateExpr);

                        if (match != null) {
                            id = match[2];
                        }
                        else {
                            match = link.match(youtubeShortExpr);

                            if (match != null){
                                id = match[1];
                            }
                            else {
                                match = link.match(youtubeEmbedExpr);

                                if (match != null){
                                    id = match[2];
                                }
                            }
                        }
                    }

                    return id;
                };

                var tryVimeo = function( link ){

                    var id = null;
                    var regExp = /(http|https):\/\/(www\.)?vimeo.com\/(\d+)($|\/)/;

                    var match = link.match(regExp);

                    if (match){
                        id = match[3];
                    }

                    return id;
                };
                
                var tryFacebook = function( link ){

                    var id = null;

                    // RegExps for Facebook video
                    var facebookExpr = /^(https?:\/\/www\.facebook\.com\/(?:video\.php\?v=(\d+)|.*?\/videos\/(\d+)\/*))$/i;

                    var match = link.match(facebookExpr);

                    if (match != null){
                        id = match[2] || match[3];
                    }

                    return id;
                };

                var fetchYouTube = function( videoId, callback ){

                    var api = 'https://www.googleapis.com/youtube/v3/videos?id='+videoId+'&key=AIzaSyDQa76EpdNPzfeTAoZUut2AnvBA0jkx3FI&part=snippet';

                    var video = {
                        provider: 'youtube',
                        id: null,
                        title: null,
                        image: null,
                        url: null
                    };

                    $.getJSON(api, function( data ){

                        if (typeof(data.items[0]) != "undefined") {
                            var v = data.items[0].snippet;

                            video.id = videoId;
                            video.title = v.title;
                            video.image = v.thumbnails.maxres ? v.thumbnails.maxres.url : v.thumbnails.default.url;
                            video.url = 'https://www.youtube.com/watch?v=' + video.id;

                            callback(video);
                        }
                    });
                };

                var fetchVimeo = function( videoId, callback ){

                    var api = 'https://vimeo.com/api/v2/video/' + videoId + '.json?callback=?';

                    var video = {
                        provider: 'vimeo',
                        id: null,
                        title: null,
                        image: null,
                        url: null
                    };

                    $.getJSON(api, function( data ){

                        if (typeof(data[0]) != "undefined") {
                            var v = data[0];

                            video.id = v.id;
                            video.title = v.title;
                            video.image = v.thumbnail_large || v.thumbnail_small;
                            video.url = v.url;

                            callback(video);
                        }
                    });
                };
                
                var fetchFacebook = function( videoId, callback ){

                    var api = 'https://graph.facebook.com/' + videoId + '?fields=picture,id,title,permalink_url&access_token=212933132386357%7ChuO0oE-d9rPMBVz4YtNj9sF4-ew';

                    var video = {
                        provider: 'facebook',
                        id: null,
                        title: null,
                        image: null,
                        url: null
                    };

                    $.getJSON(api, function( data, status, xhr ){
                        if (typeof data.error == "undefined") {
                            video.id = data.id;
                            video.title = data.title;
                            video.image = data.picture;
                            video.url = "https://www.facebook.com"+data.permalink_url;

                            callback(video);
                        }
                    });
                };

                var parseVideoLink = function( link, callback ){

                    var response = {success: false, message: 'unknown error occured, please try again', data: [] };

                    try {
                        var parser = document.createElement('a');
                    } catch(e){
                        response.message = 'Please post a valid youtube/vimeo/facebook url';
                        return response;
                    }


                    var id = tryYouTube(link);
                    if( id ){
                        return fetchYouTube(id, function(video){
                            if( video ){
                                response.success = true;
                                response.message = 'video found';
                                response.data = video;
                            }
                            callback(response);
                        });
                    }
                    
                    var id = tryVimeo(link);
                    if( id ){
                        return fetchVimeo(id, function(video){
                            if( video ){
                                response.success = true;
                                response.message = 'video found';
                                response.data = video;
                            }
                            callback(response);
                        });
                    }
                    
                    var id = tryFacebook(link);
                    if( id ){
                        return fetchFacebook(id, function(video){
                            if( video ){
                                response.success = true;
                                response.message = 'video found';
                                response.data = video;
                            }
                            callback(response);
                        });
                    }

                    response.message = 'We could not detect a YouTube or Vimeo ID, please try obtain the URL again'
                    return callback(response);
                };

                var updateVideoPreview = function(video, container){

                    var pWrap = container.find('.video-preview'),
                        pLink = container.find('.video-previewLink').not('.dummy'),
                        pImage = container.find('.video-previewImage').not('dummy'),
                        pIcon  = container.find('.video-previewIcon').not('.dummy'),
                        pSuffix = container.find('.video-previewSuffix'),
                        pDummy  = container.find('.video-dummy');

                    pDummy.hide();

                    pLink
                    .attr('href', video.url)
                    .removeClass('youtube vimeo facebook hidden')
                    .addClass(video.provider);

                    pImage
                    .css('backgroundImage', 'url('+video.image+')');

                    pIcon
                    .removeClass('fa-vimeo fa-youtube fa-facebook')
                    .addClass('fa-' + video.provider);
                    pWrap.fadeIn();
                };

                // Loop through all instances of the video field
                $("[data-video]").each(function(index){

                    var $this = $(this),
                        jsonField = $this.find('.video-json'),
                        linkField = $this.find('.video-link'),
                        pDummy = $this.find('.video-dummy'),
                        pWrap = $this.find('.video-preview');

                        try {
                            var videoJson = JSON.parse(jsonField.val());
                            jsonField.val( JSON.stringify(videoJson) );
                            linkField.val( videoJson.url );
                            updateVideoPreview(videoJson, $this);
                        }
                        catch(e){
                            pDummy.show();
                            pWrap.hide();
                            jsonField.val('');
                            linkField.val('');
                        }

                    linkField.on('focus', function(){
                        linkField.originalState = linkField.val();
                    });

                    linkField.on('change', function(){

                        if( linkField.originalState != linkField.val() ){

                            if( linkField.val().length ){

                                videoParsing = true;

                                parseVideoLink( linkField.val(), function( videoJson ){

                                    if( videoJson.success ){
                                        linkField.val( videoJson.data.url );
                                        jsonField.val( JSON.stringify(videoJson.data) );
                                        updateVideoPreview(videoJson.data, $this);
                                    }
                                    else {
                                        pDummy.show();
                                        pWrap.hide();
                                        alert(videoJson.message);
                                    }

                                    videoParsing = false;
                                });
                            }
                            else {
                                videoParsing = false;
                                jsonField.val('');
                                $this.find('.video-preview').fadeOut();
                                pDummy.show();
                                pWrap.hide();
                            }
                        }
                    });
                });

                var videoParsing = false;

                $('form').on('submit', function(e){
                    if( videoParsing ){
                        alert('Video details are still loading, please wait a moment and try again');
                        e.preventDefault();
                        return false;
                    }
                })
            });
        </script>

    @endpush
@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
