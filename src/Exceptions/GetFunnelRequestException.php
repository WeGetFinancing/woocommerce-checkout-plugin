<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Exception;

use Exception;

class GetFunnelRequestException extends Exception
{
    public const GET_POST_REQUEST_ERROR_CODE = 1;
    public const GET_POST_REQUEST_ERROR_MESSAGE = 'Error taking data from post request.';
    public const GRACEFUL_ERROR_MESSAGE = 'Unexpected request error';
}
