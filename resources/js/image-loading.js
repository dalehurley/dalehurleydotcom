// Fix for image loading issues - comprehensive solution
document.addEventListener('DOMContentLoaded', function() {
    console.log('Image loading fix initialized');

    // Function to fix image opacity and visibility
    function fixImageOpacity(img) {
        // Force opacity to 1 and remove any problematic styles
        img.style.setProperty('opacity', '1', 'important');
        img.style.setProperty('visibility', 'visible', 'important');
        img.style.setProperty('display', '', '');

        // Remove any fade/transition classes that might be interfering
        img.classList.remove('fade', 'fade-in', 'fade-out');

        // Ensure the image is visible
        if (img.style.display === 'none') {
            img.style.display = '';
        }
    }

    // Immediately fix the hero image on homepage
    const heroImage = document.querySelector('img[src*="dale-hurley.jpg"]');
    if (heroImage) {
        console.log('Hero image found, fixing immediately');
        fixImageOpacity(heroImage);
        // Additional aggressive fix for hero image
        heroImage.style.setProperty('opacity', '1', 'important');
        heroImage.style.setProperty('visibility', 'visible', 'important');
        heroImage.style.setProperty('transition', 'none', 'important');
    }

    // Handle all existing images
    const allImages = document.querySelectorAll('img');
    console.log(`Found ${allImages.length} images to fix`);

    allImages.forEach((img, index) => {
        console.log(`Processing image ${index + 1}: ${img.src}`);

        // Immediately fix opacity
        fixImageOpacity(img);

        // Handle load events
        img.addEventListener('load', function() {
            console.log(`Image loaded: ${this.src}`);
            fixImageOpacity(this);
        });

        img.addEventListener('error', function() {
            console.warn(`Image failed to load: ${this.src}`);
            fixImageOpacity(this);
        });

        // If image is already loaded (cached)
        if (img.complete && img.naturalHeight !== 0) {
            console.log(`Image already loaded: ${img.src}`);
            fixImageOpacity(img);
        }
    });

    // Create a MutationObserver to handle dynamically added images
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    // Check if the node itself is an image
                    if (node.tagName === 'IMG') {
                        console.log(`New image detected: ${node.src}`);
                        fixImageOpacity(node);
                    }

                    // Check for images within the added node
                    const images = node.querySelectorAll ? node.querySelectorAll('img') : [];
                    images.forEach(img => {
                        console.log(`New nested image detected: ${img.src}`);
                        fixImageOpacity(img);
                    });
                }
            });
        });
    });

    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Additional check every 100ms for the first 2 seconds to catch any edge cases
    let checkCount = 0;
    const intervalCheck = setInterval(() => {
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            if (window.getComputedStyle(img).opacity !== '1') {
                console.log(`Fixing opacity for image: ${img.src}`);
                fixImageOpacity(img);
            }
        });

        // Extra attention to hero image
        const heroImg = document.querySelector('img[src*="dale-hurley.jpg"]');
        if (heroImg && window.getComputedStyle(heroImg).opacity !== '1') {
            console.log('Fixing hero image opacity');
            heroImg.style.setProperty('opacity', '1', 'important');
            heroImg.style.setProperty('visibility', 'visible', 'important');
        }

        checkCount++;
        if (checkCount >= 20) { // Check for 2 seconds (20 * 100ms)
            clearInterval(intervalCheck);
            console.log('Image loading fix monitoring complete');
        }
    }, 100);
});

// Global function for manual fixes
window.fixImageLoading = function(container = document) {
    const images = container.querySelectorAll('img');
    console.log(`Manually fixing ${images.length} images`);
    images.forEach(img => {
        img.style.setProperty('opacity', '1', 'important');
        img.style.setProperty('visibility', 'visible', 'important');
    });
};

// Override any CSS that might be causing issues
const style = document.createElement('style');
style.textContent = `
    img {
        opacity: 1 !important;
        visibility: visible !important;
    }

    img[loading="lazy"], img[loading="eager"] {
        opacity: 1 !important;
        visibility: visible !important;
    }

    img[src*="dale-hurley.jpg"] {
        opacity: 1 !important;
        visibility: visible !important;
        transition: none !important;
    }
`;
document.head.appendChild(style);
