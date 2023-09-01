<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Exception;

if (!defined( 'ABSPATH' )) exit;

use WeGetFinancing\SDK\Exception\EntityValidationException;

class WpEntityValidationException extends EntityValidationException
{
    public const DATA_NOT_SET_CODE = 1;
    public const DATA_NOT_SET_MESSAGE = "Invalid generate funnel request, data is not set.";
}
