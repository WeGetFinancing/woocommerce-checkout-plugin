<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\ValueObject\PostMeta;

use WeGetFinancing\Checkout\ValueObject\YesNoVO;

if (!defined( 'ABSPATH' )) exit;

class OrderIsWgfFieldVO
{
    public const FIELD_ID = 'wegetfinancing_order_is_wgf';
    public const META = "_" . self::FIELD_ID;
    public const FIELD_NAME = 'order_is_wgf';
    public const TITLE = 'WeGetFinancing';
    public const LABEL = 'Is WeGetFinancing Transaction';

    public const DEFAULT_VALUE = YesNoVO::NO_VALUE;
}
