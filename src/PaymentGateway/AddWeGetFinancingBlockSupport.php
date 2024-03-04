<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PaymentGateway;

if (!defined( 'ABSPATH' )) exit;

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class AddWeGetFinancingBlockSupport implements ActionableInterface
{
    use AddableTrait;

    public const INIT_NAME = 'woocommerce_blocks_loaded';
    public const FUNCTION_NAME = 'execute';

    public function init(): void
    {
        error_log("AddWeGetFinancingBlockSupport::init");
        $this->addAction();
    }

    public function execute(): void
    {
        error_log("AddWeGetFinancingBlockSupport::execute");
        if (true === class_exists(AbstractPaymentMethodType::class)) {
            error_log("AddWeGetFinancingBlockSupport::execute INSIDE");
            add_action(
                WeGetFinancingBlockSupport::INIT_NAME,
                function( PaymentMethodRegistry $payment_method_registry ) {
                    $payment_method_registry->register( new WeGetFinancingBlockSupport() );
                }
            );
        }
    }
}
