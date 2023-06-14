<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Repository;

trait GetOptionRepositoryTrait
{
    public static function getOptions(): false|array
    {
        return get_option(self::getOptionsName(), []);
    }

    public static function getOption(string $optionName): mixed
    {
        $options = self::getOptions();
        if (false === array_key_exists($optionName, $options)) {
            error_log(self::class . "::getOption config option name doesn't exists: " . $optionName);
            return null;
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
