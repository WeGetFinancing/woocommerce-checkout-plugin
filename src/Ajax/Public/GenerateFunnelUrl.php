<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Ajax\Public;

if (!defined( 'ABSPATH' )) exit;

use WeGetFinancing\Checkout\AbstractActionableWithClient;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\Exception\GenerateClientException;
use WeGetFinancing\Checkout\Exception\GetFunnelRequestException;
use WeGetFinancing\Checkout\Service\RequestValidatorUtility;
use WeGetFinancing\Checkout\ValueObject\GeneralDataRequest;
use WeGetFinancing\Checkout\ValueObject\GenerateFunnelUrlRequest;
use WeGetFinancing\Checkout\Wp\AddableTrait;
use WeGetFinancing\SDK\Entity\Request\LoanRequestEntity;
use WeGetFinancing\SDK\Exception\EntityValidationException;

class GenerateFunnelUrl extends AbstractActionableWithClient
{
    use AddableTrait;

    public const ACTION_NAME = 'generateWeGetFinancingFunnelAction';
    public const INIT_NAME = 'wp_ajax_nopriv_' . self::ACTION_NAME;
    public const FUNCTION_NAME = 'execute';
    public const REMOTE_SERVER_ERROR_HTML_MESSAGE = '<strong>Remote server error</strong>';
    public const INTERNAL_SERVER_ERROR_HTML_MESSAGE = '<strong>Internal server error</strong>';
    public const GENERATE_FUNNEL_ERROR_TABLE = [
        'general' => [
            'fields' => [GeneralDataRequest::DATA],
            'message' => 'An <strong>Invalid Request</strong>',
        ],
        'firstName' => [
            'fields' => [GenerateFunnelUrlRequest::BILLING_FIRST_NAME_ID],
            'message' => '<strong>Billing First name</strong>',
        ],
        'lastName' => [
            'fields' => [GenerateFunnelUrlRequest::BILLING_LAST_NAME_ID],
            'message' => '<strong>Billing Last name</strong>',
        ],
        'street1' => [
            'fields' => [GenerateFunnelUrlRequest::BILLING_ADDRESS_1_ID,],
            'message' => '<strong>Billing Street address</strong>',
        ],
        'city' => [
            'fields' => [GenerateFunnelUrlRequest::BILLING_CITY_ID],
            'message' => '<strong>Billing Town / City</strong>',
        ],
        'state' => [
            'fields' => [GenerateFunnelUrlRequest::BILLING_STATE_ID],
            'message' => '<strong>Billing State</strong>',
        ],
        'zipcode' => [
            'fields' => [GenerateFunnelUrlRequest::BILLING_POSTCODE_ID],
            'message' => '<strong>Billing ZIP Code</strong>',
        ],
        'phone' => [
            'fields' => [GenerateFunnelUrlRequest::BILLING_PHONE_ID],
            'message' => '<strong>Billing Phone</strong>',
        ],
        'email' => [
            'fields' => [GenerateFunnelUrlRequest::BILLING_EMAIL_ID],
            'message' => '<strong>Billing Email address</strong>',
        ],
        'shipping_different' => [
            'fields' => [GenerateFunnelUrlRequest::SHIPPING_DIFFERENT_ID],
            'message' => '<strong>Shipping Address</strong>',
        ]
    ];

    public function __construct(
        protected string $apiVersion,
        protected string $softwareName,
        protected $softwarePluginVersion
    ) {
    }

    public function init(): void
    {
        $this->addAction();
    }

