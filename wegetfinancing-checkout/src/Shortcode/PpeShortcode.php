<?php

namespace WeGetFinancing\Checkout\Shortcode;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class PpeShortcode
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
        wp_enqueue_style(
            'wegetfinancing_ppe_css',
            'https://ppe.sandbox.dev.wegetfinancing.com/index.css',
            [],
            null)
        ;
        wp_enqueue_script(
            'wegetfinancing_ppe_js',
            'https://ppe.sandbox.dev.wegetfinancing.com/index.js',
            ['jquery'],
            null,
            false
        );

        return $this->twig->render(
            'store/ppe_shortcode.twig',
            [
                "ajaxUrl" => admin_url('admin-ajax.php')
            ]
        );
    }
}
