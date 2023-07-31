<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout;

if (!defined( 'ABSPATH' )) exit;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Throwable;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class App implements ActionableInterface
{
    use AddableTrait;

    public const ID = 'wegetfinancing';
    public const PLUGIN_FILE_NAME = 'wegetfinancing-checkout.php';
    public const CONFIG_DIR = 'etc';
    public const DEFAULT_SERVICE_XML_FILE = 'services.xml';
    public const INIT_LIST_PARAMS = 'app.init_list';
    public const RENDER = 'twig';
    public const FUNNEL_JS = 'app.funnel.js';
    public const CHECKOUT_BUTTON_URL = 'app.checkout_button.url';
    public const INIT_NAME = 'init';
    public const FUNCTION_NAME = 'execute';

    protected ContainerInterface $container;

    /**
     * @throws Exception
     */
    public function __construct(string $basePath)
    {
        try {
            $plugins = apply_filters('active_plugins', get_option('active_plugins' ));
            if (true === in_array('woocommerce/woocommerce.php', $plugins)) {
                $containerBuilder = new ContainerBuilder();
                $loader = new XmlFileLoader(
                    $containerBuilder,
                    new FileLocator($basePath . DIRECTORY_SEPARATOR . self::CONFIG_DIR)
                );
                $loader->load(self::DEFAULT_SERVICE_XML_FILE);
                $this->container = $containerBuilder;
                $this->container->setParameter('app.base_path', $basePath);
                $this->init();
                return;
            }
            add_action(
                'admin_notices',
                function() {
                    echo '<div class="error"><p>WeGetFinancing Checkout Plugin ' .
                        'cannot work if WooCommerce Plugin is not activated.</p></div>';
                }
            );
        } catch (Throwable $exception) {
            error_log(self::class . "::__construct unexpected error");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));
        }
    }

    public function init(): void
    {
        $this->addAction();
    }

    /**
     * @throws Throwable
     */
    public function execute(): void
    {
        $GLOBALS[self::ID] = [
            self::RENDER => $this->container->get(self::RENDER),
            self::FUNNEL_JS => $this->container->getParameter(self::FUNNEL_JS),
            self::CHECKOUT_BUTTON_URL => $this->container->getParameter(self::CHECKOUT_BUTTON_URL),
        ];

        $initList = $this->container->getParameter(self::INIT_LIST_PARAMS);
        foreach ($initList as $init) {
            try {
                /** @var ActionableInterface $obj */
                $obj = $this->container->get($init);
                $obj->init();
            } catch (Throwable $exception) {
                error_log(self::class . "::execute() Error initializing object " . $init);
                throw $exception;
            }
        }
    }
}
