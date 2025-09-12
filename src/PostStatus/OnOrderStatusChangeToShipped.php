<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\PostStatus;

if (!defined( 'ABSPATH' )) exit;

use DateTime;
use WeGetFinancing\Checkout\AbstractActionableWithClient;
use WeGetFinancing\Checkout\Exception\OnOrderStatusChangeToShippedException;
use WeGetFinancing\Checkout\Service\Logger;
use WeGetFinancing\Checkout\ValueObject\PostMeta\OrderInvIdFieldVO;
use WeGetFinancing\Checkout\Wp\AddableTrait;
use WeGetFinancing\SDK\Entity\Request\UpdateShippingStatusRequestEntity;

class OnOrderStatusChangeToShipped extends AbstractActionableWithClient
{
    use AddableTrait;

    public const INIT_NAME = 'woocommerce_order_status_changed';
    public const FUNCTION_NAME = 'execute';
    public const STATUS_ALREADY_SHIPPED_META = "wgf_wc_is_already_shipped";

    public function init(): void
    {
        $this->addAction(10, 3);
    }

    public function execute($order_id, $old_status, $new_status): void
    {
        if ('shipped' === $new_status) {
            try {
                $hasAlreadyShipped = get_post_meta($order_id, self::STATUS_ALREADY_SHIPPED_META, true);
                if ('yes' === $hasAlreadyShipped) {
                    return;
                }

                $invId = get_post_meta($order_id, OrderInvIdFieldVO::META, true);

                if (true === empty($invId)) {
                    return;
                }

                $client = $this->generateClient();

                $request = [
                    'shippingStatus' => UpdateShippingStatusRequestEntity::STATUS_SHIPPED,
                    'trackingId' => '-',
                    'trackingCompany' => '-',
                    'deliveryDate' => (new DateTime())->modify('+1 day')->format('Y-m-d'),
                    'invId' => $invId,
                ];

                $updateRequest = UpdateShippingStatusRequestEntity::make($request);

                $response = $client->updateStatus($updateRequest);

                if (true === $response->getIsSuccess()) {
                    update_post_meta(
                        $order_id,
                        self::STATUS_ALREADY_SHIPPED_META,
                        'yes'
                    );
                    return;
                }

                Logger::log(new OnOrderStatusChangeToShippedException(
                    sprintf(
                        OnOrderStatusChangeToShippedException::REMOTE_ERROR_MESSAGE,
                        $response->getCode(),
                        json_encode($request),
                        json_encode($response->getData())
                    ) . Logger::getDecorativeData(),
                    OnOrderStatusChangeToShippedException::REMOTE_ERROR_CODE
                ));
            } catch (\Throwable $exception) {
                Logger::log($exception);
            }

        }
    }
}
