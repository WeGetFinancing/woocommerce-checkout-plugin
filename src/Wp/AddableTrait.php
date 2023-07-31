<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Wp;

if (!defined( 'ABSPATH' )) exit;

/**
 * This trait give to the class the ability of being loaded into WordPress as an action.
 * It implements part of the behaviour of ActionableInterface and therefore require the existence
 * of the constant ACTION_NAME
 */
trait AddableTrait
{
    public function getInitName(): string
    {
        return self::INIT_NAME;
    }

    /**
     * Add the class as an action to WordPress
     *
     * @param int $priority
     * @param int $acceptedArgs
     * @return void
     */
    public function addAction(int $priority = 10, int $acceptedArgs = 1): void
    {
        add_action(
            $this->getInitName(),
            [$this, self::FUNCTION_NAME],
            $priority,
            $acceptedArgs
        );
    }

    /**
     * Add the class as a filter to WordPress
     *
     * @param int $priority
     * @param int $acceptedArgs
     * @return void
     */
    public function addFilter(int $priority = 10, int $acceptedArgs = 1): void
    {
        add_filter(
            $this->getInitName(),
            [$this, self::FUNCTION_NAME],
            $priority,
            $acceptedArgs
        );
    }
}
