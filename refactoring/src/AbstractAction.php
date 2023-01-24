<?php

namespace WeGetFinancing\Checkout;

use WeGetFinancing\Checkout\Wp\AddableTrait;

abstract class AbstractAction implements ActionableInterface
{
    use AddableTrait;

    public function init(): void
    {
        $this->addAction($this->getActionName(), self::FUNCTION_NAME);
    }
}
