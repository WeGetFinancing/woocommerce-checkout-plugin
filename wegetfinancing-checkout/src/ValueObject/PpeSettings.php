<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\ValueObject;

interface PpeSettings
{
    public const PRICE_SELECTOR_ID = 'wgf_ppe_settings_selector_id';
    public const PRICE_SELECTOR_NAME = 'Price Selector';
    public const PRICE_SELECTOR_DEFAULT_VALUE = '.price';
    public const PRODUCT_NAME_SELECTOR_ID = 'wgf_ppe_settings_name_selector_id';
    public const PRODUCT_NAME_SELECTOR_NAME = 'Product Name Selector';
    public const PRODUCT_NAME_SELECTOR_DEFAULT_VALUE = '.woocommerce-loop-product__title';
    public const IS_DEBUG_ID = 'wgf_ppe_settings_is_debug_id';
    public const IS_DEBUG_NAME = 'Debug';
    public const IS_DEBUG_DEFAULT_VALUE = true;
    public const MERCHANT_TOKEN_ID = 'wgf_ppe_settings_merchant_token_id';
    public const MERCHANT_TOKEN_NAME = 'Token ID';
    public const MERCHANT_TOKEN_DEFAULT_VALUE = '';
    public const IS_APPLY_NOW_ID = 'wgf_ppe_settings_is_apply_now_id';
    public const IS_APPLY_NOW_NAME = 'Apply Now';
    public const IS_APPLY_NOW_DEFAULT_VALUE = true;
    public const IS_BRANDED_ID = 'wgf_ppe_settings_is_branded_id';
    public const IS_BRANDED_NAME = 'Branded';
    public const IS_BRANDED_DEFAULT_VALUE = true;
    public const MINIMUM_AMOUNT_ID = 'wgf_ppe_settings_minimum_amount_id';
    public const MINIMUM_AMOUNT_NAME = 'Minimum Amount';
    public const MINIMUM_AMOUNT_DEFAULT_VALUE = 1000;
    public const CUSTOM_TEXT_ID = 'wgf_ppe_settings_custom_text_id';
    public const CUSTOM_TEXT_NAME = 'Custom Text';
    public const CUSTOM_TEXT_DEFAULT_VALUE = 'or just';
    public const POSITION_ID = 'wgf_ppe_settings_position_id';
    public const POSITION_NAME = 'Position';
    public const POSITION_DEFAULT_VALUE = 'center';
    public const VALID_POSITIONS = [
        'equalSpacing',
        'leading',
        'trailing',
        'center',
        'fill',
        'fillEvenly',
    ];
    public const SAVE_BUTTON_ID = 'wgf_ppe_setting_save_button';
    public const PPE_IS_CONFIGURED = "ppe_is_configured";
}
