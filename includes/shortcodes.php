<?php
// admin/admin-settings.php (or your preferred location)

function geotour_information_html( $atts ) {
    $atts = shortcode_atts( array(
        'category' => 'beach',
        'items' => 10,
        'lat' => '', 
        'lon' => '',
        'radius' => 10, 
    ), $atts, 'geotour-information' );
    // Generate a unique ID for the container
    $container_id = 'pois-container-' . uniqid();

    if ( empty( $atts['lat'] ) || empty( $atts['lon'] ) ) {
        return '<p class="geotour-warning">Please provide latitude and longitude using the <code>lat</code> and <code>lon</code> attributes.</p>';
    }

    $api_key = get_option( 'geotour_api_key' ); 

    // Construct the API URL carefully
    $api_url = "https://www.geotour.gr/wp-json/panotours/v2/listings";
    $api_url .= "?items=" . urlencode( $atts['items'] );
    $api_url .= "&language=en";
    $api_url .= "&lat=" . urlencode( $atts['lat'] );
    $api_url .= "&lon=" . urlencode( $atts['lon'] );
    $api_url .= "&radius=" . urlencode( $atts['radius'] );
    $api_url .= "&category=" . urlencode( $atts['category'] );
    $api_url .= "&apikey=" . urlencode( $api_key );

    // ... enqueuing scripts and styles ...

    $output = '<div id="' . $container_id . '" class="pois-container pois-grid" data-apiurl="' . esc_attr( $api_url ) . '"></div>';
    // Pass the container ID to the JavaScript
    wp_localize_script( 'geotour-information-script', 'geotour_api_data', array(
        'apiUrl' => $api_url,
        'containerId' => $container_id, 
    ) );
    return $output;
}
add_shortcode( 'geotour-information', 'geotour_information_html' );

?>