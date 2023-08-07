<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Exception;

if (!defined( 'ABSPATH' )) exit;

use Exception;

class PpeSettingsAjaxException extends Exception
{
    public const VALIDATE_REQUEST_UNEXPECTED_CODE = 1;
    public const VALIDATE_REQUEST_UNEXPECTED_MESSAGE = 'Validate Request unexpected error';
}
