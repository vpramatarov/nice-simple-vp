<?php

declare(strict_types=1);

namespace NiceSimpleVp\Includes;

/**
 * Load the plugin text domain for translation.
 *
 * @since    1.0.0
 */
final readonly class I18n
{
    public function loadPluginTextdomain(): void
    {
        load_plugin_textdomain(
            'nice-simple-vp',
            false,
            PLUGIN_PATH . '/languages/'
        );
    }
}