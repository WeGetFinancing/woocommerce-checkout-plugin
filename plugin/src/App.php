<?php

namespace WeGetFinancing\WCP;

use WeGetFinancing\WCP\PaymentGateway\WeGetFinancing;
use WeGetFinancing\WCP\Wp\PluginAbstract;
use WeGetFinancing\WCP\Wp\ViewHelper;

define('WGF_PLUGIN_FOLDER', basename(plugin_dir_path(__FILE__)));
define('WGF_PLUGIN_DIR', plugins_url('', __FILE__ ));
define('WGF_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('WGF_PLUGIN_URL', plugins_url() . "/" . WGF_PLUGIN_FOLDER);

class App extends PluginAbstract
{
    public function __construct() {
        parent::__construct();

        $this->isConfigured = false;
    }

    function isNotConfiguredAdminNotice() {
        if(!$this->isConfigured) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <?php _e( 'Configure WeGetFinancing Before Using It', 'wegetfinancing' ); ?>
                </p>
            </div>
            <?php
        }
    }

    function addAdminMenu() {
        add_menu_page(
            'Configuration',
            'WeGetFinancing',
            'administrator',
            'wegetfinancing',
            [$this, 'AdminPage']
        );

//        add_submenu_page(
//            'wegetfinancing',
//            'Configuration',
//            'Configuration',
//            'administrator',
//            'wegetfinancing-configuration',
//            [$this, 'ConfigurationPage']
//        );
    }

    public function AdminPage()
    {
        wp_enqueue_style( 'wgf-api_bootstrap_4',
            'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css'
        );

        ViewHelper::includeWithVariables(
            WGF_PLUGIN_PATH . 'views/admin_page.php',
            [],
            true, // PRINT_OUTPUT
            [
                    '{{saveSettingsFormAction}}' => 'saveSettings',
//                '{{formActionGetIds}}' => 'wooGetProductsIds',
//                '{{formActionCheckProduct}}' => 'checkProduct',
//                '{{ajaxLoaderGifUrl}}' => WGF_PLUGIN_URL . '/ajax-loader.gif',
            ]
        );
    }

    function saveSettingsForm() {
        ob_start();

        $data = base64_decode( $_POST['data'] );

        $data = json_decode( $data, TRUE );

        $output = ob_get_contents();
        ob_end_clean();

        foreach( $data as $key => $value ) {

            update_option( $key, $value, true );

        }

        $this->ajaxRespondString( 'Everything Updated' );
    }

//    public function ConfigurationPage()
//    {
//        echo "CONFIGURATION PAGE";
//    }

    public function init()
    {
        // Add Actions
        $this->addAction( 'admin_notices', 'isNotConfiguredAdminNotice' );

        $this->addAjaxAction('saveSettings', 'saveSettingsForm');

        $this->addAdminMenu();

        // Add Woocommerce Stuff
        $this->addWoocommerceCustomOrderStatus();
        $this->addWocommerceOrderHooks();
        $this->addWoocommercePaymentGateway();
    }

    public function addWocommercePaymentGateway()
    {
        $this->addAction( 'plugins_loaded', 'paymentGatewayInit' );


    }

    public function paymentGatewayInit() {
        add_filter( 'woocommerce_payment_gateways', function( $methods ) {
            $methods[] = WeGetFinancing::class;
            return $methods;
        } );
    }

    public function addWocommerceOrderHooks()
    {
        add_action('woocommerce_new_order', function ($order_id) {

            update_post_meta($order_id, 'inv_id', 'valore');

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
