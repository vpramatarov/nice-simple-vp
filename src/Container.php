<?php

declare(strict_types=1);

namespace NiceSimpleVp;

use NiceSimpleVp\Admin\AdminCore;
use NiceSimpleVp\CustomPostType\Faq;
use NiceSimpleVp\CustomPostType\Portfolio;
use NiceSimpleVp\CustomPostType\Metaboxes;
use NiceSimpleVp\Includes\Activator;
use NiceSimpleVp\Includes\Deactivator;
use NiceSimpleVp\Includes\I18n;
use NiceSimpleVp\Includes\Loader;
use NiceSimpleVp\Public\PublicCore;
use NiceSimpleVp\Repository\FaqRepository;
use NiceSimpleVp\Repository\PortfolioRepository;
use NiceSimpleVp\Settings\CssVariableInjector;
use NiceSimpleVp\Settings\Settings;
use NiceSimpleVp\Settings\SettingsPage;
use NiceSimpleVp\Shortcode\ShowFaq;
use NiceSimpleVp\Shortcode\ShowPortfolio;

final class Container
{
    /**
     * Instance property of Container Class.
     * This is a property in your plugin primary class.
     * You will use to create one object from Container class in whole of program execution.
     *
     * @access private
     * @var ?Container $instance create only one instance from plugin primary class
     * @static
     */
    private static ?self $instance = null;

    private Loader $loader;

    private PortfolioRepository $portfolioRepository;

    private FaqRepository $faqRepository;

    private Settings $settings;

    /**
     * Container constructor.
     * It defines related constant, include autoloader class, register activation hook,
     * deactivation hook and uninstall hook and call Core class to run dependencies for plugin
     *
     * @access private
     */
    private function __construct()
    {
        /**
         * Register activation hook.
         * Register activation hook for this plugin by invoking activate in NiceSimpleVp class.
         *
         * @param string   $file     path to the plugin file.
         * @param callback $function The function to be run when the plugin is activated.
         */
        register_activation_hook(
            __FILE__,
            function () {
                $this->activate(new Activator());
            }
        );

        /**
         * Register deactivation hook.
         * Register deactivation hook for this plugin by invoking deactivate in Container class.
         *
         * @param string   $file     path to the plugin file.
         * @param callback $function The function to be run when the plugin is deactivated.
         */
        register_deactivation_hook(
            __FILE__,
            function () {
                $this->deactivate(new Deactivator());
            }
        );

        $this->initComponents();
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new \RuntimeException('Cannot unserialize Singleton.');
    }

    /**
     * Create an instance from Container class.
     *
     * @access public
     * @return Container
     * @since  1.0.0
     */
    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Call activate method.
     * This function calls activate method from Activator class.
     * You can use from this method to run every thing you need when plugin is activated.
     *
     * @param $activator Activator
     */
    private function activate(Activator $activator): void
    {
        $activator->activate();
    }

    /**
     * Call deactivate method.
     * This function calls deactivate method from Deactivator class.
     * You can use from this method to run every thing you need when plugin is deactivated.
     *
     * @param Deactivator $deactivator
     */
    private function deactivate(Deactivator $deactivator): void
    {
        $deactivator->deactivate();
    }

    private function initComponents(): void
    {
        $this->portfolioRepository = new PortfolioRepository();
        $this->faqRepository = new FaqRepository();
        $this->settings = new Settings();
        $this->loader = new Loader();
        // init locale (i18n)
        $l18n = new I18n();
        $this->loader->addAction('plugins_loaded', $l18n, 'loadPluginTextdomain' );

        $this->registerCPT();
        $this->defineAdminHooks();
        $this->defineSettingsHooks();
        $this->definePublicHooks();
        $this->registerShortcodes();
        $this->loader->run();
    }

    /**
     * Register custom post types of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function registerCPT(): void
    {
        $cpts = [new Portfolio(), new Faq()];

        foreach ($cpts as $cpt) {
            $this->loader->addAction('init', $cpt, 'register' );

            if ($cpt instanceof Metaboxes) {
                $this->loader->addAction('add_meta_boxes', $cpt, 'addMetaBoxes' );
                $this->loader->addAction('save_post', $cpt, 'saveMetaboxesData' );
            }
        }
    }

    /**
     * Register shortcodes of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function registerShortcodes(): void
    {
        $shortcodes = [
            new ShowPortfolio($this->portfolioRepository, $this->settings),
            new ShowFaq($this->faqRepository)
        ];

        foreach ($shortcodes as $shortcode) {
            $this->loader->addAction('init', $shortcode, 'register' );
        }
    }

    /**
     * Register all the hooks related to the admin area functionality of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function defineAdminHooks(): void
    {
        $pluginAdmin = new AdminCore();

        // actions
        $this->loader->addAction('admin_enqueue_scripts', $pluginAdmin, 'enqueueStyles' );
        $this->loader->addAction('admin_enqueue_scripts', $pluginAdmin, 'enqueueScripts' );
    }

    /**
     * Register all the hooks related to the public-facing functionality of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function definePublicHooks(): void
    {
        $pluginPublic = new PublicCore($this->portfolioRepository, $this->settings);

        // actions
        $this->loader->addAction('wp_enqueue_scripts', $pluginPublic, 'enqueueStyles' );
        $this->loader->addAction('wp_enqueue_scripts', $pluginPublic, 'enqueueScripts' );
        $this->loader->addAction('wp_ajax_ns_load_more', $pluginPublic, 'handleLoadMore' );
        $this->loader->addAction('wp_ajax_nopriv_ns_load_more', $pluginPublic, 'handleLoadMore' );

        $this->loader->addAction('wp_ajax_ns_get_nonce', $pluginPublic, 'handleGetNonce');
        $this->loader->addAction('wp_ajax_nopriv_ns_get_nonce', $pluginPublic, 'handleGetNonce');
    }

    /**
     * Register hooks for the Settings page and the runtime CSS variable injector.
     *
     * @access   private
     */
    private function defineSettingsHooks(): void
    {
        $settingsPage = new SettingsPage($this->settings);
        $this->loader->addAction('admin_menu', $settingsPage, 'registerMenu');
        $this->loader->addAction('admin_init', $settingsPage, 'registerSettings');
        $this->loader->addAction('admin_enqueue_scripts', $settingsPage, 'enqueueAssets');

        // Runs after PublicCore::enqueueStyles (priority 10) so the inline style
        // attaches to the already-registered public stylesheet handle.
        $cssInjector = new CssVariableInjector($this->settings);
        $this->loader->addAction('wp_enqueue_scripts', $cssInjector, 'inject', 20);
    }
}