<?php

declare(strict_types=1);

namespace NiceSimpleVp\Settings;

final readonly class CssVariableInjector
{
    private const array CSS_VARIABLE_MAP = [
        'color_default'  => '--ns-btn-color',
        'bg_default'     => '--ns-btn-bg',
        'border_default' => '--ns-btn-border',
        'color_hover'    => '--ns-btn-color-hover',
        'bg_hover'       => '--ns-btn-bg-hover',
        'border_hover'   => '--ns-btn-border-hover',
    ];

    public function __construct(private Settings $settings) {}

    public function inject(): void
    {
        $colors = $this->settings->getButtonColors();
        $declarations = [];

        foreach (self::CSS_VARIABLE_MAP as $key => $cssVar) {
            $value = $colors[$key] ?? '';
            $default = Settings::BUTTON_COLOR_DEFAULTS[$key] ?? '';

            // Skip when the resolved value matches the static :root default already
            if ($value === $default) {
                continue;
            }

            $declarations[] = sprintf('%s: %s;', $cssVar, $value === '' ? 'transparent' : $value);
        }

        if ($declarations === []) {
            return;
        }

        $css = ':root{' . implode('', $declarations) . '}';

        wp_add_inline_style(NICE_SIMPLE_VP_PLUGIN_NAME, $css);
    }
}
