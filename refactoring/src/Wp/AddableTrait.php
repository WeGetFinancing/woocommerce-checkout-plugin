<?php

namespace WeGetFinancing\Checkout\Wp;

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
     * @return void
     */
    public function addAction(): void
    {
        add_action($this->getInitName(), [$this, self::FUNCTION_NAME]);
    }

    /**
     * Add the class as a filter to WordPress
     * @return void
     */
    public function addFilter(): void
    {
        add_filter($this->getInitName(), [$this, self::FUNCTION_NAME]);
    }
}
