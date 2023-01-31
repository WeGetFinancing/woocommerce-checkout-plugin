<?php

namespace WeGetFinancing\WCP;

use WeGetFinancing\WCP\PaymentGateway\WeGetFinancing;
use WeGetFinancing\WCP\Wp\PluginAbstract;
use WeGetFinancing\WCP\Wp\ViewHelper;
use WeGetFinancing\SDK\Client;
use WeGetFinancing\SDK\Entity\Request\AuthRequestEntity;
use WeGetFinancing\SDK\Entity\Request\LoanRequestEntity;

define('WGF_PLUGIN_FOLDER', basename(plugin_dir_path(__FILE__)));
define('WGF_PLUGIN_DIR', plugins_url('', __FILE__ ));
define('WGF_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('WGF_PLUGIN_URL', plugins_url() . "/" . WGF_PLUGIN_FOLDER);

class App extends PluginAbstract
{
    public $isConfigured = false;

    public function __construct() {
        parent::__construct();
    }

    public function init()
    {
        $this->addAction( 'admin_notices', 'isNotConfiguredAdminNotice' );
        $this->addAjaxAction('saveSettings', 'saveSettingsForm');

        add_action(
            'wp_ajax_nopriv_generateWeGetFinancingFunnelAction',
            [ $this, "generateFunnelAction" ]
        );

        $this->addAdminMenu();
        $this->addWoocommercePaymentGateway();
    }

    public function generateFunnelAction()
    {
        try {
            $data = $_POST['data'];

            $auth = AuthRequestEntity::make([
                'username' => getenv('WEGETFINANCING_CHECKOUT_USERNAME'),
                'password'  => getenv('WEGETFINANCING_CHECKOUT_PASSWORD'),
                'merchantId' => getenv('WEGETFINANCING_CHECKOUT_MERCHANT_ID'),
                'url' => getenv('WEGETFINANCING_CHECKOUT_URL')
            ]);

            $client = Client::Make($auth);

            $request = LoanRequestEntity::make([
                'first_name' => $data['billing_first_name'],
                'last_name' => $data['billing_last_name'],
                'shipping_amount' => 150,
                'version' => '1.9',
                'email' => $data['billing_email'],
                'phone' => $data['billing_phone'],
                'merchant_transaction_id' => '***',
                'success_url' => '',
                'failure_url' => '',
                'postback_url' => '',
                'billing_address' => [
                    'street1' => $data['billing_address_1'] . ' ' . $data['billing_address_2'],
                    'city' => $data['billing_city'],
                    'state' => 'NJ',
                    'zipcode' => $data['billing_postcode'],
                ],
                'shipping_address' => [
                    'street1' => $data['billing_address_1'] . ' ' . $data['billing_address_2'],
                    'city' => $data['billing_city'],
                    'state' => 'NJ',
                    'zipcode' => $data['billing_postcode'],
                ],
                'cart_items' => [
                    [
                        'sku' => 'SKU_CODE_001',
                        'displayName' => 'Cart product 001',
                        'unitPrice' => '1000',
                        'quantity' => 1,
                        'unitTax' => 21.0,
                        'category' => 'CAT_A',
                    ], [
                        'sku' => 'SKU_CODE_002',
                        'displayName' => 'Cart product 002',
                        'unitPrice' => '500',
                        'quantity' => 1,
                        'unitTax' => 21.0,
                        'category' => 'CAT_B',
                    ],
                ]
            ]);
            $response = $client->requestNewLoan($request);

            if (true === $response->getIsSuccess()) {
                return $this->ajaxRespondJson([
                    'isSuccess' => true,
                    'invId' => $response->getSuccess()->getInvId(),
                    'href' => $response->getSuccess()->getHref()
                ]);
            }
            return $this->ajaxRespondJson([
                'isSuccess' => false,
                'error' => $response->getError()->getError(),
                'message' => $response->getError()->getMessage()
            ]);
        } catch (\Throwable $exception) {
            return $this->ajaxRespondJson([
                'isSuccess' => false,
                'error' => $exception->getCode(),
                'message' => $exception->getMessage()
            ]);
        }

    }

    public function isNotConfiguredAdminNotice() {
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

    public function addAdminMenu() {
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
            true,
            ['{{saveSettingsFormAction}}' => 'saveSettings']
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

    public function addWoocommercePaymentGateway()
    {
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
