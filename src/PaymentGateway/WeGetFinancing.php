<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PaymentGateway;

if (!defined( 'ABSPATH' )) exit;

use Automattic\WooCommerce\Enums\OrderInternalStatus;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WC_Order;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Ajax\Public\GenerateFunnelUrl;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\Exception\PaymentGateway\WeGetFinancingException;
use WeGetFinancing\Checkout\Repository\GetOptionRepositoryTrait;
use WeGetFinancing\Checkout\ScheduledEvent\AutoCancelOrder;
use WeGetFinancing\Checkout\Service\Logger;
use WeGetFinancing\Checkout\ValueObject\FieldVO;
use WeGetFinancing\Checkout\ValueObject\GenerateFunnelUrlRequest;
use WeGetFinancing\Checkout\ValueObject\PaymentGateway\WeGetFinancingVO;
use WeGetFinancing\Checkout\ValueObject\PostMeta\OrderInvIdFieldVO;
use WeGetFinancing\Checkout\ValueObject\PostMeta\OrderIsWgfFieldVO;
use WeGetFinancing\Checkout\ValueObject\PostMeta\OrderWgfHrefFieldVO;
use WeGetFinancing\Checkout\ValueObject\YesNoVO;
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

        $this->{WeGetFinancingVO::IS_SANDBOX_FIELD_ID} =
            $this->get_option(WeGetFinancingVO::IS_SANDBOX_FIELD_ID, true);
        $this->{WeGetFinancingVO::USERNAME_FIELD_ID} =
            $this->get_option(WeGetFinancingVO::USERNAME_FIELD_ID);
        $this->{WeGetFinancingVO::PASSWORD_FIELD_ID} =
            $this->get_option(WeGetFinancingVO::PASSWORD_FIELD_ID);
        $this->{WeGetFinancingVO::MERCHANT_ID_FIELD_ID} =
            $this->get_option(WeGetFinancingVO::MERCHANT_ID_FIELD_ID);

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

    public function init_form_fields(): void
    {
        $this->form_fields = apply_filters(
            WeGetFinancingVO::FIELDSET_ID,
            [
                WeGetFinancingVO::IS_SANDBOX_FIELD_ID => [
                    'title' => WeGetFinancingVO::IS_SANDBOX_FIELD_TITLE,
                    'type' => WeGetFinancingVO::IS_SANDBOX_FIELD_TYPE,
                    'label' => WeGetFinancingVO::IS_SANDBOX_FIELD_LABEL,
                    'default' => WeGetFinancingVO::IS_SANDBOX_FIELD_DEFAULT,
                ],
                WeGetFinancingVO::IS_SENTRY_FIELD_ID => [
                    'title' => WeGetFinancingVO::IS_SENTRY_FIELD_TITLE,
                    'type' => WeGetFinancingVO::IS_SENTRY_FIELD_TYPE,
                    'label' => WeGetFinancingVO::IS_SENTRY_FIELD_LABEL,
                    'default' => WeGetFinancingVO::IS_SENTRY_FIELD_DEFAULT,
                ],
                WeGetFinancingVO::USERNAME_FIELD_ID => [
                    'title' => WeGetFinancingVO::USERNAME_FIELD_TITLE,
                    'type' => WeGetFinancingVO::USERNAME_FIELD_TYPE,
                    'description' => WeGetFinancingVO::USERNAME_FIELD_LABEL,
                    'default' => WeGetFinancingVO::USERNAME_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::PASSWORD_FIELD_ID => [
                    'title' => WeGetFinancingVO::PASSWORD_FIELD_TITLE,
                    'type' => WeGetFinancingVO::PASSWORD_FIELD_TYPE,
                    'description' => WeGetFinancingVO::PASSWORD_FIELD_LABEL,
                    'default' => WeGetFinancingVO::PASSWORD_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::MERCHANT_ID_FIELD_ID => [
                    'title' => WeGetFinancingVO::MERCHANT_ID_FIELD_TITLE,
                    'type' => WeGetFinancingVO::MERCHANT_ID_FIELD_TYPE,
                    'description' => WeGetFinancingVO::MERCHANT_ID_FIELD_LABEL,
                    'default' => WeGetFinancingVO::MERCHANT_ID_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::IS_ORDER_AUTO_COMPLETE_FIELD_ID => [
                    'title' => WeGetFinancingVO::IS_ORDER_AUTO_COMPLETE_FIELD_TITLE,
                    'type' => WeGetFinancingVO::IS_ORDER_AUTO_COMPLETE_FIELD_TYPE,
                    'label' => WeGetFinancingVO::IS_ORDER_AUTO_COMPLETE_FIELD_LABEL,
                    'default' => WeGetFinancingVO::IS_ORDER_AUTO_COMPLETE_FIELD_DEFAULT,
                ],
                WeGetFinancingVO::ORDER_HOLD_PERIOD_FIELD_ID => [
                    'title' => WeGetFinancingVO::ORDER_HOLD_PERIOD_FIELD_TITLE,
                    'type' => WeGetFinancingVO::ORDER_HOLD_PERIOD_FIELD_TYPE,
                    'description' => WeGetFinancingVO::ORDER_HOLD_PERIOD_FIELD_LABEL,
                    'default' => WeGetFinancingVO::ORDER_HOLD_PERIOD_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::IS_RESTOCK_ON_REFUND_FIELD_ID => [
                    'title' => WeGetFinancingVO::IS_RESTOCK_ON_REFUND_FIELD_TITLE,
                    'type' => WeGetFinancingVO::IS_RESTOCK_ON_REFUND_FIELD_TYPE,
                    'label' => WeGetFinancingVO::IS_RESTOCK_ON_REFUND_FIELD_LABEL,
                    'default' => WeGetFinancingVO::IS_RESTOCK_ON_REFUND_DEFAULT,
                ],
                WeGetFinancingVO::ORDER_PENDING_STATUS_FIELD_ID => [
                    'title' => WeGetFinancingVO::ORDER_PENDING_STATUS_FIELD_TITLE,
                    'type' => WeGetFinancingVO::ORDER_PENDING_STATUS_FIELD_TYPE,
                    'description' => WeGetFinancingVO::ORDER_PENDING_STATUS_FIELD_LABEL,
                    'default' => WeGetFinancingVO::ORDER_PENDING_STATUS_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::ERROR_SELECTOR_FIELD_ID => [
                    'title' => WeGetFinancingVO::ERROR_SELECTOR_FIELD_TITLE,
                    'type' => WeGetFinancingVO::ERROR_SELECTOR_FIELD_TYPE,
                    'description' => WeGetFinancingVO::ERROR_SELECTOR_FIELD_LABEL,
                    'default' => WeGetFinancingVO::ERROR_SELECTOR_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::ERROR_ATTACH_FIELD_ID => [
                    'title' => WeGetFinancingVO::ERROR_ATTACH_FIELD_TITLE,
                    'type' => WeGetFinancingVO::ERROR_ATTACH_FIELD_TYPE,
                    'description' => WeGetFinancingVO::ERROR_ATTACH_FIELD_LABEL,
                    'default' => WeGetFinancingVO::ERROR_ATTACH_FIELD_DEFAULT,
                    'options' => WeGetFinancingVO::ERROR_ATTACH_FIELD_VALUES,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::THANK_YOU_PAGE_MAIN_SELECTOR_FIELD_ID => [
                    'title' => WeGetFinancingVO::THANK_YOU_PAGE_MAIN_SELECTOR_FIELD_TITLE,
                    'type' => WeGetFinancingVO::THANK_YOU_PAGE_MAIN_SELECTOR_FIELD_TYPE,
                    'description' => WeGetFinancingVO::THANK_YOU_PAGE_MAIN_SELECTOR_FIELD_LABEL,
                    'default' => WeGetFinancingVO::THANK_YOU_PAGE_MAIN_SELECTOR_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::THANK_YOU_PAGE_TITLE_SELECTOR_FIELD_ID => [
                    'title' => WeGetFinancingVO::THANK_YOU_PAGE_TITLE_SELECTOR_FIELD_TITLE,
                    'type' => WeGetFinancingVO::THANK_YOU_PAGE_TITLE_SELECTOR_FIELD_TYPE,
                    'description' => WeGetFinancingVO::THANK_YOU_PAGE_TITLE_SELECTOR_FIELD_LABEL,
                    'default' => WeGetFinancingVO::THANK_YOU_PAGE_TITLE_SELECTOR_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::THANK_YOU_PAGE_NOTICE_SELECTOR_FIELD_ID => [
                    'title' => WeGetFinancingVO::THANK_YOU_PAGE_NOTICE_SELECTOR_FIELD_TITLE,
                    'type' => WeGetFinancingVO::THANK_YOU_PAGE_NOTICE_SELECTOR_FIELD_TYPE,
                    'description' => WeGetFinancingVO::THANK_YOU_PAGE_NOTICE_SELECTOR_FIELD_LABEL,
                    'default' => WeGetFinancingVO::THANK_YOU_PAGE_NOTICE_SELECTOR_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::THANK_YOU_PAGE_ORDER_OVERVIEW_SELECTOR_FIELD_ID => [
                    'title' => WeGetFinancingVO::THANK_YOU_PAGE_ORDER_OVERVIEW_SELECTOR_FIELD_TITLE,
                    'type' => WeGetFinancingVO::THANK_YOU_PAGE_ORDER_OVERVIEW_SELECTOR_FIELD_TYPE,
                    'description' => WeGetFinancingVO::THANK_YOU_PAGE_ORDER_OVERVIEW_SELECTOR_FIELD_LABEL,
                    'default' => WeGetFinancingVO::THANK_YOU_PAGE_ORDER_OVERVIEW_SELECTOR_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::THANK_YOU_PAGE_CUSTOMER_DETAILS_SELECTOR_FIELD_ID => [
                    'title' => WeGetFinancingVO::THANK_YOU_PAGE_CUSTOMER_DETAILS_SELECTOR_FIELD_TITLE,
                    'type' => WeGetFinancingVO::THANK_YOU_PAGE_CUSTOMER_DETAILS_SELECTOR_FIELD_TYPE,
                    'description' => WeGetFinancingVO::THANK_YOU_PAGE_CUSTOMER_DETAILS_SELECTOR_FIELD_LABEL,
                    'default' => WeGetFinancingVO::THANK_YOU_PAGE_CUSTOMER_DETAILS_SELECTOR_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::THANK_YOU_PAGE_ORDER_DETAILS_SELECTOR_FIELD_ID => [
                    'title' => WeGetFinancingVO::THANK_YOU_PAGE_ORDER_DETAILS_SELECTOR_FIELD_TITLE,
                    'type' => WeGetFinancingVO::THANK_YOU_PAGE_ORDER_DETAILS_SELECTOR_FIELD_TYPE,
                    'description' => WeGetFinancingVO::THANK_YOU_PAGE_ORDER_DETAILS_SELECTOR_FIELD_LABEL,
                    'default' => WeGetFinancingVO::THANK_YOU_PAGE_ORDER_DETAILS_SELECTOR_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_PENDING_FIELD_ID => [
                    'title' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_PENDING_FIELD_TITLE,
                    'type' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_PENDING_FIELD_TYPE,
                    'description' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_PENDING_FIELD_LABEL,
                    'default' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_PENDING_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_ON_HOLD_FIELD_ID => [
                    'title' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_ON_HOLD_FIELD_TITLE,
                    'type' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_ON_HOLD_FIELD_TYPE,
                    'description' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_ON_HOLD_FIELD_LABEL,
                    'default' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_ON_HOLD_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_PROCESSING_FIELD_ID => [
                    'title' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_PROCESSING_FIELD_TITLE,
                    'type' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_PROCESSING_FIELD_TYPE,
                    'description' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_PROCESSING_FIELD_LABEL,
                    'default' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_PROCESSING_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_FAILED_FIELD_ID => [
                    'title' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_FAILED_FIELD_TITLE,
                    'type' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_FAILED_FIELD_TYPE,
                    'description' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_FAILED_FIELD_LABEL,
                    'default' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_FAILED_FIELD_DEFAULT,
                    'desc_tip' => true,
                ],
                WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_ERROR_FIELD_ID => [
                    'title' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_ERROR_FIELD_TITLE,
                    'type' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_ERROR_FIELD_TYPE,
                    'description' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_ERROR_FIELD_LABEL,
                    'default' => WeGetFinancingVO::THANK_YOU_MESSAGE_ORDER_ERROR_FIELD_DEFAULT,
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
                GenerateFunnelUrlRequest::SHIPPING_FIRST_NAME_ID => GenerateFunnelUrlRequest::SHIPPING_FIRST_NAME_ID,
                GenerateFunnelUrlRequest::SHIPPING_LAST_NAME_ID => GenerateFunnelUrlRequest::SHIPPING_LAST_NAME_ID,
                GenerateFunnelUrlRequest::SHIPPING_COUNTRY_ID => GenerateFunnelUrlRequest::SHIPPING_COUNTRY_ID,
                GenerateFunnelUrlRequest::SHIPPING_ADDRESS_1_ID => GenerateFunnelUrlRequest::SHIPPING_ADDRESS_1_ID,
                GenerateFunnelUrlRequest::SHIPPING_ADDRESS_2_ID => GenerateFunnelUrlRequest::SHIPPING_ADDRESS_2_ID,
                GenerateFunnelUrlRequest::SHIPPING_CITY_ID => GenerateFunnelUrlRequest::SHIPPING_CITY_ID,
                GenerateFunnelUrlRequest::SHIPPING_STATE_ID => GenerateFunnelUrlRequest::SHIPPING_STATE_ID,
                GenerateFunnelUrlRequest::SHIPPING_POSTCODE_ID => GenerateFunnelUrlRequest::SHIPPING_POSTCODE_ID,
                GenerateFunnelUrlRequest::SHIPPING_PHONE_ID => GenerateFunnelUrlRequest::SHIPPING_PHONE_ID,
                'wgf_checkout_logo' => $GLOBALS[App::ID][App::CHECKOUT_LOGO_URL],
                'description' => $this->description,
                'payment_method_id' => $this->id,
                'checkout_button_image_url' => $GLOBALS[App::ID][App::CHECKOUT_BUTTON_URL],
                'checkout_button_alt' => WeGetFinancingVO::CHECKOUT_BUTTON_ALT,
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_action' => GenerateFunnelUrl::ACTION_NAME,
                'order_extra_field_type' => FieldVO::HIDDEN_TYPE,
                'order_inv_id_id' => OrderInvIdFieldVO::FIELD_ID,
                'order_inv_id_name' => OrderInvIdFieldVO::FIELD_NAME,
                'order_wgf_href_id' => OrderWgfHrefFieldVO::FIELD_ID,
                'order_wgf_href_name' => OrderWgfHrefFieldVO::FIELD_NAME,
                'order_is_wgf_id' => OrderIsWgfFieldVO::FIELD_ID,
                'order_is_wgf_name' => OrderIsWgfFieldVO::FIELD_NAME,
                'error_display_method' => self::getOption(WeGetFinancingVO::ERROR_ATTACH_FIELD_ID),
                'error_display_selector' => htmlspecialchars_decode(
                    self::getOption(WeGetFinancingVO::ERROR_SELECTOR_FIELD_ID),
                ),
                'nonce' => wp_create_nonce(WeGetFinancingVO::NONCE)
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
        try {
            $this->setOrderExtraFields($order_id);

            $order = wc_get_order($order_id);

            if (false === ($order instanceof WC_Order)) {
                throw new WeGetFinancingException(
                    sprintf(
                        WeGetFinancingException::PROCESS_PAYMENT_ORDER_NOT_FOUND_MESSAGE,
                        (string) $order_id
                    ),
                    WeGetFinancingException::PROCESS_PAYMENT_ORDER_NOT_FOUND_CODE,
                );
            }

            $order->update_status(OrderInternalStatus::PENDING);

            wc_reduce_stock_levels($order->get_id());

            WC()->cart->empty_cart();

            $isOrderAutoComplete = self::getOptionOrDefault(
                WeGetFinancingVO::IS_ORDER_AUTO_COMPLETE_FIELD_ID,
                WeGetFinancingVO::IS_ORDER_AUTO_COMPLETE_FIELD_DEFAULT
            );
            if (YesNoVO::YES_VALUE === $isOrderAutoComplete) {
                $holdOrderHours = (int) self::getOptionOrDefault(
                    WeGetFinancingVO::ORDER_HOLD_PERIOD_FIELD_ID,
                    WeGetFinancingVO::ORDER_HOLD_PERIOD_FIELD_DEFAULT
                );

                wp_schedule_single_event(
                    time() + $holdOrderHours * 60 * 60, // time is in seconds
                    AutoCancelOrder::INIT_NAME,
                    [ $order_id ],
                );

                $order->add_order_note(
                    sprintf(
                        "Pending Payment Order is scheduled to be retained for %s hours.",
                        (string) $holdOrderHours
                    )
                );
            }

            return [
                'result' => WeGetFinancingVO::PROCESS_PAYMENT_SUCCESS_ID,
                'redirect' => $this->get_return_url($order),
            ];
        } catch (\Throwable $exception) {
            Logger::log($exception);
            $message = sprintf(
                'We couldn’t complete your payment due to an unexpected error. ' .
                    'No charge was made. Please try again or choose a different payment method. ' .
                    'If the problem persists, contact support and reference order %s.',
                (string) $order_id
            );
            wc_add_notice($message, 'error');
            return [
                'result' => WeGetFinancingVO::PROCESS_PAYMENT_FAILURE_ID
            ];
        }
    }

    /**
     * @throws WeGetFinancingException
     */
    protected function setOrderExtraFields(int $orderId): void
    {
        $orderExtraFields = [
            [
                'name' => OrderInvIdFieldVO::FIELD_NAME,
                'meta' => OrderInvIdFieldVO::META,
            ],
            [
                'name' => OrderWgfHrefFieldVO::FIELD_NAME,
                'meta' => OrderWgfHrefFieldVO::META,
            ],
            [
                'name' => OrderIsWgfFieldVO::FIELD_NAME,
                'meta' => OrderIsWgfFieldVO::META,
            ],
        ];
        foreach ($orderExtraFields as $orderExtraField) {
            $value = $this->getSanitizedOrderExtraFieldValue($orderExtraField['name'], $orderId);
            $this->setOrderExtraFieldMeta($orderId, $orderExtraField['meta'], $value);
        }
    }

    /**
     * @throws WeGetFinancingException
     */
    protected function getSanitizedOrderExtraFieldValue(string $fieldName, int $orderId): string
    {
        if (false === array_key_exists($fieldName, $_POST)) {
            throw new WeGetFinancingException(
                sprintf(
                    WeGetFinancingException::ORDER_EXTRA_FIELD_NOT_SET_MESSAGE,
                    $fieldName,
                    (string) $orderId
                ),
                WeGetFinancingException::ORDER_EXTRA_FIELD_NOT_SET_CODE
            );
        }
        return sanitize_text_field($_POST[$fieldName]);
    }

    /**
     * @throws WeGetFinancingException
     */
    protected function setOrderExtraFieldMeta(int $orderId, string $meta, string $value): void
    {
        $result = update_post_meta($orderId, $meta, $value);
        if (false === $result) {
            throw new WeGetFinancingException(
                sprintf(
                    WeGetFinancingException::UPDATE_ORDER_EXTRA_FIELD_META_ERROR_MESSAGE,
                    $meta,
                    (string) $orderId
                ),
                WeGetFinancingException::UPDATE_ORDER_EXTRA_FIELD_META_ERROR_CODE
            );
        }
    }

    protected static function getOptionsName(): string
    {
        return self::PREFIX . App::ID . self::SUFFIX;
    }
}
