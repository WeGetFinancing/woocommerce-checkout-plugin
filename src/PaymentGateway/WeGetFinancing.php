<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PaymentGateway;

if (!defined( 'ABSPATH' )) exit;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Ajax\Public\GenerateFunnelUrl;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\PostMeta\OrderInvIdValueObject;
use WeGetFinancing\Checkout\Repository\GetOptionRepositoryTrait;
use WeGetFinancing\Checkout\Service\Logger;
use WeGetFinancing\Checkout\ValueObject\GenerateFunnelUrlRequest;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class WeGetFinancing extends \WC_Payment_Gateway implements ActionableInterface
{
    use AddableTrait;
    use GetOptionRepositoryTrait;

    public const PREFIX = 'woocommerce_';
    public const SUFFIX = '_settings';
    public const GATEWAY_ID = "wegetfinancing";
    public const INIT_NAME = 'woocommerce_update_options_payment_gateways_';
    public const FUNCTION_NAME = 'process_admin_options';
    public const METHOD_TITLE = 'WeGetFinancing';
    public const METHOD_DESCRIPTION = 'Boost your sales by adding WeGetFinancing to your checkout. ' .
    'Offer affordable monthly payments to your existing customers while you receive the money ' .
    'directly into your account, in one lump sum.';
    public const TITLE = 'WeGetFinancing';
    public const DESCRIPTION = 'Purchase now and pay later with customized financing choices. ' .
        'All credit types are welcome. No hard inquiry needed.';
    public const SUPPORTS = ['products', 'refunds'];

    protected Environment $twig;

    public function __construct()
    {
        $this->twig = $GLOBALS[App::ID][App::RENDER];
        $this->id = static::GATEWAY_ID;
        $this->has_fields = false;
        $this->icon = '';
        $this->method_title = self::METHOD_TITLE;
        $this->method_description = self::METHOD_DESCRIPTION;
        $this->title = self::TITLE;
        $this->description = self::DESCRIPTION;
        $this->supports = self::SUPPORTS;

        $this->init_form_fields();
        $this->init_settings();

        $this->{WeGetFinancingValueObject::IS_SANDBOX_FIELD_ID} =
            $this->get_option(WeGetFinancingValueObject::IS_SANDBOX_FIELD_ID, true);
        $this->{WeGetFinancingValueObject::USERNAME_FIELD_ID} =
            $this->get_option(WeGetFinancingValueObject::USERNAME_FIELD_ID);
        $this->{WeGetFinancingValueObject::PASSWORD_FIELD_ID} =
            $this->get_option(WeGetFinancingValueObject::PASSWORD_FIELD_ID);
        $this->{WeGetFinancingValueObject::MERCHANT_ID_FIELD_ID} =
            $this->get_option(WeGetFinancingValueObject::MERCHANT_ID_FIELD_ID);

        $this->init();
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
            WeGetFinancingValueObject::FIELDSET_ID,
            [
                WeGetFinancingValueObject::IS_SANDBOX_FIELD_ID => [
                    'title' => WeGetFinancingValueObject::IS_SANDBOX_FIELD_TITLE,
                    'type' => 'checkbox',
                    'label' => WeGetFinancingValueObject::IS_SANDBOX_FIELD_LABEL,
                    'default' => 'yes',
                ],
                WeGetFinancingValueObject::IS_SENTRY_FIELD_ID => [
                    'title' => WeGetFinancingValueObject::IS_SENTRY_FIELD_TITLE,
                    'type' => 'checkbox',
                    'label' => WeGetFinancingValueObject::IS_SENTRY_FIELD_LABEL,
                    'default' => 'yes',
                ],
                WeGetFinancingValueObject::USERNAME_FIELD_ID => [
                    'title' => WeGetFinancingValueObject::USERNAME_FIELD_TITLE,
                    'type' => 'text',
                    'description' => WeGetFinancingValueObject::USERNAME_FIELD_LABEL,
                    'default' => '',
                    'desc_tip' => true,
                ],
                WeGetFinancingValueObject::PASSWORD_FIELD_ID => [
                    'title' => WeGetFinancingValueObject::PASSWORD_FIELD_TITLE,
                    'type' => 'password',
                    'description' => WeGetFinancingValueObject::PASSWORD_FIELD_LABEL,
                    'default' => '',
                    'desc_tip' => true,
                ],
                WeGetFinancingValueObject::MERCHANT_ID_FIELD_ID => [
                    'title' => WeGetFinancingValueObject::MERCHANT_ID_FIELD_TITLE,
                    'type' => 'text',
                    'description' => WeGetFinancingValueObject::MERCHANT_ID_FIELD_LABEL,
                    'default' => '',
                    'desc_tip' => true,
                ],
                WeGetFinancingValueObject::ERROR_SELECTOR_FIELD_ID => [
                    'title' => WeGetFinancingValueObject::ERROR_SELECTOR_FIELD_TITLE,
                    'type' => 'text',
                    'description' => WeGetFinancingValueObject::ERROR_SELECTOR_FIELD_LABEL,
                    'default' => WeGetFinancingValueObject::ERROR_SELECTOR_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingValueObject::ERROR_ATTACH_FIELD_ID => [
                    'title' => WeGetFinancingValueObject::ERROR_ATTACH_FIELD_TITLE,
                    'type' => 'select',
                    'description' => WeGetFinancingValueObject::ERROR_ATTACH_FIELD_LABEL,
                    'default' => WeGetFinancingValueObject::ERROR_ATTACH_FIELD_DEFAULT,
                    'options' => WeGetFinancingValueObject::ERROR_ATTACH_FIELD_VALUES,
                    'desc_tip' => true,
                ],
            ]
        );
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @return void
     */
    public function admin_options(): void
    {
        echo $this->twig->render(
            'admin/payment_settings.twig',
            ['form' => $this->generate_settings_html([], false)]
        );
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @return void
     */
    public function payment_fields(): void
    {
        echo $this->twig->render(
            'store/checkout_button.twig',
            [
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
                'description' => $this->description,
                'payment_method_id' => $this->id,
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
        $this->setOrderInvIdAndHref($order_id);

        $order = wc_get_order($order_id);

        $order->update_status(
            WeGetFinancingValueObject::ON_HOLD_STATUS_ID,
            WeGetFinancingValueObject::ON_HOLD_STATUS_LABEL
        );

        wc_reduce_stock_levels($order->get_id());

        WC()->cart->empty_cart();

        return [
            'result' => WeGetFinancingValueObject::PROCESS_PAYMENT_SUCCESS_ID,
            'redirect' => $this->get_return_url($order),
        ];
    }

    protected function setOrderInvIdAndHref(int $orderId): void
    {
        if (false === array_key_exists("inv_id", $_POST)) {
            Logger::log(new \Exception("Payment process error: Inv Id not set for order id " . $orderId));
            return;
        }
        $invId = sanitize_text_field($_POST["inv_id"]);
        $updateInvId = update_post_meta($orderId, OrderInvIdValueObject::ORDER_META, $invId);
        if (false === $updateInvId) {
            Logger::log(new \Exception(
                "Payment process error updating Inv Id post meta for order id " . $orderId . " - " . $invId
            ));
        }

        if (false === array_key_exists("wgf_href", $_POST)) {
            Logger::log(new \Exception("Payment process error: HREF not set for order id " . $orderId));
            return;
        }
        $href = sanitize_text_field($_POST["wgf_href"]);
        $updateHref = update_post_meta($orderId, "wgf_href", $href);
        if (false === $updateHref) {
            Logger::log(new \Exception(
                "Payment process error updating HREF post meta for order id " . $orderId . " - " . $href
            ));
        }
    }

    protected static function getOptionsName(): string
    {
        return self::PREFIX . App::ID . self::SUFFIX;
    }
}
