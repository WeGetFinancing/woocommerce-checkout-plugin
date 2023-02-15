<?php

namespace WeGetFinancing\WCP\PostMeta;

class OrderInvId
{
    public const EXTRA_FIELDS = 'wegetfinancing_extra_fields';
    public const ORDER_INV_ID_FIELD_ID = 'wegetfinancing_order_inv_id';
    public const ORDER_INV_ID_LABEL = 'WeGetFinancing Transaction ID';

    public function init(): void
    {
        add_filter(
            'woocommerce_checkout_fields',
            [$this, 'getWegetfinancingOrderInvIdField']
        );
        add_action(
            'woocommerce_checkout_after_customer_details',
            [$this, 'printToCheckoutWegetfinancingOrderInvIdField']
        );
        add_action(
            'woocommerce_checkout_update_order_meta',
            [$this, 'saveOrUpdateWegetfinancingOrderInvIdField'],
            10,
            2
        );
        add_action(
            'woocommerce_admin_order_data_after_order_details',
            [$this, 'displayWegetfinancingOrderInvIdInAdmin']
        );
    }

    public function getWegetfinancingOrderInvIdField($fields): array
    {
        $fields[self::EXTRA_FIELDS] = [
            self::ORDER_INV_ID_FIELD_ID => [
                'type' => 'text',
                'required'      => true,
                'label' => __(self::ORDER_INV_ID_LABEL)
            ]
        ];
        return $fields;
    }

    public function printToCheckoutWegetfinancingOrderInvIdField(): void
    {
        echo '<input id="' . self::ORDER_INV_ID_FIELD_ID . '" type="hidden" value=""/>';
    }

    public function saveOrUpdateWegetfinancingOrderInvIdField($order_id, $posted): void
    {
        if (true === isset($posted[self::ORDER_INV_ID_FIELD_ID])) {
            update_post_meta(
                $order_id,
                "_" . self::ORDER_INV_ID_FIELD_ID,
                sanitize_text_field($posted[self::ORDER_INV_ID_FIELD_ID])
            );
        }
    }

    public function displayWegetfinancingOrderInvIdInAdmin($order): void
    {
        ?>
        <div class="order_data_column">
            <h4><?php _e( 'WeGetFinancing', 'woocommerce' ); ?></h4>
            <div class="address">
            <?php
                echo '<p><strong>' . __(self::ORDER_INV_ID_LABEL) . ':</strong>' .
                    get_post_meta($order->id,'_' . self::ORDER_INV_ID_FIELD_ID,true) . '</p>';
            ?>
            </div>
        </div>
        <?php
    }
    
}
