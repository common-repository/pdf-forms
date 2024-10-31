<?php
/*
Plugin Name: PDF Form Filler
Plugin URI: https://github.com/pdffiller/wp-integration-pdf-forms
Description: Fill and send form
Version: 0.2.78
Author: PDFFiller
Author URI: https://github.com/pdffiller
Text Domain: pdf-form
Domain Path: /languages
*/

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php' ;

use PdfFormsLoader\Core\PdfFormsLoader;
use PdfFormsLoader\Integrations\IntegrationsAPI;

if(!function_exists('wp_get_current_user')) {
    require_once(ABSPATH . "wp-includes/pluggable.php");
}

if (!function_exists('dd')) {
    function dd($variable){
        echo '<pre>';
        var_dump($variable);
        echo '</pre>';
        exit;
    }
}

new IntegrationsAPI();
new PdfFormsLoader();
