<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PaymentGateway;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WeGetFinancingBlockSupport extends AbstractPaymentMethodType
{
    private $gateway;

    protected $name = WeGetFinancing::GATEWAY_ID;

    public function initialize() {
        // get payment gateway settings
        $this->settings = get_option( "woocommerce_{$this->name}_settings", array() );

        // you can also initialize your payment gateway here
        // $gateways = WC()->payment_gateways->payment_gateways();
        // $this->gateway  = $gateways[ $this->name ];
    }

    public function is_active() {
        return ! empty( $this->settings[ 'enabled' ] ) && 'yes' === $this->settings[ 'enabled' ];
    }

    public function get_payment_method_script_handles() {

        wp_register_script(
            'wc-wegetfinancing-blocks-integration',
            plugin_dir_url( __DIR__ ) . 'build/index.js',
            array(
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
            ),
            null, // or time() or filemtime( ... ) to skip caching
            true
        );

        return array( 'wc-wegetfinancing-blocks-integration' );

    }

    public function get_payment_method_data()
    {
        return array(
            'title' => $this->get_setting('title'),
            // almost the same way:
            // 'title'     => isset( $this->settings[ 'title' ] ) ? $this->settings[ 'title' ] : 'Default value';
            'description' => $this->get_setting('description'),
            // if $this->gateway was initialized on line 15
            // 'supports'  => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] ),

            // example of getting a public key
            // 'publicKey' => $this->get_publishable_key(),
        );
    }
}