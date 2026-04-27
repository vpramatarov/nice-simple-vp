<?php

declare(strict_types=1);

namespace NiceSimpleVp\Settings;

final class SettingsPage
{
    public const string MENU_SLUG = 'nice-simple-vp';

    public const string OPTION_GROUP = 'nice_simple_vp_settings_group';

    private const string TAB_BUTTONS = 'buttons';

    private const string TAB_PAGINATION = 'pagination';

    private const array TABS = [self::TAB_BUTTONS, self::TAB_PAGINATION];

    public function __construct(private readonly Settings $settings) {}

    public function registerMenu(): void
    {
        add_options_page(
            __('Nice and Simple', 'nice-simple-vp'),
            __('Nice and Simple', 'nice-simple-vp'),
            'manage_options',
            self::MENU_SLUG,
            [$this, 'render']
        );
    }

    public function registerSettings(): void
    {
        register_setting(self::OPTION_GROUP, Settings::OPTION_KEY, [
            'type'              => 'array',
            'sanitize_callback' => [$this, 'sanitize'],
            'default'           => [],
        ]);

        $this->registerButtonsTab();
        $this->registerPaginationTab();
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $activeTab = $this->activeTab();

        echo '<style>
                .ns-tab-panel { display: none; }
                .ns-tab-panel.is-active { display: block; }
              </style>';

        echo '<div class="wrap">';
        printf('<h1>%s</h1>', esc_html__('Nice and Simple — Settings', 'nice-simple-vp'));

        echo '<nav id="ns-settings-tabs" class="nav-tab-wrapper">';
        foreach ($this->tabLabels() as $tab => $label) {
            $url = add_query_arg(
                ['page' => self::MENU_SLUG, 'tab' => $tab],
                admin_url('options-general.php')
            );
            $class = 'nav-tab' . ($tab === $activeTab ? ' nav-tab-active' : '');
            printf(
                '<a href="%s" class="%s" data-ns-tab="%s">%s</a>',
                esc_url($url),
                esc_attr($class),
                esc_attr($tab),
                esc_html($label)
            );
        }
        echo '</nav>';

        echo '<form action="options.php" method="post">';
        settings_fields(self::OPTION_GROUP);

        foreach (self::TABS as $tab) {
            $panelClass = 'ns-tab-panel' . ($tab === $activeTab ? ' is-active' : '');
            printf('<div class="%s" data-ns-tab="%s">', esc_attr($panelClass), esc_attr($tab));
            do_settings_sections($this->pageSlugForTab($tab));
            echo '</div>';
        }

        submit_button();
        echo '</form>';

        echo '</div>';
    }

    public function sanitize(mixed $raw): array
    {
        $previous = get_option(Settings::OPTION_KEY, []);
        if (!is_array($previous)) {
            $previous = [];
        }

        $merged = $previous;
        $raw = is_array($raw) ? $raw : [];

        // Both tabs render their fields into the same form, so every Save submits
        // both. Process unconditionally — branching on an "active tab" hint would
        // silently drop edits made on the non-visible tab.
        $colors = isset($raw['button_colors']) && is_array($raw['button_colors']) ? $raw['button_colors'] : [];
        $sanitizedColors = [];
        foreach (Settings::BUTTON_COLOR_KEYS as $key) {
            $value = isset($colors[$key]) && is_string($colors[$key]) ? trim($colors[$key]) : '';
            if ($value === '') {
                $sanitizedColors[$key] = '';
                continue;
            }
            $hex = sanitize_hex_color($value);
            $sanitizedColors[$key] = is_string($hex) ? $hex : '';
        }
        $merged['button_colors'] = $sanitizedColors;

        $limitRaw = $raw['projects_limit'] ?? '';
        if (is_numeric($limitRaw)) {
            $limit = (int) $limitRaw;
            if ($limit < Settings::LIMIT_MIN) {
                $limit = Settings::LIMIT_MIN;
            } elseif ($limit > Settings::LIMIT_MAX) {
                $limit = Settings::LIMIT_MAX;
            }
            $merged['projects_limit'] = $limit;
        } else {
            unset($merged['projects_limit']);
        }

        return $merged;
    }

