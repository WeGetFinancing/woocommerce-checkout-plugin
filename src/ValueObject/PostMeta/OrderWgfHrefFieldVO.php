<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\ValueObject\PostMeta;

if (!defined( 'ABSPATH' )) exit;

class OrderWgfHrefFieldVO
{
    public const FIELD_ID = 'wgf_href';
    public const META = self::FIELD_ID;
    public const FIELD_NAME = self::FIELD_ID;
    public const DEFAULT_VALUE = 'not-defined';
}
