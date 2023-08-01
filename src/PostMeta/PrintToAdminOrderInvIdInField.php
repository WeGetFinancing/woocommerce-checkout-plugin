<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PostMeta;

if (!defined( 'ABSPATH' )) exit;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class PrintToAdminOrderInvIdInField implements ActionableInterface
{
    use AddableTrait;

    public const INIT_NAME = 'woocommerce_admin_order_data_after_order_details';
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
    public function execute($order): void
    {
        echo $this->twig->render(
            'admin/order_inv_id.twig',
            [
                'title' => OrderInvIdValueObject::ORDER_INV_ID_FIELD_ADMIN_TITLE,
                'label' => OrderInvIdValueObject::ORDER_INV_ID_FIELD_ADMIN_LABEL,
                'value' => get_post_meta($order->id, '_' . OrderInvIdValueObject::ORDER_INV_ID_FIELD_ID, true),
            ]
        );
    }
}
