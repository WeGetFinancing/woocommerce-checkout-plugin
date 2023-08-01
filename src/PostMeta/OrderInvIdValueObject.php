<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PostMeta;

if (!defined( 'ABSPATH' )) exit;

class OrderInvIdValueObject
{
    public const EXTRA_FIELDS_ID = 'wegetfinancing_extra_fields';
    public const ORDER_INV_ID_FIELD_ID = 'wegetfinancing_order_inv_id';
    public const ORDER_META = "_" . self::ORDER_INV_ID_FIELD_ID;
    public const ORDER_INV_ID_FIELD_LABEL = 'WeGetFinancing Transaction ID';
    public const ORDER_INV_ID_FIELD_ADMIN_TITLE = 'WeGetFinancing';
    public const ORDER_INV_ID_FIELD_ADMIN_LABEL = 'Transaction ID';
}
