<?php

namespace WeGetFinancing\Checkout\PaymentGateway;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Ajax\Public\GenerateFunnelUrl;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\PostMeta\OrderInvIdValueObject;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class WeGetFinancing extends \WC_Payment_Gateway implements ActionableInterface
{
    use AddableTrait;
    public const GATEWAY_ID = "wegetfinancing";
    public const INIT_NAME = 'woocommerce_update_options_payment_gateways_';
    public const FUNCTION_NAME = 'process_admin_options';

    protected Environment $twig;

    public function __construct()
    {
        $this->twig = $GLOBALS[App::ID][App::RENDER];
        $this->id = static::GATEWAY_ID;
        $this->has_fields = false;
        $this->icon = '';
        $this->method_title = translate(WeGetFinancingValueObject::METHOD_TITLE, App::DOMAIN_LOCALE);
        $this->method_description =
            translate(WeGetFinancingValueObject::METHOD_DESCRIPTION, App::DOMAIN_LOCALE);
        $this->title = translate(WeGetFinancingValueObject::TITLE, App::DOMAIN_LOCALE);
        $this->description = translate(WeGetFinancingValueObject::DESCRIPTION, App::DOMAIN_LOCALE);
        $this->supports    = [
            'products',
            'refunds',
        ];
        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->{WeGetFinancingValueObject::IS_SANDBOX_FIELD_ID} =
            $this->get_option(WeGetFinancingValueObject::IS_SANDBOX_FIELD_ID, true);
        $this->{WeGetFinancingValueObject::USERNAME_FIELD_ID} =
            $this->get_option(WeGetFinancingValueObject::USERNAME_FIELD_ID);
        $this->{WeGetFinancingValueObject::PASSWORD_FIELD_ID} =
            $this->get_option(WeGetFinancingValueObject::PASSWORD_FIELD_ID);
        $this->{WeGetFinancingValueObject::MERCHANT_ID_FIELD_ID} =
            $this->get_option(WeGetFinancingValueObject::MERCHANT_ID_FIELD_ID);

        $this->init();
    }

    public function getInitName(): string
    {
        return self::INIT_NAME . $this->id;
    }

    public function init(): void
    {
        $this->addAction();
    }

    public function init_form_fields()
    {
        $this->form_fields = apply_filters(
            WeGetFinancingValueObject::FIELDSET_ID,
            [
                WeGetFinancingValueObject::IS_SANDBOX_FIELD_ID => [
                    'title'   =>
                        translate(WeGetFinancingValueObject::IS_SANDBOX_FIELD_TITLE, App::DOMAIN_LOCALE),
                    'type'    => 'checkbox',
                    'label'   =>
                        translate(WeGetFinancingValueObject::IS_SANDBOX_FIELD_LABEL, App::DOMAIN_LOCALE),
                    'default' => 'yes'
                ],
                WeGetFinancingValueObject::USERNAME_FIELD_ID => [
                    'title'   =>
                        translate(WeGetFinancingValueObject::USERNAME_FIELD_TITLE, App::DOMAIN_LOCALE),
                    'type'    => 'text',
                    'description'   =>
                        translate(WeGetFinancingValueObject::USERNAME_FIELD_LABEL, App::DOMAIN_LOCALE),
                    'default' => '',
                    'desc_tip'    => true,
                ],
                WeGetFinancingValueObject::PASSWORD_FIELD_ID => [
                    'title'   =>
                        translate(WeGetFinancingValueObject::PASSWORD_FIELD_TITLE, App::DOMAIN_LOCALE),
                    'type'        => 'password',
                    'description'   =>
                        translate(WeGetFinancingValueObject::PASSWORD_FIELD_LABEL, App::DOMAIN_LOCALE),
                    'default'     => '',
                    'desc_tip'    => true,
                ],
                WeGetFinancingValueObject::MERCHANT_ID_FIELD_ID => [
                    'title'   =>
                        translate(WeGetFinancingValueObject::MERCHANT_ID_FIELD_TITLE, App::DOMAIN_LOCALE),
                    'type'        => 'text',
                    'description'   =>
                        translate(WeGetFinancingValueObject::MERCHANT_ID_FIELD_LABEL, App::DOMAIN_LOCALE),
                    'default'     => '',
                    'desc_tip'    => true,
                ]
            ]
        );
    }

    /**
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function admin_options(): void
    {
        echo $this->twig->render(
            'admin/payment_settings.twig',
            [
                'form' => $this->generate_settings_html([], false)
            ]
        );
    }

    /**
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function payment_fields(): void
    {
        wp_enqueue_script(WeGetFinancingValueObject::HANDLE_FUNNEL_SCRIPT, $GLOBALS[App::ID][App::FUNNEL_JS]);

        echo $this->twig->render(
            'store/checkout_button.twig',
            [
                'description' => $this->description,
                'payment_method_id' => $this->id,
                'checkout_button_image_url' => $GLOBALS[App::ID][App::CHECKOUT_BUTTON_URL],
                'checkout_button_alt' => WeGetFinancingValueObject::CHECKOUT_BUTTON_ALT,
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_action' => GenerateFunnelUrl::ACTION_NAME,
                'order_inv_id_field_id' => OrderInvIdValueObject::ORDER_INV_ID_FIELD_ID
            ]
        );
    }

    /**
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id): array
    {
        $order = wc_get_order($order_id);
        // Mark as on-hold (we're awaiting the payment)
        $order->update_status(
            WeGetFinancingValueObject::ON_HOLD_STATUS_ID,
            translate(WeGetFinancingValueObject::ON_HOLD_STATUS_LABEL, App::DOMAIN_LOCALE)
        );
        // Reduce stock levels
        wc_reduce_stock_levels($order->get_id());
        // Remove cart
        WC()->cart->empty_cart();
        // Return thank you redirect
        return [
            'result' => WeGetFinancingValueObject::PROCESS_PAYMENT_SUCCESS_ID,
            'redirect' => $this->get_return_url($order)
        ];
    }

    static public function getOptions(): false|array
    {
        return get_option("woocommerce_" . App::ID . "_settings");
    }
}
