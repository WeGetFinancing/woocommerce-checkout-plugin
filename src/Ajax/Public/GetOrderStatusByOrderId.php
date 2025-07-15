<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Ajax\Public;

if (!defined( 'ABSPATH' )) exit;

use Throwable;
use WC_Order;
use WeGetFinancing\Checkout\AbstractActionableWithClient;
use WeGetFinancing\Checkout\Exception\GetOrderStatusByOrderIdException;
use WeGetFinancing\Checkout\Service\Logger;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class GetOrderStatusByOrderId extends AbstractActionableWithClient
{
    use AddableTrait;

    public const ACTION_NAME = 'getOrderStatusByOrderIdAction';
    public const INIT_NAME = 'wp_ajax_nopriv_' . self::ACTION_NAME;
    public const FUNCTION_NAME = 'execute';
    public const NONCE = 'wgf-get-order-status-by-order-id';
    public const ORDER_ID_FIELD = 'order_id';

    public function init(): void
    {
        $this->addAction();
    }

    public function execute(): void
    {
        try {
            check_ajax_referer(self::NONCE);

            $order = $this->getOrderFromPost();

            wp_send_json(
                [
                    "orderId" => $order->get_id(),
                    "orderStatus" => $order->get_status()
                ],
                200
            );
        } catch (GetOrderStatusByOrderIdException $exception) {
            Logger::log($exception);
            wp_send_json([], 400);
        } catch (Throwable $exception) {
            Logger::log($exception);
            wp_send_json([], 500);
        }
    }

    /**
     * @return WC_Order
     * @throws GetOrderStatusByOrderIdException
     */
    protected function getOrderFromPost(): WC_Order
    {
        if (true === array_key_exists(self::ORDER_ID_FIELD, $_POST) &&
            false === empty($_POST[self::ORDER_ID_FIELD])) {
            $orderId = sanitize_text_field($_POST[self::ORDER_ID_FIELD]);

            $order = wc_get_order($orderId);
            if (false === $order instanceof WC_Order) {
                throw new GetOrderStatusByOrderIdException(
                    GetOrderStatusByOrderIdException::ORDER_NOT_FOUND_FOR_ORDER_ID_ERROR_MESSAGE . $orderId,
                    GetOrderStatusByOrderIdException::ORDER_NOT_FOUND_FOR_ORDER_ID_ERROR_CODE
                );
            }

            return $order;
        }

        throw new GetOrderStatusByOrderIdException(
            GetOrderStatusByOrderIdException::REQUEST_ORDER_ID_EMPTY_ERROR_MESSAGE,
            GetOrderStatusByOrderIdException::REQUEST_ORDER_ID_EMPTY_ERROR_CODE
        );

    }
}
