<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Ajax\Public;

use Exception;
use Throwable;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Exception\PostbackUpdateException;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancing;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancingValueObject;
use WeGetFinancing\Checkout\PostMeta\OrderInvIdValueObject;
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
    public const SIGNATURE_ALGO = "sha256";

    protected string $version;

    public function __construct($version)
    {
        $this->version = $version;
    }

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
            $data = $this->getSignedData($request);
            $array = $this->getValidData($data);
            $args = [
                'meta_key' => OrderInvIdValueObject::ORDER_META,
                'meta_value' => $array[self::INV_ID_FIELD],
                'post_type' => 'shop_order',
                'post_status' => 'any',
                'posts_per_page' => 1,
            ];
            $posts = get_posts($args);

            $order = wc_get_order($posts[0]->ID);

            $order->update_status($this->getStatus($array[self::STATUS_FIELD]));

            echo "OK";
            die();
        } catch (Throwable $exception) {
            error_log(self::class . "::action() Error:");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
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
     * @return array
     * @throws PostbackUpdateException
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
        } else {
            $result[self::STATUS_FIELD] = sanitize_text_field($data[self::UPDATES_FIELD][self::STATUS_FIELD]);

            if (false === in_array($result[self::STATUS_FIELD], self::VALID_STATUSES, true)) {
                throw new PostbackUpdateException(
                    PostbackUpdateException::INVALID_REQUEST_INVALID_STATUS_ERROR_MESSAGE . " " .
                    $data[self::UPDATES_FIELD][self::STATUS_FIELD],
                    PostbackUpdateException::INVALID_REQUEST_INVALID_STATUS_ERROR_CODE
                );
            }
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
     * @return array
     * @throws PostbackUpdateException
     * @throws Exception
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
        $username = WeGetFinancing::getOptions()[WeGetFinancingValueObject::USERNAME_FIELD_ID];
        $password = WeGetFinancing::getOptions()[WeGetFinancingValueObject::PASSWORD_FIELD_ID];

        $string = hash(
            self::SIGNATURE_ALGO,
            $timestamp . $username . $body . $password,
            false
        );

        return $signature == $string;
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
}
