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

    public const THANK_YOU_MESSAGE_ORDER_PENDING_FIELD_ID = "wgf_thank_you_message_order_pending";
    public const THANK_YOU_MESSAGE_ORDER_PENDING_FIELD_TITLE = "Thank You Page - Message for order status Pending";
    public const THANK_YOU_MESSAGE_ORDER_PENDING_FIELD_LABEL =
        "The message displayed in the Thank You Page when the order status is Pending.";
    public const THANK_YOU_MESSAGE_ORDER_PENDING_FIELD_DEFAULT =
        "<p>We regret to inform your payment was unsuccessful.</p>" .
        "<p>Either you closed the funnel before completing it or your order was sent to us, " .
        "but we are not able to offer you a payment solution at the moment.</p>" .
        "<p>You can continue to shop and use another payment method.</p>";

    public const THANK_YOU_MESSAGE_ORDER_ON_HOLD_FIELD_ID = "wgf_thank_you_message_order_on_hold";
    public const THANK_YOU_MESSAGE_ORDER_ON_HOLD_FIELD_TITLE = "Thank You Page - Message for order status On-Hold";
    public const THANK_YOU_MESSAGE_ORDER_ON_HOLD_FIELD_LABEL =
        "The message displayed in the Thank You Page when the order status is On-Hold.";
    public const THANK_YOU_MESSAGE_ORDER_ON_HOLD_FIELD_DEFAULT =
        "<p>Congratulation! Your order was successfully sent to us and is currently awaiting payment.</p>" .
        "<p>Once we receive the payment for your order, it will be completed.</p>" .
        "<p>If you've already provided payment details, " .
        "then we will process your order manually and email you when it's completed.</p>";

    public const THANK_YOU_MESSAGE_ORDER_PROCESSING_FIELD_ID = "wgf_thank_you_message_order_processing";
    public const THANK_YOU_MESSAGE_ORDER_PROCESSING_FIELD_TITLE = "Thank You Page - Message for order status Processing";
    public const THANK_YOU_MESSAGE_ORDER_PROCESSING_FIELD_LABEL =
        "The message displayed in the Thank You Page when the order status is Processing.";
    public const THANK_YOU_MESSAGE_ORDER_PROCESSING_FIELD_DEFAULT =
        "<p>Congratulation! Your payment was successfully received, and now your order is being fulfilled.</p>";

    public const THANK_YOU_MESSAGE_ORDER_FAILED_FIELD_ID = "wgf_thank_you_message_order_failed";
    public const THANK_YOU_MESSAGE_ORDER_FAILED_FIELD_TITLE = "Thank You Page - Message for order status Failed";
    public const THANK_YOU_MESSAGE_ORDER_FAILED_FIELD_LABEL =
        "The message displayed in the Thank You Page when the order status is Failed.";
    public const THANK_YOU_MESSAGE_ORDER_FAILED_FIELD_DEFAULT =
        "<p>We regret to inform your payment was unsuccessful.</p>" .
        "<p>Your order was sent to us, but we are not able to offer you a payment solution at the moment.</p>" .
        "<p>You can continue to shop and use another payment method.</p>";

    public const THANK_YOU_MESSAGE_ORDER_ERROR_FIELD_ID = "wgf_thank_you_message_order_error";
    public const THANK_YOU_MESSAGE_ORDER_ERROR_FIELD_TITLE = "Thank You Page - Message for order Error";
    public const THANK_YOU_MESSAGE_ORDER_ERROR_FIELD_LABEL =
        "The message displayed in the Thank You Page when the order goes in unexpected error state.";
    public const THANK_YOU_MESSAGE_ORDER_ERROR_FIELD_DEFAULT =
        "<p>Your order was sent to us, " .
        "but we were unable to confirm your order due to an unexpected internal error.</p>" .
        "<p>Our customer service is being alerted and will review your case promptly.</p>" .
        "<p>Please save your order number for future review.</p>";
}
