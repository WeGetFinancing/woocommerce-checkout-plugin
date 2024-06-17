<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Ajax\Public;

if (!defined( 'ABSPATH' )) exit;

use Throwable;
use WeGetFinancing\Checkout\AbstractActionableWithClient;
use WeGetFinancing\Checkout\Service\Logger;
use WeGetFinancing\Checkout\Service\RequestValidatorUtility;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class DeleteOrderByOrderId extends AbstractActionableWithClient
{
    use AddableTrait;

    public const ACTION_NAME = 'deleteOrderByOrderIdAction';
    public const INIT_NAME = 'wp_ajax_nopriv_' . self::ACTION_NAME;
    public const FUNCTION_NAME = 'execute';
    public const NONCE = 'wgf-delete-order-by-order-id';
    public const ORDER_ID_FIELD = 'order_id';

    public function init(): void
    {
        $this->addAction();
    }

    public function execute(): void
    {
        try {
            check_ajax_referer(self::NONCE);

            if (true === array_key_exists(self::ORDER_ID_FIELD, $_POST) &&
                false === empty($_POST[self::ORDER_ID_FIELD])) {
                $delete = wp_delete_post($_POST[self::ORDER_ID_FIELD]);
                if (null === $delete || false === $delete) {
                    Logger::log(new \Exception(
                        self::class . " unsuccessfully deletion.",
                        1
                    ));
                    wp_send_json([], 404);
                }
                wp_send_json([], 200);
            }
            wp_send_json([], 400);
        } catch (Throwable $exception) {
            Logger::log($exception);
            wp_send_json([], 500);
        }
    }
}
