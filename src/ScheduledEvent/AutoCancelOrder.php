<?php

namespace WeGetFinancing\Checkout\ScheduledEvent;

use Automattic\WooCommerce\Enums\OrderInternalStatus;
use WC_Order;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Exception\ScheduledEvent\AutoCompleteOrderException;
use WeGetFinancing\Checkout\Service\Logger;
use WeGetFinancing\Checkout\Wp\AddableTrait;


class AutoCancelOrder implements ActionableInterface
{
    use AddableTrait;

    public const INIT_NAME = 'wegetfinancing_auto_cancel_order';
    public const FUNCTION_NAME = 'execute';

    public function init(): void
    {
        $this->addAction();
    }

    public function execute(int $orderId): void
    {
        try {
            $order = wc_get_order($orderId);
            if (!($order instanceof WC_Order)) {
                throw new AutoCompleteOrderException(
                    sprintf(
                        AutoCompleteOrderException::ORDER_NOT_FOUND_MESSAGE,
                        (string) $orderId
                    ),
                    AutoCompleteOrderException::ORDER_NOT_FOUND_CODE
                );
            }

            $order->add_order_note("Called WGF AutoCancelOrder for status: " . $order->get_status(), false);

            if( OrderInternalStatus::PENDING === $order->get_status() ) {
                $order->update_status(
                    OrderInternalStatus::CANCELLED,
                    'Order auto-canceled by scheduled event due to expiration.'
                );
            }
        } catch (\Throwable $exception) {
            Logger::log($exception);
        }
    }
}