<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Page\Public;

if (!defined( 'ABSPATH' )) exit;

use Automattic\WooCommerce\Enums\OrderInternalStatus;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Ajax\Public\GetOrderStatusByOrderId;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancing;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancingValueObject;

class CheckoutThankyou implements ActionableInterface
{
    public const INIT_NAME = 'woocommerce_thankyou';
    public const PAGE_TEMPLATE = 'store/checkout_thankyou.twig';

    public function __construct(
        protected Environment $twig,
    ) {
    }

    public function init(): void
    {
        add_action(self::INIT_NAME, [$this, 'execute']);
    }

    /**
     * @param mixed $order_id
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function execute(mixed $order_id): void
    {
        wp_enqueue_script(
            WeGetFinancingValueObject::HANDLE_FUNNEL_SCRIPT, $GLOBALS[App::ID][App::FUNNEL_JS],
            ['jquery'],
            null,
            true
        );

        echo $this->twig->render(
            self::PAGE_TEMPLATE,
            [
                'wgf_href' => get_post_meta($order_id, 'wgf_href', true),
                'order_id' => $order_id,
                'nonce' => wp_create_nonce(GetOrderStatusByOrderId::NONCE),
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_action' => GetOrderStatusByOrderId::ACTION_NAME,
                'oder_status_pending' => 'pending',
                'order_status_on_hold' => 'on-hold',
                'order_status_processing' => 'processing',
                'order_status_completed' => 'completed',
                'order_status_cancelled' => 'cancelled',
                'order_status_refunded' => 'refunded',
                'order_status_failed' => 'failed',
                'thank_you_main_selector' => WeGetFinancing::getOption(
                    WeGetFinancingValueObject::THANK_YOU_PAGE_MAIN_SELECTOR_FIELD_ID
                ),
                'thank_you_title_selector' => WeGetFinancing::getOption(
                    WeGetFinancingValueObject::THANK_YOU_PAGE_TITLE_SELECTOR_FIELD_ID
                ),
                'thank_you_notice_selector' => WeGetFinancing::getOption(
                    WeGetFinancingValueObject::THANK_YOU_PAGE_NOTICE_SELECTOR_FIELD_ID
                ),
                'thank_you_order_overview_selector' => WeGetFinancing::getOption(
                    WeGetFinancingValueObject::THANK_YOU_PAGE_ORDER_OVERVIEW_SELECTOR_FIELD_ID
                ),
                'thank_you_customer_details_selector' => WeGetFinancing::getOption(
                    WeGetFinancingValueObject::THANK_YOU_PAGE_CUSTOMER_DETAILS_SELECTOR_FIELD_ID
                ),
                'thank_you_order_details_selector' => WeGetFinancing::getOption(
                    WeGetFinancingValueObject::THANK_YOU_PAGE_ORDER_DETAILS_SELECTOR_FIELD_ID
                ),
                'thank_you_message_order_pending' => WeGetFinancing::getOption(
                    WeGetFinancingValueObject::THANK_YOU_MESSAGE_ORDER_PENDING_FIELD_ID
                ),
                'thank_you_message_order_on_hold' => WeGetFinancing::getOption(
                    WeGetFinancingValueObject::THANK_YOU_MESSAGE_ORDER_ON_HOLD_FIELD_ID
                ),
                'thank_you_message_order_processing' => WeGetFinancing::getOption(
                    WeGetFinancingValueObject::THANK_YOU_MESSAGE_ORDER_PROCESSING_FIELD_ID
                ),
                'thank_you_message_order_failed' => WeGetFinancing::getOption(
                    WeGetFinancingValueObject::THANK_YOU_MESSAGE_ORDER_FAILED_FIELD_ID
                ),
                'thank_you_message_order_error' => WeGetFinancing::getOption(
                    WeGetFinancingValueObject::THANK_YOU_MESSAGE_ORDER_ERROR_FIELD_ID
                ),
            ]
        );
    }
}
