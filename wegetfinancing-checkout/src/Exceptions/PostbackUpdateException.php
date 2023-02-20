<?php

namespace WeGetFinancing\Checkout\Exception;

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

}
