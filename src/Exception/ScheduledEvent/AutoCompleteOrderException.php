<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Exception\ScheduledEvent;

if (!defined( 'ABSPATH' )) exit;

use Exception;

class AutoCompleteOrderException extends Exception
{
    public const ORDER_NOT_FOUND_CODE = 1;
    public const ORDER_NOT_FOUND_MESSAGE = 'Order not found with id: %s';
}