/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./sass/main.scss":
/*!************************!*\
  !*** ./sass/main.scss ***!
  \************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./src/modules/events.js":
/*!*******************************!*\
  !*** ./src/modules/events.js ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ EVENTS)
/* harmony export */ });
// /src/modules/events.js
class EVENTS {
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
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
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
          const day = date.toLocaleDateString('en-GB', {
            day: 'numeric'
          });
          const month = date.toLocaleDateString('en-GB', {
            month: 'long'
          });
          const year = date.toLocaleDateString('en-GB', {
            year: 'numeric'
          });
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

/***/ }),

/***/ "./src/modules/pois.js":
/*!*****************************!*\
  !*** ./src/modules/pois.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
//import $ from 'jquery';

var thelang = document.getElementsByTagName('html')[0].getAttribute('lang');
document.addEventListener('DOMContentLoaded', event => {
  const poiContainers = document.querySelectorAll('.pois-container');
  poiContainers.forEach(container => {
    // Get the container ID from the localized data
    const containerId = container.id;

    // Initialize POIS with the container ID
    new POIS(containerId);
  });
});
class POIS {
  constructor(containerId) {
    // Select the container by ID (moved inside the constructor)
    this.container = document.getElementById(containerId);

    // Check if the container element exists before accessing its dataset
    if (this.container) {
      this.apiUrl = this.container.dataset.apiurl;
      this.radius = this.container.dataset.radius || 10; // Default radius
      this.items = this.container.dataset.items || 12; // Default items
      this.getPOIData();
    } else {
      //console.error('POIS container element not found:', containerId);
    }
  }
  getPOIData() {
    const url = `${this.apiUrl}`;
    console.log("API URL:", url);
    fetch(url).then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    }).then(data => {
      this.displayPOIs(data);
    }).catch(error => {
      console.error("Error fetching POI data:", error);
      this.container.innerHTML = "<p>Error loading POIs.</p>";
    });
  }
  displayPOIs(pois) {
    let html = "";
    pois.forEach(poi => {
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
    this.container.innerHTML = html;
  }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (POIS);

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _sass_main_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../sass/main.scss */ "./sass/main.scss");
/* harmony import */ var _modules_pois__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modules/pois */ "./src/modules/pois.js");
/* harmony import */ var _modules_events__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./modules/events */ "./src/modules/events.js");
//import $ from 'jquery';

// Our modules / classes


const docready = new _modules_pois__WEBPACK_IMPORTED_MODULE_1__["default"]();
const theevents = new _modules_events__WEBPACK_IMPORTED_MODULE_2__["default"]();
})();

/******/ })()
;
//# sourceMappingURL=index.js.map