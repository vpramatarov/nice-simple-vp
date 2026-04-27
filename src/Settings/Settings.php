<?php

declare(strict_types=1);

namespace NiceSimpleVp\Settings;

final class Settings
{
    public const string OPTION_KEY = 'nice_simple_vp_settings';

    public const array BUTTON_COLOR_KEYS = [
        'color_default',
        'bg_default',
        'border_default',
        'color_hover',
        'bg_hover',
        'border_hover',
    ];

    public const array BUTTON_COLOR_DEFAULTS = [
        'color_default'  => '#d7b56d',
        'bg_default'     => '',
        'border_default' => '#d7b56d',
        'color_hover'    => '#ffffff',
        'bg_hover'       => '#c5a47e',
        'border_hover'   => '#c5a47e',
    ];

    public const int LIMIT_MIN = 1;

    public const int LIMIT_MAX = 100;

    private ?array $cache = null;

    public function getProjectsLimit(): int
    {
        $raw = $this->all()['projects_limit'] ?? null;

        if (!is_numeric($raw)) {
            return NICE_SIMPLE_VP_LIMIT_PER_PAGE;
        }

        $limit = (int) $raw;
        if ($limit < self::LIMIT_MIN || $limit > self::LIMIT_MAX) {
            return NICE_SIMPLE_VP_LIMIT_PER_PAGE;
        }

        return $limit;
    }

    /**
     * @return array{color_default: string, bg_default: string, border_default: string, color_hover: string, bg_hover: string, border_hover: string}
     */
    public function getButtonColors(): array
    {
        $stored = $this->all()['button_colors'] ?? [];
        if (!is_array($stored)) {
            $stored = [];
        }

        $colors = self::BUTTON_COLOR_DEFAULTS;
        foreach (self::BUTTON_COLOR_KEYS as $key) {
            if (!array_key_exists($key, $stored) || !is_string($stored[$key])) {
                continue;
            }

            $value = trim($stored[$key]);
            if ($value === '') {
                $colors[$key] = '';
                continue;
            }

            /**
             * @note:
             * Re-sanitize on read so a corrupted/legacy/direct-DB write can never
             * reach the inline CSS injector with a value that breaks out of :root{...}.
             */
            $hex = sanitize_hex_color($value);
            $colors[$key] = is_string($hex) ? $hex : self::BUTTON_COLOR_DEFAULTS[$key];
        }

        return $colors;
    }

    private function all(): array
    {
        if ($this->cache === null) {
            $stored = get_option(self::OPTION_KEY, []);
            $this->cache = is_array($stored) ? $stored : [];
        }

        return $this->cache;
    }
}
