/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

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
class POIS {
  constructor(container) {
    this.container = document.querySelector('.pois-container');
    this.apiUrl = this.container.dataset.apiurl;
    this.getPOIData();
  }
  getPOIData() {
    console.log("API URL:", this.apiUrl); // Add this line
    fetch(this.apiUrl).then(response => {
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

/***/ }),

/***/ "./sass/main.scss":
/*!************************!*\
  !*** ./sass/main.scss ***!
  \************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


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
//import $ from 'jquery';

// Our modules / classes

const docready = new _modules_pois__WEBPACK_IMPORTED_MODULE_1__["default"]();
})();

/******/ })()
;
//# sourceMappingURL=index.js.map