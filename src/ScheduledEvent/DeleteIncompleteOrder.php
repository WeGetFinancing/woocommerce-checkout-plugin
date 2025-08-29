<?php

namespace WeGetFinancing\Checkout\ScheduledEvent;

use WC_Order;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Exception\PostbackUpdateException;


class DeleteIncompleteOrder implements ActionableInterface
{
    public const INIT_NAME = 'woocommerce_admin_order_data_after_order_details';
    public const FUNCTION_NAME = 'execute';

    public function init(): void
    {
        $this->addAction();
    }

    public function execute(int $orderId)
    {
        $order = wc_get_order($orderId);
        if (false === $order instanceof WC_Order) {
            throw new PostbackUpdateException(
                PostbackUpdateException::INVALID_POST_ID_ERROR_MESSAGE . $orderId,
                PostbackUpdateException::INVALID_POST_ID_ERROR_CODE
            );
        }
        $order->update_status(STATUS_ABORTED, 'Order was not completed in time');
    }
}