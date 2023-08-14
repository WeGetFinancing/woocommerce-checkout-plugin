<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Service;

if (!defined( 'ABSPATH' )) exit;

use Throwable;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancing;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancingValueObject;
use function Sentry\captureException;
use function Sentry\init;

class Logger
{
    protected const DSN = 'https://1757ae63ca464acc93a7bca35ed049f5@sentry.dev.wegetfinancing.com/25';

    static public function log(Throwable $exception): void
    {
        self::wpLog($exception);
        if (
            "yes" === WeGetFinancing::getOption(
            WeGetFinancingValueObject::IS_SENTRY_FIELD_ID,
            "yes"
            )
        ) {
            self::sentryLog($exception);
        }


    }

    static public function wpLog(Throwable $exception): void
    {
        error_log("WeGetFinancing Error Class: " . $exception::class);
        error_log($exception->getCode() . ' - ' . $exception->getMessage());
        error_log(print_r($exception->getTraceAsString(), true));
    }

    static public function sentryLog(Throwable $exception): void
    {
        init(['dsn' => self::DSN ]);
        captureException($exception);
    }

    static public function getDecorativeData(): string
    {
        return
            " - PGV: " . App::PLUGIN_VERSION . " - INT: " . App::INTEGRATION_NAME . " " . App::getIntegrationVersion();
    }
}
