<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PostMeta;

if (!defined( 'ABSPATH' )) exit;

use Throwable;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Exception\PostMeta\SetWgfOptionsDefaultsException;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancing;
use WeGetFinancing\Checkout\Service\Logger;
use WeGetFinancing\Checkout\ValueObject\PaymentGateway\WeGetFinancingVO;
use WeGetFinancing\Checkout\ValueObject\PostMeta\OrderInvIdFieldVO;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class SetWgfOptionsDefaults implements ActionableInterface
{
    use AddableTrait;

    public const INIT_NAME = 'woocommerce_checkout_update_order_meta';
    public const FUNCTION_NAME = 'execute';
    public const DEFAULTS = [
        WeGetFinancingVO::IS_SANDBOX_FIELD_ID => WeGetFinancingVO::IS_SANDBOX_FIELD_DEFAULT,
        WeGetFinancingVO::IS_SENTRY_FIELD_ID => WeGetFinancingVO::IS_SENTRY_FIELD_DEFAULT,
        WeGetFinancingVO::IS_ORDER_AUTO_COMPLETE_FIELD_ID => WeGetFinancingVO::IS_ORDER_AUTO_COMPLETE_FIELD_DEFAULT,
        WeGetFinancingVO::ORDER_HOLD_PERIOD_FIELD_ID => WeGetFinancingVO::ORDER_HOLD_PERIOD_FIELD_DEFAULT,
        WeGetFinancingVO::IS_RESTOCK_ON_REFUND_FIELD_ID => WeGetFinancingVO::IS_RESTOCK_ON_REFUND_DEFAULT
    ];
    public function init(): void
    {
        $this->addAction(10, 0);
    }

    public function execute(): void
    {
        try {
            foreach (self::DEFAULTS as $optionId => $defaultValue) {
                $this->setOptionDefault($optionId, $defaultValue);
            }
        } catch (Throwable $exception) {
            Logger::log($exception);
        }
    }

    /**
     * @throws SetWgfOptionsDefaultsException
     */
    protected function setOptionDefault(string $optionId, mixed $defaultValue): void
    {
        $value = WeGetFinancing::getOption($optionId);
        if (empty($value)) {
            if (false === WeGetFinancing::setOption($optionId, $defaultValue)) {
                throw new SetWgfOptionsDefaultsException(
                    sprintf(
                        SetWgfOptionsDefaultsException::ERROR_SET_DEFAULT_OPTION_MESSAGE,
                        $optionId,
                        $defaultValue
                    ),
                    SetWgfOptionsDefaultsException::ERROR_SET_DEFAULT_OPTION_CODE
                );
            }
        }
    }
}