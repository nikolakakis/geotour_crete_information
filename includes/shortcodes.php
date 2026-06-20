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
    ), $atts, 'geotour-information' );

    // Generate a unique ID for the container
    $container_id = 'pois-container-' . uniqid();

    if ( empty( $atts['lat'] ) || empty( $atts['lon'] ) ) {
        return '<p class="geotour-warning">Please provide latitude and longitude using the <code>lat</code> and <code>lon</code> attributes.</p>';
    }

    // Construct the local proxy API URL
    $api_url = get_rest_url( null, 'geotour/v1/listings' );
    $api_url = add_query_arg( array(
        'items'    => $atts['max-items'],
        'lat'      => $atts['lat'],
        'lon'      => $atts['lon'],
        'radius'   => $atts['radius'],
        'category' => $atts['category'],
    ), $api_url );

    $container_classes = "pois-container pois-grid layout-cols-" . esc_attr($atts['columns']) . " theme-" . esc_attr($atts['theme']) . " anim-" . esc_attr($atts['animation']) . " gap-" . esc_attr($atts['gap']);

    $output = '<div id="' . $container_id . '" class="' . $container_classes . '" data-apiurl="' . esc_attr( $api_url ) . '"></div>';

    // Use wp_localize_script to pass data. This is generally better.
    wp_localize_script( 'geotour-shared-content-script', 'geotour_api_data_' . $container_id, array(  // Unique object name
        'apiUrl'      => $api_url,
        'containerId' => $container_id,
    ) );

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

    // Use wp_localize_script here as well.  Consistent data passing.
    wp_localize_script( 'geotour-shared-content-script', 'geotourEventsParams_' . $container_id, $atts ); // Unique object name

	// Output a container for the events (you can customize this)
    $container_classes = "geotour-events-container layout-cols-" . esc_attr($atts['columns']) . " theme-" . esc_attr($atts['theme']) . " anim-" . esc_attr($atts['animation']) . " gap-" . esc_attr($atts['gap']);
    return '<div id="' . $container_id. '" class="' . $container_classes . '"></div>';
}
add_shortcode( 'geotour_events', 'geotour_events_shortcode' );