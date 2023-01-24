<?php

namespace WeGetFinancing\Checkout;

/**
 * This interface describe a class that has a single purpose, that can be activated via the method execute
 * and can be hooked into WordPress at the WordPress action named as getActionName method provide.
 *
 * We suggest also to store the action name constant into a homonym constant called ACTION_NAME
 */
interface ActionableInterface
{
    public const FUNCTION_NAME = 'execute';

    /**
     * Return the value of the constant ACTION_NAME
     * @return string
     */
    public function getActionName(): string;

    /**
     * This called represent the purpose of this class, somehow like __invoke() should be.
     * Sometimes it could have side effect like rendering template and echos it.
     * @return void
     */
    public function execute(): void;

    /**
     * Add the class to WordPress
     * It should call the
     * @return void
     */
    public function init(): void;
}
