<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Ajax\Public;

use WeGetFinancing\Checkout\AbstractActionableWithClient;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\Exception\GenerateClientException;
use WeGetFinancing\Checkout\Exception\GetFunnelRequestException;
use WeGetFinancing\Checkout\Service\RequestValidatorUtility;
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
    public const UNEXPECTED_ERROR_HTML_MESSAGE = '<strong>Unexpected error</strong>';
    public const REMOTE_SERVER_ERROR_HTML_MESSAGE = '<strong>Remote server error</strong>';
    public const INTERNAL_SERVER_ERROR_HTML_MESSAGE = '<strong>Internal server error</strong>';
    public const GENERATE_FUNNEL_ERROR_TABLE = [
        'firstName' => [
            'fields' => [GenerateFunnelUrlRequest::BILLING_FIRST_NAME_ID],
            'message' => '<strong>Billing First name</strong>',
        ],
        'lastName' => [
            'fields' => [GenerateFunnelUrlRequest::BILLING_LAST_NAME_ID],
            'message' => '<strong>Billing Last name</strong>',
        ],
        'street1' => [
            'fields' => [
                GenerateFunnelUrlRequest::BILLING_ADDRESS_1_ID, GenerateFunnelUrlRequest::BILLING_ADDRESS_2_ID,
            ],
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
    ];

    protected array $violations = [];

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
                        'href' => $data['href'],
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
        } catch (GenerateClientException $exception) {
            wp_send_json(
                [
                    'isSuccess' => false,
                    'message' => '<strong>' . translate(
                        GenerateClientException::GRACEFUL_ERROR_MESSAGE,
                        App::DOMAIN_LOCALE
                    ) . '</strong>',
                ],
                200
            );
        } catch (GetFunnelRequestException $exception) {
            wp_send_json(
                [
                    'isSuccess' => false,
                    'message' => '<strong>' . translate(
                        GetFunnelRequestException::GRACEFUL_ERROR_MESSAGE,
                        App::DOMAIN_LOCALE
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
                    'message' => self::UNEXPECTED_ERROR_HTML_MESSAGE,
                ],
                200
            );
        }
    }

    /**
     * @throws EntityValidationException
     * @return array
     */
    public function getRequest(): array
    {
        $data = $_POST['data'];
        $result = [];
        $violations = [];

        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $data,
                GenerateFunnelUrlRequest::BILLING_FIRST_NAME_ID
            )
        ) {
            $violations[] = [
                'field' => 'firstName',
                'message' => 'firstName cannot be empty.',
            ];
        }
        $result[GenerateFunnelUrlRequest::BILLING_FIRST_NAME_ID] =
            sanitize_text_field($data[GenerateFunnelUrlRequest::BILLING_FIRST_NAME_ID]);

        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $data,
                GenerateFunnelUrlRequest::BILLING_LAST_NAME_ID
            )
        ) {
            $violations[] = [
                'field' => 'lastName',
                'message' => 'lastName cannot be empty.',
            ];
        }
        $result[GenerateFunnelUrlRequest::BILLING_LAST_NAME_ID] =
            sanitize_text_field($data[GenerateFunnelUrlRequest::BILLING_LAST_NAME_ID]);

        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $data,
                GenerateFunnelUrlRequest::BILLING_EMAIL_ID
            )
        ) {
            $violations[] = [
                'field' => 'email',
                'message' => 'email cannot be empty.',
            ];
        }
        $result[GenerateFunnelUrlRequest::BILLING_EMAIL_ID] =
            sanitize_email($data[GenerateFunnelUrlRequest::BILLING_EMAIL_ID]);

        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $data,
                GenerateFunnelUrlRequest::BILLING_PHONE_ID
            )
        ) {
            $violations[] = [
                'field' => 'phone',
                'message' => 'phone cannot be empty.',
            ];
        }
        $result[GenerateFunnelUrlRequest::BILLING_PHONE_ID] =
            sanitize_text_field($data[GenerateFunnelUrlRequest::BILLING_PHONE_ID]);

        if (false === empty($violations)) {
            throw new EntityValidationException(
                'Invalid generate funnel request',
                11,
                null,
                $violations
            );
        }

        return $result;
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
            $customer = WC()->cart->get_customer();

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
                'billing_address' => [
                    'street1' => $customer->get_billing_address() . ' ' . $customer->get_billing_address_2(),
                    'city' => $customer->get_billing_city(),
                    'state' => $customer->get_billing_state(),
                    'zipcode' => $customer->get_billing_postcode(),
                ],
                'shipping_address' => [
                    'street1' => $customer->get_shipping_state() . ' ' . $customer->get_shipping_address_2(),
                    'city' => $customer->get_shipping_city(),
                    'state' => $customer->get_shipping_state(),
                    'zipcode' => $customer->get_shipping_postcode(),
                ],
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
