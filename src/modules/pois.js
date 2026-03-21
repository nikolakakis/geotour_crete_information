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
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                this.poisData = data;
                this.displayPOIs(data); 
                this.attachModalEvents();
            })
            .catch(error => {
                console.error("Error fetching POI data:", error);
                this.container.innerHTML = "<p>Error loading POIs.</p>";
            });
    }

    displayPOIs(pois) {
        let html = "";
        pois.forEach((poi, index) => {
            const hasPremiumData = poi.description || (poi.media_files && poi.media_files.length > 0);
            const linkClass = hasPremiumData ? 'geotour-poi-link premium-trigger' : 'geotour-poi-link';
            
            html += `
            <div class="poi-item">
                <a class="${linkClass}" target="_blank" href="${poi.url}" data-index="${index}">
                <div class="poi-image-wrapper"> 
                    <img src="${poi.listingsThumbUrl}" alt="${poi.title} featured image" loading="lazy">
                    <div class="poi-categories">${poi.listingCategory.map(cat => cat.name).join(', ')}</div>
                    <div class="poi-distance">${poi.distance} km</div>
                </div>
                </a>
                <h3><a class="${linkClass}" target="_blank" href="${poi.url}" data-index="${index}">${poi.title}</a></h3>
                <p class="poi-excerpt">${poi.excerpt}</p> 
            </div>
            `; 
        });

        this.container.innerHTML = html;
        this.createModalContainer();
    }

    createModalContainer() {
        if (!document.getElementById('geotour-modal-overlay')) {
            const modalHTML = `
                <div id="geotour-modal-overlay" class="geotour-modal-overlay">
                    <div class="geotour-modal-content">
                        <button class="geotour-modal-close" aria-label="Close modal">&times;</button>
                        <div id="geotour-modal-gallery" class="geotour-modal-gallery"></div>
                        <div class="geotour-modal-details">
                            <div id="geotour-modal-tags" class="modal-tags"></div>
                            <h2 id="geotour-modal-title" class="modal-title"></h2>
                            <div class="modal-meta">
                                <span id="geotour-modal-distance"></span>
                            </div>
                            <div id="geotour-modal-desc" class="modal-desc"></div>
                            <a id="geotour-modal-link" href="#" target="_blank" class="btn-read-more" rel="noopener noreferrer">Find out more</a>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);

            document.querySelector('.geotour-modal-close').addEventListener('click', () => {
                document.getElementById('geotour-modal-overlay').classList.remove('active');
            });
            document.getElementById('geotour-modal-overlay').addEventListener('click', (e) => {
                if (e.target.id === 'geotour-modal-overlay') {
                    document.getElementById('geotour-modal-overlay').classList.remove('active');
                }
            });
            
            // Slider Global Navigation Actions
            document.addEventListener('click', (e) => {
                if(e.target.closest('.slider-nav-prev')) {
                    const slider = document.querySelector('.slider-container');
                    if(slider) slider.scrollBy({ left: -slider.clientWidth, behavior: 'smooth' });
                }
                if(e.target.closest('.slider-nav-next')) {
                    const slider = document.querySelector('.slider-container');
                    if(slider) slider.scrollBy({ left: slider.clientWidth, behavior: 'smooth' });
                }
            });
        }
    }

    attachModalEvents() {
        const triggers = this.container.querySelectorAll('.premium-trigger');
        triggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault(); // Stop standard navigation
                const index = trigger.getAttribute('data-index');
                const poi = this.poisData[index];
                this.openModal(poi);
            });
        });
    }

    openModal(poi) {
        // Populate modal data
        document.getElementById('geotour-modal-title').textContent = poi.title;
        document.getElementById('geotour-modal-tags').textContent = poi.listingCategory ? poi.listingCategory.map(cat => cat.name).join(' • ') : '';
        document.getElementById('geotour-modal-distance').innerHTML = `📍 ${poi.distance} km away`;
        
        // Handle descriptions
        const descHTML = poi.description ? poi.description : `<p>${poi.excerpt}</p>`;
        document.getElementById('geotour-modal-desc').innerHTML = descHTML;
        
        document.getElementById('geotour-modal-link').href = poi.url;

        // Build Gallery
        const galleryContainer = document.getElementById('geotour-modal-gallery');
        let sliderHTML = '<div class="slider-container">';
        
        const mediaFiles = poi.media_files && poi.media_files.length > 0 ? poi.media_files : [{ large: poi.listingsThumbUrl, thumbnail: poi.listingsThumbUrl }];
        
        mediaFiles.forEach((media, i) => {
            // Lazy load except for first image
            sliderHTML += `
                <div class="slide">
                    <img src="${media.large}" alt="${poi.title} - Image ${i+1}" loading="${i === 0 ? 'eager' : 'lazy'}">
                </div>
            `;
        });
        sliderHTML += '</div>';

        if (mediaFiles.length > 1) {
            sliderHTML += `
                <div class="slider-nav">
                    <button class="slider-nav-prev" aria-label="Previous image">❮</button>
                    <button class="slider-nav-next" aria-label="Next image">❯</button>
                </div>
            `;
        }

        galleryContainer.innerHTML = sliderHTML;

        // Open Modal
        document.getElementById('geotour-modal-overlay').classList.add('active');
    }
}

export default POIS;