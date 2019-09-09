<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>elFinder 2.0 - Standalone Popup</title>

    <!-- jQuery and jQuery UI (REQUIRED) -->
    <link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/themes/smoothness/jquery-ui.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
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
    <!-- Include jQuery, jQuery UI, elFinder (REQUIRED) -->

    <script type="text/javascript">
        $().ready(function () {
            var elf = $('#elfinder').elfinder({
                // set your elFinder options here
                <?php if ($locale) {
        ?>
                    lang: '<?= $locale ?>', // locale
                <?php
    } ?>
                customData: {
                    _token: '<?= csrf_token() ?>'
                },
                url: '<?= route('elfinder.connector') ?>',  // connector URL
                dialog: {width: 900, modal: true, title: 'Select a file'},
                resizable: false,
                commandsOptions: {
                    getfile: {
                        oncomplete: 'destroy'
                    }
                },
                getFileCallback: function (file) {
                    window.parent.processSelectedFile(file.path, '<?= $input_id?>');
                    parent.jQuery.colorbox.close();
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