    public function execute(): void
    {
        try {
            $client = $this->generateClient();
            $request = $this->getRequest();
            $loanRequest = $this->getLoanRequest($request);
            $response = $client->requestNewLoan($loanRequest);
            $data = $response->getData();

            if (true === $response->getIsSuccess()) {
                wp_send_json(
                    [
                        'isSuccess' => true,
                        'invId' => $data['invId'],
                        'href' => $data['href']
                    ],
                    200
                );
            }

            error_log(self::class . "::execute() Remote error requesting new loan url. Request:");
            error_log(print_r($data, true));

            wp_send_json(
                [
                    'isSuccess' => false,
                    'message' => self::REMOTE_SERVER_ERROR_HTML_MESSAGE,
                ],
                200
            );
        } catch (EntityValidationException $exception) {
            $violations = [];
            foreach ($exception->getViolations() as $violation) {
                if (false === array_key_exists($violation['field'], self::GENERATE_FUNNEL_ERROR_TABLE)) {
                    $violations['generic'] = [
                        'fields' => [],
                        'messages' => self::INTERNAL_SERVER_ERROR_HTML_MESSAGE,
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
            wp_send_json(
                [
                    'isSuccess' => false,
                    'violations' => $violations,
                ],
                200
            );
        } catch (GenerateClientException | GetFunnelRequestException $exception) {
            wp_send_json(
                [
                    'isSuccess' => false,
                    'message' => '<strong>' . __(
                        'Unexpected network error',
                        'wegetfinancing-payment-gateway'
                    ) . '</strong>',
                ],
                200
            );
        } catch (\Throwable $exception) {
            error_log(self::class . "::execute unexpected error.");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            wp_send_json(
                [
                    'isSuccess' => false,
                    'message' => '<strong>' . __(
                            'Unexpected error',
                            'wegetfinancing-payment-gateway'
                        ) . '</strong>',
                ],
                200
            );
        }
    }

    /**
     * @throws EntityValidationException
     * @return array
     */
    protected function getRequest(): array
    {
        $this->validateGeneralDataRequest();
        $generalData = $this->getGeneralDataFromRequest();
        $addressData = $this->getAddressDataFromRequest();

        if (false === empty($this->violations)) {
            throw new EntityValidationException(
                'Invalid generate funnel request',
                11,
                null,
                $this->violations
            );
        }

        return array_merge($generalData, $addressData);
    }

    protected function getGeneralDataFromRequest(): array
    {
        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $_POST[GeneralDataRequest::DATA],
                GenerateFunnelUrlRequest::BILLING_FIRST_NAME_ID
            )
        ) {
            $this->violations[] = [
                'field' => 'firstName',
                'message' => 'firstName cannot be empty.',
            ];
        }
        $result[GenerateFunnelUrlRequest::BILLING_FIRST_NAME_ID] = sanitize_text_field(
            $_POST[GeneralDataRequest::DATA][GenerateFunnelUrlRequest::BILLING_FIRST_NAME_ID]
        );

        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $_POST[GeneralDataRequest::DATA],
                GenerateFunnelUrlRequest::BILLING_LAST_NAME_ID
            )
        ) {
            $this->violations[] = [
                'field' => 'lastName',
                'message' => 'lastName cannot be empty.',
            ];
        }
        $result[GenerateFunnelUrlRequest::BILLING_LAST_NAME_ID] = sanitize_text_field(
            $_POST[GeneralDataRequest::DATA][GenerateFunnelUrlRequest::BILLING_LAST_NAME_ID]
        );

        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $_POST[GeneralDataRequest::DATA],
                GenerateFunnelUrlRequest::BILLING_EMAIL_ID
            )
        ) {
            $this->violations[] = [
                'field' => 'email',
                'message' => 'email cannot be empty.',
            ];
        } else {
            $result[GenerateFunnelUrlRequest::BILLING_EMAIL_ID] = sanitize_email(
                $_POST[GeneralDataRequest::DATA][GenerateFunnelUrlRequest::BILLING_EMAIL_ID]
            );
            if (true === empty($result[GenerateFunnelUrlRequest::BILLING_EMAIL_ID])) {
                $this->violations[] = [
                    'field' => 'email',
                    'message' => 'email must be a valid e-mail.',
                ];
            }
        }

        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $_POST[GeneralDataRequest::DATA],
                GenerateFunnelUrlRequest::BILLING_PHONE_ID
            )
        ) {
            $this->violations[] = [
                'field' => 'phone',
                'message' => 'phone cannot be empty.',
            ];
        }
        $result[GenerateFunnelUrlRequest::BILLING_PHONE_ID] = sanitize_text_field(
            $_POST[GeneralDataRequest::DATA][GenerateFunnelUrlRequest::BILLING_PHONE_ID]
        );

        return $result;
    }

    protected function getAddressDataFromRequest(): array
    {
        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $_POST[GeneralDataRequest::DATA],
                GenerateFunnelUrlRequest::BILLING_ADDRESS_1_ID
            )
        ) {
            $this->violations[] = [
                'field' => 'street1',
                'message' => 'street1 cannot be empty.',
            ];
        }
        $billingAddress = sanitize_text_field(
            $_POST[GeneralDataRequest::DATA][GenerateFunnelUrlRequest::BILLING_ADDRESS_1_ID]
        );
        $billingAddress2 = true === array_key_exists(
                GenerateFunnelUrlRequest::BILLING_ADDRESS_2_ID,
                $_POST[GeneralDataRequest::DATA]
            )
            ? sanitize_text_field($_POST[GeneralDataRequest::DATA][GenerateFunnelUrlRequest::BILLING_ADDRESS_2_ID])
            : '';
        $billingAddress = (true === empty($billingAddress2))
            ? $billingAddress
            : $billingAddress . ' ' . $billingAddress2;

        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $_POST[GeneralDataRequest::DATA],
                GenerateFunnelUrlRequest::BILLING_CITY_ID
            )
        ) {
            $this->violations[] = [
                'field' => 'city',
                'message' => 'city cannot be empty.',
            ];
        }
        $billingCity = sanitize_text_field($_POST[GeneralDataRequest::DATA][GenerateFunnelUrlRequest::BILLING_CITY_ID]);

        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $_POST[GeneralDataRequest::DATA],
                GenerateFunnelUrlRequest::BILLING_STATE_ID
            )
        ) {
            $this->violations[] = [
                'field' => 'state',
                'message' => 'state cannot be empty.',
            ];
        }
        $billingState = sanitize_text_field(
            $_POST[GeneralDataRequest::DATA][GenerateFunnelUrlRequest::BILLING_STATE_ID]
        );

        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $_POST[GeneralDataRequest::DATA],
                GenerateFunnelUrlRequest::BILLING_POSTCODE_ID
            )
        ) {
            $this->violations[] = [
                'field' => 'zipcode',
                'message' => 'zipcode cannot be empty.',
            ];
        }
        $billingZipcode = sanitize_text_field(
            $_POST[GeneralDataRequest::DATA][GenerateFunnelUrlRequest::BILLING_POSTCODE_ID]
        );

        $request['billingAddress'] = [
            'street1' => $billingAddress,
            'city' => $billingCity,
            'state' => $billingState,
            'zipcode' => $billingZipcode,
        ];

        $request['shippingAddress'] = [
            'street1' => $billingAddress,
            'city' => $billingCity,
            'state' => $billingState,
            'zipcode' => $billingZipcode,
        ];

        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $_POST[GeneralDataRequest::DATA],
                GenerateFunnelUrlRequest::SHIPPING_DIFFERENT_ID
            )
        ) {
            $this->violations[] = [
                'field' => 'shipping_different',
                'message' => 'Invalid request, please contact our customer support.',
            ];
        }

        $shippingToDifferent = sanitize_text_field(
            $_POST[GeneralDataRequest::DATA][GenerateFunnelUrlRequest::SHIPPING_DIFFERENT_ID]
        );
        if ('true' === $shippingToDifferent) {
            $this->violations[] = [
                'field' => 'shipping_different',
                'message' => 'shipping_different has to be the same of billing one.',
            ];
        }

        return $request;
    }

    /**
     * @param array $request
     * @throws EntityValidationException
     * @throws GetFunnelRequestException
     * @return LoanRequestEntity
     */
    protected function getLoanRequest(array $request): LoanRequestEntity
    {
        try {
            $cartItems = [];

            foreach (WC()->cart->get_cart() as $item) {
                $product = $item['data'];

                $terms = get_the_terms($product->get_id(), 'product_cat');
                $category = '';
                foreach ($terms as $term) {
                    $category = $term->name;
                }

                $cartItems[] = [
                    'sku' => true === empty($product->get_sku()) ? 'not_defined' : $product->get_sku(),
                    'displayName' => $product->get_name(),
                    'unitPrice' => (string) $item['line_subtotal'] / $item['quantity'],
                    'quantity' => (int) $item['quantity'],
                    'unitTax' => (string) $item['line_subtotal_tax'] / $item['quantity'],
                    'category' => $category,
                ];
            }

            $requestArray = [
                'first_name' => $request[GenerateFunnelUrlRequest::BILLING_FIRST_NAME_ID],
                'last_name' => $request[GenerateFunnelUrlRequest::BILLING_LAST_NAME_ID],
                'shipping_amount' => WC()->cart->get_shipping_total(),
                'version' => $this->apiVersion,
                'email' => $request[GenerateFunnelUrlRequest::BILLING_EMAIL_ID],
                'phone' => $request[GenerateFunnelUrlRequest::BILLING_PHONE_ID],
                'merchant_transaction_id' => '**',
                'success_url' => '',
                'failure_url' => '',
                'postback_url' => PostbackUpdate::getPostbackUpdateUrl(),
                'software_name' => $this->softwareName,
                'software_version' => $this->getSoftwareVersion(),
                'software_plugin_version' => $this->softwarePluginVersion,
                'billing_address' => $request['billingAddress'],
                'shipping_address' => $request['shippingAddress'],
                'cart_items' => $cartItems,
            ];

            return LoanRequestEntity::make($requestArray);
        } catch (EntityValidationException $exception) {
            error_log(self::class . "::getLoanRequest() entity validation error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            error_log(json_encode($exception->getViolations()));
            throw $exception;
        } catch (\Throwable $exception) {
            error_log(self::class . "::getLoanRequest() unexpected error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            throw new GetFunnelRequestException(
                GetFunnelRequestException::GET_POST_REQUEST_ERROR_MESSAGE,
                GetFunnelRequestException::GET_POST_REQUEST_ERROR_CODE
            );
        }
    }

    protected function getSoftwareVersion(): string
    {
        global $wp_version;
        return $wp_version . '-' . constant('WOOCOMMERCE_VERSION');
    }
}
