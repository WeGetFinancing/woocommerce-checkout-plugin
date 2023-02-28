<?php

namespace WeGetFinancing\Checkout\PostStatus;

use DateTime;
use Functional\Entity\Request\UpdateShippingStatusRequestEntityTest;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Exception\GenerateFunnelClientException;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancing;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancingValueObject;
use WeGetFinancing\Checkout\PostMeta\OrderInvIdValueObject;
use WeGetFinancing\Checkout\Wp\AddableTrait;
use WeGetFinancing\SDK\Client;
use WeGetFinancing\SDK\Entity\AuthEntity;
use WeGetFinancing\SDK\Entity\Request\UpdateShippingStatusRequestEntity;
use WeGetFinancing\SDK\Exception\EntityValidationException;

class OnOrderStatusChangeToShipped implements ActionableInterface
{
    use AddableTrait;
    public const INIT_NAME = 'woocommerce_order_status_changed';
    public const FUNCTION_NAME = 'execute';
    public const STATUS_ALREADY_SHIPPED_META = "wgf_wc_is_already_shipped";

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
        $this->addAction(10, 3);
    }

    public function execute($order_id, $old_status, $new_status): void
    {
        if ('shipped' === $new_status) {
            $hasAlreadyShipped = get_post_meta($order_id, self::STATUS_ALREADY_SHIPPED_META, true);
            if ('yes' === $hasAlreadyShipped) {
                return;
            }

            $invId = get_post_meta($order_id,'_' . OrderInvIdValueObject::ORDER_INV_ID_FIELD_ID,true);
            
            if (true === empty($invId)) {
                return;
            }
            
            $client = $this->generateClient();

            $updateRequest = UpdateShippingStatusRequestEntity::make([
                'shippingStatus' => UpdateShippingStatusRequestEntity::STATUS_SHIPPED,
                'trackingId' => '-',
                'trackingCompany' => '-',
                'deliveryDate' => (new DateTime())->modify('+1 day')->format('Y-m-d'),
                'invId' => $invId
            ]);

            $response = $client->updateStatus($updateRequest);

            if (true === $response->getIsSuccess()) {
                update_post_meta(
                    $order_id,
                    self::STATUS_ALREADY_SHIPPED_META,
                    'yes'
                );
                return;
            }

            error_log("OnOrderStatusChangeToShipped::execute Error code " . $response->getCode());
            error_log("OnOrderStatusChangeToShipped::execute Error data " .
                print_r($response->getData(), true));

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
            error_log("OnOrderStatusChangeToShipped::generateClient entity validation error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            error_log(json_encode($exception->getViolations()));
            throw new GenerateFunnelClientException(
                GenerateFunnelClientException::GENERATE_CLIENT_VALIDATION_ERROR_MESSAGE,
                GenerateFunnelClientException::GENERATE_CLIENT_VALIDATION_ERROR_CODE
            );
        } catch (\Throwable $exception) {
            error_log("OnOrderStatusChangeToShipped::generateClient unexpected error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
            throw new GenerateFunnelClientException(
                GenerateFunnelClientException::GENERATE_CLIENT_UNEXPECTED_ERROR_MESSAGE,
                GenerateFunnelClientException::GENERATE_CLIENT_UNEXPECTED_ERROR_CODE
            );
        }
    }
}
