<?php

declare(strict_types=1);

namespace Service;

use ReflectionException;
use ReflectionObject;
use Throwable;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancing;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancingValueObject;

class Logger
{
    static public function log(Throwable $exception): void
    {
        self::wpLog($exception);
        if ("yes" === WeGetFinancing::getOption(WeGetFinancingValueObject::IS_SENTRY_FIELD_ID)) {
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
        try {
            $reflectionObject = new ReflectionObject($exception);
            $reflectionObjectProp = $reflectionObject->getProperty('message');
            $reflectionObjectProp->setAccessible(true);
            $reflectionObjectProp->setValue($exception, $exception->getMessage() . self::getDecorativeSentryData());
        } catch (Throwable $throwable) {
            self::wpLog($throwable);
        }


    }

    static public function getDecorativeSentryData(): string
    {
        return
            " - PGV: " . App::PLUGIN_VERSION . " - INT: " . App::INTEGRATION_NAME . " " . App::getIntegrationVersion();
    }
}
