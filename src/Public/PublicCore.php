<?php

declare(strict_types=1);

namespace NiceSimpleVp\Public;

use NiceSimpleVp\Includes\Utils;
use NiceSimpleVp\Repository\PortfolioRepository;

final readonly class PublicCore
{
    public function __construct(private PortfolioRepository $portfolioRepository) {}


    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueStyles(): void
    {
        wp_enqueue_style(NICE_SIMPLE_VP_PLUGIN_NAME, plugin_dir_url( __FILE__ ) . 'css'. DIRECTORY_SEPARATOR . 'nice-simple-public.css', [], NICE_SIMPLE_VP_VERSION, 'all' );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueueScripts(): void
    {
        wp_register_script(
            NICE_SIMPLE_VP_PLUGIN_NAME,
            plugin_dir_url( __FILE__ ) . 'js'. DIRECTORY_SEPARATOR . 'nice-simple-public.js',
            ['jquery'],
            NICE_SIMPLE_VP_VERSION,
            ['in_footer' => true, 'strategy'  => 'async']
        );

        wp_localize_script(NICE_SIMPLE_VP_PLUGIN_NAME, 'nsJs', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'buttonText' => __('Load More', 'nice-simple-vp'),
            'loadingText' => __('Loading...', 'nice-simple-vp'),
            'projectsLimit' => 12,
            'nonce' => wp_create_nonce('ns_nonce')
        ]);

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

    public function handleGetNonce(): void
    {
        wp_send_json_success(['nonce' => wp_create_nonce('ns_nonce')]);
    }

    public function handleLoadMore(): void
    {
        if (!check_ajax_referer('ns_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => __('Security check failed.', 'nice-simple-vp')]);
        }
        $offset = isset($_POST['offset']) ? absint($_POST['offset']) : 0;
        $limit_req = isset($_POST['limit']) ? absint($_POST['limit']) : NICE_SIMPLE_VP_LIMIT_PER_PAGE;
        $limit = max(1, min(NICE_SIMPLE_VP_LIMIT_PER_PAGE, $limit_req)); // clamp the limit between 1 and NICE_SIMPLE_VP_LIMIT_PER_PAGE
        $total = $this->portfolioRepository->count();
        $projects = $this->portfolioRepository->get($limit, $offset);

        if (empty($projects)) {
            wp_send_json_error(['message' => __('No more projects', 'nice-simple-vp')]);
        }

        $html = '';
        foreach ($projects as $project) {
            $html .= Utils::renderProjectCardHtml($project, $this->portfolioRepository->getTaxonomyName());
        }

        $has_more = ($offset + $limit) < $total;

        wp_send_json_success(['html' => $html, 'has_more' => $has_more]);
    }
}