document.addEventListener('DOMContentLoaded', function () {
    const sliderTrack = document.querySelector('.slider-track');
    const sliderItems = document.querySelectorAll('.slider-item');
    const prevButton = document.querySelector('.slider-button.prev');
    const nextButton = document.querySelector('.slider-button.next');

    let currentIndex = 0;
    const totalItems = sliderItems.length;

    // Function to update the slider position
    function updateSliderPosition() {
        const itemWidth = sliderItems[0].clientWidth;
        sliderTrack.style.transform = `translateX(-${currentIndex * itemWidth}px)`;
    }

    // Event listener for the next button
    nextButton.addEventListener('click', function () {
        if (currentIndex < totalItems - 1) {
            currentIndex++;
        } else {
            currentIndex = 0; // Loop back to the first item
        }
        updateSliderPosition();
    });

    // Event listener for the previous button
    prevButton.addEventListener('click', function () {
        if (currentIndex > 0) {
            currentIndex--;
        } else {
            currentIndex = totalItems - 1; // Loop to the last item
        }
        updateSliderPosition();
    });

    // Optional: Auto-slide functionality
    let autoSlideInterval;

    function startAutoSlide() {
        autoSlideInterval = setInterval(function () {
            if (currentIndex < totalItems - 1) {
                currentIndex++;
            } else {
                currentIndex = 0;
            }
            updateSliderPosition();
        }, 3000); // Change slide every 3 seconds
    }

    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
    }

    // Start auto-slide when the page loads
    startAutoSlide();

    // Optional: Pause auto-slide when hovering over the slider
    sliderTrack.addEventListener('mouseenter', stopAutoSlide);
    sliderTrack.addEventListener('mouseleave', startAutoSlide);
});





 // Function to show toast notifications
 function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast');

    // Create toast element
    const toast = document.createElement('div');
    toast.classList.add('px-6', 'py-4', 'rounded-lg', 'shadow-lg', 'text-white', 'flex', 'items-center', 'space-x-4', 'transition-opacity', 'duration-300', 'opacity-0');

    // Set background color based on type
    if (type === 'success') {
        toast.classList.add('bg-green-500');
    } else if (type === 'error') {
        toast.classList.add('bg-red-500');
    }

    // Add message to toast
    toast.innerHTML = `
        <span>${message}</span>
    `;

    // Add toast to container
    toastContainer.appendChild(toast);

    // Trigger reflow to enable transition
    setTimeout(() => {
        toast.classList.remove('opacity-0');
        toast.classList.add('opacity-100');
    }, 10);

    // Automatically remove toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('opacity-100');
        toast.classList.add('opacity-0');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Function to update the cart counter
function updateCartCounter() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartCounter = document.getElementById('cartCounter');
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    cartCounter.textContent = totalItems;
}

// Function to add item to cart
function addToCart(productName, productPrice, productImage) {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const existingItem = cart.find(item => item.name === productName);

    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            name: productName,
            price: parseFloat(productPrice),
            image: productImage,
            quantity: 1
        });
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCounter(); // Update the cart counter
    showToast(`${productName} added to cart!`, 'success');
}

// Initialize the cart counter when the page loads
window.onload = () => {
    updateCartCounter();
};

// Add event listeners to all "Add to Cart" buttons
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', () => {
        const productName = button.getAttribute('data-name');
        const productPrice = button.getAttribute('data-price');
        const productImage = button.getAttribute('data-image');
        addToCart(productName, productPrice, productImage);
    });
});



//////////////////===/////////
const btn = document.querySelector("button.mobile-menu-button");
const menu = document.querySelector(".mobile-menu");

btn.addEventListener("click", () => {
  menu.classList.toggle("hidden");
});



