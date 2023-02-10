<?php

namespace WeGetFinancing\Checkout;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use WeGetFinancing\Checkout\Wp\AddableTrait;

class App extends AbstractAction
{
    use AddableTrait;

    public const ACTION_NAME = 'init';
    public const DEFAULT_SERVICE_XML_FILE = 'services.xml';
    public const INIT_LIST_PARAMS = 'initList';

    protected ContainerInterface $container;

    /**
     * @throws Exception
     */
    public function __construct(string $basePath)
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new XmlFileLoader($containerBuilder, new FileLocator($basePath));
        $loader->load(self::DEFAULT_SERVICE_XML_FILE);
        $this->container = $containerBuilder;
        $this->init();
    }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $initList = $this->container->getParameter(self::INIT_LIST_PARAMS);
        foreach ($initList as $init) {
            $this->container->get($init);
        }
    }
}