    public function enqueueAssets(string $hook): void
    {
        if ($hook !== 'settings_page_' . self::MENU_SLUG) {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script(
            'nice-simple-vp-settings',
            plugins_url('src/Admin/js/settings.js', PLUGIN_PATH . 'init.php'),
            ['wp-color-picker', 'jquery'],
            NICE_SIMPLE_VP_VERSION,
            true
        );
    }

    public function renderColorField(array $args): void
    {
        $key = $args['key'] ?? '';
        if (!in_array($key, Settings::BUTTON_COLOR_KEYS, true)) {
            return;
        }

        $colors = $this->settings->getButtonColors();
        $value = $colors[$key];
        $default = Settings::BUTTON_COLOR_DEFAULTS[$key];

        printf(
            '<input type="text" class="ns-color-field" name="%1$s[button_colors][%2$s]" id="%2$s" value="%3$s" data-default-color="%4$s">',
            esc_attr(Settings::OPTION_KEY),
            esc_attr($key),
            esc_attr($value),
            esc_attr($default)
        );
    }

    public function renderLimitField(): void
    {
        $value = $this->settings->getProjectsLimit();

        printf(
            '<input type="number" name="%1$s[projects_limit]" id="projects_limit" value="%2$d" min="%3$d" max="%4$d" class="small-text">',
            esc_attr(Settings::OPTION_KEY),
            $value,
            Settings::LIMIT_MIN,
            Settings::LIMIT_MAX
        );

        printf(
            '<p class="description">%s</p>',
            esc_html(sprintf(
                /* translators: %d: hardcoded fallback limit. */
                __('Number of projects shown per page (initial render and Load More batch). Falls back to %d if unset.', 'nice-simple-vp'),
                NICE_SIMPLE_VP_LIMIT_PER_PAGE
            ))
        );
    }

    private function registerButtonsTab(): void
    {
        $page = $this->pageSlugForTab(self::TAB_BUTTONS);

        add_settings_section(
            'ns_buttons_default',
            __('Default state', 'nice-simple-vp'),
            static fn() => print '<p>' . esc_html__('Colors used when buttons are at rest. Leave a field blank to use the theme default (transparent for background).', 'nice-simple-vp') . '</p>',
            $page
        );

        $defaultFields = [
            'color_default'  => __('Text color', 'nice-simple-vp'),
            'bg_default'     => __('Background color', 'nice-simple-vp'),
            'border_default' => __('Border color', 'nice-simple-vp'),
        ];
        foreach ($defaultFields as $key => $label) {
            add_settings_field(
                $key,
                $label,
                [$this, 'renderColorField'],
                $page,
                'ns_buttons_default',
                ['key' => $key, 'label_for' => $key]
            );
        }

        add_settings_section(
            'ns_buttons_hover',
            __('Hover state', 'nice-simple-vp'),
            static fn() => print '<p>' . esc_html__('Colors used when buttons are hovered or active.', 'nice-simple-vp') . '</p>',
            $page
        );

        $hoverFields = [
            'color_hover'  => __('Text color', 'nice-simple-vp'),
            'bg_hover'     => __('Background color', 'nice-simple-vp'),
            'border_hover' => __('Border color', 'nice-simple-vp'),
        ];
        foreach ($hoverFields as $key => $label) {
            add_settings_field(
                $key,
                $label,
                [$this, 'renderColorField'],
                $page,
                'ns_buttons_hover',
                ['key' => $key, 'label_for' => $key]
            );
        }
    }

    private function registerPaginationTab(): void
    {
        $page = $this->pageSlugForTab(self::TAB_PAGINATION);

        add_settings_section(
            'ns_pagination_load_more',
            __('Load More', 'nice-simple-vp'),
            static fn() => print '<p>' . esc_html__('Controls how the [show_projects] grid paginates.', 'nice-simple-vp') . '</p>',
            $page
        );

        add_settings_field(
            'projects_limit',
            __('Projects per page', 'nice-simple-vp'),
            [$this, 'renderLimitField'],
            $page,
            'ns_pagination_load_more',
            ['label_for' => 'projects_limit']
        );
    }

    private function activeTab(): string
    {
        $requested = isset($_GET['tab']) && is_string($_GET['tab'])
            ? sanitize_key(wp_unslash($_GET['tab']))
            : self::TAB_BUTTONS;

        return in_array($requested, self::TABS, true) ? $requested : self::TAB_BUTTONS;
    }

    private function pageSlugForTab(string $tab): string
    {
        return self::MENU_SLUG . '-' . $tab;
    }

    /**
     * @return array<string, string>
     */
    private function tabLabels(): array
    {
        return [
            self::TAB_BUTTONS    => __('Buttons', 'nice-simple-vp'),
            self::TAB_PAGINATION => __('Pagination', 'nice-simple-vp'),
        ];
    }
}
