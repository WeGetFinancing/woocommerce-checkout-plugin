<?php

namespace WeGetFinancing\Checkout\PostMeta;

use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class GetOrderInvIdField implements ActionableInterface
{
    use AddableTrait;
    public const INIT_NAME = 'woocommerce_checkout_fields';
    public const FUNCTION_NAME = 'execute';

    public function init(): void
    {
        $this->addFilter();
    }

    public function execute($fields): array
    {
        $fields[OrderInvIdValueObject::EXTRA_FIELDS_ID] = [
            OrderInvIdValueObject::ORDER_INV_ID_FIELD_ID => [
                'type' => 'text',
                'required' => true,
                'label' => translate(OrderInvIdValueObject::ORDER_INV_ID_FIELD_LABEL, App::DOMAIN_LOCALE)
            ]
        ];
        return $fields;
    }
}
