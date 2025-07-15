<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PaymentGateway;

use Automattic\WooCommerce\Enums\OrderInternalStatus;

if (!defined( 'ABSPATH' )) exit;

class WeGetFinancingValueObject
{
    public const FIELDSET_ID = "wgf_form_fields";
    public const IS_SANDBOX_FIELD_ID = "wgf_is_sandbox";
    public const IS_SANDBOX_FIELD_TITLE = "Sandbox Environment";
    public const IS_SANDBOX_FIELD_LABEL = "Enable/Disable Sandbox Environment";
    public const IS_SENTRY_FIELD_ID = "wgf_is_sentry";
    public const IS_SENTRY_FIELD_TITLE = "Use Sentry Log System";
    public const IS_SENTRY_FIELD_LABEL = "If enabled, each error other than logged into your wordpress will be sent ".
        "to our centralised log system.";
    public const USERNAME_FIELD_ID = "wgf_username";
    public const USERNAME_FIELD_TITLE = "Username";
    public const USERNAME_FIELD_LABEL = "WeGetFinancing Username";
    public const PASSWORD_FIELD_ID = "wgf_password";
    public const PASSWORD_FIELD_TITLE = "Password";
    public const PASSWORD_FIELD_LABEL = "WeGetFinancing Password";
    public const MERCHANT_ID_FIELD_ID = "wgf_merchant_id";
    public const MERCHANT_ID_FIELD_TITLE = "Merchant ID";
    public const MERCHANT_ID_FIELD_LABEL = "WeGetFinancing Merchant ID";
    public const ON_HOLD_STATUS_ID = OrderInternalStatus::ON_HOLD;
    public const ON_HOLD_STATUS_LABEL = "Awaiting WeGetFinancing payment";
    public const PROCESS_PAYMENT_SUCCESS_ID = "success";
    public const HANDLE_FUNNEL_SCRIPT = "wgf-checkout-funnel";
    public const CHECKOUT_BUTTON_ALT = "WeGetFinancing Checkout Button";
    public const ERROR_SELECTOR_FIELD_ID = "wgf_error_selector";
    public const ERROR_SELECTOR_FIELD_TITLE = "Display error selector";
    public const ERROR_SELECTOR_FIELD_LABEL =
        "The selector used to find the element where to attach the error messages";
    public const ERROR_SELECTOR_FIELD_DEFAULT = '.entry-content > .woocommerce';
    public const ERROR_ATTACH_FIELD_ID = "wgf_error_attach_type";
    public const ERROR_ATTACH_FIELD_TITLE = "Display error method";
    public const ERROR_ATTACH_FIELD_LABEL = "The method used to display the html errors, " .
        "it can be prepend (before the selector) or append (after the selector)";
    public const ERROR_ATTACH_FIELD_DEFAULT = self::ERROR_ATTACH_FIELD_PREPEND_VALUE;
    public const ERROR_ATTACH_FIELD_PREPEND_VALUE = 'prepend';
    public const ERROR_ATTACH_FIELD_PREPEND_LABEL = 'Prepend';
    public const ERROR_ATTACH_FIELD_APPEND_VALUE = 'append';
    public const ERROR_ATTACH_FIELD_APPEND_LABEL = 'Append';
    public const ERROR_ATTACH_FIELD_VALUES = [
        self::ERROR_ATTACH_FIELD_PREPEND_VALUE => self::ERROR_ATTACH_FIELD_PREPEND_LABEL,
        self::ERROR_ATTACH_FIELD_APPEND_VALUE => self::ERROR_ATTACH_FIELD_APPEND_LABEL,
    ];
    public const NONCE = 'generate-funnel-url';

    public const THANK_YOU_PAGE_MAIN_SELECTOR_FIELD_ID = "wgf_thank_you_main_selector";
    public const THANK_YOU_PAGE_MAIN_SELECTOR_FIELD_TITLE = "Main thank you page selector";
    public const THANK_YOU_PAGE_MAIN_SELECTOR_FIELD_LABEL =
        "The selector used to find the main element that manages the thank you page.";
    public const THANK_YOU_PAGE_MAIN_SELECTOR_FIELD_DEFAULT = 'main';

    public const THANK_YOU_PAGE_TITLE_SELECTOR_FIELD_ID = "wgf_thank_you_title_selector";
    public const THANK_YOU_PAGE_TITLE_SELECTOR_FIELD_TITLE = "Title thank you page selector";
    public const THANK_YOU_PAGE_TITLE_SELECTOR_FIELD_LABEL =
        "The selector used to find the title element that manages the thank you page.";
    public const THANK_YOU_PAGE_TITLE_SELECTOR_FIELD_DEFAULT = '.entry-header .entry-title';

    public const THANK_YOU_PAGE_NOTICE_SELECTOR_FIELD_ID = "wgf_thank_you_notice_selector";
    public const THANK_YOU_PAGE_NOTICE_SELECTOR_FIELD_TITLE = "Notice thank you page selector";
    public const THANK_YOU_PAGE_NOTICE_SELECTOR_FIELD_LABEL =
        "The selector used to find the notice element that manages the thank you page.";
    public const THANK_YOU_PAGE_NOTICE_SELECTOR_FIELD_DEFAULT =
        '.entry-content .woocommerce .woocommerce-order p.woocommerce-notice';

    public const THANK_YOU_PAGE_ORDER_OVERVIEW_SELECTOR_FIELD_ID = "wgf_thank_you_order_overview_selector";
    public const THANK_YOU_PAGE_ORDER_OVERVIEW_SELECTOR_FIELD_TITLE = "Order Overview thank you page selector";
    public const THANK_YOU_PAGE_ORDER_OVERVIEW_SELECTOR_FIELD_LABEL =
        "The selector used to find the order overview element that manages the thank you page.";
    public const THANK_YOU_PAGE_ORDER_OVERVIEW_SELECTOR_FIELD_DEFAULT = '.entry-content .woocommerce-order-overview';

    public const THANK_YOU_PAGE_CUSTOMER_DETAILS_SELECTOR_FIELD_ID = "wgf_thank_you_customer_details_selector";
    public const THANK_YOU_PAGE_CUSTOMER_DETAILS_SELECTOR_FIELD_TITLE = "Customer details thank you page selector";
    public const THANK_YOU_PAGE_CUSTOMER_DETAILS_SELECTOR_FIELD_LABEL =
        "The selector used to find the customer details element that manages the thank you page.";
    public const THANK_YOU_PAGE_CUSTOMER_DETAILS_SELECTOR_FIELD_DEFAULT =
        '.entry-content .woocommerce-customer-details';

    public const THANK_YOU_PAGE_ORDER_DETAILS_SELECTOR_FIELD_ID = "wgf_thank_you_order_details_selector";
    public const THANK_YOU_PAGE_ORDER_DETAILS_SELECTOR_FIELD_TITLE = "Order details thank you page selector";
    public const THANK_YOU_PAGE_ORDER_DETAILS_SELECTOR_FIELD_LABEL =
        "The selector used to find the order details element that manages the thank you page.";
    public const THANK_YOU_PAGE_ORDER_DETAILS_SELECTOR_FIELD_DEFAULT = '.entry-content .woocommerce-order-details';
}
