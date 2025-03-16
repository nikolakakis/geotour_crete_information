document.addEventListener('DOMContentLoaded', function () {
    if (typeof L === 'undefined') {
        console.error('Leaflet library is not loaded.');
        return;
    }

    L.Icon.Default.imagePath = 'https://unpkg.com/leaflet@1.9.4/dist/images/';

    // Initialize Leaflet map for information shortcode
    if (typeof window.geotourMap !== 'undefined') {
        window.geotourMap.remove();
        delete window.geotourMap;
    }

    // Use default lat/lon from PHP, fallback if not available
    const defaultLat = geotourSettings.defaultLat || '35.035557';
    const defaultLon = geotourSettings.defaultLon || '24.789770';

    // --- UPDATES START HERE ---

    // 1. Set the input field values on page load.
    document.getElementById('lat').value = defaultLat;
    document.getElementById('lon').value = defaultLon;

    // 2. Update the preview URL.  We need to construct it dynamically.
    function updatePreviewUrl() {
        const apiKey = geotourSettings.apiKey;
        const lat = document.getElementById('lat').value;
        const lon = document.getElementById('lon').value;
        const radius = document.getElementById('radius').value;
        const maxItems = document.getElementById('max-items').value;
        const categories = Array.from(document.querySelectorAll('input[name="category"]:checked')).map(el => el.value).join(',');
        const language = document.getElementById('language').value; // Get selected language


        const apiUrl = `https://www.geotour.gr/wp-json/panotours/v2/listings?language=${language}&lat=${lat}&lon=${lon}&radius=${radius}&category=${categories}&items=${maxItems}&apikey=${apiKey}`;
        document.getElementById('preview-container').setAttribute('data-apiurl', apiUrl);
    }

    updatePreviewUrl(); // Call it once on load.
    generateShortcode(); //Also generate shortcode and make the preview on load


    // --- UPDATES END HERE ---

    window.geotourMap = L.map('map').setView([defaultLat, defaultLon], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(window.geotourMap);

    var marker = L.marker([defaultLat, defaultLon], { draggable: true }).addTo(window.geotourMap);
    marker.on('dragend', function (e) {
        var latlng = marker.getLatLng();
        document.getElementById('lat').value = latlng.lat;
        document.getElementById('lon').value = latlng.lng;
        debounceGenerateShortcode();
    });

    // Initialize Leaflet map for events shortcode
    if (typeof window.geotourMapEvents !== 'undefined') {
        window.geotourMapEvents.remove();
        delete window.geotourMapEvents;
    }
     // Use default lat/lon from PHP, fallback if not available
    const defaultLatEvents = geotourSettings.defaultLat || '35.035557';
    const defaultLonEvents = geotourSettings.defaultLon || '24.789770';

    window.geotourMapEvents = L.map('map-events').setView([defaultLatEvents, defaultLonEvents], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(window.geotourMapEvents);

    var markerEvents = L.marker([defaultLatEvents, defaultLonEvents], { draggable: true }).addTo(window.geotourMapEvents);
    markerEvents.on('dragend', function (e) {
        var latlng = markerEvents.getLatLng();
        document.getElementById('lat-events').value = latlng.lat;
        document.getElementById('lon-events').value = latlng.lng;
        debounceGenerateShortcodeEvents();
    });

    // Copy shortcode functionality
    const copyButton = document.getElementById('copy-shortcode');
    const shortcodeTextarea = document.getElementById('generated-shortcode');

    if (copyButton && shortcodeTextarea) { // Check if elements exist
        copyButton.addEventListener('click', function() {
            shortcodeTextarea.select();
            document.execCommand('copy');
            // Optional: Provide feedback to the user (e.g., change button text)
            copyButton.textContent = 'Copied!';
            setTimeout(() => { copyButton.textContent = 'Copy Shortcode'; }, 2000); // Reset after 2 seconds
        });
    }

    function generateShortcode() {
        var lat = document.getElementById('lat').value;
        var lon = document.getElementById('lon').value;
        var radius = document.getElementById('radius').value;
        var maxItems = document.getElementById('max-items').value;
        var categories = Array.from(document.querySelectorAll('input[name="category"]:checked')).map(function (el) {
            return el.value;
        }).join(',');
        var language = document.getElementById('language').value;

        var shortcode = `[geotour-information category="${categories}" lat="${lat}" lon="${lon}" max-items="${maxItems}" radius="${radius}" language="${language}"]`;
        document.getElementById('generated-shortcode').value = shortcode;

        // Fetch and display data in the preview container
        // var apiUrl = `https://www.geotour.gr/wp-json/panotours/v2/listings?language=${language}&lat=${lat}&lon=${lon}&radius=${radius}&category=${categories}&items=${maxItems}&apikey=${geotourSettings.apiKey}`; //OLD
        var apiUrl = document.getElementById('preview-container').getAttribute('data-apiurl'); // Get the URL from the data attribute
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                var html = '';
                data.forEach(poi => {
                    html += `
                        <div class="poi-item">
                            <a target="_blank" href="${poi.url}">
                                <div class="poi-image-wrapper">
                                    <img src="${poi.listingsThumbUrl}" alt="${poi.title} featured image">
                                    <div class="poi-categories">${poi.listingCategory.map(cat => cat.name).join(', ')}</div>
                                    <div class="poi-distance">${poi.distance} km</div>
                                </div>
                            </a>
                            <h3><a target="_blank" href="${poi.url}">${poi.title}</a></h3>
                            <p class="poi-excerpt">${poi.excerpt}</p>
                        </div>
                    `;
                });
                document.getElementById('preview-container').innerHTML = html;
            })
            .catch(error => {
                console.error('Error fetching POI data:', error);
                document.getElementById('preview-container').innerHTML = '<p>Error loading POIs.</p>';
            });
    }

    function generateShortcodeEvents() {
        var lat = document.getElementById('lat-events').value;
        var lon = document.getElementById('lon-events').value;
        var radius = document.getElementById('radius-events').value;
        var maxItems = document.getElementById('max-items-events').value;

        var shortcode = `[geotour_events lat="${lat}" lon="${lon}" max-items="${maxItems}" radius="${radius}"]`;
        document.getElementById('generated-shortcode-events').value = shortcode;

        // Fetch and display data in the preview container
        var apiUrl = `https://www.geotour.gr/wp-json/tribe/events/v1/events?lat=${lat}&lon=${lon}&radius=${radius}&max-items=${maxItems}&apikey=${geotourSettings.apiKey}`;
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                var html = '';
                data.forEach(event => {
                    html += `
                        <div class="event-item">
                            <a target="_blank" href="${event.url}">
                                <div class="event-image-wrapper">
                                    <img src="${event.image}" alt="${event.title} featured image">
                                    <div class="event-date">${event.date}</div>
                                </div>
                            </a>
                            <h3><a target="_blank" href="${event.url}">${event.title}</a></h3>
                            <p class="event-excerpt">${event.excerpt}</p>
                        </div>
                    `;
                });
                document.getElementById('preview-container-events').innerHTML = html;
            })
            .catch(error => {
                console.error('Error fetching event data:', error);
                document.getElementById('preview-container-events').innerHTML = '<p>Error loading events.</p>';
            });
    }

    var debounceTimer;
    function debounceGenerateShortcode() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            updatePreviewUrl(); // Update preview URL before fetching
            generateShortcode();
        }, 2000);
    }

    var debounceTimerEvents;
    function debounceGenerateShortcodeEvents() {
        clearTimeout(debounceTimerEvents);
        debounceTimerEvents = setTimeout(generateShortcodeEvents, 2000);
    }

    document.querySelectorAll('#geotour-information-shortcode-form input, #geotour-information-shortcode-form select').forEach(function (el) {
        el.addEventListener('input', debounceGenerateShortcode);
    });

    document.querySelectorAll('#geotour-events-shortcode-form input').forEach(function (el) {
        el.addEventListener('input', debounceGenerateShortcodeEvents);
    });

    // Tab switching functionality
    document.getElementById('information-tab-link').addEventListener('click', function (e) {
        e.preventDefault();
        document.getElementById('information-tab').style.display = 'block';
        document.getElementById('events-tab').style.display = 'none';
        document.getElementById('information-tab-link').classList.add('nav-tab-active');
        document.getElementById('events-tab-link').classList.remove('nav-tab-active');
        window.geotourMap.invalidateSize(); // Ensure the map is resized correctly
    });

    document.getElementById('events-tab-link').addEventListener('click', function (e) {
        e.preventDefault();
        document.getElementById('information-tab').style.display = 'none';
        document.getElementById('events-tab').style.display = 'block';
        document.getElementById('information-tab-link').classList.remove('nav-tab-active');
        document.getElementById('events-tab-link').classList.add('nav-tab-active');
        window.geotourMapEvents.invalidateSize(); // Ensure the map is resized correctly
    });

    // Initialize the default tab
    document.getElementById('information-tab').style.display = 'block';
    document.getElementById('events-tab').style.display = 'none';
    window.geotourMap.invalidateSize(); // Ensure the map is resized correctly on load

    // Add event listener for the "Set Default Location" button
    document.getElementById('set-default-location').addEventListener('click', function() {
        const lat = marker.getLatLng().lat;
        const lon = marker.getLatLng().lng;

        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'geotour_set_default_location',
                lat: lat,
                lon: lon,
                _wpnonce: geotourSettings.nonce
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Default location updated successfully!');
            } else {
                alert('Failed to update default location.');
            }
        });
    });
});