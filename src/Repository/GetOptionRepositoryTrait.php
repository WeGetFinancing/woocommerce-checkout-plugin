<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Repository;

use Service\Logger;
use WeGetFinancing\Checkout\Exception\GetOptionRepositoryTraitException;

if (!defined( 'ABSPATH' )) exit;

trait GetOptionRepositoryTrait
{
    public static function getOptions(): false|array
    {
        return get_option(self::getOptionsName(), []);
    }

    public static function getOption(string $optionName, mixed $default = null): mixed
    {
        $options = self::getOptions();
        if (false === array_key_exists($optionName, $options)) {
            Logger::log(new GetOptionRepositoryTraitException(
                GetOptionRepositoryTraitException::OPTION_NAME_NOT_EXISTS_MESSAGE,
                GetOptionRepositoryTraitException::OPTION_NAME_NOT_EXISTS_CODE
            ));
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
