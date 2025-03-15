<div class="wrap">
    <h2>Information Shortcode Builder</h2>
    <form id="geotour-information-shortcode-form">
        <div id="map" style="height: 400px;"></div>
        <label for="lat">Latitude:</label>
        <input type="text" id="lat" name="lat" value="35.2" required>
        <label for="lon">Longitude:</label>
        <input type="text" id="lon" name="lon" value="25.1" required>
        <label for="radius">Radius:</label>
        <input type="number" id="radius" name="radius" value="10" required>
        <label for="max-items">Max Items:</label>
        <input type="number" id="max-items" name="max-items" value="12" required>
        <label for="categories">Categories:</label>
        <div id="categories">
            <input type="checkbox" name="category" value="archaeological-site"> Archaeological Sites
            <input type="checkbox" name="category" value="beach"> Beach
            <input type="checkbox" name="category" value="environment"> Environmental Points of Interest
            <input type="checkbox" name="category" value="point-of-archaeological-or-historical-interest"> Cultural Points of Interest
            <input type="checkbox" name="category" value="religion-pois-en"> Religion Points of Interest
            <input type="checkbox" name="category" value="villages-en"> Villages
            <input type="checkbox" name="category" value="fortifications"> Fortifications
            <input type="checkbox" name="category" value="ruins"> Ruins
            <input type="checkbox" name="category" value="museum-en"> Museums
            <input type="checkbox" name="category" value="locations-areas"> Locations & Areas
            
        </div>
        <label for="language">Language:</label>
        <select id="language" name="language">
            <option value="en">English</option>
        </select>
    </form>
    <h2>Generated Shortcode</h2>
    <textarea id="generated-shortcode" readonly></textarea>
    <h2>Preview</h2>
    <?php $api_key = get_option('geotour_api_key'); ?>
    <div id="preview-container" class="pois-container" data-apiurl="https://www.geotour.gr/wp-json/panotours/v2/listings?language=en&lat=35.2&lon=25.1&radius=10&category=environment,fortifications,locations-areas&max-items=12&apikey=<?php echo esc_attr($api_key); ?>"></div>
</div>