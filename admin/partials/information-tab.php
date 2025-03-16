<?php
// Path: admin/partials/information-tab.php
?>

<div class="wrap">
    <h2>Information Shortcode Builder</h2>

    <div class="geotour-guidelines">
        <p>To use the wizard, you need to set the point on the map below first. From this point, all the listings from the selected categories that are within the specified radius will be displayed.</p>
        <p>The "Max Items" number determines the maximum number of listings that can be shown at a time.  The maximum number cannot exceed 20 per shortcode.</p>
        <p>Language is currently limited to English.</p>
    </div>

    <form id="geotour-information-shortcode-form">

        <div class="geotour-step">
            <h3>Step 1 (REQUIRED). Choose a point</h3>
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
        <div class="geotour-step">
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

              <label for="language">Language:</label>
              <select id="language" name="language">
                  <option value="en">English</option>
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
    <?php $api_key = get_option('geotour_api_key'); ?>
    <div id="preview-container" class="pois-container" data-apiurl="https://www.geotour.gr/wp-json/panotours/v2/listings?language=en&lat=35.2&lon=25.1&radius=10&category=environment,fortifications,locations-areas&max-items=12&apikey=<?php echo esc_attr($api_key); ?>"></div>
</div>