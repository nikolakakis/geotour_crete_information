<div class="wrap">
    <h2>Events Shortcode Builder NOT IMPLEMENTED YET</h2>
    <p style="font-size: 16px;">The events sharing is a feature that will be added in the future</p>
    <form id="geotour-events-shortcode-form">
        <div id="map-events" style="height: 400px;"></div>
        <label for="lat-events">Latitude:</label>
        <input type="text" id="lat-events" name="lat" value="35.2" required>
        <label for="lon-events">Longitude:</label>
        <input type="text" id="lon-events" name="lon" value="25.1" required>
        <label for="radius-events">Radius:</label>
        <input type="number" id="radius-events" name="radius" value="10" required>
        <label for="max-items-events">Max Items:</label>
        <input type="number" id="max-items-events" name="max-items" value="12" required>
    </form>
    <h2>Generated Shortcode for events (not working at the moment)</h2>
    <textarea id="generated-shortcode-events" readonly></textarea>
    <h2>Preview</h2>
    <?php $api_key = get_option('geotour_api_key'); ?>
    <div id="preview-container-events" class="geotour-events-container" data-apiurl="https://www.geotour.gr/wp-json/tribe/events/v1/events?lat=35.2&lon=25.1&radius=10&max-items=12&apikey=<?php echo esc_attr($api_key); ?>"></div>
</div>