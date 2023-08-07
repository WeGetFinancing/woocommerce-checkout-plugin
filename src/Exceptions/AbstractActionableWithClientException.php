<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Exception;

if (!defined( 'ABSPATH' )) exit;

use Exception;

class AbstractActionableWithClientException extends Exception
{
    public const VALIDATION_ERROR_CODE = 1;
    public const VALIDATION_ERROR_MESSAGE = 'Impossible generate WeGetFinancing client with provided data.';
    public const VALIDATION_JSON_CODE = 2;
    public const GENERATE_CLIENT_UNEXPECTED_ERROR_CODE = 3;
    public const GENERATE_CLIENT_UNEXPECTED_ERROR_MESSAGE = 'Unexpected error connecting to WeGetFinancing network.';
    public const GRACEFUL_ERROR_MESSAGE = 'Unexpected network error';
    public const REMOTE_ERROR_CODE = 3;
    public const REMOTE_ERROR_MESSAGE = 'Remote error requesting new loan url.';
    public const REMOTE_ERROR_REQUEST_CODE = 4;
}
