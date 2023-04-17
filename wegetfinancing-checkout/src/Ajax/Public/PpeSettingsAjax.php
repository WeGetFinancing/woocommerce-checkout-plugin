<?php

namespace WeGetFinancing\Checkout\Ajax\Public;

use Exception;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\Exception\GenerateClientException;
use WeGetFinancing\Checkout\Exception\GetFunnelRequestException;
use WeGetFinancing\Checkout\ValueObject\PpeSettings;
use WeGetFinancing\Checkout\Wp\AddableTrait;
use WeGetFinancing\Checkout\Repository\PpeSettings as PpeSettingsRepository;
use WeGetFinancing\SDK\Exception\EntityValidationException;

class PpeSettingsAjax implements ActionableInterface
{
    use AddableTrait;
    public const ACTION_NAME = 'upsertPpeSettingsAction';
    public const INIT_NAME = 'wp_ajax_nopriv_' . self::ACTION_NAME;
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
                $this->ajaxRespondJson([
                    'isSuccess' => false,
                    'violations' => $violations
                ]);
            }

            PpeSettingsRepository::setOption(
                PpeSettings::PRICE_ELECTOR,
                $_POST['data'][PpeSettings::PRICE_ELECTOR]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::PRODUCT_NAME_SELECTOR,
                $_POST['data'][PpeSettings::PRODUCT_NAME_SELECTOR]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::MERCHANT_TOKEN,
                $_POST['data'][PpeSettings::MERCHANT_TOKEN]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::MINIMUM_AMOUNT,
                $_POST['data'][PpeSettings::MINIMUM_AMOUNT]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::CUSTOM_TEXT,
                $_POST['data'][PpeSettings::CUSTOM_TEXT]
            );

            PpeSettingsRepository::setOption(
                PpeSettings::POSITION,
                $_POST['data'][PpeSettings::POSITION]
            );



            error_log("GenerateFunnelUrl::execute request new loan funnel error.");

            error_log(print_r($data, true));
            $this->ajaxRespondJson([
                'isSuccess' => false,
                'message' => '<strong>Remote server error</strong>'
            ]);
        } catch (EntityValidationException $exception) {
            $violations = [];
            foreach ($exception->getViolations() as $violation) {
                if (false === array_key_exists($violation['field'], self::GENERATE_FUNNEL_ERROR_TABLE)) {
                    $violations['generic'] = [
                        'fields' => [],
                        'messages' => '<strong>Internal server error</strong>'
                    ];
                    continue;
                }
                $violations[$violation['field']]['fields'] =
                    self::GENERATE_FUNNEL_ERROR_TABLE[$violation['field']]['fields'];
                $violations[$violation['field']]['messages'][] = str_replace(
                    $violation['field'],
                    self::GENERATE_FUNNEL_ERROR_TABLE[$violation['field']]['message'],
                    $violation['message']
                );
            }
            $this->ajaxRespondJson([
                'isSuccess' => false,
                'violations' => $violations
            ]);
        } catch (GenerateClientException $exception) {
            $this->ajaxRespondJson([
                'isSuccess' => false,
                'message' => '<strong>' . translate(
                        GenerateClientException::GRACEFUL_ERROR_MESSAGE,
                        App::DOMAIN_LOCALE
                    ) . '</strong>'
            ]);
        } catch (GetFunnelRequestException $exception) {
            $this->ajaxRespondJson([
                'isSuccess' => false,
                'message' => '<strong>' . translate(
                        GetFunnelRequestException::GRACEFUL_ERROR_MESSAGE,
                        App::DOMAIN_LOCALE
                    ) . '</strong>'
            ]);
        } catch (\Throwable $exception) {
            error_log("GenerateFunnelUrl::execute unexpected error.");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            $this->ajaxRespondJson([
                'isSuccess' => false,
                'message' => '<strong>Unexpected error</strong>'
            ]);
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

            if (false === array_key_exists(PpeSettings::PRICE_ELECTOR, $data) ||
                true === empty($data[PpeSettings::PRICE_ELECTOR])) {
                $violations[] = [
                    'field' => PpeSettings::PRICE_ELECTOR,
                    'message' => PpeSettings::PRICE_ELECTOR . ' cannot be empty.'
                ];
            }

            if (false === array_key_exists(PpeSettings::PRODUCT_NAME_SELECTOR, $data) ||
                true === empty($data[PpeSettings::PRODUCT_NAME_SELECTOR])) {
                $violations[] = [
                    'field' => PpeSettings::PRODUCT_NAME_SELECTOR,
                    'message' => PpeSettings::PRODUCT_NAME_SELECTOR . ' cannot be empty.'
                ];
            }

            if (false === array_key_exists(PpeSettings::MERCHANT_TOKEN, $data) ||
                true === empty($data[PpeSettings::MERCHANT_TOKEN])) {
                $violations[] = [
                    'field' => PpeSettings::MERCHANT_TOKEN,
                    'message' => PpeSettings::MERCHANT_TOKEN . ' cannot be empty.'
                ];
            }

            if (false === array_key_exists(PpeSettings::MINIMUM_AMOUNT, $data) ||
                true === empty($data[PpeSettings::MINIMUM_AMOUNT])) {
                $violations[] = [
                    'field' => PpeSettings::MINIMUM_AMOUNT,
                    'message' => PpeSettings::MINIMUM_AMOUNT . ' cannot be empty.'
                ];
            }

            if (false === array_key_exists(PpeSettings::POSITION, $data) ||
                true === empty($data[PpeSettings::POSITION])) {
                $violations[] = [
                    'field' => PpeSettings::POSITION,
                    'message' => PpeSettings::POSITION . ' cannot be empty.'
                ];
            }

            if (false === in_array($data[PpeSettings::POSITION], PpeSettings::VALID_POSITIONS)) {
                $violations[] = [
                    'field' => PpeSettings::PRICE_ELECTOR,
                    'message' => PpeSettings::PRICE_ELECTOR . ' is not a valid position.'
                ];
            }

            return $violations;

        } catch (\Throwable $exception) {
            error_log(self::class . "::validateRequest unexpected error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            throw new Exception(self::class . "::validateRequest unexpected error");
        }
    }

    protected function ajaxRespondJson(array $responseArray): void
    {
        echo json_encode($responseArray);
        wp_die();
    }
}
