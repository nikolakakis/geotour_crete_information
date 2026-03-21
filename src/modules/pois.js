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
            const buttonText = hasPremiumData ? 'View Details' : 'View on Geotour';
            
            html += `
            <div class="poi-item">
                <div class="poi-image-wrapper"> 
                    <a class="${linkClass}" target="_blank" href="${poi.url}" data-index="${index}">
                        <img src="${poi.listingsThumbUrl}" alt="${poi.title} featured image" loading="lazy">
                        <div class="poi-categories">${poi.listingCategory ? poi.listingCategory.map(cat => cat.name).join(', ') : ''}</div>
                        <div class="poi-distance">${poi.distance} km</div>
                    </a>
                </div>
                <div class="poi-content">
                    <h3><a class="${linkClass}" target="_blank" href="${poi.url}" data-index="${index}">${poi.title}</a></h3>
                    <p class="poi-excerpt">${poi.excerpt}</p> 
                    <div class="poi-btn-wrapper">
                        <a class="${linkClass} poi-action-btn" target="_blank" href="${poi.url}" data-index="${index}">
                            ${buttonText} <span class="poi-btn-icon">→</span>
                        </a>
                    </div>
                </div>
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
                            <a id="geotour-modal-link" href="#" target="_blank" class="btn-read-more" rel="noopener noreferrer">Explore more on Geotour</a>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);

            // Add global event delegation exactly ONCE.
            if (!window.geotourModalEventsAttached) {
                window.geotourModalEventsAttached = true;

                document.addEventListener('click', (e) => {
                    // 1. Close Button & Overlay Clicking
                    if (e.target.closest('.geotour-modal-close') || e.target.id === 'geotour-modal-overlay') {
                        e.preventDefault();
                        const overlay = document.getElementById('geotour-modal-overlay');
                        if (overlay && overlay.classList.contains('active')) {
                            overlay.classList.add('closing');
                            setTimeout(() => {
                                overlay.classList.remove('active', 'closing');
                            }, 400); // Matches CSS transition duration
                        }
                    }

                    // 2. Slider Navigation Actions
                    const prevBtn = e.target.closest('.slider-nav-prev');
                    const nextBtn = e.target.closest('.slider-nav-next');
                    const dotBtn = e.target.closest('.slider-dot');

                    if (prevBtn && !prevBtn.classList.contains('disabled')) {
                        const slider = document.querySelector('.slider-container');
                        if(slider) slider.scrollBy({ left: -slider.clientWidth, behavior: 'smooth' });
                    }
                    if (nextBtn && !nextBtn.classList.contains('disabled')) {
                        const slider = document.querySelector('.slider-container');
                        if(slider) slider.scrollBy({ left: slider.clientWidth, behavior: 'smooth' });
                    }
                    if (dotBtn) {
                        const slider = document.querySelector('.slider-container');
                        const index = parseInt(dotBtn.dataset.index, 10);
                        if(slider) slider.scrollTo({ left: index * slider.clientWidth, behavior: 'smooth' });
                    }
                });
            }
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
            sliderHTML += `<div class="slider-counter">1 / ${mediaFiles.length}</div>`;
            sliderHTML += `
                <div class="slider-nav">
                    <button class="slider-nav-prev disabled" aria-label="Previous image">❮</button>
                    <button class="slider-nav-next" aria-label="Next image">❯</button>
                </div>
            `;
            let dotsHTML = '<div class="slider-pagination">';
            mediaFiles.forEach((_, i) => {
                dotsHTML += `<button class="slider-dot ${i === 0 ? 'active' : ''}" data-index="${i}" aria-label="Go to slide ${i+1}"></button>`;
            });
            dotsHTML += '</div>';
            sliderHTML += dotsHTML;
        }

        galleryContainer.innerHTML = sliderHTML;

        // Reset scroll position and attach listener for swiping/scrolling logic
        const sliderContainer = galleryContainer.querySelector('.slider-container');
        if (sliderContainer && mediaFiles.length > 1) {
            sliderContainer.scrollLeft = 0; // Ensure it starts at first slide

            const updateSliderState = () => {
                const index = Math.round(sliderContainer.scrollLeft / sliderContainer.clientWidth);
                const counter = galleryContainer.querySelector('.slider-counter');
                if (counter) counter.textContent = `${index + 1} / ${mediaFiles.length}`;
                
                const prevBtn = galleryContainer.querySelector('.slider-nav-prev');
                const nextBtn = galleryContainer.querySelector('.slider-nav-next');
                
                if (prevBtn) {
                    if (index === 0) prevBtn.classList.add('disabled');
                    else prevBtn.classList.remove('disabled');
                }
                
                if (nextBtn) {
                    if (index === mediaFiles.length - 1) nextBtn.classList.add('disabled');
                    else nextBtn.classList.remove('disabled');
                }

                const dots = galleryContainer.querySelectorAll('.slider-dot');
                dots.forEach((dot, i) => {
                    dot.classList.toggle('active', i === index);
                });
            };

            // Listen to scroll to seamlessly update UI during touch swiping and clicking bounds
            sliderContainer.addEventListener('scroll', updateSliderState, { passive: true });
        }

        // Open Modal
        document.getElementById('geotour-modal-overlay').classList.add('active');
    }
}

export default POIS;