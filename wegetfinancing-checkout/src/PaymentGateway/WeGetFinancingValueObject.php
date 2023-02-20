<?php

namespace WeGetFinancing\Checkout\PaymentGateway;

class WeGetFinancingValueObject
{
    public const METHOD_TITLE = "WeGetFinancing";
    public const METHOD_DESCRIPTION = "Boost your sales by adding WeGetFinancing to your checkout. " .
        "Offer affordable monthly payments to your existing customers while you receive the money" .
        " directly into your account, in one lump sum.";
    public const TITLE = "WeGetFinancing";
    public const DESCRIPTION = "Pay monthly, obtain instant approval with no extensive paperwork." .
        " Get credit approval in just seconds, so you can complete your purchase immediately, hassle-free.";
    public const FIELDSET_ID = "wgf_form_fields";
    public const IS_SANDBOX_FIELD_ID = "wgf_is_sandbox";
    public const IS_SANDBOX_FIELD_TITLE = "Sandbox Environment";
    public const IS_SANDBOX_FIELD_LABEL = "Enable/Disable Sandbox Environment";
    public const USERNAME_FIELD_ID = "wgf_username";
    public const USERNAME_FIELD_TITLE = "Username";
    public const USERNAME_FIELD_LABEL = "WeGetFinancing Username";
    public const PASSWORD_FIELD_ID = "wgf_password";
    public const PASSWORD_FIELD_TITLE = "Password";
    public const PASSWORD_FIELD_LABEL = "WeGetFinancing Password";
    public const MERCHANT_ID_FIELD_ID = "wgf_merchant_id";
    public const MERCHANT_ID_FIELD_TITLE = "Merchant ID";
    public const MERCHANT_ID_FIELD_LABEL = "WeGetFinancing Merchant ID";
    public const ON_HOLD_STATUS_ID = "on-hold";
    public const ON_HOLD_STATUS_LABEL = "Awaiting WeGetFinancing payment";
    public const PROCESS_PAYMENT_SUCCESS_ID = "success";
    public const HANDLE_FUNNEL_SCRIPT = "wgf-checkout-funnel";
    public const CHECKOUT_BUTTON_ALT = "WeGetFinancing Checkout Button";
}
