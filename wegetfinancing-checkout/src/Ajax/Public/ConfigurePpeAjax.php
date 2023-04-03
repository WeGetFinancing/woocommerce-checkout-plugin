<?php

namespace WeGetFinancing\Checkout\Ajax\Public;

use WeGetFinancing\Checkout\AbstractActionableWithClient;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\Exception\GenerateClientException;
use WeGetFinancing\Checkout\Exception\GetFunnelRequestException;
use WeGetFinancing\Checkout\Wp\AddableTrait;
use WeGetFinancing\SDK\Entity\Request\LoanRequestEntity;
use WeGetFinancing\SDK\Exception\EntityValidationException;

class ConfigurePpeAjax extends AbstractActionableWithClient
{
    use AddableTrait;
    public const ACTION_NAME = 'generateWeGetFinancingFunnelAction';
    public const INIT_NAME = 'wp_ajax_nopriv_' . self::ACTION_NAME;
    public const FUNCTION_NAME = 'execute';
    public const SOFTWARE_NAME = 'WordPress-WooCommerce';

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

    protected function ajaxRespondJson(array $responseArray): void
    {
        echo json_encode($responseArray);
        wp_die();
    }
}
