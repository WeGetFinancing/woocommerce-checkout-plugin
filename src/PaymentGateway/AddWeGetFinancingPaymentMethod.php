<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PaymentGateway;

if (!defined( 'ABSPATH' )) exit;

use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class AddWeGetFinancingPaymentMethod implements ActionableInterface
{
    use AddableTrait;

    public const INIT_NAME = 'woocommerce_payment_gateways';
    public const FUNCTION_NAME = 'execute';

    public function init(): void
    {
        $this->addFilter();
    }

    public function execute(array $methods): array
    {
        $methods[] = WeGetFinancing::class;
        return $methods;
    }
}
