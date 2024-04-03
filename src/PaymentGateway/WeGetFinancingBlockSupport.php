<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PaymentGateway;

if (!defined( 'ABSPATH' )) exit;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use WeGetFinancing\Checkout\Ajax\Public\GenerateFunnelUrl;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\PostMeta\OrderInvIdValueObject;
use WeGetFinancing\Checkout\ValueObject\GenerateFunnelUrlRequest;

final class WeGetFinancingBlockSupport extends AbstractPaymentMethodType
{
    public const HANDLE = 'wc-wegetfinancing-blocks-integration';
    public const INIT_NAME = 'woocommerce_blocks_payment_method_type_registration';
    private mixed $gateway;
    protected $name = WeGetFinancing::GATEWAY_ID;

    public function initialize(): void
    {
        $this->settings = WeGetFinancing::getOptions();
    }

    public function is_active(): bool
    {
        return ! empty( $this->settings[ 'enabled' ] ) && 'yes' === $this->settings[ 'enabled' ];
    }

    public function get_payment_method_script_handles(): array
    {
        wp_enqueue_script(
            self::HANDLE,
            plugin_dir_url( dirname(__DIR__, 1) ) . 'build/index.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
            ],
            null,
            true
        );

        return [self::HANDLE];

    }

    public function get_payment_method_data(): array
    {
        return [
            'title' => WeGetFinancing::TITLE,
            'payment_method_id' => WeGetFinancing::GATEWAY_ID,
            'description' => WeGetFinancing::DESCRIPTION,
            'checkout_logo_image_url' => $GLOBALS[App::ID][App::CHECKOUT_LOGO_URL],
            'checkout_button_image_url' => $GLOBALS[App::ID][App::CHECKOUT_BUTTON_URL],
            'checkout_button_alt' => WeGetFinancingValueObject::CHECKOUT_BUTTON_ALT,
            'supports' => WeGetFinancing::SUPPORTS,
            'ajax_action' => GenerateFunnelUrl::ACTION_NAME,
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(WeGetFinancingValueObject::NONCE),
            'order_inv_id_field_id' => OrderInvIdValueObject::ORDER_INV_ID_FIELD_ID,

            GenerateFunnelUrlRequest::BILLING_FIRST_NAME_ID => GenerateFunnelUrlRequest::BILLING_FIRST_NAME_ID,
            GenerateFunnelUrlRequest::BILLING_LAST_NAME_ID => GenerateFunnelUrlRequest::BILLING_LAST_NAME_ID,
            GenerateFunnelUrlRequest::BILLING_COUNTRY_ID => GenerateFunnelUrlRequest::BILLING_COUNTRY_ID,
            GenerateFunnelUrlRequest::BILLING_ADDRESS_1_ID => GenerateFunnelUrlRequest::BILLING_ADDRESS_1_ID,
            GenerateFunnelUrlRequest::BILLING_ADDRESS_2_ID => GenerateFunnelUrlRequest::BILLING_ADDRESS_2_ID,
            GenerateFunnelUrlRequest::BILLING_CITY_ID => GenerateFunnelUrlRequest::BILLING_CITY_ID,
            GenerateFunnelUrlRequest::BILLING_STATE_ID => GenerateFunnelUrlRequest::BILLING_STATE_ID,
            GenerateFunnelUrlRequest::BILLING_POSTCODE_ID => GenerateFunnelUrlRequest::BILLING_POSTCODE_ID,
            GenerateFunnelUrlRequest::BILLING_PHONE_ID => GenerateFunnelUrlRequest::BILLING_PHONE_ID,
            GenerateFunnelUrlRequest::BILLING_EMAIL_ID => GenerateFunnelUrlRequest::BILLING_EMAIL_ID,

            GenerateFunnelUrlRequest::SHIPPING_DIFFERENT_ID => GenerateFunnelUrlRequest::SHIPPING_DIFFERENT_ID,

            GenerateFunnelUrlRequest::SHIPPING_FIRST_NAME_ID => GenerateFunnelUrlRequest::SHIPPING_FIRST_NAME_ID,
            GenerateFunnelUrlRequest::SHIPPING_LAST_NAME_ID => GenerateFunnelUrlRequest::SHIPPING_LAST_NAME_ID,
            GenerateFunnelUrlRequest::SHIPPING_COUNTRY_ID => GenerateFunnelUrlRequest::SHIPPING_COUNTRY_ID,
            GenerateFunnelUrlRequest::SHIPPING_ADDRESS_1_ID => GenerateFunnelUrlRequest::SHIPPING_ADDRESS_1_ID,
            GenerateFunnelUrlRequest::SHIPPING_ADDRESS_2_ID => GenerateFunnelUrlRequest::SHIPPING_ADDRESS_2_ID,
            GenerateFunnelUrlRequest::SHIPPING_CITY_ID => GenerateFunnelUrlRequest::SHIPPING_CITY_ID,
            GenerateFunnelUrlRequest::SHIPPING_STATE_ID => GenerateFunnelUrlRequest::SHIPPING_STATE_ID,
            GenerateFunnelUrlRequest::SHIPPING_POSTCODE_ID => GenerateFunnelUrlRequest::SHIPPING_POSTCODE_ID,
            GenerateFunnelUrlRequest::SHIPPING_PHONE_ID => GenerateFunnelUrlRequest::SHIPPING_PHONE_ID,
        ];
    }
}