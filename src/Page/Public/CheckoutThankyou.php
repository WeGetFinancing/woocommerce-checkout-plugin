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
                'oder_status_pending' => "pending",
                'order_status_on_hold' => "on-hold",
                'order_status_processing' => "processing",
                'order_status_completed' => "completed",
                'order_status_cancelled' => "cancelled",
                'order_status_refunded' => "refunded",
                'order_status_failed' => "failed",
            ]
        );
    }
}
