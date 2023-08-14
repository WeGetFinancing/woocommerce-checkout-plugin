<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Exception;

if (!defined( 'ABSPATH' )) exit;

use WeGetFinancing\SDK\Exception\EntityValidationException;

class OnOrderStatusChangeToShippedException extends EntityValidationException
{
    public const REMOTE_ERROR_CODE = 1;
    public const REMOTE_ERROR_MESSAGE = 'OnOrderStatusChangeToShipped Remote error: ';
}
