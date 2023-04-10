<?php

namespace WeGetFinancing\Checkout;

use WeGetFinancing\Checkout\Exception\GenerateClientException;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancing;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancingValueObject;
use WeGetFinancing\SDK\Client;
use WeGetFinancing\SDK\Entity\AuthEntity;
use WeGetFinancing\SDK\Exception\EntityValidationException;

abstract class AbstractActionableWithClient implements ActionableInterface
{
    /**
     * @return Client
     * @throws GenerateClientException
     */
    protected function generateClient(): Client
    {
        try {
            $options = WeGetFinancing::getOptions();

            $auth = AuthEntity::make([
                'username' => $options[WeGetFinancingValueObject::USERNAME_FIELD_ID],
                'password'  => $options[WeGetFinancingValueObject::PASSWORD_FIELD_ID],
                'merchantId' => $options[WeGetFinancingValueObject::MERCHANT_ID_FIELD_ID],
                'prod' => false === ("yes" === $options[WeGetFinancingValueObject::IS_SANDBOX_FIELD_ID])
            ]);

            return Client::Make($auth);
        } catch (EntityValidationException $exception) {
            error_log("AbstractActionableWithClient::generateClient entity validation error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            error_log(json_encode($exception->getViolations()));
            throw new GenerateClientException(
                GenerateClientException::GENERATE_CLIENT_VALIDATION_ERROR_MESSAGE,
                GenerateClientException::GENERATE_CLIENT_VALIDATION_ERROR_CODE
            );
        } catch (\Throwable $exception) {
            error_log("AbstractActionableWithClient::generateClient unexpected error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            throw new GenerateClientException(
                GenerateClientException::GENERATE_CLIENT_UNEXPECTED_ERROR_MESSAGE,
                GenerateClientException::GENERATE_CLIENT_UNEXPECTED_ERROR_CODE
            );
        }
    }
}