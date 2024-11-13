<?php

/**
 * Enqueue scripts and styles.
 */

function geotour_shared_content_enqueue_scripts() {

    // Enqueue the CSS file
    wp_enqueue_style( 
        'geotour-shared-content-styles', 
        plugin_dir_url( __FILE__ ) . '../build/index.css',
        array(), 
        '1.0.0', 
        'all' 
    );

    // Enqueue the JavaScript file
    wp_enqueue_script( 
        'geotour-shared-content-script', 
        plugin_dir_url( __FILE__ ) . '../build/index.js',
        array( 'jquery' ), 
        '1.0.0', 
        true 
    );
}
add_action( 'wp_enqueue_scripts', 'geotour_shared_content_enqueue_scripts' );

?>