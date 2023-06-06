<?php

declare(strict_types=1);

namespace WeGetFinancing\Checkout\Shortcode;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancing;
use WeGetFinancing\Checkout\PaymentGateway\WeGetFinancingValueObject;
use WeGetFinancing\Checkout\Repository\PpeSettingsRepository;
use WeGetFinancing\Checkout\ValueObject\PpeSettings;

class PpeShortcode
{
    public const INIT_NAME = 'wegetfinancing-ppe';
    public const STYLE_HANDLE = 'wegetfinancing_ppe_css';
    public const SCRIPT_HANDLE = 'wegetfinancing_ppe_js';
    public const PAGE_TEMPLATE = 'store/ppe_shortcode.twig';

    protected bool $isSandbox = true;

    public function __construct(
        protected Environment $twig,
        protected string $scriptProd,
        protected string $scriptSandbox,
        protected string $styleProd,
        protected string $styleSandbox
    ) {
        $this->isSandbox = ("yes" === WeGetFinancing::getOptions()[WeGetFinancingValueObject::IS_SANDBOX_FIELD_ID]);
    }

    public function init(): void
    {
        add_shortcode(self::INIT_NAME, [$this, 'execute']);
    }

    /**
     * @param mixed $attributes
     * @param mixed $content
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @return string
     */
    public function execute(mixed $attributes, mixed $content = ""): string
    {
        $isSetup = PpeSettingsRepository::getOptionOrDefault(
            PpeSettings::PPE_IS_CONFIGURED,
            false
        );

        if (false === $isSetup) {
            return "";
        }

        wp_enqueue_style(
            self::STYLE_HANDLE,
            $this->getStyle(),
            [],
            null
        );

        wp_enqueue_script(
            self::SCRIPT_HANDLE,
            $this->getScript(),
            ['jquery'],
            null,
            true
        );

        return $this->twig->render(
            self::PAGE_TEMPLATE,
            [
                'ppePriceSelectorValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::PRICE_SELECTOR_ID,
                    PpeSettings::PRICE_SELECTOR_DEFAULT_VALUE
                ),
                'ppeProductNameSelectorValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::PRODUCT_NAME_SELECTOR_ID,
                    PpeSettings::PRODUCT_NAME_SELECTOR_DEFAULT_VALUE
                ),
                'ppeIsDebugValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::IS_DEBUG_ID,
                    PpeSettings::IS_DEBUG_DEFAULT_VALUE
                ) ? "true" : "false",
                'ppeTokenValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::MERCHANT_TOKEN_ID,
                    PpeSettings::MERCHANT_TOKEN_DEFAULT_VALUE
                ),
                'ppeIsApplyNowValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::IS_APPLY_NOW_ID,
                    PpeSettings::IS_APPLY_NOW_DEFAULT_VALUE
                ) ? "true" : "false",
                'ppeIsBrandedValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::IS_BRANDED_ID,
                    PpeSettings::IS_BRANDED_DEFAULT_VALUE
                ) ? "true" : "false",
                'ppeMinAmountValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::MINIMUM_AMOUNT_ID,
                    PpeSettings::MINIMUM_AMOUNT_DEFAULT_VALUE
                ),
                'ppeCustomTextValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::CUSTOM_TEXT_ID,
                    PpeSettings::CUSTOM_TEXT_DEFAULT_VALUE
                ),
                'ppePositionValue' => PpeSettingsRepository::getOptionOrDefault(
                    PpeSettings::POSITION_ID,
                    PpeSettings::POSITION_DEFAULT_VALUE
                ),
            ]
        );
    }

    protected function getStyle(): string
    {
        return true === $this->isSandbox
            ? $this->styleSandbox
            : $this->styleProd;
    }

    protected function getScript(): string
    {
        return true === $this->isSandbox
            ? $this->scriptSandbox
            : $this->scriptProd;
    }
}
