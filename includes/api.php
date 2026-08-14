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

// Deliberately English-only, unlike geotour_fetch_listings_proxy() above: this
// proxies The Events Calendar's own REST API, and tribe_events posts aren't a
// translatable post type in geotour-translate (GT_Language::translatable_post_types())
// — no Greek content exists for events anywhere in the system yet, so a
// language param here would have nothing to select. See SHARING-STRATEGY.md
// (geotour workspace root) for the licensing question this re-syndication
// raises and the plan for closing this gap.
function geotour_fetch_events_proxy( $request ) {
    $target_url = "https://www.geotour.gr/wp-json/tribe/events/v1/events";
    
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
    $lat      = $request->get_param('lat');
    $lon      = $request->get_param('lon');
    $category = $request->get_param('category');
    $items    = $request->get_param('items') ? $request->get_param('items') : 10;
    $radius   = $request->get_param('radius') ? $request->get_param('radius') : 10;

    $api_key = get_option('geotour_api_key');

    if ( empty( $api_key ) ) {
        return new WP_Error( 'geotour_error', 'API Key not configured', array( 'status' => 403 ) );
    }

    // Site-wide setting (Content Language section, admin-settings.php) — every
    // shortcode on this site requests the same language; there's no per-shortcode
    // override. Defaults to 'en' to match geotour.gr's own source language.
    $language = get_option( 'geotour_content_language', 'en' );

    $target_url = "https://www.geotour.gr/wp-json/panotours/v2/listings?items={$items}&language=" . urlencode( $language ) . "&lat={$lat}&lon={$lon}&radius={$radius}&category={$category}&apikey=" . urlencode( $api_key );

    $response = wp_remote_get( $target_url, array(
        'timeout' => 15,
        'headers' => array(
            'Referer' => home_url(),
        ),
    ) );

    if ( is_wp_error( $response ) ) {
        return new WP_Error( 'geotour_error', 'Failed to connect to data server', array( 'status' => 500 ) );
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body );

    $rest_response = rest_ensure_response( $data );

    // Forward the upstream privilege-level header — rest_ensure_response()
    // doesn't carry response headers through on its own, and the admin
    // preview/wizard reads this to show the account's API tier.
    $privilege_level = wp_remote_retrieve_header( $response, 'X-Geotour-Privilege-Level' );
    if ( $privilege_level ) {
        $rest_response->header( 'X-Geotour-Privilege-Level', $privilege_level );
    }

    return $rest_response;
}
