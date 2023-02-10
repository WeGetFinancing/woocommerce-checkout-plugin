<?php

namespace WeGetFinancing\WCP;

use WeGetFinancing\WCP\Ajax\Public\GenerateFunnelUrl;
use WeGetFinancing\WCP\PaymentGateway\WeGetFinancing;
use WeGetFinancing\WCP\Wp\PluginAbstract;

define('WGF_PLUGIN_FOLDER', basename(plugin_dir_path(__FILE__)));
define('WGF_PLUGIN_DIR', plugins_url('', __FILE__ ));
define('WGF_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('WGF_PLUGIN_URL', plugins_url() . "/" . WGF_PLUGIN_FOLDER);

class App extends PluginAbstract
{
    public function __construct() {
        parent::__construct();
    }

    public function init()
    {
        $this->addWoocommercePaymentGateway();

    }

    public function addWoocommercePaymentGateway()
    {
        add_filter( 'woocommerce_payment_gateways', function( $methods ) {
            $methods[] = WeGetFinancing::class;
            return $methods;
        } );

    }

    public function addWoCommerceOrderHooks()
    {
        add_action('woocommerce_new_order', function ($order_id) {

            update_post_meta($order_id, 'inv_id', 'value');

        }, 10, 1);
    }

    public function addWoocommerceCustomOrderStatus()
    {

        register_post_status( 'wgf-wc-shipped', [
            'label'                     => 'Shipped',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Shipped (%s)', 'Shipped (%s)' )
        ]);

        // Add to list of WC Order statuses
        add_filter( 'wc_order_statuses', function ( $order_statuses ) {

            $new_order_statuses = array();

            // add new order status after processing
            foreach ( $order_statuses as $key => $status ) {

                $new_order_statuses[ $key ] = $status;

                if ( 'wc-processing' === $key ) {
                    $new_order_statuses['wgf-wc-shipped'] = 'Shipped';
                }
            }

            return $new_order_statuses;
        });
    }

}
