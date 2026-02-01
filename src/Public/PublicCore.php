<?php

declare(strict_types=1);

namespace NiceSimpleVp\Public;

final readonly class PublicCore
{
    public function __construct(private string $pluginName, private string $version) {}


    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueStyles(): void
    {
        wp_enqueue_style( $this->pluginName, plugin_dir_url( __FILE__ ) . 'css'. DIRECTORY_SEPARATOR . 'nice-simple-public.css', [], $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueueScripts(): void
    {
//        wp_enqueue_script(
//            $this->pluginName,
//            plugin_dir_url( __FILE__ ) . 'js'. DIRECTORY_SEPARATOR . 'nice-simple-public.js',
//            ['jquery'],
//            $this->version,
//            ['in_footer' => true, 'strategy'  => 'async']
//        );

        wp_register_script(
            'ns_mobile',
            plugin_dir_url( __FILE__ ) . 'js'. DIRECTORY_SEPARATOR .'mobile.js',
            [],	// dependencies
            false,
            ['in_footer' => true, 'strategy' => 'async']
        );

        wp_register_script(
            'show_projects_portfolio',
            plugin_dir_url( __FILE__ ) . 'js'. DIRECTORY_SEPARATOR .'show_projects.js',
            [],	// dependencies
            false,
            ['in_footer' => true, 'strategy' => 'async']
        );
    }
}