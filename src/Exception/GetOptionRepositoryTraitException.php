<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Exception;

if (!defined( 'ABSPATH' )) exit;

use WeGetFinancing\SDK\Exception\EntityValidationException;

class GetOptionRepositoryTraitException extends EntityValidationException
{
    public const OPTION_NAME_NOT_EXISTS_CODE = 1;
    public const OPTION_NAME_NOT_EXISTS_MESSAGE = 'Option config does not exists. Option name: ';
}
