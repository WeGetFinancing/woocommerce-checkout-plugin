<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PostMeta;

if (!defined( 'ABSPATH' )) exit;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class PrintToCheckoutOrderInvIdField implements ActionableInterface
{
    use AddableTrait;

    public const INIT_NAME = 'woocommerce_checkout_after_customer_details';
    public const FUNCTION_NAME = 'execute';

    protected Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function init(): void
    {
        $this->addAction();
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function execute(): void
    {
        echo $this->twig->render(
            'store/checkout_inv_id.twig',
            ['inv_id' => OrderInvIdValueObject::ORDER_INV_ID_FIELD_ID]
        );
    }
}
