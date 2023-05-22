<?php

namespace WeGetFinancing\Checkout\Service;

class RequestValidatorUtility
{
    static public function checkIfArrayKeyNotExistsOrEmpty(array $data, string $field): bool
    {
        return (false === array_key_exists($field, $data) ||
            true === empty($data[$field]));
    }
}
