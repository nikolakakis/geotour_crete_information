
//import $ from 'jquery';

var thelang = document.getElementsByTagName('html')[0].getAttribute('lang');

document.addEventListener('DOMContentLoaded', (event) => {
    const poiContainers = document.querySelectorAll('.pois-container');
    poiContainers.forEach(container => {
        // Get the container ID from the localized data
        const containerId = container.id; 

        // Initialize POIS with the container ID
        new POIS(containerId); 
    });
});

class POIS{
    constructor(containerId) { 
        // Select the container by ID (moved inside the constructor)
        this.container = document.getElementById(containerId); 

        // Check if the container element exists before accessing its dataset
        if (this.container) { 
            this.apiUrl = this.container.dataset.apiurl;
            this.getPOIData();
        } else {
            //console.error('POIS container element not found:', containerId);
        }
    }

    getPOIData() {
        console.log("API URL:", this.apiUrl); // Add this line
        fetch(this.apiUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                this.displayPOIs(data);   
            })
            .catch(error => {
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

export default POIS;