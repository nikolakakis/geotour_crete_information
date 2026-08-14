<?php

// includes/shortcodes.php

function geotour_information_html( $atts ) {
    $atts = shortcode_atts( array(
        'category'  => 'beach',
        'max-items' => 10,
        'lat'       => '',
        'lon'       => '',
        'radius'    => 10,
        'columns'   => 'auto',
        'theme'     => 'card',
        'animation' => 'slide-up',
        'gap'       => 'medium',
        'color'     => '#0073aa',
    ), $atts, 'geotour-information' );

    // Generate a unique ID for the container
    $container_id = 'pois-container-' . uniqid();

    if ( empty( $atts['lat'] ) || empty( $atts['lon'] ) ) {
        return '<p class="geotour-warning">Please provide latitude and longitude using the <code>lat</code> and <code>lon</code> attributes.</p>';
    }

    $color = sanitize_hex_color( $atts['color'] ) ?: '#0073aa';

    // Local proxy (includes/api.php:geotour_fetch_listings_proxy) — the API
    // key and content language are read server-side from this plugin's own
    // settings there, so neither one ever reaches this shortcode's HTML.
    $api_url = add_query_arg(
        array(
            'items'    => rawurlencode( $atts['max-items'] ),
            'lat'      => rawurlencode( $atts['lat'] ),
            'lon'      => rawurlencode( $atts['lon'] ),
            'radius'   => rawurlencode( $atts['radius'] ),
            'category' => rawurlencode( $atts['category'] ),
        ),
        rest_url( 'geotour/v1/listings' )
    );

    $container_classes = "pois-container pois-grid layout-cols-" . esc_attr($atts['columns']) . " theme-" . esc_attr($atts['theme']) . " anim-" . esc_attr($atts['animation']) . " gap-" . esc_attr($atts['gap']);

    $output = '<div id="' . $container_id . '" class="' . $container_classes . '" data-apiurl="' . esc_attr( $api_url ) . '" style="--geotour-primary: ' . esc_attr( $color ) . '"></div>';

    return $output;
}
add_shortcode( 'geotour-information', 'geotour_information_html' );



function geotour_events_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'lat'       => '35.337042', // Default latitude
            'lon'       => '24.684551', // Default longitude
            'radius'    => '10',       // Default radius in km
            'max-items' => '6',        // Default maximum items
            'columns'   => 'auto',
            'theme'     => 'card',
            'animation' => 'slide-up',
            'gap'       => 'medium',
        ),
        $atts,
        'geotour_events'
    );

    // Sanitize and validate attributes (important!)
    $latitude  = floatval( $atts['lat'] );
    $longitude = floatval( $atts['lon'] );
    $radius    = floatval( $atts['radius'] );
    $maxItems  = intval( $atts['max-items'] );

	$container_id = 'geotour-events-container-' . uniqid();

	// Output a container for the events (you can customize this)
    $container_classes = "geotour-events-container layout-cols-" . esc_attr($atts['columns']) . " theme-" . esc_attr($atts['theme']) . " anim-" . esc_attr($atts['animation']) . " gap-" . esc_attr($atts['gap']);
    return '<div id="' . $container_id. '" class="' . $container_classes . '"></div>';
}
add_shortcode( 'geotour_events', 'geotour_events_shortcode' );