<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PostMeta;

if (!defined( 'ABSPATH' )) exit;

use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\ValueObject\PostMeta\OrderInvIdFieldVO;
use WeGetFinancing\Checkout\ValueObject\PostMeta\OrderIsWgfFieldVO;
use WeGetFinancing\Checkout\ValueObject\PostMeta\OrderWgfHrefFieldVO;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class SaveOrUpdateOrderHiddenFields implements ActionableInterface
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
        if (true === isset($posted[OrderInvIdFieldVO::FIELD_NAME])) {
            update_post_meta(
                $order_id,
                OrderInvIdFieldVO::META,
                sanitize_text_field($posted[OrderInvIdFieldVO::FIELD_NAME])
            );
        }
        if (true === isset($posted[OrderWgfHrefFieldVO::FIELD_NAME])) {
            update_post_meta(
                $order_id,
                OrderWgfHrefFieldVO::META,
                sanitize_text_field($posted[OrderWgfHrefFieldVO::FIELD_NAME])
            );
        }
        if (true === isset($posted[OrderIsWgfFieldVO::FIELD_NAME])) {
            update_post_meta(
                $order_id,
                OrderIsWgfFieldVO::META,
                sanitize_text_field($posted[OrderIsWgfFieldVO::FIELD_NAME])
            );
        }
    }
}
