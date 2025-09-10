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

            $status = $order->get_status();

            $order->add_order_note(
                sprintf(
                    "Executing Automated Order Cancellation for Order ID: %s with status: %s",
                    (string) $orderId,
                    (string) $status
                )
            );

            if (OrderInternalStatus::PENDING === "wc-" . $status) {
                wc_increase_stock_levels($order);
                $order->update_status(
                    OrderInternalStatus::CANCELLED,
                    'Expired holding period.'
                );
                return;
            }
        } catch (\Throwable $exception) {
            Logger::log($exception);
        }
    }
}