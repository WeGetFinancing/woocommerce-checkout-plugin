<?php

namespace WeGetFinancing\Checkout\Exception;

use Exception;

class GenerateFunnelClientException extends Exception
{
    public const GENERATE_CLIENT_VALIDATION_ERROR_CODE = 1;
    public const GENERATE_CLIENT_VALIDATION_ERROR_MESSAGE =
        'Impossible generate WeGetFinancing client with provided data.';
    public const GENERATE_CLIENT_UNEXPECTED_ERROR_CODE = 2;
    public const GENERATE_CLIENT_UNEXPECTED_ERROR_MESSAGE = 'Unexpected error connecting to WeGetFinancing network.';
    public const GRACEFUL_ERROR_MESSAGE = 'Unexpected network error';
}
