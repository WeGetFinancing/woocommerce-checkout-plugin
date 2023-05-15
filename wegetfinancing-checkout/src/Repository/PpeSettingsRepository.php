<?php

namespace WeGetFinancing\Checkout\Repository;

use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancing;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancingValueObject;

class PpeSettingsRepository
{
    public const PREFIX = 'wegetfinancing_';
    public const SUFFIX = '_settings';


    static public function getOptions(): false|array
    {
        return get_option(self::getOptionsName(), []);
    }

    /**
     * @param string $optionName
     * @return bool
     */
    static public function getOption(string $optionName): mixed
    {
        $options = self::getOptions();
        if (false === array_key_exists($optionName, $options)) {
            error_log(self::class . "::getOption config option name doesn't exists: " . $optionName);
            return null;
        }
        return $options[$optionName];
    }

    static public function setOptions(array $options, $autoload = null): bool
    {
        return update_option(self::getOptionsName(), $options);
    }

    static public function setOption(string $optionName, mixed $optionValue, $autoload = null): bool
    {
        $options = self::getOptions();
        $options[$optionName] = $optionValue;
        return self::setOptions($options, $autoload);
    }

    static public function getOptionOrDefault($optionName, $defaultValue): int|string|bool
    {
        $option = self::getOption($optionName);
        return true === is_null($option)
            ? $defaultValue
            : $option;
    }

    static protected function getOptionsName(): string
    {
        return self::PREFIX . App::ID . self::SUFFIX;
    }
}
