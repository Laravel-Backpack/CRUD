<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>elFinder 2.0 - File Picker</title>

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

    <?php
    $mimeTypes = implode(',', array_map(function ($t) {
        return "'".$t."'";
    }, explode(',', $type)));
    ?>

    <script type="text/javascript">
        $().ready(function () {
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
                resizable: false,
                ui: ['toolbar', 'path','stat'],
                onlyMimes: [<?= $mimeTypes ?>],
                rememberLastDir : false,
                height: 300,
                defaultView: 'list',
                getFileCallback: function (file) {
                    window.parent.processSelectedFile(file, '<?= $input_id?>');
                    console.log(file);
                },

                uiOptions : {
                    // toolbar configuration
                    toolbar : [
                        ['home', 'up'],
                        ['upload'],

                        ['quicklook'],

                    ],
                    // directories tree options
                    tree : {
                        // expand current root on init
                        openRootOnLoad : true,
                        // auto load current dir parents
                        syncTree : true
                    },
                    // navbar options
                    navbar : {
                        minWidth : 150,
                        maxWidth : 500
                    },

                    // current working directory options
                    cwd : {
                        // display parent directory in listing as ".."
                        oldSchool : false
                    }
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
