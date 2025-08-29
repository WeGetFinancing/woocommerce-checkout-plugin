<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PostMeta;

if (!defined( 'ABSPATH' )) exit;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\ValueObject\PostMeta\FieldVO;
use WeGetFinancing\Checkout\ValueObject\PostMeta\OrderInvIdFieldVO;
use WeGetFinancing\Checkout\ValueObject\PostMeta\OrderIsWgfFieldVO;
use WeGetFinancing\Checkout\ValueObject\PostMeta\OrderWgfHrefFieldVO;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class PrintToCheckoutOrderHiddenFields implements ActionableInterface
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
            'store/checkout_hidden_fields.twig',
            [
                'hidden_type' => FieldVO::HIDDEN_TYPE,
                'wgf_inv_id_id' => OrderInvIdFieldVO::FIELD_ID,
                'wgf_inv_id_name' => OrderInvIdFieldVO::FIELD_NAME,
                'wgf_inv_id_default' => OrderInvIdFieldVO::DEFAULT_VALUE,
                'wgf_href_id' => OrderWgfHrefFieldVO::FIELD_ID,
                'wgf_href_name' => OrderWgfHrefFieldVO::FIELD_NAME,
                'wgf_href_default' => OrderWgfHrefFieldVO::DEFAULT_VALUE,
                'wgf_is_wgf_id' => OrderIsWgfFieldVO::FIELD_ID,
                'wgf_is_wgf_name' => OrderIsWgfFieldVO::FIELD_NAME,
                'wgf_is_wgf_default' => OrderIsWgfFieldVO::DEFAULT_VALUE,
            ]
        );
    }
}
