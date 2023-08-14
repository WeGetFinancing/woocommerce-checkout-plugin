<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Repository;

if (!defined( 'ABSPATH' )) exit;

use WeGetFinancing\Checkout\Service\Logger;
use WeGetFinancing\Checkout\Exception\GetOptionRepositoryTraitException;

trait GetOptionRepositoryTrait
{
    public static function getOptions(): false|array
    {
        return get_option(self::getOptionsName(), []);
    }

    /**
     * @param string $optionName
     * @param mixed|null $default
     * @return mixed
     */
    public static function getOption(string $optionName, mixed $default = null): mixed
    {
        $options = self::getOptions();
        if (false === array_key_exists($optionName, $options)) {
            return $default;
        }
        return $options[$optionName];
    }

    public static function getOptionOrDefault($optionName, $defaultValue): int|string|bool
    {
        $option = self::getOption($optionName);
        return true === is_null($option)
            ? $defaultValue
            : $option;
    }
}
