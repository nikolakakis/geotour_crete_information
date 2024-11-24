<?php

/**
 * Add a top-level menu page.
 */
function geotour_plugin_menu() {
    add_menu_page(
        'Geotour Crete Information', // Page title
        'Geotour Crete', // Menu title
        'manage_options', // Capability required to access the page
        'geotour-crete-information', // Menu slug
        'geotour_plugin_options_page', // Callback function to render the page
        'dashicons-location-alt', // Icon (using Dashicons)
        20 // Position (adjust as needed)
    );
}
add_action( 'admin_menu', 'geotour_plugin_menu' );

/**
 * Callback function to render the plugin options page.
 */
function geotour_plugin_options_page() {
    $api_key = get_option( 'geotour_api_key' );
    $api_key_valid = geotour_check_api_key( $api_key ); // Check API key validity

    ?>
    <div class="wrap">
        
    <div class="geotour-plugin-header" style="text-align: center; margin-bottom: 20px;"> 
            <img src="<?php echo plugins_url( '../banner-772x250.png', __FILE__ ); ?>" alt="Geotour Crete Sharing Plugin Banner" style="max-width: 100%; height: auto;">
            <h1 style="margin-top: 10px;">Geotour Crete Sharing Plugin</h1> 
        </div>

        <div class="geotour-plugin-notice" style="border: 1px solid #31708f; background-color: #d9edf7; color: #31708f; padding: 10px; margin-bottom: 20px;">
            <p><em>You will need to obtain a valid API key from Geotour website. The API key is unique to a certain domain name. So if you have multiple domain names you will need multiple API keys.</em></p>
            <p>For more information about using this plugin, please visit: <a href="https://www.geotour.gr/about-geotour/geotour-share-plugin/" target="_blank">https://www.geotour.gr/about-geotour/geotour-share-plugin/</a></p>
        </div>
        <?php // if ( $api_key ) : ?>
            <!--
            <div class="notice notice-<?php // echo $api_key_valid ? 'success' : 'error'; ?>">
                <p>Geotour API Key is <?php // echo $api_key_valid ? 'valid' : 'invalid'; ?>.</p>
            </div>
        -->
        <?php // endif; ?>
        <form method="post" action="options.php">
            <?php 
            settings_fields( 'geotour_plugin_settings_group' );
            do_settings_sections( 'geotour-crete-information' );
            submit_button(); 
            ?>
        </form>
        <h2 style="margin: 10px 0px;">Shortcodes examples</h2>
            <p>Here are some short code examples that you can use to retrieve data from Geotour Crete<br />
                The parameteres used are:</p>
            <ul style="list-style-type: disc; padding-left: 15px; ">
                <li>lon: it is the longitude of the POSITION of either a Place (Listing) or a venue in case of an Event, which is a number and in Crete the value is between 23.4 and 26.3.</li>
                <li>lat: it is the latitude of the POSITION of either a Place (Listing) or a venue in case of an Event, which is a number and in Crete the value is between 34.7 and 35.7.</li>
                <li>radius: the kilometers from the position to search for Places, or Events based on the Venue of the event</li>
                <li>category: the categories of the place to search for. (No valid for Events). One or more values separated with a comma can be used to filter the Places. Some categories are: </li>
                <ul style="list-style-type: circle; padding-left: 35px; ">
                    <li>archaeological-site</li>
                    <li>religion-pois-en</li>
                    <li>beach</li>
                    <li>museum-en</li>
                    <li>villages-en</li>
                    <li>environment</li>
                    <li>fortifications</li>
                    <li>locations-areas</li>
                    <li>point-of-archaeological-or-historical-interest</li>
                </ul>
                <li>max-items: the maximum number of items to return for each shortcode</li>
            </ul>
            <h3>Shortcodes for Places</h3>
            <p><em>A valid API key is required for the specific domain that will use the shortcode.</em></p>
            <p><strong>Multiple shortcodes with different parameters can be used in the same page, eg one for beaches and another for archaeological sites and so on.</strong></p>
            <p>[geotour-information category="villages-en" lat="35.035" lon="24.789" max-items="5"]</p>
            <p>[geotour-information category="environment,fortifications,locations-areas" lat="35.2" lon="25.1" max-items="12" radius="5"]</p>
            <p>[geotour-information category="museum-en" lat="35.1" lon="24.8" max-items="3"]</p>
            <p>[geotour-information category="beach,restaurant" lat="35.3" lon="25.2" max-items="15"]</p>
            <h3>Shortcodes for Events</h3>
            <p><em>No key is required for this.</em></p>
            <p><strong>Only one event shortcode per page can exist.</strong></p>
            <p>[geotour_events lat="35.337042" lon="24.684551" radius="20" max-items="3"]</p>
       
    </div>
    <?php
}

/**
 * Function to check the validity of the API key.
 */
function geotour_check_api_key( $api_key ) {
    $test_url = 'https://www.geotour.gr/wp-json/panotours/v2/listings?language=en&lat=35.035557&lon=24.789770&radius=30&category=pois&apikey=' . $api_key;

    $response = wp_remote_get( $test_url );
    $response_body = wp_remote_retrieve_body( $response );
    $response_data = json_decode( $response_body );

    // Check for the "rest_forbidden" error code
    if ( isset( $response_data->code ) && $response_data->code === 'rest_forbidden' ) {
        return false;
    }

    return true; 
}

/**
 * Register the API key setting.
 */
function geotour_plugin_settings() {
    register_setting( 
        'geotour_plugin_settings_group', // Settings group name
        'geotour_api_key' // Option name
    );

    add_settings_section(
        'geotour_api_section', // Section ID
        'API Key Settings', // Section title
        'geotour_api_section_callback', // Callback function for section content
        'geotour-crete-information' // Page slug
    );

    add_settings_field(
        'geotour_api_key', // Field ID
        'API Key', // Field title
        'geotour_api_key_render', // Callback function to render the field
        'geotour-crete-information', // Page slug
        'geotour_api_section' // Section ID
    );
}
add_action( 'admin_init', 'geotour_plugin_settings' );

/**
 * Callback function for API key section content.
 */
function geotour_api_section_callback() {
    echo '<p>Enter your Geotour API key to enable information retrieval.</p>';
}

/**
 * Callback function to render the API key field.
 */
function geotour_api_key_render() {
    $api_key = get_option( 'geotour_api_key' ); 
    ?>
    <input type="text" name="geotour_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" />
    <?php
}

?>