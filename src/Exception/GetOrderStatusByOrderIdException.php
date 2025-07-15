<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Exception;

if (!defined( 'ABSPATH' )) exit;

use Exception;

class GetOrderStatusByOrderIdException extends Exception
{
    public const REQUEST_ORDER_ID_EMPTY_ERROR_CODE = 1;
    public const REQUEST_ORDER_ID_EMPTY_ERROR_MESSAGE = 'Error: Invalid request with empty order id.';
    public const ORDER_NOT_FOUND_FOR_ORDER_ID_ERROR_CODE = 2;
    public const ORDER_NOT_FOUND_FOR_ORDER_ID_ERROR_MESSAGE = 'Error: Order not found for order id: ';
}
