<?php

/**
 * WeGetFinancing Payment Gateway
 *
 * @package           WeGetFinancingPaymentGateway
 * @author            Riccardo De Leo
 * @copyright         2023 Emerging Payments Technologies
 * @license           LGPL-3.0-only
 *
 * @wordpress-plugin
 * Plugin Name:       WeGetFinancing Payment Gateway
 * Plugin URI:        https://github.com/WeGetFinancing/magento-checkout-plugin
 * Description:       Integrate WeGetFinancing payment gateway to woocommerce
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Riccardo De Leo
 * Author URI:        https://github.com/WeGetFinancing
 * Text Domain:       wegetfinancing-checkout
 * License:           LGPL-3.0-only
 * License URI:       https://github.com/WeGetFinancing/magento-checkout-plugin#LICENSE
 * Update URI:        https://github.com/WeGetFinancing/magento-checkout-plugin
 */

require __DIR__ . '/vendor/autoload.php';

new \WeGetFinancing\WCP\App();
