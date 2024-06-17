<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Ajax\Public;

if (!defined( 'ABSPATH' )) exit;

use Exception;
use Throwable;
use WC_Order;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Exception\PostbackUpdateException;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancing;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancingValueObject;
use WeGetFinancing\Checkout\PostMeta\OrderInvIdValueObject;
use WeGetFinancing\Checkout\Service\Logger;
use WeGetFinancing\Checkout\Service\RequestValidatorUtility;
use WeGetFinancing\Checkout\Wp\AddableTrait;
use WP_REST_Request;

class PostbackUpdate implements ActionableInterface
{
    use AddableTrait;

    public const INIT_NAME = 'rest_api_init';
    public const FUNCTION_NAME = 'execute';
    public const REST_PREFIX = "/?rest_route=/";
    public const REST_NAMESPACE = 'wegetfinancing/v1';
    public const REST_ROUTE = '/postback/';
    public const METHOD = 'POST';
    public const VERSION_FIELD = "version";
    public const INV_ID_FIELD = "request_token";
    public const UPDATES_FIELD = "updates";
    public const STATUS_FIELD = "status";
    public const AMOUNT_FIELD = "amount";
    public const TRANSACTION_ID_FIELD = "merchant_transaction_id";
    public const WGF_APPROVED_STATUS = "approved";
    public const WGF_PREAPPROVED_STATUS = "preapproved";
    public const WGF_REJECTED_STATUS = "rejected";
    public const WGF_REFUND_STATUS = "refund";
    public const VALID_STATUSES = [
        self::WGF_APPROVED_STATUS,
        self::WGF_PREAPPROVED_STATUS,
        self::WGF_REJECTED_STATUS,
        self::WGF_REFUND_STATUS,
    ];
    public const WC_PROCESSING_STATUS = "wc-processing";
    public const WC_FAILED_STATUS = "wc-failed";
    public const WC_REFUNDED_STATUS = "refunded";
    public const REFUND_REASON = "Order refunded from WeGetFinancing";
    public const SIGNATURE_ALGO = "sha256";
    public const QUERY_COLUMN = 'post_id';

    protected object $wpdb;

    public function init(): void
    {
        $this->addAction();
    }

    public function execute()
    {
        register_rest_route(
            self::REST_NAMESPACE,
            self::REST_ROUTE,
            [
                'methods' => self::METHOD,
                'callback' => [$this, 'action'],
                'permission_callback' => function () {
                    return true;
                },
            ]
        );
    }

    /**
     * @param WP_REST_Request $request
     */
    public function action(WP_REST_Request $request): void
    {
        try {
            set_time_limit(60);

            $raw = $this->getSignedData($request);
            $data = $this->getValidData($raw);

            global $wpdb;
            $this->wpdb = $wpdb;

            $order = $this->getOrderWhereInvId($data[self::INV_ID_FIELD]);

            $status = $this->getStatus($data[self::STATUS_FIELD]);

            if (self::WC_REFUNDED_STATUS === $status) {
                $this->refundOrder($order, $raw);
            } else {
                $order->update_status($status);
            }

            echo "OK";
            die();
        } catch (Throwable $exception) {
            Logger::log($exception);
            echo "NO";
            die();
        }
    }

    public static function getPostbackUpdateUrl(): string
    {
        return get_site_url() . self::REST_PREFIX . self::REST_NAMESPACE . self::REST_ROUTE;
    }

