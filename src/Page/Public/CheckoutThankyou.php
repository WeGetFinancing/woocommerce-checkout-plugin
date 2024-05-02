<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Page\Public;

if (!defined( 'ABSPATH' )) exit;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancing;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancingValueObject;
use WeGetFinancing\Checkout\Repository\PpeSettingsRepository;
use WeGetFinancing\Checkout\ValueObject\PpeSettings;

class CheckoutThankyou implements ActionableInterface
{
    public const INIT_NAME = 'woocommerce_thankyou';
    public const PAGE_TEMPLATE = 'store/checkout_thankyou.twig';

    public function __construct(
        protected Environment $twig,
    ) {
    }

    public function init(): void
    {
        add_action(self::INIT_NAME, [$this, 'execute']);
    }

    /**
     * @param mixed $order_id
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function execute(mixed $order_id): void
    {
        wp_enqueue_script(
            WeGetFinancingValueObject::HANDLE_FUNNEL_SCRIPT, $GLOBALS[App::ID][App::FUNNEL_JS],
            ['jquery'],
            null,
            true
        );

        echo $this->twig->render(
            self::PAGE_TEMPLATE,
            [
                'wgf_href' => get_post_meta($order_id, 'wgf_href', true),
            ]
        );
    }
}
