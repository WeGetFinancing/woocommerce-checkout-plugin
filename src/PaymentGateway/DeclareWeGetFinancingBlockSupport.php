<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PaymentGateway;

if (!defined( 'ABSPATH' )) exit;

use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Wp\AddableTrait;
use Automattic\WooCommerce\Utilities\FeaturesUtil;

class DeclareWeGetFinancingBlockSupport implements ActionableInterface
{
    use AddableTrait;

    public const INIT_NAME = 'before_woocommerce_init';
    public const FUNCTION_NAME = 'execute';
    public const FEATURE_ID = 'cart_checkout_blocks';

    public function __construct(protected string $pluginFile)
    {}

    public function init(): void
    {
        $this->addAction();
        error_log("DeclareWeGetFinancingBlockSupport::init");
    }

    public function execute(): void
    {
        error_log("DeclareWeGetFinancingBlockSupport::execute");
        if(true === class_exists(FeaturesUtil::class)) {
            FeaturesUtil::declare_compatibility(
                self::FEATURE_ID,
                $this->pluginFile,
                true // true (compatible, default) or false (not compatible)
            );
        }
    }
}