    /**
     * @param array $data
     * @throws PostbackUpdateException
     * @return array
     */
    protected function getValidData(array $data): array
    {
        $result = [];

        if (RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty($data, self::VERSION_FIELD)) {
            throw new PostbackUpdateException(
                PostbackUpdateException::INVALID_REQUEST_EMPTY_INV_ID_ERROR_MESSAGE,
                PostbackUpdateException::INVALID_REQUEST_EMPTY_INV_ID_ERROR_CODE
            );
        }
        $result[self::VERSION_FIELD] = sanitize_text_field($data[self::VERSION_FIELD]);

        if (RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty($data, self::INV_ID_FIELD)) {
            throw new PostbackUpdateException(
                PostbackUpdateException::INVALID_REQUEST_EMPTY_INV_ID_ERROR_MESSAGE,
                PostbackUpdateException::INVALID_REQUEST_EMPTY_INV_ID_ERROR_CODE
            );
        }
        $result[self::INV_ID_FIELD] = sanitize_text_field($data[self::INV_ID_FIELD]);

        if (RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty($data, self::UPDATES_FIELD)) {
            throw new PostbackUpdateException(
                PostbackUpdateException::INVALID_REQUEST_EMPTY_UPDATES_ERROR_MESSAGE,
                PostbackUpdateException::INVALID_REQUEST_EMPTY_UPDATES_ERROR_CODE
            );
        }
        $result[self::UPDATES_FIELD] = sanitize_text_field($data[self::UPDATES_FIELD]);

        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $data[self::UPDATES_FIELD],
                self::STATUS_FIELD
            )
        ) {
            throw new PostbackUpdateException(
                PostbackUpdateException::INVALID_REQUEST_EMPTY_STATUS_ERROR_MESSAGE,
                PostbackUpdateException::INVALID_REQUEST_EMPTY_STATUS_ERROR_CODE
            );
        }
        $result[self::STATUS_FIELD] = sanitize_text_field($data[self::UPDATES_FIELD][self::STATUS_FIELD]);

        if (false === in_array($result[self::STATUS_FIELD], self::VALID_STATUSES, true)) {
            throw new PostbackUpdateException(
                PostbackUpdateException::INVALID_REQUEST_INVALID_STATUS_ERROR_MESSAGE . " " .
                $data[self::UPDATES_FIELD][self::STATUS_FIELD],
                PostbackUpdateException::INVALID_REQUEST_INVALID_STATUS_ERROR_CODE
            );
        }

        if (RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty($data, self::TRANSACTION_ID_FIELD)) {
            throw new PostbackUpdateException(
                PostbackUpdateException::INVALID_REQUEST_EMPTY_TRANSACTION_ID_ERROR_MESSAGE,
                PostbackUpdateException::INVALID_REQUEST_EMPTY_TRANSACTION_ID_ERROR_CODE
            );
        }
        $result[self::TRANSACTION_ID_FIELD] = sanitize_text_field($data[self::TRANSACTION_ID_FIELD]);

        return $result;
    }

    /**
     * @param WP_REST_Request $request
     * @throws PostbackUpdateException
     * @throws Exception
     * @return array
     */
    protected function getSignedData(WP_REST_Request $request): array
    {
        $signature = $request->get_header('x-signature');
        $timestamp = $request->get_header('x-timestamp');
        $body = $request->get_body();

        if (false === $this->verifySignature($signature, $body, $timestamp)) {
            throw new Exception("Invalid Signature");
        }

        if (true === empty($body)) {
            throw new PostbackUpdateException(
                PostbackUpdateException::EMPTY_BODY_ERROR_MESSAGE,
                PostbackUpdateException::EMPTY_BODY_ERROR_CODE
            );
        }

        $data = json_decode($body, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new PostbackUpdateException(
                json_last_error() . " - " . json_last_error_msg(),
                PostbackUpdateException::JSON_DECODE_ERROR_CODE
            );
        }

        return $data;
    }

    protected function verifySignature(string $signature, string $body, string $timestamp): bool
    {
        $username = WeGetFinancing::getOption(WeGetFinancingValueObject::USERNAME_FIELD_ID);
        $password = WeGetFinancing::getOption(WeGetFinancingValueObject::PASSWORD_FIELD_ID);

        $string = hash(
            self::SIGNATURE_ALGO,
            $timestamp . $username . $body . $password,
            false
        );

        return $signature === $string;
    }

    protected function getStatus(string $status): bool|string
    {
        return match ($status) {
            self::WGF_APPROVED_STATUS => self::WC_PROCESSING_STATUS,
            self::WGF_PREAPPROVED_STATUS => WeGetFinancingValueObject::ON_HOLD_STATUS_ID,
            self::WGF_REJECTED_STATUS => self::WC_FAILED_STATUS,
            self::WGF_REFUND_STATUS => self::WC_REFUNDED_STATUS,
            default => false,
        };
    }

    /**
     * @param string $invId
     * @return array
     * @throws PostbackUpdateException
     */
    protected function selectOrderIdWhereInvId(string $invId): array
    {
        $sql = $this->wpdb->prepare(
            "SELECT post_id FROM {$this->wpdb->prefix}postmeta WHERE meta_key = '" .
            OrderInvIdValueObject::ORDER_META . "' AND meta_value = %s",
            $invId
        );

        $results = $this->wpdb->get_results($sql);
        if (false === is_array($results)) {
            throw new PostbackUpdateException(
                PostbackUpdateException::INVALID_SQL_RESULT_ERROR_MESSAGE,
                PostbackUpdateException::INVALID_SQL_RESULT_ERROR_CODE
            );
        }

        return $results;
    }

    /**
     * @throws PostbackUpdateException
     */
    protected function getOrderWhereInvId(string $invId): WC_Order
    {
        $results = $this->selectOrderIdWhereInvId($invId);
        $found = count($results);

        if (0 === $found) {
            sleep(15);
            $results = $this->selectOrderIdWhereInvId($invId);
            $found = count($results);

            if (0 === $found) {
                sleep(30);
                $results = $this->selectOrderIdWhereInvId($invId);
                $found = count($results);

                if (0 === $found) {
                    throw new PostbackUpdateException(
                        PostbackUpdateException::ORDER_NOT_FOUND_ERROR_MESSAGE . $invId,
                        PostbackUpdateException::ORDER_NOT_FOUND_ERROR_CODE
                    );
                }
            }
        }

        if (1 < $found) {
            throw new PostbackUpdateException(
                PostbackUpdateException::MULTIPLE_ORDERS_FOUND_ERROR_MESSAGE . $invId,
                PostbackUpdateException::MULTIPLE_ORDERS_FOUND_ERROR_CODE
            );
        }

        if (false === property_exists($results[0], self::QUERY_COLUMN)) {
            throw new PostbackUpdateException(
                PostbackUpdateException::INVALID_RESULT_ORDER_ERROR_MESSAGE . $invId,
                PostbackUpdateException::INVALID_RESULT_ORDER_ERROR_CODE
            );
        }

        $order = wc_get_order($results[0]->{self::QUERY_COLUMN});
        if (false === $order instanceof WC_Order) {
            throw new PostbackUpdateException(
                PostbackUpdateException::INVALID_POST_ID_ERROR_MESSAGE . $results[0]->{self::QUERY_COLUMN},
                PostbackUpdateException::INVALID_POST_ID_ERROR_CODE
            );
        }

        return $order;
    }

    /**
     * @throws Exception
     */
    protected function refundOrder(WC_Order $order, array $raw): void
    {
        if( self::WC_REFUNDED_STATUS == $order->get_status() ) {
            return;
        }

        if (
            RequestValidatorUtility::checkIfArrayKeyNotExistsOrEmpty(
                $raw[self::UPDATES_FIELD],
                self::AMOUNT_FIELD
            )
        ) {
            throw new PostbackUpdateException(
                PostbackUpdateException::INVALID_REFUND_REQUEST_EMPTY_AMOUNT_ERROR_MESSAGE,
                PostbackUpdateException::INVALID_REFUND_REQUEST_EMPTY_AMOUNT_ERROR_CODE
            );
        }
        $amount = sanitize_text_field($raw[self::UPDATES_FIELD][self::AMOUNT_FIELD]);

        wc_create_refund([
            'amount'         => wc_format_decimal($amount),
            'reason'         => self::REFUND_REASON,
            'order_id'       => $order->get_id(),
            'line_items'     => [],
            'refund_payment' => false,
            'restock_items'  => true,
        ]);
    }
}
