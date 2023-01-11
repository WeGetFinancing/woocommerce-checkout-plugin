<?php
/*
Plugin Name: Woocommerce Checkout Plugin
Plugin URI:  https://github.com/wegetfinancing/woocommerce-checkout-plugin
Description: WeGetFinancing Woocommerce Checkout Plugin
Version:     1.0.0
Author:      Riccardo De Leo
Author URI:  https://github.com/javanile
License:
License URI: https://github.com/.../LICENSE
*/

add_action('query', function($sql) {
    var_dump($sql);
    return $sql;
});
