<?php

namespace WeGetFinancing\Checkout\Ajax\Public;

use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\Exception\GenerateFunnelClientException;
use WeGetFinancing\Checkout\Exception\GetFunnelRequestException;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancing;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancingValueObject;
use WeGetFinancing\Checkout\Wp\AddableTrait;
use WeGetFinancing\SDK\Client;;

use WeGetFinancing\SDK\Entity\AuthEntity;
use WeGetFinancing\SDK\Entity\Request\LoanRequestEntity;
use WeGetFinancing\SDK\Exception\EntityValidationException;

class GenerateFunnelUrl implements ActionableInterface
{
    use AddableTrait;
    public const ACTION_NAME = 'generateWeGetFinancingFunnelAction';
    public const INIT_NAME = 'wp_ajax_nopriv_' . self::ACTION_NAME;
    public const FUNCTION_NAME = 'execute';
    public const SOFTWARE_NAME = 'WordPress-WooCommerce';
    public const GENERATE_FUNNEL_ERROR_TABLE = [
        'firstName' => [
            'fields' => ['billing_first_name'],
            'message' => '<strong>Billing First name</strong>'
        ],
        'lastName' => [
            'fields' => ['billing_last_name'],
            'message' => '<strong>Billing Last name</strong>'
        ],
        'street1' => [
            'fields' => ['billing_address_1', 'billing_address_2'],
            'message' => '<strong>Billing Street address</strong>'
        ],
        'city' => [
            'fields' => ['billing_city'],
            'message' => '<strong>Billing Town / City</strong>'
        ],
        'state' => [
            'fields' => ['billing_state'],
            'message' => '<strong>Billing State</strong>'
        ],
        'zipcode' => [
            'fields' => ['billing_postcode'],
            'message' => '<strong>Billing ZIP Code</strong>'
        ],
        'phone' => [
            'fields' => ['billing_phone'],
            'message' => '<strong>Billing ZIP Code</strong>'
        ],
        'email' => [
            'fields' => ['billing_phone'],
            'message' => '<strong>Billing Email address</strong>'
        ],
    ];

    protected string $apiUrlProduction;

    protected string $apiUrlSandbox;


    public function __construct(
        string $apiUrlProduction,
        string $apiUrlSandbox
    ) {
        $this->apiUrlProduction = $apiUrlProduction;
        $this->apiUrlSandbox = $apiUrlSandbox;
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
            $response = $client->requestNewLoan($request);
            $data = $response->getData();

            if (true === $response->getIsSuccess()) {
                $this->ajaxRespondJson([
                    'isSuccess' => true,
                    'invId' => $data['invId'],
                    'href' => $data['href']
                ]);
            }

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
        } catch (GenerateFunnelClientException $exception) {
            $this->ajaxRespondJson([
                'isSuccess' => false,
                'message' => '<strong>' . translate(
                        GenerateFunnelClientException::GRACEFUL_ERROR_MESSAGE,
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
     * @return LoanRequestEntity
     * @throws EntityValidationException
     * @throws GetFunnelRequestException
     */
    protected function getRequest(): LoanRequestEntity
    {
        try {
            $data = $_POST['data'];
            $violations = [];
            if (false === array_key_exists('billing_first_name', $data) ||
                true === empty($data['billing_first_name'])) {
                $violations[] = [
                    'field' => 'firstName',
                    'message' => 'firstName cannot be empty.'
                ];
            }
            if (false === array_key_exists('billing_last_name', $data) ||
                true === empty($data['billing_last_name'])) {
                $violations[] = [
                    'field' => 'lastName',
                    'message' => 'lastName cannot be empty.'
                ];
            }
            if (false === array_key_exists('billing_email', $data) ||
                true === empty($data['billing_email'])) {
                $violations[] = [
                    'field' => 'email',
                    'message' => 'email cannot be empty.'
                ];
            }
            if (false === array_key_exists('billing_phone', $data) ||
                true === empty($data['billing_phone'])) {
                $violations[] = [
                    'field' => 'phone',
                    'message' => 'phone cannot be empty.'
                ];
            }
            if (false === empty($violations)) {
                throw new EntityValidationException(
                    'Invalid generate funnel request',
                    11,
                    null,
                    $violations
                );
            }


            $customer = WC()->cart->get_customer();
            $cartItems = [];

            foreach ( WC()->cart->get_cart() as $item ) {
                $product = $item['data'];

                $terms = get_the_terms( $product->get_id(), 'product_cat' );
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

            global $wp_version;

            $requestArray = [
                'first_name' => $data['billing_first_name'],
                'last_name' => $data['billing_last_name'],
                'shipping_amount' => WC()->cart->get_shipping_total(),
                'version' => '1.9',
                'email' => $data['billing_email'],
                'phone' => $data['billing_phone'],
                'merchant_transaction_id' => '**',
                'success_url' => '',
                'failure_url' => '',
                'postback_url' => PostbackUpdate::getPostbackUpdateUrl(),
                'software_name' => self::SOFTWARE_NAME,
                'software_version' => $wp_version . '-' . constant('WOOCOMMERCE_VERSION'),
                'software_plugin_version' => '-',
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
                'cart_items' => $cartItems
            ];

            return LoanRequestEntity::make($requestArray);
        } catch (EntityValidationException $exception) {
            error_log("GenerateFunnelUrl::getRequestArray entity validation error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            error_log(json_encode($exception->getViolations()));
            throw $exception;
        } catch (\Throwable $exception) {
            error_log("GenerateFunnelUrl::getRequestArray unexpected error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            throw new GetFunnelRequestException(
                GetFunnelRequestException::GET_POST_REQUEST_ERROR_MESSAGE,
                GetFunnelRequestException::GET_POST_REQUEST_ERROR_CODE
            );
        }
    }

    /**
     * @return Client
     * @throws GenerateFunnelClientException
     */
    protected function generateClient(): Client
    {
        try {
            $options = WeGetFinancing::getOptions();

            $auth = AuthEntity::make([
                'username' => $options[WeGetFinancingValueObject::USERNAME_FIELD_ID],
                'password'  => $options[WeGetFinancingValueObject::PASSWORD_FIELD_ID],
                'merchantId' => $options[WeGetFinancingValueObject::MERCHANT_ID_FIELD_ID],
                'url' => "yes" === $options[WeGetFinancingValueObject::IS_SANDBOX_FIELD_ID]
                    ? $this->apiUrlSandbox
                    : $this->apiUrlProduction
            ]);

            return Client::Make($auth);
        } catch (EntityValidationException $exception) {
            error_log("GenerateFunnelUrl::generateClient entity validation error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            error_log(json_encode($exception->getViolations()));
            throw new GenerateFunnelClientException(
                GenerateFunnelClientException::GENERATE_CLIENT_VALIDATION_ERROR_MESSAGE,
                GenerateFunnelClientException::GENERATE_CLIENT_VALIDATION_ERROR_CODE
            );
        } catch (\Throwable $exception) {
            error_log("GenerateFunnelUrl::generateClient unexpected error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            throw new GenerateFunnelClientException(
                GenerateFunnelClientException::GENERATE_CLIENT_UNEXPECTED_ERROR_MESSAGE,
                GenerateFunnelClientException::GENERATE_CLIENT_UNEXPECTED_ERROR_CODE
            );
        }
    }

    protected function ajaxRespondJson(array $responseArray): void
    {
        echo json_encode($responseArray);
        wp_die();
    }
}
