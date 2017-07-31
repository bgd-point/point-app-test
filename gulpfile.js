var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.styles([
        'theme-modified.css',
        'browser-modified.css',
        '../plugins/sweetalert/sweetalert.css',
        '../plugins/sweetalert/themes/google.css',
        '../plugins/selectize/css/selectize.css'
    ], 'public/core/assets/css');

    mix.scripts([
        'dragscroll.js',
        'csrf.js',
        'jsCookie.js',
        'autosize.min.js',
        'tempdata-function.js',
        'datatables-function.js',
        'jquery.inputmask.bundle.min.js',
        'blockui/jquery.blockui.js',
        'blockui/blockui.js',
        '../plugins/sweetalert/sweetalert.min.js',
        'sweetalert-function.js',
        'autoNumeric.js',
        'autoNumeric-function.js',
        '../plugins/selectize/js/standalone/selectize.min.js',
        '../plugins/gritter/js/jquery.gritter.min.js',
        'function.js'
    ], 'public/core/assets/js');

    mix.version(['core/assets/js/all.js', 'core/assets/css/all.css'])
});
