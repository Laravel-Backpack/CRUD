const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Backpack maintainers use mix to:
 | - install and update CSS and JS assets;
 | - copy everything that needs to be published into src/public
 |
 | All JS will be bundled into one file (see bundle.js).
 |
 | How to use (for maintainers only):
 | - cd vendor/backpack/crud
 | - npm install
 | - npm run prod
 | (this will also publish the assets for you to test, so no need to do that too)
 */

// merge all needed JS into a big bundle file
mix.js('src/resources/assets/js/bundle.js', 'src/public/packages/backpack/base/js/');

// merge all needed CSS into a big bundle file
mix.sass('src/resources/assets/scss/bundle.scss', 'src/public/packages/backpack/base/css/')
	.options({
      processCssUrls: false
    });

// copy the Backstrap CSS
mix.copy('node_modules/@digitallyhappy/backstrap/dist/css', 'src/public/packages/@digitallyhappy/backstrap/css');

// copy fonts and other assets
mix.copy('node_modules/line-awesome/dist/line-awesome', 'src/public/packages/line-awesome')
	.copy('node_modules/source-sans-pro', 'src/public/packages/source-sans-pro')
	.copy('node_modules/animate.css/animate.min.css', 'src/public/packages/animate.css/animate.min.css')
	.copy('node_modules/noty/lib', 'src/public/packages/noty');


// copy CRUD filters JS into packages
mix.copy('node_modules/bootstrap-datepicker/dist', 'src/public/packages/bootstrap-datepicker/dist')
	.copy('node_modules/moment/min', 'src/public/packages/moment/min')
	.copy('node_modules/select2/dist', 'src/public/packages/select2/dist')
	.copy('node_modules/jquery-colorbox', 'src/public/packages/jquery-colorbox')
	.copy('node_modules/jquery-ui-dist', 'src/public/packages/jquery-ui-dist')
	.copy('node_modules/select2-bootstrap-theme/dist', 'src/public/packages/select2-bootstrap-theme/dist')
	.copy('node_modules/bootstrap-daterangepicker/daterangepicker.css', 'src/public/packages/bootstrap-daterangepicker/daterangepicker.css')
	.copy('node_modules/bootstrap-daterangepicker/daterangepicker.js', 'src/public/packages/bootstrap-daterangepicker/daterangepicker.js')
	.copy('node_modules/pc-bootstrap4-datetimepicker/build', 'src/public/packages/pc-bootstrap4-datetimepicker/build')
	.copy('node_modules/cropperjs/dist', 'src/public/packages/cropperjs/dist')
	.copy('node_modules/jquery-cropper/dist', 'src/public/packages/jquery-cropper/dist')
	.copy('node_modules/ckeditor', 'src/public/packages/ckeditor')
	.copy('node_modules/bootstrap-colorpicker/dist', 'src/public/packages/bootstrap-colorpicker/dist')
	.copy('node_modules/bootstrap-iconpicker/bootstrap-iconpicker', 'src/public/packages/bootstrap-iconpicker/bootstrap-iconpicker')
	.copy('node_modules/bootstrap-iconpicker/icon-fonts', 'src/public/packages/bootstrap-iconpicker/icon-fonts')
	.copy('node_modules/simplemde/dist', 'src/public/packages/simplemde/dist')
	.copy('node_modules/easymde/dist', 'src/public/packages/easymde/dist')
	.copy('node_modules/summernote/dist', 'src/public/packages/summernote/dist')
	.copy('node_modules/tinymce', 'src/public/packages/tinymce')
	.copy('node_modules/nestedSortable', 'src/public/packages/nestedSortable')
	.copy('node_modules/datatables.net', 'src/public/packages/datatables.net')
	.copy('node_modules/datatables.net-bs4', 'src/public/packages/datatables.net-bs4')
	.copy('node_modules/datatables.net-fixedheader', 'src/public/packages/datatables.net-fixedheader')
	.copy('node_modules/datatables.net-fixedheader-bs4', 'src/public/packages/datatables.net-fixedheader-bs4')
	.copy('node_modules/datatables.net-responsive', 'src/public/packages/datatables.net-responsive')
	.copy('node_modules/datatables.net-responsive-bs4', 'src/public/packages/datatables.net-responsive-bs4')
	.copy('node_modules/places.js/dist', 'src/public/packages/places.js/dist')
	.copy('node_modules/urijs/src/URI.min.js', 'src/public/packages/URI.js/URI.min.js');

// FOR MAINTAINERS
// copy asset files from Base's public folder the main app's public folder
// so that you don't have to publish the assets with artisan to test them
mix.copyDirectory('src/public', '../../../public')
