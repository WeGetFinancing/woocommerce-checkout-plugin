<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Ajax\Admin;

use Exception;
use Throwable;
use WeGetFinancing\Checkout\AbstractActionableWithClient;
use WeGetFinancing\Checkout\Service\RequestValidatorUtility;
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

            PpeSettingsRepository::setOption(
                PpeSettings::PRICE_SELECTOR_ID,
                $this->data[PpeSettings::PRICE_SELECTOR_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::PRODUCT_NAME_SELECTOR_ID,
                $this->data[PpeSettings::PRODUCT_NAME_SELECTOR_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::MERCHANT_TOKEN_ID,
                $this->data[PpeSettings::MERCHANT_TOKEN_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::MINIMUM_AMOUNT_ID,
                $this->data[PpeSettings::MINIMUM_AMOUNT_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::POSITION_ID,
                $this->data[PpeSettings::POSITION_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::CUSTOM_TEXT_ID,
                $this->data[PpeSettings::CUSTOM_TEXT_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::IS_DEBUG_ID,
                $this->data[PpeSettings::IS_DEBUG_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::IS_BRANDED_ID,
                $this->data[PpeSettings::IS_BRANDED_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::IS_APPLY_NOW_ID,
                $this->data[PpeSettings::IS_APPLY_NOW_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::PPE_IS_CONFIGURED,
                true
            );

            wp_send_json(
                ['isSuccess' => true],
                201
            );
        } catch (Throwable $exception) {
            error_log(self::class . "::execute() unexpected error.");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
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
            $data = $_POST['data'];
            $this->data = [];
            $this->violations = [];

            if (RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty($data, PpeSettings::PRICE_SELECTOR_ID)) {
                $this->violations[] = [
                    'field' => PpeSettings::PRICE_SELECTOR_ID,
                    'message' => '<b>' . PpeSettings::PRICE_SELECTOR_NAME . '</b> cannot be empty.',
                ];
            } else {
                $this->data[PpeSettings::PRICE_SELECTOR_ID] = sanitize_text_field(
                    $data[PpeSettings::PRICE_SELECTOR_ID]
                );
            }

            if (
                RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                    $data,
                    PpeSettings::PRODUCT_NAME_SELECTOR_ID
                )
            ) {
                $this->violations[] = [
                    'field' => PpeSettings::PRODUCT_NAME_SELECTOR_ID,
                    'message' => '<b>' . PpeSettings::PRODUCT_NAME_SELECTOR_NAME . '</b> cannot be empty.',
                ];
            } else {
                $this->data[PpeSettings::PRODUCT_NAME_SELECTOR_ID] = sanitize_text_field(
                    $data[PpeSettings::PRODUCT_NAME_SELECTOR_ID]
                );
            }

            if (RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty($data, PpeSettings::MERCHANT_TOKEN_ID)) {
                $this->violations[] = [
                    'field' => PpeSettings::MERCHANT_TOKEN_ID,
                    'message' => '<b>' . PpeSettings::MERCHANT_TOKEN_NAME . '</b> cannot be empty.',
                ];
            } else {
                $this->data[PpeSettings::MERCHANT_TOKEN_ID] = sanitize_text_field(
                    $data[PpeSettings::MERCHANT_TOKEN_ID]
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

            if (RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty($data, PpeSettings::MINIMUM_AMOUNT_ID)) {
                $this->violations[] = [
                    'field' => PpeSettings::MINIMUM_AMOUNT_ID,
                    'message' => '<b>' . PpeSettings::MINIMUM_AMOUNT_NAME . '</b> cannot be empty.',
                ];
            } else {
                $this->data[PpeSettings::MINIMUM_AMOUNT_ID] = sanitize_text_field(
                    $data[PpeSettings::MINIMUM_AMOUNT_ID]
                );
            }

            if (RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty($data, PpeSettings::POSITION_ID)) {
                $this->violations[] = [
                    'field' => PpeSettings::POSITION_ID,
                    'message' => '<b>' . PpeSettings::POSITION_NAME . '</b> cannot be empty.',
                ];
            } else {
                $this->data[PpeSettings::POSITION_ID] = sanitize_text_field($data[PpeSettings::POSITION_ID]);

                if (false === in_array($this->data[PpeSettings::POSITION_ID], PpeSettings::VALID_POSITIONS, true)) {
                    $this->violations[] = [
                        'field' => PpeSettings::POSITION_ID,
                        'message' => '<b>' . PpeSettings::POSITION_NAME . '</b> "' . $data[PpeSettings::POSITION_ID] .
                            '" is not a valid position.',
                    ];
                }
            }

            $this->data[PpeSettings::CUSTOM_TEXT_ID] =
                RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty($data, PpeSettings::CUSTOM_TEXT_ID)
                    ? ''
                    : sanitize_text_field($data[PpeSettings::CUSTOM_TEXT_ID]);

            $this->data[PpeSettings::IS_DEBUG_ID] = true === isset($_POST['data'][PpeSettings::IS_DEBUG_ID]) &&
                "true" === sanitize_text_field($_POST['data'][PpeSettings::IS_DEBUG_ID]);

            $this->data[PpeSettings::IS_BRANDED_ID] = true === isset($_POST['data'][PpeSettings::IS_BRANDED_ID]) &&
                "true" === sanitize_text_field($_POST['data'][PpeSettings::IS_BRANDED_ID]);

            $this->data[PpeSettings::IS_APPLY_NOW_ID] = true === isset($_POST['data'][PpeSettings::IS_APPLY_NOW_ID]) &&
                "true" === sanitize_text_field($_POST['data'][PpeSettings::IS_APPLY_NOW_ID]);
        } catch (Throwable $exception) {
            error_log(self::class . "::validateRequest unexpected error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            throw new Exception(self::class . "::validateRequest unexpected error");
        }
    }
}
