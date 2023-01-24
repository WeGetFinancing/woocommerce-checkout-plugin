<?php

namespace WeGetFinancing\Checkout\Wp;

/**
 * This trait give to the class the ability of being loaded into WordPress as an action.
 * It implements part of the behaviour of ActionableInterface and therefore require the existence
 * of the constant ACTION_NAME
 */
trait AddableTrait
{
    /**
     * Add an action to WordPress
     *
     * @param string $actionName
     * @param string $functionName
     * @return void
     */
    public function addAction(string $actionName, string $functionName): void
    {
        add_action($actionName, [$this, $functionName]);
    }

    public function getActionName(): string
    {
        return self::ACTION_NAME;
    }
}
