<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>elFinder 2.0 - CKEditor</title>

    <!-- jQuery and jQuery UI (REQUIRED) -->
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>

    <!-- elFinder CSS (REQUIRED) -->
    <link rel="stylesheet" type="text/css" href="<?= asset($dir.'/css/elfinder.min.css') ?>">
    <!-- <link rel="stylesheet" type="text/css" href="<?= asset($dir.'/css/theme.css') ?>"> -->
    <link rel="stylesheet" type="text/css" href="<?= asset('packages/backpack/base/css/elfinder.backpack.theme.css') ?>">

    <!-- elFinder JS (REQUIRED) -->
    <script src="<?= asset($dir.'/js/elfinder.min.js') ?>"></script>

    <?php if ($locale) {
    ?>
        <!-- elFinder translation (OPTIONAL) -->
        <script src="<?= asset($dir."/js/i18n/elfinder.$locale.js") ?>"></script>
    <?php
} ?>

    <!-- elFinder initialization (REQUIRED) -->
    <script type="text/javascript" charset="utf-8">
        // Helper function to get parameters from the query string.
        function getUrlParam(paramName) {
            var reParam = new RegExp('(?:[\?&]|&amp;)' + paramName + '=([^&]+)', 'i') ;
            var match = window.location.search.match(reParam) ;

            return (match && match.length > 1) ? match[1] : '' ;
        }

        $().ready(function() {
            var funcNum = getUrlParam('CKEditorFuncNum');

            var elf = $('#elfinder').elfinder({
                // set your elFinder options here
                resizable: false,
                <?php if ($locale) {
        ?>
                    lang: '<?= $locale ?>', // locale
                <?php
    } ?>
                customData: {
                    _token: '<?= csrf_token() ?>'
                },
                url: '<?= route('elfinder.connector') ?>',  // connector URL
                getFileCallback : function(file) {
                    window.opener.CKEDITOR.tools.callFunction(funcNum, file.url);
                    window.close();
                }
            }).elfinder('instance');
        });
        $(window).resize(function(){
            var h = ($(window).height());
            if($('#elfinder').height() != h){
                $('#elfinder').height(h).resize();
            }
        });
    </script>
</head>
<body class="elfinder">
    <!-- Element where elFinder will be created (REQUIRED) -->
    <div id="elfinder"></div>
</body>
</html>
