document.addEventListener('DOMContentLoaded', function() {

    // --- Image Thumbnail Interaction ---
    const mainImage = document.getElementById('mainImage');
    const thumbnails = document.querySelectorAll('.thumbnail-container .img-thumbnail');

    window.changeImage = function(newSrc) {
        if (mainImage) {
            mainImage.src = newSrc;
        }
        // Update active state on thumbnails
        thumbnails.forEach(thumb => {
            // Simple check based on src - might need refinement if URLs are complex
            if (thumb.getAttribute('onclick').includes(newSrc)) {
                thumb.classList.add('active');
            } else {
                thumb.classList.remove('active');
            }
        });
    }

    // Add initial active state based on main image src (if needed, assumes first thumb matches)
    if (thumbnails.length > 0 && mainImage) {
         // Set the first thumbnail as active initially if its onclick matches the main image src
         const firstThumbOnClick = thumbnails[0].getAttribute('onclick');
         if (firstThumbOnClick && firstThumbOnClick.includes(mainImage.src)) {
              thumbnails[0].classList.add('active');
         }
         // Add click listener to handle active state visually without relying solely on changeImage function
         thumbnails.forEach(thumb => {
            thumb.addEventListener('click', function() {
                thumbnails.forEach(t => t.classList.remove('active')); // Remove active from all
                this.classList.add('active'); // Add active to clicked one
                // The changeImage function called via onclick will handle the src change
            });
         });
    }


    // --- Color Swatch Interaction ---
    const colorSwatches = document.querySelectorAll('.color-swatch');
    const selectedColorSpan = document.getElementById('selectedColor');

    colorSwatches.forEach(swatch => {
        swatch.addEventListener('click', function() {
            // Remove active class from all swatches
            colorSwatches.forEach(s => s.classList.remove('active'));
            // Add active class to the clicked swatch
            this.classList.add('active');
            // Update the selected color text (optional)
            if (selectedColorSpan) {
                selectedColorSpan.textContent = this.dataset.color || ''; // Use data attribute
            }
            // You might want to store the selected color value elsewhere (e.g., hidden input)
            console.log('Selected color:', this.dataset.color);
        });
    });

    // --- Size Option Interaction ---
    const sizeOptions = document.querySelectorAll('.size-option');

    sizeOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all size options
            sizeOptions.forEach(o => o.classList.remove('active'));
            // Add active class to the clicked option
            this.classList.add('active');
            // You might want to store the selected size value elsewhere
            console.log('Selected size:', this.textContent);
        });
    });

    // --- Quantity Selector Interaction ---
    const quantityInput = document.getElementById('quantityInput');

    window.changeQuantity = function(amount) {
        if (quantityInput) {
            let currentValue = parseInt(quantityInput.value, 10);
            let newValue = currentValue + amount;

            // Prevent quantity from going below 1
            if (newValue < 1) {
                newValue = 1;
            }

            quantityInput.value = newValue;
            console.log('Quantity:', newValue);
        }
    }

    // --- Add to Cart / Wishlist Button Feedback (Example) ---
    const addToCartButton = document.querySelector('.btn-add-to-cart');
    const wishlistButton = document.querySelector('.btn-wishlist');

    if (addToCartButton) {
        addToCartButton.addEventListener('click', function() {
            // Example: Provide visual feedback or trigger an API call
            console.log('Add to Cart clicked');
            alert('Added to cart! (Placeholder)');
            // You would typically gather selected color, size, quantity here
        });
    }

    if (wishlistButton) {
        wishlistButton.addEventListener('click', function() {
            // Example: Toggle icon, provide feedback, or trigger an API call
            console.log('Add to Wishlist clicked');
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('far'); // Toggle between regular
                icon.classList.toggle('fas'); // and solid heart
            }
            alert('Added to wishlist! (Placeholder)');
        });
    }

});
