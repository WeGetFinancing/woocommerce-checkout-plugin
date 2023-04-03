<?php

namespace WeGetFinancing\Checkout\Shortcode;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WeGetFinancing\Checkout\Ajax\Public\ConfigurePpeAjax;

class WeGetFinancingPpe
{
    public const INIT_NAME = 'wegetfinancing-ppe';

    protected Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function init(): void
    {
        add_shortcode(self::INIT_NAME, [$this, 'execute']);
    }

    /**
     * @param mixed $attributes
     * @param mixed $content
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function execute(mixed $attributes, mixed $content = ""): string
    {
        return $this->twig->render(
            'view/store',
            [
                "ajaxUrl" => admin_url('admin-ajax.php'),
                "ajaxAction" => ConfigurePpeAjax::INIT_NAME
            ]
        );
    }
}
