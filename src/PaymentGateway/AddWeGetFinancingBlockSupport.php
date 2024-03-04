<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PaymentGateway;

if (!defined( 'ABSPATH' )) exit;

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class AddWeGetFinancingBlockSupport implements ActionableInterface
{
    use AddableTrait;

    public const INIT_NAME = 'woocommerce_blocks_payment_method_type_registration';
    public const FUNCTION_NAME = 'execute';

    public function init(): void
    {
        $this->addAction();
    }

    public function execute(PaymentMethodRegistry $payment_method_registry): void
    {
        $payment_method_registry->register(new WeGetFinancingBlockSupport());
    }
}
