<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PaymentGateway;

if (!defined( 'ABSPATH' )) exit;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use WeGetFinancing\Checkout\Ajax\Public\GenerateFunnelUrl;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\PostMeta\OrderInvIdValueObject;
use WeGetFinancing\Checkout\Repository\GetOptionRepositoryTrait;
use WeGetFinancing\Checkout\ValueObject\GenerateFunnelUrlRequest;

final class WeGetFinancingBlockSupport extends AbstractPaymentMethodType
{
    use GetOptionRepositoryTrait;
    public const HANDLE = 'wc-wegetfinancing-blocks-integration';

    private mixed $gateway;

    protected $name = WeGetFinancing::GATEWAY_ID;
    
    public function initialize(): void
    {
        // get payment gateway settings
        $this->settings = self::getOptions();

        // you can also initialize your payment gateway here
        // $gateways = WC()->payment_gateways->payment_gateways();
        // $this->gateway  = $gateways[ $this->name ];
    }

    public function is_active(): bool
    {
        return ! empty( $this->settings[ 'enabled' ] ) && 'yes' === $this->settings[ 'enabled' ];
    }

    public function get_payment_method_script_handles(): array
    {
        wp_register_script(
            self::HANDLE,
            plugin_dir_url( __DIR__ ) . 'dist/build/index.js',
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
            'description' => WeGetFinancing::DESCRIPTION,
            'payment_method_id' => WeGetFinancing::GATEWAY_ID,
            'checkout_button_image_url' => $GLOBALS[App::ID][App::CHECKOUT_BUTTON_URL],
            'checkout_button_alt' => WeGetFinancingValueObject::CHECKOUT_BUTTON_ALT,
            'ajax_url' => admin_url('admin-ajax.php'),
            'ajax_action' => GenerateFunnelUrl::ACTION_NAME,
            'order_inv_id_field_id' => OrderInvIdValueObject::ORDER_INV_ID_FIELD_ID,
            'error_display_method' => self::getOption(WeGetFinancingValueObject::ERROR_ATTACH_FIELD_ID),
            'error_display_selector' => htmlspecialchars_decode(
                self::getOption(WeGetFinancingValueObject::ERROR_SELECTOR_FIELD_ID),
            ),
            'nonce' => wp_create_nonce(WeGetFinancingValueObject::NONCE)
        ];
    }

    protected static function getOptionsName(): string
    {
        return WeGetFinancing::PREFIX . App::ID . WeGetFinancing::SUFFIX;
    }
}