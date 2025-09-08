<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Exception\PostMeta;

if (!defined( 'ABSPATH' )) exit;

use Exception;

class SetWgfOptionsDefaultsException extends Exception
{
    public const ERROR_SET_DEFAULT_OPTION_CODE = 1;
    public const ERROR_SET_DEFAULT_OPTION_MESSAGE = 'Failed to set WGF option < %s > to < %s >.';

}