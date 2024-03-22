<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\ValueObject;

if (!defined( 'ABSPATH' )) exit;

interface GenerateFunnelUrlRequest
{
    public const BILLING_FIRST_NAME_ID = 'billing_first_name';
    public const BILLING_LAST_NAME_ID = 'billing_last_name';
    public const BILLING_COUNTRY_ID = 'billing_country';
    public const BILLING_ADDRESS_1_ID = 'billing_address_1';
    public const BILLING_ADDRESS_2_ID = 'billing_address_2';
    public const BILLING_CITY_ID = 'billing_city';
    public const BILLING_STATE_ID = 'billing_state';
    public const BILLING_POSTCODE_ID = 'billing_postcode';
    public const BILLING_PHONE_ID = 'billing_phone';
    public const BILLING_EMAIL_ID = 'billing_email';
    public const SHIPPING_DIFFERENT_ID = 'ship-to-different-address-checkbox';
    public const SHIPPING_FIRST_NAME_ID = 'shipping_first_name';
    public const SHIPPING_LAST_NAME_ID = 'shipping_last_name';
    public const SHIPPING_COUNTRY_ID = 'shipping_country';
    public const SHIPPING_ADDRESS_1_ID = 'shipping_address_1';
    public const SHIPPING_ADDRESS_2_ID = 'shipping_address_2';
    public const SHIPPING_CITY_ID = 'shipping_city';
    public const SHIPPING_STATE_ID = 'shipping_state';
    public const SHIPPING_POSTCODE_ID = 'shipping_postcode';
    public const SHIPPING_PHONE_ID = 'shipping_phone';
}
