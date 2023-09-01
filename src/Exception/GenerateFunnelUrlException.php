<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Exception;

if (!defined( 'ABSPATH' )) exit;

use WeGetFinancing\SDK\Exception\EntityValidationException;

class GenerateFunnelUrlException extends EntityValidationException
{
    public const ERROR_GENERATING_CLIENT_MESSAGE = 'Invalid credentials generating the WegetFinancing Client';
    public const UNEXPECTED_NETWORK_ERROR_MESSAGE = 'Unexpected network error';
    public const UNEXPECTED_ERROR_MESSAGE = 'Unexpected error';
    public const REQUEST_VALIDATION_ERROR_MESSAGE = 'Invalid generate funnel request';
    public const REQUEST_VALIDATION_ERROR_CODE = 1;
    public const LOAN_REQUEST_VALIDATION_ERROR_MESSAGE = 'LoanRequestEntity::make() entity validation error';
    public const LOAN_REQUEST_VALIDATION_ERROR_CODE = 2;
    public const REQUEST_VALIDATION_JSON_CODE = 3;
    public const LOAN_REQUEST_VALIDATION_JSON_CODE = 4;
}
