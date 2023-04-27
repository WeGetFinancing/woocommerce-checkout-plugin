<?php

namespace WeGetFinancing\Checkout\ValueObject;

interface PpeSettings
{
    public const PRICE_ELECTOR = 'ppe_selector';
    public const PRODUCT_NAME_SELECTOR = 'ppe_selector';
    public const IS_DEBUG = 'is_debug';
    public const MERCHANT_TOKEN = 'merchant_token';
    public const IS_APPLY_NOW = 'is_apply_now';
    public const IS_BRANDED = 'is_branded';
    public const MINIMUM_AMOUNT = 'minimum_amount';
    public const CUSTOM_TEXT = 'custom_text';
    public const POSITION = 'minimum_amount';
    public const VALID_POSITIONS = [
        'equalSpacing',
        'leading',
        'trailing',
        'center',
        'fill',
        'fillEvenly'
    ];

}
