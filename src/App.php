<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout;

if (!defined( 'ABSPATH' )) exit;

use Exception;
use WeGetFinancing\Checkout\Service\Logger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Throwable;
use WeGetFinancing\Checkout\Exception\AppException;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class App implements ActionableInterface
{
    use AddableTrait;

    public const ID = 'wegetfinancing';
    public const PLUGIN_VERSION = '1.4.2';
    public const INTEGRATION_NAME = 'WordPress-WooCommerce';
    public const CONFIG_DIR = 'etc';
    public const DEFAULT_SERVICE_XML_FILE = 'services.xml';
    public const INIT_LIST_PARAMS = 'app.init_list';
    public const RENDER = 'twig';
    public const FUNNEL_JS = 'app.funnel.js';
    public const CHECKOUT_LOGO_URL = 'app.checkout_logo_url';
    public const CHECKOUT_BUTTON_URL = 'app.checkout_button.url';
    public const INIT_NAME = 'init';
    public const FUNCTION_NAME = 'execute';

    protected ContainerInterface $container;

    /**
     * @throws Exception
     */
    public function __construct(private string $basePath, private string $filePath)
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
                $this->container->setParameter('app.file_path', $filePath);
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
            Logger::log($exception);
            Logger::log(new AppException(
            AppException::CONSTRUCT_ERROR_MESSAGE . Logger::getDecorativeData(),
            AppException::CONSTRUCT_ERROR_CODE
            ));
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
            self::CHECKOUT_BUTTON_URL => plugins_url(
                $this->container->getParameter(self::CHECKOUT_BUTTON_URL),
                $this->filePath
            ),
            self::CHECKOUT_LOGO_URL => plugins_url(
                $this->container->getParameter(self::CHECKOUT_LOGO_URL),
                $this->filePath
            )
        ];

        $initList = $this->container->getParameter(self::INIT_LIST_PARAMS);
        foreach ($initList as $init) {
            try {
                /** @var ActionableInterface $obj */
                $obj = $this->container->get($init);
                $obj->init();
            } catch (Throwable $exception) {
                Logger::log($exception);
                throw new AppException(
                    AppException::INIT_ERROR_MESSAGE . Logger::getDecorativeData(),
                    AppException::INIT_ERROR_CODE)
                ;
            }
        }
    }

    static public function getIntegrationVersion(): string
    {
        global $wp_version;
        return $wp_version . '-' . constant('WOOCOMMERCE_VERSION');
    }
}
