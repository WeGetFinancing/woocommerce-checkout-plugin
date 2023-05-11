<?php

namespace WeGetFinancing\Checkout\Ajax\Admin;

use Exception;
use Throwable;
use WeGetFinancing\Checkout\AbstractActionableWithClient;
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

    public function init(): void
    {
        $this->addAction();
    }

    public function execute(): void
    {
        try {
            $violations = $this->validateRequest();

            if (false === empty($violations)) {
                wp_send_json(
                    [
                        'isSuccess' => false,
                        'violations' => $violations
                    ],
                    200
                );
            }

            PpeSettingsRepository::setOption(
                PpeSettings::PRICE_SELECTOR_ID,
                $_POST['data'][PpeSettings::PRICE_SELECTOR_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::PRODUCT_NAME_SELECTOR_ID,
                $_POST['data'][PpeSettings::PRODUCT_NAME_SELECTOR_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::MERCHANT_TOKEN_ID,
                $_POST['data'][PpeSettings::MERCHANT_TOKEN_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::MINIMUM_AMOUNT_ID,
                $_POST['data'][PpeSettings::MINIMUM_AMOUNT_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::POSITION_ID,
                $_POST['data'][PpeSettings::POSITION_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::CUSTOM_TEXT_ID,
                true === isset($_POST['data'][PpeSettings::CUSTOM_TEXT_ID])
                    ? $_POST['data'][PpeSettings::CUSTOM_TEXT_ID]
                    : ''
            );

            PpeSettingsRepository::setOption(
                PpeSettings::IS_DEBUG_ID,
                true === isset($_POST['data'][PpeSettings::IS_DEBUG_ID]) &&
                    "true" === $_POST['data'][PpeSettings::IS_DEBUG_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::IS_BRANDED_ID,
                true === isset($_POST['data'][PpeSettings::IS_BRANDED_ID]) &&
                "true" === $_POST['data'][PpeSettings::IS_BRANDED_ID]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::IS_APPLY_NOW_ID,
                true === isset($_POST['data'][PpeSettings::IS_APPLY_NOW_ID]) &&
                "true" === $_POST['data'][PpeSettings::IS_APPLY_NOW_ID]
            );

            wp_send_json(
                ['isSuccess' => true],
                201
            );
        } catch (Throwable $exception) {
            error_log(self::class . "::execute() unexpected error.");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            wp_send_json([], 500);
        }
    }

    /**
     * @throws Exception
     */
    protected function validateRequest(): array
    {
        try {
            $data = $_POST['data'];
            $violations = [];

            if ($this->checkIfArrayKeyNotExistsOrEmpty($data, PpeSettings::PRICE_SELECTOR_ID)) {
                $violations[] = [
                    'field' => PpeSettings::PRICE_SELECTOR_ID,
                    'message' => '<b>' . PpeSettings::PRICE_SELECTOR_NAME . '</b> cannot be empty.'
                ];
            }

            if ($this->checkIfArrayKeyNotExistsOrEmpty($data, PpeSettings::PRODUCT_NAME_SELECTOR_ID)) {
                $violations[] = [
                    'field' => PpeSettings::PRODUCT_NAME_SELECTOR_ID,
                    'message' => '<b>' .PpeSettings::PRODUCT_NAME_SELECTOR_NAME . '</b> cannot be empty.'
                ];
            }

            if ($this->checkIfArrayKeyNotExistsOrEmpty($data, PpeSettings::MERCHANT_TOKEN_ID)) {
                $violations[] = [
                    'field' => PpeSettings::MERCHANT_TOKEN_ID,
                    'message' => '<b>' .PpeSettings::MERCHANT_TOKEN_NAME . '</b> cannot be empty.'
                ];
            }

            $client = $this->generateClient();
            $response = $client->testPpe($data[PpeSettings::MERCHANT_TOKEN_ID]);
            if (PpeClient::TEST_ERROR_RESPONSE === $response['status'] ||
                PpeClient::TEST_EMPTY_RESPONSE === $response['status']) {
                $violations[] = [
                    'field' => PpeSettings::MERCHANT_TOKEN_ID,
                    'message' => '<b>' .PpeSettings::MERCHANT_TOKEN_NAME . '</b> Error: ' . $response['message']
                ];
            }

            if ($this->checkIfArrayKeyNotExistsOrEmpty($data, PpeSettings::MINIMUM_AMOUNT_ID)) {
                $violations[] = [
                    'field' => PpeSettings::MINIMUM_AMOUNT_ID,
                    'message' => '<b>' .PpeSettings::MINIMUM_AMOUNT_NAME . '</b> cannot be empty.'
                ];
            }

            if ($this->checkIfArrayKeyNotExistsOrEmpty($data, PpeSettings::POSITION_ID)) {
                $violations[] = [
                    'field' => PpeSettings::POSITION_ID,
                    'message' =>'<b>' .  PpeSettings::POSITION_NAME . '</b> cannot be empty.'
                ];
            } elseif (false === in_array($data[PpeSettings::POSITION_ID], PpeSettings::VALID_POSITIONS)) {
                $violations[] = [
                    'field' => PpeSettings::POSITION_ID,
                    'message' => '<b>' . PpeSettings::POSITION_NAME . '</b> "' . $data[PpeSettings::POSITION_ID] .
                        '" is not a valid position.'
                ];
            }

            return $violations;

        } catch (Throwable $exception) {
            error_log(self::class . "::validateRequest unexpected error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            throw new Exception(self::class . "::validateRequest unexpected error");
        }
    }

    public function checkIfArrayKeyNotExistsOrEmpty(array $data, string $field): bool
    {
        return (false === array_key_exists($field, $data) ||
            true === empty($data[$field]));
    }
}
