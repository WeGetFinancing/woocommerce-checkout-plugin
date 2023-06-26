<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout;

use WeGetFinancing\Checkout\Exception\GenerateClientException;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancing;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancingValueObject;
use WeGetFinancing\Checkout\Service\RequestValidatorUtility;
use WeGetFinancing\Checkout\ValueObject\GeneralDataRequest;
use WeGetFinancing\SDK\Client;
use WeGetFinancing\SDK\Entity\AuthEntity;
use WeGetFinancing\SDK\Exception\EntityValidationException;

abstract class AbstractActionableWithClient implements ActionableInterface
{
    protected array $violations = [];

    /**
     * @throws GenerateClientException
     * @return Client
     */
    protected function generateClient(): Client
    {
        try {
            $isSandbox = WeGetFinancing::getOption(WeGetFinancingValueObject::IS_SANDBOX_FIELD_ID);
            $auth = AuthEntity::make([
                'username' => WeGetFinancing::getOption(
                    WeGetFinancingValueObject::USERNAME_FIELD_ID,
                    ''
                ),
                'password'  => WeGetFinancing::getOption(
                    WeGetFinancingValueObject::PASSWORD_FIELD_ID,
                ''
                ),
                'merchantId' => WeGetFinancing::getOption(
                    WeGetFinancingValueObject::MERCHANT_ID_FIELD_ID,
                ''
                ),
                'prod' => false === ("yes" === $isSandbox),
            ]);
            return Client::Make($auth);
        } catch (EntityValidationException $exception) {
            error_log(self::class . "::generateClient entity validation error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            error_log(json_encode($exception->getViolations()));
            throw new GenerateClientException(
                GenerateClientException::GENERATE_CLIENT_VALIDATION_ERROR_MESSAGE,
                GenerateClientException::GENERATE_CLIENT_VALIDATION_ERROR_CODE
            );
        } catch (\Throwable $exception) {
            error_log(self::class . "::generateClient unexpected error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            throw new GenerateClientException(
                GenerateClientException::GENERATE_CLIENT_UNEXPECTED_ERROR_MESSAGE,
                GenerateClientException::GENERATE_CLIENT_UNEXPECTED_ERROR_CODE
            );
        }
    }

    /**
     * @throws EntityValidationException
     */
    protected function validateGeneralDataRequest()
    {
        $this->violations = [];

        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $_POST,
                GeneralDataRequest::DATA
            )
        ) {
            $this->violations[] = [
                'field' => 'general',
                'message' => 'general was sent to the server.',
            ];

            error_log(self::class . "::validateGeneralDataRequest ERROR: data is not set in the request.");

            throw new EntityValidationException(
                'Invalid data funnel request',
                12,
                null,
                $this->violations
            );
        }
    }
}
