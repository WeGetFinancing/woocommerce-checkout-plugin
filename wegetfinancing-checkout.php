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
 * Plugin URI:        https://github.com/WeGetFinancing/woocommerce-checkout-plugin
 * Description:       Integrate WeGetFinancing payment gateway to woocommerce
 * Version:           1.2.1
 * Requires at least: 5.0
 * Requires PHP:      8.0
 * Author:            Riccardo De Leo
 * Author URI:        https://github.com/WeGetFinancing
 * Text Domain:       wegetfinancing-payment-gateway
 * License:           LGPL-3.0-only
 * License URI:       https://github.com/WeGetFinancing/woocommerce-checkout-plugin#LICENSE
 */

require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use WeGetFinancing\Checkout\App;

new App(__DIR__, __FILE__);
