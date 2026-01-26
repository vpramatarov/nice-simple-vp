<?php

declare(strict_types=1);

namespace NiceSimpleVp\Includes;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class Activator
{
    public function activate(): void
    {
        if (version_compare(PHP_VERSION, '8.3.0', '<')) {
            wp_die(
                __('This plugin requires PHP version 8.3 or higher. Please update the PHP version on your server.', 'nice-simple-vp'),
                __('Incompatible PHP version', 'nice-simple-vp'),
                ['back_link' => true]
            );
        }
    }
}