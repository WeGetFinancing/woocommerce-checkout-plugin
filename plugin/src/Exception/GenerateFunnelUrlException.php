<?php

namespace WeGetFinancing\WCP\Exception;

class GenerateFunnelUrlException extends \Exception
{
    public const GENERATE_CLIENT_ERROR_CODE = 1;
    public const GENERATE_CLIENT_ERROR_MESSAGE = 'Error connecting to WeGetFinancing network.';
}
