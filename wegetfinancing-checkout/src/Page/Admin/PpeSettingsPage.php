<?php

namespace WeGetFinancing\Checkout\Page\Admin;

use Twig\Environment;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\ActionableInterface;

class PpeSettingsPage implements ActionableInterface
{
    public const PAGE_TITLE = 'WeGetFinancing PPE Configuration Dashboard';
    public const MENU_TITLE = 'WeGetFinancing PPE';
    public const CAPABILITY = 'manage_options';
    public const MENU_SLUG = 'wgf-ppe-dashboard';
    public const METHOD_RENDERER = 'render';
    public const ICON = 'dashicons-schedule';
    public const PAGE_TEMPLATE = 'admin/ppe_settings.twig';

    protected Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function init(): void
    {
        add_action(
            'admin_menu',
            [$this, 'execute']
        );
    }
    public function execute(): void
    {
        wp_enqueue_style(
            'bootstrap5css',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css'
        );
        wp_enqueue_style(
            'bootstrap5ico',
            'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css'
        );
        wp_enqueue_script(
            'bootstrap5',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js',
            ['jquery'],
            '',
            true
        );
        add_menu_page(
            __(self::PAGE_TITLE, App::DOMAIN_LOCALE),
            __(self::MENU_TITLE, App::DOMAIN_LOCALE),
            self::CAPABILITY,
            self::MENU_SLUG,
            [$this, self::METHOD_RENDERER],
            self::ICON
        );
    }

    public function render(): void
    {
        echo $this->twig->render(
            self::PAGE_TEMPLATE,
            [
                'pageTitle' => self::PAGE_TITLE
            ]
        );
    }

}
