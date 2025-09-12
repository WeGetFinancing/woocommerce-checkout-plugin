<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout;

if (!defined( 'ABSPATH' )) exit;

use WeGetFinancing\Checkout\Exception\AbstractActionableWithClientException;
use WeGetFinancing\Checkout\Exception\WpEntityValidationException;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancing;
use WeGetFinancing\Checkout\Service\Logger;
use WeGetFinancing\Checkout\Service\RequestValidatorUtility;
use WeGetFinancing\Checkout\ValueObject\GeneralDataRequest;
use WeGetFinancing\Checkout\ValueObject\PaymentGateway\WeGetFinancingVO;
use WeGetFinancing\SDK\Client;
use WeGetFinancing\SDK\Entity\AuthEntity;
use WeGetFinancing\SDK\Exception\EntityValidationException;

abstract class AbstractActionableWithClient implements ActionableInterface
{
    protected array $violations = [];

    /**
     * @return Client
     * @throws AbstractActionableWithClientException
     */
    protected function generateClient(): Client
    {
        try {
            $auth = AuthEntity::make($this->getClientOptions());
            return Client::Make($auth);
        } catch (EntityValidationException $exception) {
            Logger::log($exception);
            Logger::log(new AbstractActionableWithClientException(
                json_encode($exception->getViolations()),
                AbstractActionableWithClientException::VALIDATION_JSON_CODE
            ));
            throw new AbstractActionableWithClientException(
                AbstractActionableWithClientException::VALIDATION_ERROR_MESSAGE . Logger::getDecorativeData(),
                AbstractActionableWithClientException::VALIDATION_ERROR_CODE
            );
        } catch (\Throwable $exception) {
            Logger::log($exception);
            throw new AbstractActionableWithClientException(
                AbstractActionableWithClientException::GENERATE_CLIENT_UNEXPECTED_ERROR_MESSAGE .
                    Logger::getDecorativeData(),
                AbstractActionableWithClientException::GENERATE_CLIENT_UNEXPECTED_ERROR_CODE
            );
        }
    }

    /**
     * @throws AbstractActionableWithClientException
     */
    protected function getClientOptions(): array
    {
        try {
            $isSandbox = WeGetFinancing::getOption(WeGetFinancingVO::IS_SANDBOX_FIELD_ID);
            $username = WeGetFinancing::getOption(
                WeGetFinancingVO::USERNAME_FIELD_ID,
                ''
            );
            $password = WeGetFinancing::getOption(
                WeGetFinancingVO::PASSWORD_FIELD_ID,
                ''
            );
            $merchantId = WeGetFinancing::getOption(
                WeGetFinancingVO::MERCHANT_ID_FIELD_ID,
                ''
            );

            if (empty($username) || empty($password) || empty($merchantId)) {
                throw new AbstractActionableWithClientException(
                    AbstractActionableWithClientException::GENERATE_CLIENT_ERROR_MESSAGE . Logger::getDecorativeData(),
                    AbstractActionableWithClientException::GENERATE_CLIENT_ERROR_CODE
                );
            }

            return [
                'username' => $username,
                'password'  => $password,
                'merchantId' => $merchantId,
                'prod' => false === ("yes" === $isSandbox),
            ];
        } catch (AbstractActionableWithClientException $exception) {
            Logger::log($exception);
            throw new AbstractActionableWithClientException(
                AbstractActionableWithClientException::GENERATE_CLIENT_ERROR_MESSAGE_GRACEFUL,
                AbstractActionableWithClientException::GENERATE_CLIENT_ERROR_CODE
            );
        }
    }

    /**
     * @throws EntityValidationException
     */
    protected function validateGeneralDataRequest(): void
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

            throw new WpEntityValidationException(
                WpEntityValidationException::DATA_NOT_SET_MESSAGE,
                WpEntityValidationException::DATA_NOT_SET_CODE,
                null,
                $this->violations
            );
        }
    }
}
