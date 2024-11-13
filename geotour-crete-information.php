<?php
/**
 * Plugin Name: Geotour Crete Information
 * Plugin URI: https://www.geotour.gr/geotour-share-plugin/
 * Description: Get information from Geotour Crete, https://www.geotour.gr from other Wordpress websites.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.4 
 * Author: Nikolakakis Manolis
 * Author URI: https://www.panotours.gr/
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
        $links[] = '<a href="https://example.com/my-plugin-docs/" target="_blank">Docs & FAQs</a>';
        $links[] = '<a href="https://example.com/my-plugin-videos/" target="_blank">Video Tutorials</a>';
    }
    return $links;
}
add_filter( 'plugin_row_meta', 'my_plugin_add_plugin_meta_links', 10, 2 ); 
?>