<?php
// Path: admin/partials/information-tab.php
?>

<div class="wrap">
    <h2>Information Shortcode Builder</h2>

    <div class="geotour-guidelines">
        <p>To use the wizard, you need to set the point on the map below first. From this point, all the listings from the selected categories that are within the specified radius will be displayed.</p>
        <p>The "Max Items" number determines the maximum number of listings that can be shown at a time.  The maximum number cannot exceed 20 per shortcode.</p>
        <p>Choose the content language for this shortcode below — each <code>[geotour-information]</code> shortcode can use a different one. The <a href="#geotour_content_language">Content Language</a> setting above is only the default for shortcodes that don't set their own.</p>
    </div>

    <form id="geotour-information-shortcode-form">

        <div class="geotour-step">
            <h3>Step 1 (REQUIRED). Choose a point</h3>
            <button type="button" id="set-default-location" class="geotour-default-location-button">Set Default Location</button> 
            <span style="margin-left: 20px;">If you want to set a new default location, change the map pin in the map below and click on the button.</span>
            <div id="map" class="geotour-map-container"></div>
        </div>

        <div class="geotour-step">
            <h3>Step 2. Choose Categories</h3>
            <div id="categories" class="geotour-categories-container">
                <label class="geotour-category-label">
                    <input type="checkbox" name="category" value="archaeological-site" checked>
                    <span class="geotour-checkbox-custom"></span>
                    Archaeological Sites
                </label>
                <label class="geotour-category-label">
                    <input type="checkbox" name="category" value="beach">
                    <span class="geotour-checkbox-custom"></span>
                    Beach
                </label>
                <label class="geotour-category-label">
                    <input type="checkbox" name="category" value="environment">
                    <span class="geotour-checkbox-custom"></span>
                    Environmental Points of Interest
                </label>
                <label class="geotour-category-label">
                    <input type="checkbox" name="category" value="point-of-archaeological-or-historical-interest">
                    <span class="geotour-checkbox-custom"></span>
                    Cultural Points of Interest
                </label>
                <label class="geotour-category-label">
                    <input type="checkbox" name="category" value="religion-pois-en">
                    <span class="geotour-checkbox-custom"></span>
                    Religion Points of Interest
                </label>
                <label class="geotour-category-label">
                    <input type="checkbox" name="category" value="villages-en">
                    <span class="geotour-checkbox-custom"></span>
                    Villages
                </label>
                <label class="geotour-category-label">
                    <input type="checkbox" name="category" value="fortifications">
                    <span class="geotour-checkbox-custom"></span>
                    Fortifications
                </label>
                <label class="geotour-category-label">
                    <input type="checkbox" name="category" value="ruins">
                    <span class="geotour-checkbox-custom"></span>
                    Ruins
                </label>
                <label class="geotour-category-label">
                    <input type="checkbox" name="category" value="museum-en">
                    <span class="geotour-checkbox-custom"></span>
                    Museums
                </label>
                <label class="geotour-category-label">
                    <input type="checkbox" name="category" value="locations-areas">
                    <span class="geotour-checkbox-custom"></span>
                    Locations & Areas
                </label>
            </div>
        </div>
        <div class="geotour-step step3-container">
           <h3>Step 3. Other parameters</h3>
           <div class="geotour-chosen-point-container">
              <label for="lat">Chosen Point:</label>
              <input type="text" id="lat" name="lat" value="35.2" required disabled>
              <input type="text" id="lon" name="lon" value="25.1" required disabled>
          </div>

          <div class="geotour-other-inputs">
              <label for="radius">Radius (km):</label>
              <input type="number" id="radius" name="radius" value="10" required>

              <label for="max-items">Max Items:</label>
              <input type="number" id="max-items" name="max-items" value="12" required>

              <label for="primary-color">Primary Color:</label>
              <input type="color" id="primary-color" name="primary-color" value="#0073aa">

              <label for="lang">Language:</label>
              <select id="lang" name="lang">
                  <?php
                  $current_default_lang = get_option( 'geotour_content_language', 'en' );
                  foreach ( geotour_crete_information_supported_languages() as $lang_code => $lang_label ) :
                  ?>
                      <option value="<?php echo esc_attr( $lang_code ); ?>" <?php selected( $current_default_lang, $lang_code ); ?>><?php echo esc_html( $lang_label ); ?></option>
                  <?php endforeach; ?>
              </select>
          </div>
      </div>
    </form>

    <div class="geotour-step geotour-final-step">
        <h3>Final Step. Generated Shortcode for the Points of Interest</h3>
        <p>Copy the shortcode below and paste it into your page or post content where you want the points of interest to appear.</p>
        <div class="geotour-shortcode-copy-container">
            <textarea id="generated-shortcode" readonly></textarea>
            <button id="copy-shortcode" class="geotour-copy-button">Copy Shortcode</button>
        </div>
    </div>

    <h2>Preview</h2>
    <?php
    $preview_api_url = add_query_arg(
        array(
            'lat'      => '35.2',
            'lon'      => '25.1',
            'radius'   => '10',
            'category' => 'environment,fortifications,locations-areas',
            'items'    => '12',
            'lang'     => $current_default_lang,
        ),
        rest_url( 'geotour/v1/listings' )
    );
    ?>
    <div id="preview-container" class="pois-container" data-apiurl="<?php echo esc_url( $preview_api_url ); ?>" data-lang="<?php echo esc_attr( $current_default_lang ); ?>"></div>
</div>