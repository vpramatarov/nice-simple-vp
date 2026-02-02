<?php

declare(strict_types=1);

/**
 *
 * Nice and Simple Plugin
 *
 * @link              https://nice-simple.com
 * @since             1.0.0
 * @package           NiceSimpleVp
 *
 * @wordpress-plugin
 * Plugin Name:       Nice and Simple
 * Plugin URI:        https://nice-simple.com
 * Description:       Nice and Simple Plugin to add additional functionality
 * Version:           1.0.4
 * Author:            Velizar Pramatarov <velizarpramatrov@yahoo.com>
 * Author URI:        https://vpramatarov.eu
 * License:           GPL-2.0
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nice-simple-vp
 * Domain Path:       /languages
 * Requires PHP:      8.3
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) exit;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use NiceSimpleVp\Container;

define("PLUGIN_PATH", plugin_dir_path(__FILE__));
const NICE_SIMPLE_VP_VERSION = '1.0.4';

add_action('plugins_loaded', [Container::class, 'getInstance']);