
//import $ from 'jquery';

var thelang = document.getElementsByTagName('html')[0].getAttribute('lang');


class POIS{
    constructor(container) {
        this.container = document.querySelector('.pois-container');
        this.apiUrl = this.container.dataset.apiurl;

        this.getPOIData();
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