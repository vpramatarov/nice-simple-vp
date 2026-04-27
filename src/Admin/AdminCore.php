<?php

declare(strict_types=1);

namespace NiceSimpleVp\Admin;

final readonly class AdminCore
{
    public function __construct() {}


    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueStyles(): void
    {
        wp_enqueue_style(NICE_SIMPLE_VP_PLUGIN_NAME, plugin_dir_url( __FILE__ ) . 'css'. DIRECTORY_SEPARATOR . 'nice-simple-admin.css', [], NICE_SIMPLE_VP_VERSION, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueScripts(): void
    {
        wp_enqueue_script(NICE_SIMPLE_VP_PLUGIN_NAME, plugin_dir_url( __FILE__ ) . 'js'. DIRECTORY_SEPARATOR. 'nice-simple-admin.js', ['jquery'], NICE_SIMPLE_VP_VERSION, ['in_footer' => true, 'strategy'  => 'async'] );
    }
}