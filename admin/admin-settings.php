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
        <h1>Geotour Crete Information Settings</h1>
        <p><em>You will need to obtain a valid API key from Geotour website. The API key is unique to a certain domain name. So if you have multiple domain names you will need multiple API keys.</em></p>
        <form method="post" action="options.php">
            <?php 
            settings_fields( 'geotour_plugin_settings_group' );
            do_settings_sections( 'geotour-crete-information' );
            submit_button(); 
            ?>
        </form>

        <?php if ( $api_key ) : ?>
            <div class="notice notice-<?php echo $api_key_valid ? 'success' : 'error'; ?>">
                <p>Geotour API Key is <?php echo $api_key_valid ? 'valid' : 'invalid'; ?>.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Function to check the validity of the API key.
 */
function geotour_check_api_key( $api_key ) {
    $test_url = 'https://www.geotour.gr/wp-json/panotours/v2/listings?language=en&lat=35.035557&lon=24.789770&radius=10&category=beach&apikey=' . $api_key;

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