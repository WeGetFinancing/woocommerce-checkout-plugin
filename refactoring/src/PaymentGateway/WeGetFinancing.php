<?php

namespace WeGetFinancing\Checkout\PaymentGateway;

use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class WeGetFinancing extends \WC_Payment_Gateway implements ActionableInterface
{
    use AddableTrait;

    public const ACTION_NAME = 'woocommerce_update_options_payment_gateways_';

    public function __construct() {
        $this->id = 'wegetfinancing';
        $this->has_fields = false;
        $this->method_title = "WeGetFinancing Checkout";
        $this->method_descriptions = "Offer affordable monthly payments to your existing customers while you receive the money directly into your account, in one lump sum.";

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title        = $this->get_option( 'wegetfinancing_checkout_title' );
        $this->description  = $this->get_option( 'wegetfinancing_checkout_description' );
        $this->is_sandbox  = $this->get_option( 'wegetfinancing_checkout_is_sandbox' );
        $this->username  = $this->get_option( 'wegetfinancing_checkout_username' );
        $this->password  = $this->get_option( 'wegetfinancing_checkout_password' );
        $this->merchant_id  = $this->get_option( 'wegetfinancing_checkout_merchant_id' );


        // Actions
        add_action(
            '' . $this->id,
            [ $this, 'process_admin_options' ]
        );
    }


    public function init(): void
    {
        $this->addAction(
                $this->getActionName() . $this->id,
                self::FUNCTION_NAME
        );
    }

    /**
     * Initialize Gateway Settings Form Fields
     */
    public function init_form_fields() {

        $this->form_fields = apply_filters( 'wc_wgf_form_fields', array(

            'enabled' => array(
                'title'   => __( 'Enable/Disable', 'wc-gateway-wgf' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable WeGetFinancing Payment', 'wc-gateway-wgf' ),
                'default' => 'yes'
            ),

            'is_sandbox' => array(
                'title'   => __( 'Enable/Disable', 'wc-gateway-wgf' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Sandbox', 'wc-gateway-wgf' ),
                'default' => 'yes'
            ),

            'title' => array(
                'title'       => __( 'Title', 'wc-gateway-wgf' ),
                'type'        => 'text',
                'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-wgf' ),
                'default'     => __( 'WeGetFinancing', 'wc-gateway-wgf' ),
                'desc_tip'    => true,
            ),

            'description' => array(
                'title'       => __( 'Description', 'wc-gateway-wgf' ),
                'type'        => 'textarea',
                'description' => __( 'Pay with WeGetFinancing', 'wc-gateway-wgf' ),
                'default'     => __( 'Pay monthly with WeGetFinancing', 'wc-gateway-wgf' ),
                'desc_tip'    => true,
            ),

            'username' => array(
                'title'       => __( 'Username', 'wc-gateway-wgf' ),
                'type'        => 'text',
                'description' => __( 'WGF Username', 'wc-gateway-wgf' ),
                'default'     => __( '', 'wc-gateway-wgf' ),
                'desc_tip'    => true,
            ),

            'password' => array(
                'title'       => __( 'Password', 'wc-gateway-wgf' ),
                'type'        => 'password',
                'description' => __( 'WGF Password', 'wc-gateway-wgf' ),
                'default'     => __( '', 'wc-gateway-wgf' ),
                'desc_tip'    => true,
            ),

            'merchant_id' => array(
                'title'       => __( 'Merchant ID', 'wc-gateway-wgf' ),
                'type'        => 'text',
                'description' => __( 'WGF Merchant ID', 'wc-gateway-wgf' ),
                'default'     => __( '', 'wc-gateway-wgf' ),
                'desc_tip'    => true,
            ),
        ) );
    }

    public function admin_options() {
        ?>

        <h2>WeGetFinancing Payment Gateway</h2>

        <table class="form-table">

            <?php $this->generate_settings_html(); ?>

        </table>

        <?php
    }

    public function payment_fields(){

        global $woocommerce;

        $order_id = $woocommerce->session->order_awaiting_payment;

        $order = wc_get_order( $order_id );

        if ( $description = $this->get_description() ) {
            echo wpautop( wptexturize( $description ) );
        }

        ?>
        <script>
            jQuery(document).ready(function() {
                jQuery('form[name="checkout"] input[name="payment_method"]').eq(0).prop('checked', true).attr( 'checked', 'checked' );
                wGF_WC_Gateway();
            });

            jQuery(document).on("change", "form[name='checkout'] input[name='payment_method']", function(){
                if ( 0 === jQuery('form[name="checkout"] input[name="payment_method"]' ).filter( ':checked' ).size() ) {
                    jQuery(this).prop('checked', true).attr( 'checked', 'checked' );
                };
                wGF_WC_Gateway();
            });

            function wGF_WC_Gateway(){
                if(jQuery('form[name="checkout"] input[name="payment_method"]:checked').val() === '<?php echo $this->id; ?>'){
                    console.log("Using my gateway");

                    jQuery('form[name="checkout"] #place_order').hide()

                    if(jQuery('#wgf_checkout_button').length <= 0) {
                        jQuery('form[name="checkout"] #place_order').parent().append(`
                            <a id="wgf_checkout_button"  class="wgf_checkout_button" href="#wgf_checkout">
                                <img src="https://wiki.dev.wegetfinancing.com/public/brand/resources/buttons/logoCircular.svg" alt="WeGetFinancing Checkout Button">
                            </a>
                        `)

                        jQuery('#wgf_checkout_button').click((e) => {

                            // jQuery('form[name="checkout"] #place_order').click()

                        })
                    }

                    jQuery('form[name="checkout"]').on('submit', function(e){
                        // Process using custom gateway
                        // Etc etc
                        e.preventDefault();
                    });
                }else{
                    // Not using gateway

                    jQuery('form[name="checkout"] #place_order').show()
                    jQuery('.wgf_checkout_button').remove()

                    console.log("Not using my gateway. Proceed as usual");

                    jQuery('form[name="checkout"]').unbind('submit');
                }
            };
        </script>
        <?php
    }

    /**
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id ) {

        $order = wc_get_order( $order_id );

        // Mark as on-hold (we're awaiting the payment)
        $order->update_status( 'on-hold', __( 'Awaiting WeGetFinancing payment', 'wc-gateway-wgf' ) );

        // Reduce stock levels
        $order->reduce_order_stock();

        // Remove cart
        WC()->cart->empty_cart();

        // Return thankyou redirect
        return array(
            'result' 	=> 'success',
            'redirect'	=> $this->get_return_url( $order )
        );
    }
}
