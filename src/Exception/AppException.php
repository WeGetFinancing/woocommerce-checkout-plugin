<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Exception;

if (!defined( 'ABSPATH' )) exit;

use Exception;

class AppException extends Exception
{
    public const CONSTRUCT_ERROR_CODE = 1;
    public const CONSTRUCT_ERROR_MESSAGE = "Unexpected App::__construct() error";
    public const INIT_ERROR_CODE = 2;
    public const INIT_ERROR_MESSAGE = 'Error initializing object: ';
}
