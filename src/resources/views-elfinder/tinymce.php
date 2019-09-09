
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>elFinder 2.0 - TinyMCE</title>

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

    <!-- TinyMCE Popup class (REQUIRED) -->
    <script type="text/javascript" src="<?= asset($dir.'/js/tiny_mce_popup.js') ?>"></script>

    <?php if ($locale) {
    ?>
        <!-- elFinder translation (OPTIONAL) -->
        <script src="<?= asset($dir."/js/i18n/elfinder.$locale.js") ?>"></script>
    <?php
} ?>


    <script type="text/javascript">
        var FileBrowserDialogue = {
            init: function() {
                // Here goes your code for setting your custom things onLoad.
            },
            mySubmit: function (URL) {
                var win = tinyMCEPopup.getWindowArg('window');

                // pass selected file path to TinyMCE
                win.document.getElementById(tinyMCEPopup.getWindowArg('input')).value = URL;

                // are we an image browser?
                if (typeof(win.ImageDialog) != 'undefined') {
                    // update image dimensions
                    if (win.ImageDialog.getImageData) {
                        win.ImageDialog.getImageData();
                    }
                    // update preview if necessary
                    if (win.ImageDialog.showPreviewImage) {
                        win.ImageDialog.showPreviewImage(URL);
                    }
                }

                // close popup window
                tinyMCEPopup.close();
            }
        }

        tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);

        $().ready(function() {
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
                url : '<?= route('elfinder.connector') ?>',  // connector URL
                getFileCallback: function(file) { // editor callback
                    FileBrowserDialogue.mySubmit(file.url); // pass selected file path to TinyMCE
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
