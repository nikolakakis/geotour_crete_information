<?php

/**
 * Enqueue scripts and styles.
 */

function geotour_shared_content_enqueue_scripts() {

    // Enqueue the CSS file
    wp_enqueue_style(
        'geotour-shared-content-styles',
        'https://geotour.local/wp-content/plugins/geotour_crete_information/build/index.css', // Absolute URL
        array(),
        '1.2.0',
        'all'
    );

    wp_enqueue_script(
        'geotour-shared-content-script',
        'https://geotour.local/wp-content/plugins/geotour_crete_information/build/index.js', // Absolute URL
        array('jquery'),
        '1.2.0',
        true
    );
    
    // Get the shortcode attributes from the content
    $shortcode_atts = geotour_extract_shortcode_atts( 'geotour_events' );

    // Pass the data as a JavaScript object
    wp_add_inline_script( 
        'geotour-shared-content-script', 
        'const geotourEventsParams = ' . json_encode( $shortcode_atts ) . ';', 
        'before' 
    );
}
add_action( 'wp_enqueue_scripts', 'geotour_shared_content_enqueue_scripts' );

function geotour_extract_shortcode_atts( $shortcode_name ) {
    global $post;
    $atts = array(
        'lat' => '35.337042', // Default latitude (e.g., Athens)
        'lon' => '24.684551', // Default longitude
        'radius' => '10', // Default radius in km
        'max-items' => '9',  // Default maximum items
    );

    if ( $post && has_shortcode( $post->post_content, $shortcode_name ) ) {
        $pattern = get_shortcode_regex( array( $shortcode_name ) );
        if ( preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches )
             && array_key_exists( 2, $matches ) && array_key_exists( 0, $matches[2] ) ) {
            $atts = shortcode_parse_atts( $matches[3][0] );
        }
    }
    return $atts;
}


?>