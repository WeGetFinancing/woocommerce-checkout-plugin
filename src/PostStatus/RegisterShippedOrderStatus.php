<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PostStatus;

if (!defined( 'ABSPATH' )) exit;

use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class RegisterShippedOrderStatus implements ActionableInterface
{
    use AddableTrait;

    public const INIT_NAME = 'wc_order_statuses';
    public const FUNCTION_NAME = 'execute';

    public function init(): void
    {
        register_post_status('wc-shipped', [
            'label'                     => 'Shipped',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Shipped (%s)', 'Shipped (%s)'),
        ]);

        $this->addFilter();
    }

    public function execute($order_statuses): array
    {
        $new_order_statuses = [];

        // add new order status after processing
        foreach ($order_statuses as $key => $status) {
            $new_order_statuses[ $key ] = $status;

            if ('wc-processing' === $key) {
                $new_order_statuses['wc-shipped'] = 'Shipped';
            }
        }

        return $new_order_statuses;
    }
}
