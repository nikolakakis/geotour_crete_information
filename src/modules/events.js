// /src/modules/events.js
export default class EVENTS {
    constructor() {
      // The [geotour_events] shortcode names its container
      // "geotour-events-container-{uniqid}" (see geotour_events_shortcode()
      // in includes/shortcodes.php) — this was previously looking for the
      // bare, unsuffixed id, which never matched anything, so this class
      // fetched from the API on every single page load regardless of
      // whether the shortcode was even used. Finding the real element first
      // fixes both: it now only runs on a page that actually has the
      // shortcode, and it actually finds the box to fill in when it does.
      this.container = document.querySelector('[id^="geotour-events-container"]');
      if (!this.container) {
        return;
      }

      // Get shortcode parameters (make sure you have a way to access them,
      // e.g., using wp_localize_script as mentioned before)
      this.userLat = parseFloat(geotourEventsParams.lat);
      this.userLng = parseFloat(geotourEventsParams.lon);
      this.radius = parseFloat(geotourEventsParams.radius);
      this.maxItems = parseInt(geotourEventsParams['max-items'], 10) || 6;

      // geotour.gr's own geotour/v3/nearby-events feed now does the
      // distance filtering and sorting server-side, so this just passes the
      // shortcode's values straight through instead of fetching everything
      // and filtering it in the browser like the old Tribe-backed version did.
      const params = new URLSearchParams({
        lat: this.userLat,
        lon: this.userLng,
        radius: this.radius,
        max_items: this.maxItems,
      });
      this.apiUrl = `/wp-json/geotour/v1/events?${params.toString()}`;
      this.fetchEvents();
    }

    async fetchEvents() {
      try {
        const response = await fetch(this.apiUrl);
        const data = await response.json();
        this.events = Array.isArray(data) ? data : [];
        this.displayEvents();
      } catch (error) {
        console.error('Error fetching events:', error);
        // Add error handling logic here (e.g., display an error message)
      }
    }

    displayEvents() {
      // Generate HTML to display events
      const eventsContainer = this.container;
      if (eventsContainer) {
        eventsContainer.innerHTML = ''; // Clear previous content

        if (this.events.length > 0) {

            this.events.forEach(event => {
                const eventDiv = document.createElement('div');
                eventDiv.classList.add('geotour-event');

                const organizerName = event.organizer_name || 'N/A';
                const organizerLink = event.organizer_url || '#';

                const startDate = new Date(event.start.replace(' ', 'T'));
                const day = startDate.toLocaleDateString('en-GB', { day: 'numeric' });
                const month = startDate.toLocaleDateString('en-GB', { month: 'long' });
                const year = startDate.toLocaleDateString('en-GB', { year: 'numeric' });

                // Generate "X days event" string
                let daysEventString = '';
                if (event.end) {
                    const endDate = new Date(event.end.replace(' ', 'T'));
                    const daysDifference = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
                    daysEventString = daysDifference > 1 ? `<p class="days-event">${daysDifference} days event</p>` : '';
                }

                const imageStyle = event.image ? ` style="background-image: url(${event.image});"` : '';

                eventDiv.innerHTML = `
                    <a target="_blank" href="${event.url}"><div class="featured-image"${imageStyle}></div></a>
                    <h3><a href="${event.url}">${event.title}</a></h3>
                    <div class="event-details">
                        <div class="column">
                        <p class="date">
                            <span class="day">${day}</span>
                            <span class="month">${month}</span>
                            <span class="year">${year}</span>
                        </p>
                        <div class="event-duration">${daysEventString}</div>
                        </div>
                        <div class="column">
                        ${event.venue_name ? `<p><span>Taking place in:</span> ${event.venue_url ? `<a target="_blank" href="${event.venue_url}">${event.venue_name}</a>` : event.venue_name}</p>` : ''}
                        ${event.organizer_name ? `<p><span>Organizer:</span> <a target="_blank" href="${organizerLink}">${organizerName}</a></p>` : ''}
                        </div>
                    </div>
                `;
                eventsContainer.appendChild(eventDiv);
            });

            // "More Events" link if the server had more within range than we asked for
            const moreEventsLink = document.createElement('a');
            moreEventsLink.href = 'https://www.geotour.gr/events/';
            moreEventsLink.target = '_blank';
            moreEventsLink.textContent = 'View More Events';
            eventsContainer.appendChild(moreEventsLink);
        } else {
          eventsContainer.innerHTML = '<p>No events found within the specified radius.</p>';
        }
      }
    }
  }
