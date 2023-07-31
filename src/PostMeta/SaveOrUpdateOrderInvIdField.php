<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PostMeta;

if (!defined( 'ABSPATH' )) exit;

use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class SaveOrUpdateOrderInvIdField implements ActionableInterface
{
    use AddableTrait;

    public const INIT_NAME = 'woocommerce_checkout_update_order_meta';
    public const FUNCTION_NAME = 'execute';

    public function init(): void
    {
        $this->addAction(10, 2);
    }

    public function execute($order_id, $posted): void
    {
        if (true === isset($posted[OrderInvIdValueObject::ORDER_INV_ID_FIELD_ID])) {
            update_post_meta(
                $order_id,
                OrderInvIdValueObject::ORDER_META,
                sanitize_text_field($posted[OrderInvIdValueObject::ORDER_INV_ID_FIELD_ID])
            );
        }
    }
}
