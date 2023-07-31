<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Service;

if (!defined( 'ABSPATH' )) exit;

class RequestValidatorUtility
{
    public static function checkIfArrayKeyNotExistsOrEmpty(array $data, string $field): bool
    {
        return (false === array_key_exists($field, $data) ||
            true === empty($data[$field]));
    }
}
