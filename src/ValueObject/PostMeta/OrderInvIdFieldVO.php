<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\ValueObject\PostMeta;

if (!defined( 'ABSPATH' )) exit;

class OrderInvIdFieldVO
{
    public const FIELD_ID = 'wegetfinancing_order_inv_id';
    public const META = '_' . self::FIELD_ID;
    public const FIELD_NAME = 'inv_id';
    public const FIELD_LABEL = 'WeGetFinancing Transaction ID';
    public const ADMIN_TITLE = 'WeGetFinancing';
    public const ADMIN_LABEL = 'Transaction ID';
    public const DEFAULT_VALUE = 'not-defined';
}
