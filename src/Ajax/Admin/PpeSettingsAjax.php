<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Ajax\Admin;

if (!defined( 'ABSPATH' )) exit;

use Exception;
use WeGetFinancing\Checkout\Page\Admin\PpeSettingsPage;
use WeGetFinancing\Checkout\Service\Logger;
use Throwable;
use WeGetFinancing\Checkout\AbstractActionableWithClient;
use WeGetFinancing\Checkout\Exception\PpeSettingsAjaxException;
use WeGetFinancing\Checkout\Service\RequestValidatorUtility;
use WeGetFinancing\Checkout\ValueObject\GeneralDataRequest;
use WeGetFinancing\Checkout\ValueObject\PpeSettings;
use WeGetFinancing\Checkout\Wp\AddableTrait;
use WeGetFinancing\Checkout\Repository\PpeSettingsRepository;
use WeGetFinancing\SDK\Service\PpeClient;

class PpeSettingsAjax extends AbstractActionableWithClient
{
    use AddableTrait;

    public const ACTION_NAME = 'upsertPpeSettingsAction';
    public const INIT_NAME = 'wp_ajax_' . self::ACTION_NAME;
    public const FUNCTION_NAME = 'execute';

    protected array $violations;
    protected array $data;

    public function init(): void
    {
        $this->addAction();
    }

    public function execute(): void
    {
        try {
            $this->securityChecks();
            $this->initRequest();

            if (false === empty($this->violations)) {
                wp_send_json(
                    [
                        'isSuccess' => false,
                        'violations' => $this->violations,
                    ],
                    200
                );
            }

            $this->setOptions();

            wp_send_json(
                ['isSuccess' => true],
                201
            );
        } catch (Throwable $exception) {
            Logger::log($exception);
            PpeSettingsRepository::setOption(
                PpeSettings::PPE_IS_CONFIGURED,
                false
            );
            wp_send_json([], 500);
        }
    }

    /**
     * @throws Exception
     */
    protected function initRequest(): void
    {
        try {
            $this->data = [];

            $this->validateGeneralDataRequest();

            $this->initFieldIfNotEmpty(
                $_POST[GeneralDataRequest::DATA],
                PpeSettings::PRICE_SELECTOR_ID,
                PpeSettings::PRICE_SELECTOR_NAME
            );

            $this->initFieldIfNotEmpty(
                $_POST[GeneralDataRequest::DATA],
                PpeSettings::PRODUCT_NAME_SELECTOR_ID,
                PpeSettings::PRODUCT_NAME_SELECTOR_NAME
            );

            if (
                RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                    $_POST[GeneralDataRequest::DATA],
                    PpeSettings::MERCHANT_TOKEN_ID
                )
            ) {
                $this->violations[] = [
                    'field' => PpeSettings::MERCHANT_TOKEN_ID,
                    'message' => '<b>' . PpeSettings::MERCHANT_TOKEN_NAME . '</b> cannot be empty.',
                ];
            } else {
                $this->data[PpeSettings::MERCHANT_TOKEN_ID] = sanitize_text_field(
                    $_POST[GeneralDataRequest::DATA][PpeSettings::MERCHANT_TOKEN_ID]
                );

                $client = $this->generateClient();
                $response = $client->testPpe($this->data[PpeSettings::MERCHANT_TOKEN_ID]);
                if (
                    PpeClient::TEST_ERROR_RESPONSE === $response['status'] ||
                    PpeClient::TEST_EMPTY_RESPONSE === $response['status']
                ) {
                    $this->violations[] = [
                        'field' => PpeSettings::MERCHANT_TOKEN_ID,
                        'message' => '<b>' . PpeSettings::MERCHANT_TOKEN_NAME . '</b> Error: ' . $response['message'],
                    ];
                }
            }

            $this->initFieldIfNotEmpty(
                $_POST[GeneralDataRequest::DATA],
                PpeSettings::MINIMUM_AMOUNT_ID,
                PpeSettings::MINIMUM_AMOUNT_NAME
            );

            $this->initFieldIfNotEmpty(
                $_POST[GeneralDataRequest::DATA],
                PpeSettings::FONT_SIZE_ID,
                PpeSettings::FONT_SIZE_NAME
            );

            if (
                RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                    $_POST[GeneralDataRequest::DATA],
                    PpeSettings::POSITION_ID
                )
            ) {
                $this->violations[] = [
                    'field' => PpeSettings::POSITION_ID,
                    'message' => '<b>' . PpeSettings::POSITION_NAME . '</b> cannot be empty.',
                ];
            } else {
                $this->data[PpeSettings::POSITION_ID] = sanitize_text_field(
                    $_POST[GeneralDataRequest::DATA][PpeSettings::POSITION_ID]
                );

                if (
                    false === in_array(
                        $this->data[PpeSettings::POSITION_ID],
                        PpeSettings::VALID_POSITIONS,
                        true
                    )
                ) {
                    $this->violations[] = [
                        'field' => PpeSettings::POSITION_ID,
                        'message' => '<b>' . PpeSettings::POSITION_NAME . '</b> "' .
                            $this->data[PpeSettings::POSITION_ID] . '" is not a valid position.',
                    ];
                }
            }

