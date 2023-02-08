<?php

namespace WeGetFinancing\WCP\Ajax\Public;

use WeGetFinancing\SDK\Client;
use WeGetFinancing\SDK\Entity\Request\AuthRequestEntity;
use WeGetFinancing\SDK\Entity\Request\LoanRequestEntity;
use WeGetFinancing\SDK\Exception\EntityValidationException;
use WeGetFinancing\WCP\Exception\GenerateFunnelUrlException;

class GenerateFunnelUrl
{
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

    public function init()
    {
        add_action(
            'wp_ajax_nopriv_generateWeGetFinancingFunnelAction',
            [ $this, "generateFunnelAction" ]
        );
    }

    public function generateFunnelAction()
    {
        try {
            $data = $_POST['data'];
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
                'postback_url' => '',
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

            $client = $this->generateClient();
            $request = LoanRequestEntity::make($requestArray);
            $response = $client->requestNewLoan($request);

            if (true === $response->getIsSuccess()) {
                return $this->ajaxRespondJson([
                    'isSuccess' => true,
                    'invId' => $response->getSuccess()->getInvId(),
                    'href' => $response->getSuccess()->getHref()
                ]);
            }

            error_log($response->getError()->getError());
            error_log(print_r($response->getError()->getMessage(), true));
            return $this->ajaxRespondJson([
                'isSuccess' => false,
                'message' => '<strong>Remote server error</strong>'
            ]);
        } catch (EntityValidationException $exception) {
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            error_log(json_encode($exception->getViolations()));

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

            return $this->ajaxRespondJson([
                'isSuccess' => false,
                'violations' => $violations
            ]);
        } catch (\Throwable $exception) {
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            return $this->ajaxRespondJson([
                'isSuccess' => false,
                'message' => '<strong>Unexpected server error</strong>'
            ]);
        }
    }

    /**
     * @return Client
     * @throws GenerateFunnelUrlException
     */
    protected function generateClient(): Client
    {
        try {
            $auth = AuthRequestEntity::make([
                'username' => getenv('WEGETFINANCING_CHECKOUT_USERNAME'),
                'password'  => getenv('WEGETFINANCING_CHECKOUT_PASSWORD'),
                'merchantId' => getenv('WEGETFINANCING_CHECKOUT_MERCHANT_ID'),
                'url' => getenv('WEGETFINANCING_CHECKOUT_URL')
            ]);

            return Client::Make($auth);
        } catch (EntityValidationException $exception) {
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            error_log(json_encode($exception->getViolations()));
            throw new GenerateFunnelUrlException(
                GenerateFunnelUrlException::GENERATE_CLIENT_ERROR_MESSAGE,
                GenerateFunnelUrlException::GENERATE_CLIENT_ERROR_CODE
            );
        } catch (\Throwable $exception) {
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            throw new GenerateFunnelUrlException(
                GenerateFunnelUrlException::GENERATE_CLIENT_ERROR_MESSAGE,
                GenerateFunnelUrlException::GENERATE_CLIENT_ERROR_CODE
            );
        }
    }

    protected function ajaxRespondJson(array $responseArray)
    {
        echo json_encode($responseArray);
        wp_die();
    }
}
