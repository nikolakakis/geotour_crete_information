<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'geotour/v1', '/listings', array(
        'methods' => 'GET',
        'callback' => 'geotour_fetch_listings_proxy',
        'permission_callback' => '__return_true' // Allow public frontend access
    ) );
    
    register_rest_route( 'geotour/v1', '/events', array(
        'methods' => 'GET',
        'callback' => 'geotour_fetch_events_proxy',
        'permission_callback' => '__return_true' // Allow public frontend access
    ) );
} );

function geotour_fetch_events_proxy( $request ) {
    // geotour.gr replaced The Events Calendar with its own "geotour_events"
    // system — this now proxies its geotour/v3/nearby-events feed instead of
    // Tribe's REST API. That feed already filters by distance and sorts
    // nearest-first server-side, so this proxy just passes the shortcode's
    // lat/lon/radius/max_items straight through instead of fetching
    // everything and filtering in the browser like the old Tribe-backed
    // version did.
    $params = array_filter( array(
        'lat'       => $request->get_param( 'lat' ),
        'lon'       => $request->get_param( 'lon' ),
        'radius'    => $request->get_param( 'radius' ),
        'max_items' => $request->get_param( 'max_items' ),
    ), function ( $value ) {
        return $value !== null && $value !== '';
    } );

    $target_url = add_query_arg( $params, 'https://www.geotour.gr/wp-json/geotour/v3/nearby-events' );

    $response = wp_remote_get( $target_url, array(
        'timeout'     => 15,
        'headers'     => array(
            'Referer'   => home_url() // Explicitly sets the domain for validation
        )
    ) );

    if ( is_wp_error( $response ) ) {
        return new WP_Error( 'geotour_error', 'Failed to connect to events server', array( 'status' => 500 ) );
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body );

    return rest_ensure_response( $data );
}

function geotour_fetch_listings_proxy( $request ) {
    // 1. Get parameters from the local JS request
    $lat = $request->get_param('lat');
    $lon = $request->get_param('lon');
    $category = $request->get_param('category');
    $items = $request->get_param('items') ? $request->get_param('items') : 10;
    $radius = $request->get_param('radius') ? $request->get_param('radius') : 10;
    
    // 2. Retrieve the hidden API key from the WP options table
    $api_key = get_option('geotour_api_key');

    // Validate required parameters (at least coordinates and key, although key is in options)
    if (empty($api_key)) {
        return new WP_Error( 'geotour_error', 'API Key not configured', array( 'status' => 403 ) );
    }

    // Per-shortcode content language, passed through from [geotour-information
    // lang="..."] (see geotour_information_html() in shortcodes.php). Falls
    // back to the site-wide "Content Language" setting for any caller that
    // doesn't send one — e.g. an older cached page, or a direct REST call.
    // geotour.gr's REST layer degrades unknown/unpublished languages to
    // English with HTTP 200, so an unrecognized value is still safe.
    $language = $request->get_param( 'lang' ) ? $request->get_param( 'lang' ) : get_option( 'geotour_content_language', 'en' );

    // 3. Build the target URL
    $target_url = "https://www.geotour.gr/wp-json/panotours/v2/listings?items={$items}&language=" . urlencode( $language ) . "&lat={$lat}&lon={$lon}&radius={$radius}&category={$category}";

    // 4. Make the secure server-to-server request
    $response = wp_remote_get( $target_url, array(
        'timeout'     => 15,
        'headers'     => array(
            'Referer'   => home_url(), // Explicitly sets the domain for validation
            'X-API-Key' => $api_key    // Pass key in header
        )
    ) );

    // fallback: also append the apikey parameter if the API expects it in URL. The TODO says: "Pass key in header (recommended) or URL". We can do both to be safe, or just append it to the URL if the current code did so.
    // The shortcode currently appends `&apikey=` to url. Let's do that for backwards compatibility on the Geotour server.
    $target_url .= "&apikey=" . urlencode($api_key);
    $response = wp_remote_get( $target_url, array(
        'timeout'     => 15,
        'headers'     => array(
            'Referer'   => home_url() // Explicitly sets the domain for validation
        )
    ) );

    // 5. Handle errors
    if ( is_wp_error( $response ) ) {
        return new WP_Error( 'geotour_error', 'Failed to connect to data server', array( 'status' => 500 ) );
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body );

    // 6. Return data back to the frontend JS
    return rest_ensure_response( $data );
}
