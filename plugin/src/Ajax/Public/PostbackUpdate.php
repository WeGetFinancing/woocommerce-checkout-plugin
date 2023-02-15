<?php

namespace WeGetFinancing\WCP\Ajax\Public;

use WeGetFinancing\WCP\PaymentGateway\WeGetFinancing;
use WP_REST_Request;

class PostbackUpdate
{
    public function init(): void
    {
        add_action(
            'rest_api_init',
            function () {
                register_rest_route(
                    'wegetfinancing/v1', '/postback/',
                    [
                        'methods' => 'POST',
                        'callback' => function (WP_REST_Request $request) {
                            return [
                                'option' => get_option("woocommerce_" . WeGetFinancing::GATEWAY_ID . "_settings"),
                                'response' => json_decode($request->get_body())
                            ];
                        },
                        'permission_callback' => function() {
                            return true;
                        },
                    ]
                );
            }
        );
    }
}
