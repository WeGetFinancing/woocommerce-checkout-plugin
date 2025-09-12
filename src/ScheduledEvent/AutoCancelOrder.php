<?php

namespace WeGetFinancing\Checkout\ScheduledEvent;

use Automattic\WooCommerce\Enums\OrderInternalStatus;
use WC_Order;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Exception\ScheduledEvent\AutoCompleteOrderException;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancing;
use WeGetFinancing\Checkout\Service\Logger;
use WeGetFinancing\Checkout\ValueObject\PaymentGateway\WeGetFinancingVO;
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

            $pendingStatus = WeGetFinancing::getOptionOrDefault(
                WeGetFinancingVO::ORDER_PENDING_STATUS_FIELD_ID,
                WeGetFinancingVO::ORDER_PENDING_STATUS_FIELD_DEFAULT
            );
            if ($pendingStatus === $status) {
                wc_increase_stock_levels($order);
                $order->update_status(
                    OrderInternalStatus::CANCELLED,
                    'Expired holding period.'
                );
                return;
            }

            $order->add_order_note(
                sprintf(
                    "Automated Order Cancellation not executed because status different to %s",
                    (string) $pendingStatus
                )
            );
        } catch (\Throwable $exception) {
            Logger::log($exception);
        }
    }
}