<?php

namespace WeGetFinancing\Checkout\PaymentGateway;

use Twig\Environment;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class WeGetFinancing extends \WC_Payment_Gateway implements ActionableInterface
{
    use AddableTrait;

    public const GATEWAY_ID = 'wegetfinancing';
    public const INIT_NAME = 'woocommerce_update_options_payment_gateways_';
    public const FUNCTION_NAME = 'process_admin_options';

    protected Environment $twig;

    public function __construct()
    {
        $this->twig = $GLOBALS['wegetfinancing_twig'];
        $this->id = self::GATEWAY_ID;
        $this->has_fields = false;
        $this->icon = ''; // TODO: icon.
        $this->method_title = "WeGetFinancing";
        $this->method_description = "Boost your sales by adding WeGetFinancing to your checkout. " .
            "Offer affordable monthly payments to your existing customers while you receive the money" .
            " directly into your account, in one lump sum.";
        $this->title = "WeGetFinancing";
        $this->description = "Pay monthly, obtain instant approval with no extensive paperwork." .
            " Get credit approval in just seconds, so you can complete your purchase immediately, hassle-free.";

        $this->countries = ['US'];
        $this->availability = ['US'];

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->wgf_is_sandbox = $this->get_option('wgf_is_sandbox', true);
        $this->wgf_username = $this->get_option('wgf_username');
        $this->wgf_password = $this->get_option('wgf_password');
        $this->wgf_merchant_id = $this->get_option('wgf_merchant_id');
    }

    public function getInitName(): string
    {
        return self::INIT_NAME . $this->id;
    }

    public function init(): void
    {
        $this->addAction();
    }

    public function init_form_fields()
    {
        $this->form_fields = apply_filters(
            'wc_wgf_form_fields',
            [
                'wgf_is_sandbox' => [
                    'title'   => __( 'Sandbox Environment', 'wc-gateway-wgf' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable/Disable Sandbox Environment', 'wc-gateway-wgf' ),
                    'default' => 'yes'
                ],
                'wgf_username' => [
                    'title'       => __( 'Username', 'wc-gateway-wgf' ),
                    'type'        => 'text',
                    'description' => __( 'WeGetFinancing Username', 'wc-gateway-wgf' ),
                    'default'     => __( '', 'wc-gateway-wgf' ),
                    'desc_tip'    => true,
                ],
                'wgf_password' => [
                    'title'       => __( 'Password', 'wc-gateway-wgf' ),
                    'type'        => 'password',
                    'description' => __( 'WeGetFinancing Password', 'wc-gateway-wgf' ),
                    'default'     => __( '', 'wc-gateway-wgf' ),
                    'desc_tip'    => true,
                ],
                'wgf_merchant_id' => [
                    'title'       => __( 'Merchant ID', 'wc-gateway-wgf' ),
                    'type'        => 'text',
                    'description' => __( 'WeGetFinancing Merchant ID', 'wc-gateway-wgf' ),
                    'default'     => __( '', 'wc-gateway-wgf' ),
                    'desc_tip'    => true,
                ]
            ]
        );
    }

    public function admin_options(): void
    {
        echo $this->twig->render(
            'admin/payment_settings.twig',
            ['form' => $this->generate_settings_html([], false)]
        );
    }

    public function payment_fields(): void
    {
        wp_enqueue_script(
            'wgf-checkout-funnel',
            'https://cdn.wegetfinancing.com/libs/1.0/getfinancing.js'
        );

        echo $this->twig->render(
            'store/checkout.twig',
            [
                'description' => $this->description,
                'payment_method_id' => $this->id,
                'ajax_url' => admin_url('admin-ajax.php')
            ]
        );
    }

    /**
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id): array
    {
        $order = wc_get_order( $order_id );
        // Mark as on-hold (we're awaiting the payment)
        $order->update_status( 'on-hold', __( 'Awaiting WeGetFinancing payment', 'wc-gateway-wgf' ) );
        // Reduce stock levels
        wc_reduce_stock_levels($order->get_id());
        // Remove cart
        WC()->cart->empty_cart();
        // Return thankyou redirect
        return [
            'result' 	=> 'success',
            'redirect'	=> $this->get_return_url($order)
        ];
    }
}
