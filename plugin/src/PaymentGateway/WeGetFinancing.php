<?php

namespace WeGetFinancing\WCP\PaymentGateway;

class WeGetFinancing extends \WC_Payment_Gateway
{
    public function __construct() {
        $this->id = 'wegetfinancing';
        $this->has_fields = false;
        $this->method_title = "WeGetFinancing";
        $this->method_descriptions = "Pay month by month";

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        $this->is_sandbox = $this->get_option( 'is_sandbox' );
        $this->username = $this->get_option( 'username' );
        $this->password = $this->get_option( 'password' );
        $this->merchant_id = $this->get_option( 'merchant_id' );


        // Actions
        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            [ $this, 'process_admin_options' ]
        );
    }

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

    public function payment_fields()
    {
        global $woocommerce;

        $order_id = $woocommerce->session->order_awaiting_payment;

        if ( $description = $this->get_description() ) {
            echo wpautop( wptexturize( $description ) );
        }

        wp_enqueue_script(
                'wgf-checkout-funnel',
            'https://cdn.wegetfinancing.com/libs/1.0/getfinancing.js'
        );

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

                    jQuery('form[name="checkout"] #place_order').hide()

                    if(jQuery('#wgf_checkout_button').length <= 0) {
                        jQuery('form[name="checkout"] #place_order').parent().append(`
                            <a id="wgf_checkout_button"  class="wgf_checkout_button" href="#wgf_checkout">
                                <img src="https://wiki.dev.wegetfinancing.com/public/brand/resources/buttons/logoCircular.svg" alt="WeGetFinancing Checkout Button">
                            </a>
                        `)

                        jQuery('#wgf_checkout_button').click((e) => {

                            let wgfFunnelData = {};
                            wgfFunnelData['billing_first_name'] = jQuery('#billing_first_name').val();
                            wgfFunnelData['billing_last_name'] = jQuery('#billing_last_name').val();
                            wgfFunnelData['billing_email'] = jQuery('#billing_email').val();
                            wgfFunnelData['billing_phone'] = jQuery('#billing_phone').val();

                            let requestNewFunnelData = {
                                'action': 'generateWeGetFinancingFunnelAction',
                                'data': wgfFunnelData
                            };

                            jQuery.ajax({
                                type: "post",
                                dataType: "json",
                                url: "<?php echo admin_url('admin-ajax.php'); ?>",
                                data: requestNewFunnelData,
                                success: function(response){

                                    console.log("generateWeGetFinancingFunnelAction RESPONSE:\n" + JSON.stringify(response));

                                    if (response.isSuccess === false) {
                                        if ( jQuery(".woocommerce-error").length ) {
                                            jQuery(".woocommerce-error").parent().remove();
                                        }

                                        let error_message = '<div class="woocommerce-notices-wrapper">' +
                                            '<ul class="woocommerce-error" id="wegetfinancing-woocommerce-error">';

                                        if ("message" in response) {
                                            error_message += "<li>" + response.message + "</li>";
                                        }
                                        if ("violations" in response) {
                                            jQuery.each(response.violations, function( index, violation ) {
                                                jQuery.each(violation.messages, function( index, message ) {
                                                    error_message += "<li>" + message + "</li>";
                                                });
                                                jQuery.each(violation.fields, function( index, field ) {
                                                    jQuery("#" + field).closest( '.form-row' ).addClass( 'woocommerce-invalid' );
                                                });
                                            });
                                        }

                                        error_message += "</ul></div>";

                                        jQuery(".entry-content > .woocommerce").prepend(error_message);
                                    } else {
                                        new GetFinancing(
                                            response.href,
                                            function() {
                                                jQuery('form[name="checkout"] #place_order').click()
                                            }.bind(self),
                                            function() {}
                                        );
                                    }
                                },
                                error: function(){
                                    if ( jQuery(".woocommerce-error").length ) {
                                        jQuery(".woocommerce-error").parent().remove();
                                    }

                                    jQuery(".woocommerce").prepend('<div class="woocommerce-notices-wrapper">' +
                                        '<ul class="woocommerce-error" id="wegetfinancing-woocommerce-error">' +
                                        '<li>Unexpected server error</li>' +
                                        '</ul>' +
                                        '</div>');
                                },
                            });


                        })
                    }

                    jQuery('form[name="checkout"]').on('submit', function(e){
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
