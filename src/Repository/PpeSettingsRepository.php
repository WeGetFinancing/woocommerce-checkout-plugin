<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Repository;

if (!defined( 'ABSPATH' )) exit;

use WeGetFinancing\Checkout\App;

class PpeSettingsRepository
{
    use GetOptionRepositoryTrait;

    public const PREFIX = 'wegetfinancing_';
    public const SUFFIX = '_settings';

    protected static function getOptionsName(): string
    {
        return self::PREFIX . App::ID . self::SUFFIX;
    }

    public static function setOptions(array $options, $autoload = null): bool
    {
        return update_option(self::getOptionsName(), $options);
    }

    public static function setOption(string $optionName, mixed $optionValue, $autoload = null): bool
    {
        $options = self::getOptions();
        $options[$optionName] = $optionValue;
        return self::setOptions($options, $autoload);
    }
}
