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

    // Serve from cache when another visitor (or the same one, reloading)
    // asked for the same parameters within the last 15 minutes, instead of
    // making geotour.gr do the work again. The key deliberately excludes the
    // API key, which is constant per site.
    $cache_key = 'geotour_listings_' . md5( wp_json_encode( array( $lat, $lon, $category, $items, $radius, $language ) ) );
    $cached = get_transient( $cache_key );
    if ( false !== $cached ) {
        return rest_ensure_response( $cached );
    }

    // 3. Build the target URL. The key is passed both as a header and as the
    // `apikey` query parameter in a single request — geotour.gr's endpoint
    // has historically been called with the URL form, so both are sent
    // together rather than firing two separate requests to find out which
    // one it honors.
    $target_url = "https://www.geotour.gr/wp-json/panotours/v2/listings?items={$items}&language=" . urlencode( $language ) . "&lat={$lat}&lon={$lon}&radius={$radius}&category={$category}&apikey=" . urlencode( $api_key );

    // 4. Make the secure server-to-server request
    $response = wp_remote_get( $target_url, array(
        'timeout'     => 15,
        'headers'     => array(
            'Referer'   => home_url(), // Explicitly sets the domain for validation
            'X-API-Key' => $api_key    // Pass key in header too
        )
    ) );

    // 5. Handle errors
    if ( is_wp_error( $response ) ) {
        return new WP_Error( 'geotour_error', 'Failed to connect to data server', array( 'status' => 500 ) );
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body );

    // Only cache a response that actually looks like data — an upstream
    // error shouldn't get pinned in place for 15 minutes.
    if ( 200 === wp_remote_retrieve_response_code( $response ) && null !== $data ) {
        set_transient( $cache_key, $data, 15 * MINUTE_IN_SECONDS );
    }

    // 6. Return data back to the frontend JS
    return rest_ensure_response( $data );
}
