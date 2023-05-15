<?php

namespace WeGetFinancing\Checkout\Page\Admin;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WeGetFinancing\Checkout\Ajax\Admin\PpeSettingsAjax;
use WeGetFinancing\Checkout\App;
use WeGetFinancing\Checkout\ActionableInterface;
use WeGetFinancing\Checkout\Repository\PpeSettingsRepository;
use WeGetFinancing\Checkout\ValueObject\PpeSettings;

class PpeSettingsPage implements ActionableInterface
{
    public const PAGE_TITLE = 'WeGetFinancing PPE Configuration Dashboard';
    public const MENU_TITLE = 'WeGetFinancing PPE';
    public const CAPABILITY = 'manage_options';
    public const MENU_SLUG = 'wgf-ppe-dashboard';
    public const METHOD_RENDERER = 'render';
    public const ICON = 'dashicons-schedule';
    public const PAGE_TEMPLATE = 'admin/ppe_settings.twig';
    public const BOOTSTRAP_STYLE_HANDLE = 'bootstrap5css';
    public const BOOTSTRAP_STYLE_ICONS_HANDLE = 'bootstrap5ico';
    public const BOOTSTRAP_SCRIPT_HANDLE = 'bootstrap5';

    public function __construct(
        protected Environment $twig,
        protected string $bootstrapScript,
        protected string $bootstrapStyle,
        protected string $bootstrapStyleIcons
    ) {}

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
            self::BOOTSTRAP_STYLE_HANDLE,
            $this->bootstrapStyle
        );
        wp_enqueue_style(
            self::BOOTSTRAP_STYLE_ICONS_HANDLE,
            $this->bootstrapStyleIcons
        );
        wp_enqueue_script(
            self::BOOTSTRAP_SCRIPT_HANDLE,
            $this->bootstrapScript,
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

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render(): void
    {
        echo $this->twig->render(
            self::PAGE_TEMPLATE,
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'ajaxAction' => PpeSettingsAjax::ACTION_NAME,
                'ppePriceSelectorId' => PpeSettings::PRICE_SELECTOR_ID,
                'ppePriceSelectorName' => PpeSettings::PRICE_SELECTOR_NAME,
                'ppePriceSelectorValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::PRICE_SELECTOR_ID, 
                    PpeSettings::PRICE_SELECTOR_DEFAULT_VALUE
                ),
                'ppeProductNameSelectorId' => PpeSettings::PRODUCT_NAME_SELECTOR_ID,
                'ppeProductNameSelectorName' => PpeSettings::PRODUCT_NAME_SELECTOR_NAME,
                'ppeProductNameSelectorValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::PRODUCT_NAME_SELECTOR_ID,
                    PpeSettings::PRODUCT_NAME_SELECTOR_DEFAULT_VALUE
                ),
                'ppeIsDebugId' => PpeSettings::IS_DEBUG_ID,
                'ppeIsDebugName' => PpeSettings::IS_DEBUG_NAME,
                'ppeIsDebugValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::IS_DEBUG_ID,
                    PpeSettings::IS_DEBUG_DEFAULT_VALUE
                ),
                'ppeTokenId' => PpeSettings::MERCHANT_TOKEN_ID,
                'ppeTokenName' => PpeSettings::MERCHANT_TOKEN_NAME,
                'ppeTokenValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::MERCHANT_TOKEN_ID,
                    PpeSettings::MERCHANT_TOKEN_DEFAULT_VALUE
                ),
                'ppeIsApplyNowId' => PpeSettings::IS_APPLY_NOW_ID,
                'ppeIsApplyNowName' => PpeSettings::IS_APPLY_NOW_NAME,
                'ppeIsApplyNowValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::IS_APPLY_NOW_ID,
                    PpeSettings::IS_APPLY_NOW_DEFAULT_VALUE
                ),
                'ppeIsBrandedId' => PpeSettings::IS_BRANDED_ID,
                'ppeIsBrandedName' => PpeSettings::IS_BRANDED_NAME,
                'ppeIsBrandedValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::IS_BRANDED_ID,
                    PpeSettings::IS_BRANDED_DEFAULT_VALUE
                ),
                'ppeMinAmountId' => PpeSettings::MINIMUM_AMOUNT_ID,
                'ppeMinAmountName' => PpeSettings::MINIMUM_AMOUNT_NAME,
                'ppeMinAmountValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::MINIMUM_AMOUNT_ID,
                    PpeSettings::MINIMUM_AMOUNT_DEFAULT_VALUE
                ),
                'ppeCustomTextId' => PpeSettings::CUSTOM_TEXT_ID,
                'ppeCustomTextName' => PpeSettings::CUSTOM_TEXT_NAME,
                'ppeCustomTextValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::CUSTOM_TEXT_ID,
                    PpeSettings::CUSTOM_TEXT_DEFAULT_VALUE
                ),
                'ppePositionId' => PpeSettings::POSITION_ID,
                'ppePositionName' => PpeSettings::POSITION_NAME,
                'ppePositionValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::POSITION_ID,
                    PpeSettings::POSITION_DEFAULT_VALUE
                ),
                'ppeSaveButtonId' => PpeSettings::SAVE_BUTTON_ID
            ]
        );
    }
}
