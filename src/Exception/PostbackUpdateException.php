<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Exception;

if (!defined( 'ABSPATH' )) exit;

use Exception;

class PostbackUpdateException extends Exception
{
    public const EMPTY_BODY_ERROR_CODE = 1;
    public const EMPTY_BODY_ERROR_MESSAGE = 'Request with empty body.';
    public const JSON_DECODE_ERROR_CODE = 2;
    public const INVALID_REQUEST_EMPTY_VERSION_ERROR_CODE = 3;
    public const INVALID_REQUEST_EMPTY_VERSION_ERROR_MESSAGE = 'Version field is empty.';
    public const INVALID_REQUEST_EMPTY_INV_ID_ERROR_CODE = 4;
    public const INVALID_REQUEST_EMPTY_INV_ID_ERROR_MESSAGE = 'InvId - request_token field is empty.';
    public const INVALID_REQUEST_EMPTY_UPDATES_ERROR_CODE = 5;
    public const INVALID_REQUEST_EMPTY_UPDATES_ERROR_MESSAGE = 'Updates field is empty.';
    public const INVALID_REQUEST_EMPTY_STATUS_ERROR_CODE = 6;
    public const INVALID_REQUEST_EMPTY_STATUS_ERROR_MESSAGE = 'Status field is empty.';
    public const INVALID_REQUEST_INVALID_STATUS_ERROR_CODE = 7;
    public const INVALID_REQUEST_INVALID_STATUS_ERROR_MESSAGE = 'Status field is invalid.';
    public const INVALID_REQUEST_EMPTY_TRANSACTION_ID_ERROR_CODE = 8;
    public const INVALID_REQUEST_EMPTY_TRANSACTION_ID_ERROR_MESSAGE = 'Transaction Id field is empty.';
    public const INVALID_SQL_RESULT_ERROR_CODE = 9;
    public const INVALID_SQL_RESULT_ERROR_MESSAGE = "The query to find the order by invId returned an invalid result.";
    public const ORDER_NOT_FOUND_ERROR_CODE = 10;
    public const ORDER_NOT_FOUND_ERROR_MESSAGE = "Order not found for invId: ";
    public const MULTIPLE_ORDERS_FOUND_ERROR_CODE = 11;
    public const MULTIPLE_ORDERS_FOUND_ERROR_MESSAGE = "Multiple orders found for invId. ";
    public const INVALID_RESULT_ORDER_ERROR_CODE = 12;
    public const INVALID_RESULT_ORDER_ERROR_MESSAGE =
        "The order found is invalid, please check the database integrity. InvId: ";
    public const INVALID_POST_ID_ERROR_CODE = 13;
    public const INVALID_POST_ID_ERROR_MESSAGE = "The post id found is invalid, id: ";
    public const INVALID_REFUND_REQUEST_EMPTY_AMOUNT_ERROR_MESSAGE = 'Amount field is empty.';
    public const INVALID_REFUND_REQUEST_EMPTY_AMOUNT_ERROR_CODE = 14;
}
