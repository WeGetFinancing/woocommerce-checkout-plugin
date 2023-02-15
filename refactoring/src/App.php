<?php

namespace WeGetFinancing\Checkout;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class App implements ActionableInterface
{
    use AddableTrait;

    public const CONFIG_DIR = 'etc';
    public const DEFAULT_SERVICE_XML_FILE = 'services.xml';
    public const INIT_LIST_PARAMS = 'app.init_list';
    public const INIT_NAME = 'init';
    public const FUNCTION_NAME = 'execute';

    protected ContainerInterface $container;

    /**
     * @throws Exception
     */
    public function __construct(string $basePath)
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new XmlFileLoader(
            $containerBuilder,
            new FileLocator($basePath . DIRECTORY_SEPARATOR . self::CONFIG_DIR)
        );
        $loader->load(self::DEFAULT_SERVICE_XML_FILE);
        $this->container = $containerBuilder;
        $this->container->setParameter('app.base_path', $basePath);
        $this->init();
    }

    public function init():void
    {
        $this->addAction();
    }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $GLOBALS['wegetfinancing_twig'] = $this->container->get('twig');
        $initList = $this->container->getParameter(self::INIT_LIST_PARAMS);
        foreach ($initList as $init) {
            /** @var ActionableInterface $obj */
            $obj = $this->container->get($init);
            $obj->init();
        }
    }
}
