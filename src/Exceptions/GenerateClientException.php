<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Exception;

if (!defined( 'ABSPATH' )) exit;

use Exception;

class GenerateClientException extends Exception
{
    public const GENERATE_CLIENT_VALIDATION_ERROR_CODE = 1;
    public const GENERATE_CLIENT_VALIDATION_ERROR_MESSAGE =
        'Impossible generate WeGetFinancing client with provided data.';
    public const GENERATE_CLIENT_UNEXPECTED_ERROR_CODE = 2;
    public const GENERATE_CLIENT_UNEXPECTED_ERROR_MESSAGE = 'Unexpected error connecting to WeGetFinancing network.';
    public const GRACEFUL_ERROR_MESSAGE = 'Unexpected network error';
}
