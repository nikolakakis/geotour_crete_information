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
/** Haversine distance in km — mirrors events.js's own client-side copy. */
function geotour_events_distance_km( $lat1, $lon1, $lat2, $lon2 ) {
    $earth_radius = 6371;
    $d_lat = deg2rad( $lat2 - $lat1 );
    $d_lon = deg2rad( $lon2 - $lon1 );
    $a = sin( $d_lat / 2 ) ** 2 + cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * sin( $d_lon / 2 ) ** 2;
    return $earth_radius * 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
}

function geotour_fetch_events_proxy( $request ) {
    $target_url = "https://www.geotour.gr/wp-json/tribe/events/v1/events";

    // The Events Calendar's own REST API has no radius/geo filter to delegate
    // to, so every partner site used to receive geotour.gr's entire unfiltered
    // upcoming-events list on every single pageview, with radius filtering
    // happening only in the browser afterwards (events.js). Caching the raw
    // upstream fetch means that unavoidable "full list" cost is paid at most
    // once per 15 minutes total, not once per visitor.
    $cached_body = get_transient( 'geotour_events_cache' );

    if ( false === $cached_body ) {
        $response = wp_remote_get( $target_url, array(
            'timeout'     => 15,
            'headers'     => array(
                'Referer'   => home_url() // Explicitly sets the domain for validation
            )
        ) );

        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'geotour_error', 'Failed to connect to events server', array( 'status' => 500 ) );
        }

        $cached_body = wp_remote_retrieve_body( $response );
        set_transient( 'geotour_events_cache', $cached_body, 15 * MINUTE_IN_SECONDS );
    }

    $data = json_decode( $cached_body );

    // Server-side radius filtering — lat/lon/radius always reach this proxy
    // (see geotourEventsParams, scripts.php) but were previously ignored
    // entirely, so every visitor's browser downloaded and filtered the full
    // list itself regardless of the shortcode's configured radius.
    $lat    = $request->get_param( 'lat' );
    $lon    = $request->get_param( 'lon' );
    $radius = $request->get_param( 'radius' );

    if ( isset( $data->events ) && is_array( $data->events ) && is_numeric( $lat ) && is_numeric( $lon ) && is_numeric( $radius ) ) {
        $data->events = array_values( array_filter( $data->events, function ( $event ) use ( $lat, $lon, $radius ) {
            if ( empty( $event->venue->geo_lat ) || empty( $event->venue->geo_lng ) ) {
                return false;
            }
            return geotour_events_distance_km( (float) $lat, (float) $lon, (float) $event->venue->geo_lat, (float) $event->venue->geo_lng ) <= (float) $radius;
        } ) );
    }

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
