<?php

namespace WeGetFinancing\Checkout\ValueObject;

interface GenerateFunnelUrlRequest
{
    public const BILLING_FIRST_NAME_ID = 'billing_first_name';
    public const BILLING_LAST_NAME_ID = 'billing_last_name';
    public const BILLING_ADDRESS_1_ID = 'billing_address_1';
    public const BILLING_ADDRESS_2_ID = 'billing_address_2';
    public const BILLING_CITY_ID = 'billing_city';
    public const BILLING_STATE_ID = 'billing_state';
    public const BILLING_POSTCODE_ID = 'billing_postcode';
    public const BILLING_PHONE_ID = 'billing_phone';
    public const BILLING_EMAIL_ID = 'billing_email';
}