            $this->data[PpeSettings::CUSTOM_TEXT_ID] =
                RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                    $_POST[GeneralDataRequest::DATA],
                    PpeSettings::CUSTOM_TEXT_ID
                )
                    ? ''
                    : sanitize_text_field($_POST[GeneralDataRequest::DATA][PpeSettings::CUSTOM_TEXT_ID]);

            $this->data[PpeSettings::IS_PPE_ACTIVE_ID] = $this->getBooleanFromRequestData(
                PpeSettings::IS_PPE_ACTIVE_ID
            );
            $this->data[PpeSettings::IS_DEBUG_ID] = $this->getBooleanFromRequestData(
                PpeSettings::IS_DEBUG_ID
            );
            $this->data[PpeSettings::IS_BRANDED_ID] = $this->getBooleanFromRequestData(
                PpeSettings::IS_BRANDED_ID
            );
//            PPE Is apply now is disabled till further development
//            $this->data[PpeSettings::IS_APPLY_NOW_ID] = $this->getBooleanFromRequestData(
//                PpeSettings::IS_APPLY_NOW_ID
//            );
            $this->data[PpeSettings::IS_APPLY_NOW_ID] = false;
            $this->data[PpeSettings::IS_HOVER_ID] = $this->getBooleanFromRequestData(
                PpeSettings::IS_HOVER_ID
            );
        } catch (Throwable $exception) {
            Logger::log($exception);
            throw new PpeSettingsAjaxException(
                PpeSettingsAjaxException::VALIDATE_REQUEST_UNEXPECTED_MESSAGE . Logger::getDecorativeData(),
                PpeSettingsAjaxException::VALIDATE_REQUEST_UNEXPECTED_CODE
            );
        }
    }

    protected function initFieldIfNotEmpty(array $data, string $field, string $fieldName):void
    {
        if (RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty($data, $field)) {
            $this->violations[] = [
                'field' => $field,
                'message' => '<b>' . $fieldName . '</b> cannot be empty.',
            ];
        } else {
            $this->data[$field] = sanitize_text_field($data[$field]);
        }
    }

    /**
     * @return void
     * @throws PpeSettingsAjaxException
     */
    protected function securityChecks(): void
    {
        check_admin_referer(PpeSettingsPage::NONCE);

        if (false === current_user_can('administrator')) {
            throw new PpeSettingsAjaxException(
                PpeSettingsAjaxException::NOT_ADMIN_MESSAGE . Logger::getDecorativeData(),
                PpeSettingsAjaxException::NOT_ADMIN_CODE
            );
        }
    }

    protected function setOptions(): void
    {
        $options = [
            PpeSettings::IS_PPE_ACTIVE_ID => $this->data[PpeSettings::IS_PPE_ACTIVE_ID],
            PpeSettings::PRICE_SELECTOR_ID => $this->data[PpeSettings::PRICE_SELECTOR_ID],
            PpeSettings::PRODUCT_NAME_SELECTOR_ID => $this->data[PpeSettings::PRODUCT_NAME_SELECTOR_ID],
            PpeSettings::MERCHANT_TOKEN_ID => $this->data[PpeSettings::MERCHANT_TOKEN_ID],
            PpeSettings::MINIMUM_AMOUNT_ID => $this->data[PpeSettings::MINIMUM_AMOUNT_ID],
            PpeSettings::POSITION_ID => $this->data[PpeSettings::POSITION_ID],
            PpeSettings::CUSTOM_TEXT_ID => $this->data[PpeSettings::CUSTOM_TEXT_ID],
            PpeSettings::IS_DEBUG_ID => $this->data[PpeSettings::IS_DEBUG_ID],
            PpeSettings::IS_BRANDED_ID => $this->data[PpeSettings::IS_BRANDED_ID],
//            Is Apply Now is currently disabled
//            PpeSettings::IS_APPLY_NOW_ID => $this->data[PpeSettings::IS_APPLY_NOW_ID],
            PpeSettings::IS_APPLY_NOW_ID => false,
            PpeSettings::IS_HOVER_ID => $this->data[PpeSettings::IS_HOVER_ID],
            PpeSettings::FONT_SIZE_ID => $this->data[PpeSettings::FONT_SIZE_ID],
            PpeSettings::PPE_IS_CONFIGURED => true,
        ];

        foreach ($options as $key => $value) {
            PpeSettingsRepository::setOption($key, $value);
        }
    }

    protected function getBooleanFromRequestData(string $name): bool
    {
        return true === isset($_POST[GeneralDataRequest::DATA][$name]) &&
            "true" === sanitize_text_field($_POST[GeneralDataRequest::DATA][$name]);
    }
}
