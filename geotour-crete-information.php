<?php
/**
 * Plugin Name: Geotour Crete Information
 * Description: Get information from Geotour Crete, https://www.geotour.gr from other Wordpress websites.
 * Version: 0.1.0
 * Author: Nikolakakis Manolis Geotour developer
 */


// Include necessary files
//require_once plugin_dir_path( __FILE__ ) . 'includes/api.php';
//require_once plugin_dir_path( __FILE__ ) . 'includes/scripts.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes.php'; 
//require_once plugin_dir_path( __FILE__ ) . 'includes/apiadminpages.php'; 
//require_once plugin_dir_path( __FILE__ ) . 'includes/api-logs.php'; 


// Register activation/deactivation hooks (if needed)
//register_activation_hook( __FILE__, 'geotour_shared_content_activate' );
//register_deactivation_hook( __FILE__, 'geotour_shared_content_deactivate' );

// ... (activation and deactivation functions can go here) ... 


function geotour_enqueue_scripts() {
    wp_enqueue_style( 'geotour-styles', plugins_url( 'build/style.css', __FILE__ ) ); 
    wp_enqueue_script( 'geotour-scripts', plugins_url( 'build/index.js', __FILE__ ), array( 'jquery' ), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'geotour_enqueue_scripts' ); 
?>