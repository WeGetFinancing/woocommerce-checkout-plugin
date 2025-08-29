<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\ValueObject\PostMeta;

if (!defined( 'ABSPATH' )) exit;

class FieldVO
{
    public const TEXT_TYPE = 'text';
    public const HIDDEN_TYPE = 'hidden';
    public const TEXTAREA_TYPE = 'textarea';
    public const SELECT_TYPE = 'select';
    public const CHECKBOX_TYPE = 'checkbox';
    public const RADIO_TYPE = 'radio';
}