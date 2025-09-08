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
     * @param string $optionId
     * @param mixed|null $default
     * @return mixed
     */
    public static function getOption(string $optionId, mixed $default = null): mixed
    {
        $options = self::getOptions();
        if (false === array_key_exists($optionId, $options)) {
            return $default;
        }
        return $options[$optionId];
    }

    public static function getOptionOrDefault($optionId, $defaultValue): int|string|bool
    {
        $option = self::getOption($optionId);
        return true === is_null($option)
            ? $defaultValue
            : $option;
    }

    public static function setOptions(array $options, $autoload = null): bool
    {
        return update_option(self::getOptionsName(), $options, $autoload);
    }

    public static function setOption(string $optionId, mixed $optionValue, $autoload = null): bool
    {
        $options = self::getOptions();
        $options[$optionId] = $optionValue;
        return self::setOptions($options, $autoload);
    }
}
