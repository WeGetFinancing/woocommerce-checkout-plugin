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
 * Version:           1.4.1
 * Requires at least: 5.0
 * Requires PHP:      8.0
 * Author:            Riccardo De Leo
 * Author URI:        https://github.com/WeGetFinancing
 * Text Domain:       wegetfinancing-payment-gateway
 * License:           LGPL-3.0-only
 * License URI:       https://github.com/WeGetFinancing/woocommerce-checkout-plugin#LICENSE
 */

require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use Automattic\WooCommerce\Utilities\FeaturesUtil;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancingBlockSupport;

new App(__DIR__, __FILE__);

add_action('before_woocommerce_init', function () {
    if (true === class_exists(FeaturesUtil::class)) {
        FeaturesUtil::declare_compatibility(
            'cart_checkout_blocks',
            __FILE__,
            true // true (compatible, default) or false (not compatible)
        );
    }
});


add_action('woocommerce_blocks_loaded', function () {
    if (true === class_exists(AbstractPaymentMethodType::class)) {
        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            function (PaymentMethodRegistry $payment_method_registry) {
                $payment_method_registry->register(new WeGetFinancingBlockSupport());
            }
        );
    }
});
