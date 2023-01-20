<?php
/*
Plugin Name: WordPress WooCommerce Checkout Plugin
Plugin URI:  https://github.com/wegetfinancing/woocommerce-checkout-plugin
Description: WeGetFinancing Woocommerce Checkout Plugin
Version:     1.0.0
Author:      Riccardo De Leo
Author URI:  https://github.com/wegetfinancing
License:
License URI: https://github.com/.../LICENSE
*/

require __DIR__ . '/vendor/autoload.php';

use WeGetFinancing\WCP\App;

new App();
