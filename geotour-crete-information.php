<?php
/**
 * Plugin Name: Geotour Crete Information
 * Plugin URI: https://www.geotour.gr/about-geotour/geotour-share-plugin/
 * Description: Provide information from Geotour Crete, https://www.geotour.gr to other Wordpress websites.
 * Version: 1.4.1
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: Nikolakakis Manolis
 * Author URI: https://www.geotour.gr/
 */

// Include necessary files.
require_once plugin_dir_path( __FILE__ ) . 'includes/scripts.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/admin-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes.php';

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Add plugin meta links.
 */
function my_plugin_add_plugin_meta_links( $links, $file ) {
    if ( plugin_basename( __FILE__ ) === $file ) {
        $links[] = '<a href="https://www.geotour.gr/about-geotour/geotour-share-plugin/#faq" target="_blank">FAQs</a>';
        $links[] = '<a href="https://www.geotour.gr/about-geotour/geotour-share-plugin/#tutorial" target="_blank">Tutorial</a>';
    }
    return $links;
}
add_filter( 'plugin_row_meta', 'my_plugin_add_plugin_meta_links', 10, 2 );

// Define a constant for the plugin's base URL.  This is MUCH better.
define( 'GEOTOUR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );