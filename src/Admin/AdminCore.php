<?php

declare(strict_types=1);

namespace NiceSimpleVp\Admin;

final readonly class AdminCore
{
    public function __construct(private string $pluginName, private string $version) {}


    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueStyles(): void
    {
        wp_enqueue_style( $this->pluginName, plugin_dir_url( __FILE__ ) . 'css'. DIRECTORY_SEPARATOR . 'nice-simple-admin.css', [], $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueScripts(): void
    {
        wp_enqueue_script( $this->pluginName, plugin_dir_url( __FILE__ ) . 'js'. DIRECTORY_SEPARATOR. 'nice-simple-admin.js', ['jquery'], $this->version, ['in_footer' => true, 'strategy'  => 'async'] );
    }
}