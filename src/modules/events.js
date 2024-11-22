export default class EVENTS {
    constructor() {
      // Get shortcode parameters (make sure you have a way to access them, 
      // e.g., using wp_localize_script as mentioned before)
      this.userLat = parseFloat(geotourEventsParams.lat);  
      this.userLng = parseFloat(geotourEventsParams.lon);
      this.radius = parseFloat(geotourEventsParams.radius); 
  
      this.apiUrl = 'https://www.geotour.gr/wp-json/tribe/events/v1/events';
      this.fetchEvents();
    }
  
    async fetchEvents() {
      try {
        const response = await fetch(this.apiUrl);
        const data = await response.json();
        this.events = data.events;
        this.filterAndDisplayEvents();
      } catch (error) {
        console.error('Error fetching events:', error);
        // Add error handling logic here (e.g., display an error message)
      }
    }
  
    haversineDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Earth radius in km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = 
          Math.sin(dLat / 2) * Math.sin(dLat / 2) +
          Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
          Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        const distance = R * c; // Distance in km
        return distance;
    }
  
    filterAndDisplayEvents() {
      const filteredEvents = this.events.filter(event => {
        const eventLat = event.venue.geo_lat;
        const eventLng = event.venue.geo_lng;
        const distance = this.haversineDistance(this.userLat, this.userLng, eventLat, eventLng);        

        return distance <= this.radius;
      });
  
      // Generate HTML to display filteredEvents
      const eventsContainer = document.getElementById('geotour-events-container'); // Replace with your actual container ID
      if (eventsContainer) {
        eventsContainer.innerHTML = ''; // Clear previous content
       


        if (filteredEvents.length > 0) {            

            const maxItems = parseInt(geotourEventsParams['max-items'], 10) || 6; // Get maxItems, default to 6
            const displayedEvents = filteredEvents.slice(0, maxItems); // Limit the displayed events

            displayedEvents.forEach(event => {
                const eventDiv = document.createElement('div');
                eventDiv.classList.add('geotour-event');

                let organizerName = 'N/A';
                let organizerLink = '#'; // Default link if no organizer
                if (event.organizer && event.organizer.length > 0) {
                organizerName = event.organizer[0].organizer;
                organizerLink = event.organizer[0].url; 
                }

                const date = new Date(event.start_date);
                const day = date.toLocaleDateString('en-GB', { day: 'numeric' });
                const month = date.toLocaleDateString('en-GB', { month: 'long' });
                const year = date.toLocaleDateString('en-GB', { year: 'numeric' });  
                // Calculate date difference
                //const startDate = new Date(event.start_date);
                const endDate = new Date(event.end_date); 
                const daysDifference = Math.ceil((endDate - date) / (1000 * 60 * 60 * 24));

                // Generate "X days event" string
                const daysEventString = daysDifference > 1 ? `<p class="days-event">${daysDifference} days event</p>` : "";


                eventDiv.innerHTML = `
                    <a target="_blank" href="${event.url}"><div class="featured-image" style="background-image: url(${event.image.url});"></div></a> 
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
                        <p><span>Taking place in:</span> <a target="_blank" href="${event.venue.url}">${event.venue.venue}</a></p>
                        <p><span>Organizer:</span> <a target="_blank" href="${organizerLink}">${organizerName}</a></p> 
                        </div>
                    </div>
                `;
                eventsContainer.appendChild(eventDiv);
            });

            // Add "More Events" link if needed
            if (filteredEvents.length > maxItems) {
                const moreEventsLink = document.createElement('a');
                moreEventsLink.href = 'https://www.geotour.gr/events';
                moreEventsLink.target = '_blank';
                moreEventsLink.textContent = 'View More Events';
                eventsContainer.appendChild(moreEventsLink);
            }
        } else {
          eventsContainer.innerHTML = '<p>No events found within the specified radius.</p>';
        }
      }
    }
  }