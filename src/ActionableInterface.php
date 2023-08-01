<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout;

if (!defined( 'ABSPATH' )) exit;

/**
 * This interface describe a class with the following behaviour:
 *  - Single purpose class
 *  - Can be hooked into WordPress via a core function like add_action, add_filter, etc.
 *  - The name of the action, filter or different function has to be defined in the constant INIT_NAME
 */
interface ActionableInterface
{
    /**
     * Register the method execute into the WordPress framework as an action, filter, etc.
     * @return void
     */
    public function init(): void;
}
