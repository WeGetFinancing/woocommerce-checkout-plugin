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

    public function payment_fields()
    {
        global $woocommerce;

        $order_id = $woocommerce->session->order_awaiting_payment;

        $order = wc_get_order( $order_id );

        if ( $description = $this->get_description() ) {
            echo wpautop( wptexturize( $description ) );
        }

        wp_enqueue_script(
                'wgf-checkout-funnel',
            'https://cdn.wegetfinancing.com/libs/1.0/getfinancing.js'
        );

        ?>
        <input type="hidden" id="wegetfinancing_checkout_order_id" value="<?php echo $order_id; ?>">
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

                            let wgfFunnelData = {};
                            wgfFunnelData['billing_first_name'] = jQuery('#billing_first_name').val();
                            wgfFunnelData['billing_last_name'] = jQuery('#billing_last_name').val();
                            wgfFunnelData['billing_address_1'] = jQuery('#billing_address_1').val();
                            wgfFunnelData['billing_address_2'] = jQuery('#billing_address_2').val();
                            wgfFunnelData['billing_city'] = jQuery('#billing_city').val();
                            wgfFunnelData['billing_postcode'] = jQuery('#billing_postcode').val();
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
                                    console.log("generateWeGetFinancingFunnelAction RESPONSE:\n" + response);

                                    if (response.isSuccess === false) {
                                        alert(response.error + " " + response.message)
                                    }

                                    new GetFinancing(
                                        response.href,
                                        function() {
                                            jQuery('form[name="checkout"] #place_order').click()
                                        }.bind(self),
                                        function() {}
                                    )
                                },
                                error: function(response){
                                    console.log("generateWeGetFinancingFunnelAction ERROR RESPONSE:\n" + response);
                                    alert("unexpected error");
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
