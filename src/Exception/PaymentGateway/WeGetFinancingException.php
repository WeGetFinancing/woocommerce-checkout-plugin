<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Exception\PaymentGateway;

if (!defined( 'ABSPATH' )) exit;

use Exception;

class WeGetFinancingException extends Exception
{
    public const ORDER_EXTRA_FIELD_NOT_SET_CODE = 1;
    public const ORDER_EXTRA_FIELD_NOT_SET_MESSAGE =
        'Payment process error, order extra field < %s > is not set for order id: %s';
    public const UPDATE_ORDER_EXTRA_FIELD_META_ERROR_CODE = 2;
    public const UPDATE_ORDER_EXTRA_FIELD_META_ERROR_MESSAGE =
        'Payment process error updating order meta < %s > for order id: %s';

}
