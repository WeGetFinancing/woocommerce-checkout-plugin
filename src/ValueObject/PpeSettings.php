<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\ValueObject;

if (!defined( 'ABSPATH' )) exit;

interface PpeSettings
{
    public const NOT_CONFIGURED_LABEL =
        'WeGetFinancing Checkout is not configured, please configure it via WooCommerce > Settings > Payments.';
    public const IS_PPE_ACTIVE_ID = 'wgf_ppe_settings_is_ppe_active_id';
    public const IS_PPE_ACTIVE_NAME = 'If checked, PPE is Active';
    public const IS_PPE_ACTIVE_DEFAULT_VALUE = true;
    public const PRICE_SELECTOR_ID = 'wgf_ppe_settings_selector_id';
    public const PRICE_SELECTOR_NAME = 'Price Selector';
    public const PRICE_SELECTOR_LABEL = 'Class to find the prices on the page';
    public const PRICE_SELECTOR_DEFAULT_VALUE = '.price';
    public const PRODUCT_NAME_SELECTOR_ID = 'wgf_ppe_settings_name_selector_id';
    public const PRODUCT_NAME_SELECTOR_NAME = 'Product Name Selector';
    public const PRODUCT_NAME_SELECTOR_LABEL =
        'The selector of the product name. If applyNow is set to true, this selector is required.';
    public const PRODUCT_NAME_SELECTOR_DEFAULT_VALUE = '.woocommerce-loop-product__title';
    public const IS_DEBUG_ID = 'wgf_ppe_settings_is_debug_id';
    public const IS_DEBUG_NAME = 'Debug';
    public const IS_DEBUG_DEFAULT_VALUE = true;
    public const MERCHANT_TOKEN_ID = 'wgf_ppe_settings_merchant_token_id';
    public const MERCHANT_TOKEN_NAME = 'Token ID';
    public const MERCHANT_TOKEN_LABEL = "Merchants need to add their WeGetFinancing's access token";
    public const MERCHANT_TOKEN_DEFAULT_VALUE = '';
    public const IS_APPLY_NOW_ID = 'wgf_ppe_settings_is_apply_now_id';
    public const IS_APPLY_NOW_NAME = 'Apply Now';
    public const IS_APPLY_NOW_DEFAULT_VALUE = false;
    public const IS_BRANDED_ID = 'wgf_ppe_settings_is_branded_id';
    public const IS_BRANDED_NAME = 'Branded';
    public const IS_BRANDED_DEFAULT_VALUE = true;
    public const MINIMUM_AMOUNT_ID = 'wgf_ppe_settings_minimum_amount_id';
    public const MINIMUM_AMOUNT_NAME = 'Minimum Amount';
    public const MINIMUM_AMOUNT_LABEL = 'We will not show the PPE if the amount is below this number.';
    public const MINIMUM_AMOUNT_DEFAULT_VALUE = 1000;
    public const CUSTOM_TEXT_ID = 'wgf_ppe_settings_custom_text_id';
    public const CUSTOM_TEXT_NAME = 'Custom Text';
    public const CUSTOM_TEXT_LABEL = 'Add any text before the monthly payment in the message under the price.';
    public const CUSTOM_TEXT_DEFAULT_VALUE = 'or just';
    public const IS_HOVER_ID = 'wgf_ppe_settings_is_hover_id';
    public const IS_HOVER_NAME = 'Hover';
    public const IS_HOVER_DEFAULT_VALUE = true;
    public const FONT_SIZE_ID = 'wgf_ppe_settings_font_size_id';
    public const FONT_SIZE_NAME = 'Font Size';
    public const FONT_SIZE_LABEL = 'The font size described in percentage.';
    public const FONT_SIZE_DEFAULT_VALUE = '90';
    public const POSITION_ID = 'wgf_ppe_settings_position_id';
    public const POSITION_NAME = 'Position';
    public const POSITION_LABEL =
        "Positions the widget horizontally, the available options are: 'flex-start' | 'center' | 'flex-end'.";
    public const POSITION_DEFAULT_VALUE = 'center';
    public const VALID_POSITIONS = [
        'flex-start',
        'center',
        'flex-end',
    ];
    public const SAVE_BUTTON_ID = 'wgf_ppe_setting_save_button';
    public const PPE_IS_CONFIGURED = "ppe_is_configured";
    public const DATA_SUCCESSFULLY_SAVED_MESSAGE = 'Data successfully saved.';
    public const UNEXPECTED_SERVER_ERROR_MESSAGE = 'Unexpected server error, please contact our tech support.';
}
