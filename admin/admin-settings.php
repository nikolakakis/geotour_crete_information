<?php
// admin/admin-settings.php
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
add_action('admin_menu', 'geotour_plugin_menu');

/**
* Enqueue admin scripts and styles.
*/
add_action('admin_enqueue_scripts', 'geotour_admin_enqueue_scripts');
function geotour_admin_enqueue_scripts($hook) {
      if ('toplevel_page_geotour-crete-information' === $hook) {
        wp_enqueue_style('leaflet-css', 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
        wp_enqueue_script('leaflet-js', 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true);
        wp_enqueue_script('geotour-shortcode-wizard', plugin_dir_url(__FILE__) . 'js/shortcode-wizard.js', array('jquery', 'leaflet-js'), '1.0.0', true);
        wp_enqueue_style('geotour-admin-styles', plugin_dir_url(__FILE__) . 'css/admin-styles.css', array(), '1.0.0');
        wp_enqueue_script('geotour-index', plugin_dir_url(__FILE__) . '../build/index.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('geotour-index-styles', plugin_dir_url(__FILE__) . '../build/index.css', array(), '1.0.0');
    
        $api_key = get_option('geotour_api_key');
            $default_lat = get_option('geotour_default_lat', '35.035557');
            $default_lon = get_option('geotour_default_lon', '24.789770');
    
        wp_localize_script('geotour-shortcode-wizard', 'geotourSettings', array(
          'apiKey' => $api_key,
          'nonce' => wp_create_nonce('geotour_set_default_location'),
                'defaultLat' => $default_lat,
                'defaultLon' => $default_lon
        ));
    
            // TEMPORARY FIX:  Define geotourEventsParams to prevent the error.
            wp_localize_script('geotour-index', 'geotourEventsParams', array());
      }
    }

/**
* Callback function to render the plugin options page.
*/
function geotour_plugin_options_page() {
  $api_key = get_option('geotour_api_key');
  $default_lat = get_option('geotour_default_lat', '35.035557');
  $default_lon = get_option('geotour_default_lon', '24.789770');
  $api_key_valid = geotour_check_api_key($api_key); // Check API key validity

  ?>
  <div class="wrap">
    <div class="geotour-plugin-header" style="text-align: center; margin-bottom: 20px;">
      <img src="<?php echo plugins_url('../banner-772x250.png', __FILE__); ?>" alt="Geotour Crete Sharing Plugin Banner" style="max-width: 100%; height: auto;">
      <h1 style="margin-top: 10px;">Geotour Crete Sharing Plugin</h1>
    </div>

    <div class="geotour-plugin-notice" style="border: 1px solid #31708f; background-color: #d9edf7; color: #31708f; padding: 10px; margin-bottom: 20px;">
      <p><em>You will need to obtain a valid API key from Geotour website. The API key is unique to a certain domain name. So if you have multiple domain names you will need multiple API keys.</em></p>
      <p>For more information about using this plugin, please visit: <a href="https://www.geotour.gr/about-geotour/geotour-share-plugin/" target="_blank">https://www.geotour.gr/about-geotour/geotour-share-plugin/</a></p>
    </div>

    <form method="post" action="options.php">
      <?php
      settings_fields('geotour_plugin_settings_group');
      do_settings_sections('geotour-crete-information');
      submit_button();
      ?>
    </form>

    <div class="nav-tab-wrapper">
      <a href="#information-tab" id="information-tab-link" class="nav-tab nav-tab-active">Information Shortcode</a>
      <a href="#events-tab" id="events-tab-link" class="nav-tab">Events Shortcode</a>
    </div>

    <div id="information-tab" class="tab-content">
      <?php include(plugin_dir_path(__FILE__) . 'partials/information-tab.php'); ?>
    </div>

    <div id="events-tab" class="tab-content">
      <?php include(plugin_dir_path(__FILE__) . 'partials/events-tab.php'); ?>
    </div>
  </div>
  <?php
}

/**
* Function to check the validity of the API key.
*/
function geotour_check_api_key($api_key) {
  $test_url = 'https://www.geotour.gr/wp-json/panotours/v2/listings?language=en&lat=35.035557&lon=24.789770&radius=30&category=pois&apikey=' . $api_key;

  $response = wp_remote_get($test_url);
  $response_body = wp_remote_retrieve_body($response);
  $response_data = json_decode($response_body);

  // Check for the "rest_forbidden" error code
  if (isset($response_data->code) && $response_data->code === 'rest_forbidden') {
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
    'geotour_api_key'        // Option name
  );

  // Register new options for default latitude and longitude
  register_setting(
    'geotour_plugin_settings_group',
    'geotour_default_lat'
  );
  register_setting(
    'geotour_plugin_settings_group',
    'geotour_default_lon'
  );

  add_settings_section(
    'geotour_api_section',      // Section ID
    'API Key Settings',       // Section title
    'geotour_api_section_callback', // Callback function for section content
    'geotour-crete-information'   // Page slug
  );

  add_settings_field(
    'geotour_api_key',        // Field ID
    'API Key',            // Field title
    'geotour_api_key_render',    // Callback function to render the field
    'geotour-crete-information',   // Page slug
    'geotour_api_section'      // Section ID
  );
}
add_action('admin_init', 'geotour_plugin_settings');

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
  $api_key = get_option('geotour_api_key');
  ?>
  <input type="text" name="geotour_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
  <?php
}

function geotour_set_default_location() {
  check_ajax_referer('geotour_set_default_location');

  if (isset($_POST['lat']) && isset($_POST['lon'])) {
    update_option('geotour_default_lat', sanitize_text_field($_POST['lat']));
    update_option('geotour_default_lon', sanitize_text_field($_POST['lon']));
    wp_send_json_success();
  } else {
    wp_send_json_error();
  }
}
add_action('wp_ajax_geotour_set_default_location', 'geotour_set_default_location');