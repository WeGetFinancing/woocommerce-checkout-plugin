<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Ajax\Public;

if (!defined( 'ABSPATH' )) exit;

use Exception;
use Throwable;
use WC_Order;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Service\Logger;
use WeGetFinancing\Checkout\Wp\AddableTrait;
use WP_REST_Request;

class OrderUnsuccess implements ActionableInterface
{
    use AddableTrait;

    public const INIT_NAME = 'rest_api_init';
    public const FUNCTION_NAME = 'execute';
    public const REST_PREFIX = "/?rest_route=/";
    public const REST_NAMESPACE = 'wegetfinancing/v1';
    public const REST_ROUTE = '/order-unsuccess/';
    public const METHOD = 'POST';
    public const VERSION_FIELD = "version";
    public const INV_ID_FIELD = "request_token";

    protected object $wpdb;

    public function init(): void
    {
        $this->addAction();
    }

    public function execute(): void
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

            $headers = $request->get_headers();
            $body = $request->get_body();
            throw new Exception(json_encode([ 'headers' => $headers, 'body' => $body ], JSON_PRETTY_PRINT));;
            echo "OK";
            die();
        } catch (Throwable $exception) {
            Logger::log($exception);
            echo "NO";
            die();
        }
    }

    public static function getOrderUnsuccessUrl(): string
    {
        return get_site_url() . self::REST_PREFIX . self::REST_NAMESPACE . self::REST_ROUTE;
    }


}
