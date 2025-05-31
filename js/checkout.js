// Function to load cart items into the checkout page
function loadCart() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartItems = document.getElementById('cartItems');
    cartItemsCheckout.innerHTML = ''; // Clear existing items

    let subtotal = 0;
    cart.forEach((item) => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;

        const cartItem = document.createElement('div');
        cartItem.classList.add('flex', 'justify-between', 'items-center');
        cartItem.innerHTML = `
            <div class="flex items-center">
                <img src="${item.image}" alt="${item.name}" class="w-12 h-12 object-cover rounded-md mr-4">
                <div>
                    <h3 class="text-lg font-semibold">${item.name}</h3>
                    <p class="text-gray-500">Qty: ${item.quantity}</p>
                </div>
            </div>
            <span class="text-blue-500 font-bold">$${itemTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}</span>
        `;
        cartItems.appendChild(cartItem);
    });

    const taxRate = 0.08; // 8% tax
    const tax = subtotal * taxRate;
    const total = subtotal + tax;

    document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}`;
    document.getElementById('tax').textContent = `$${tax.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}`;
    document.getElementById('cartTotal').textContent = `$${total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}`;

    return total; // Return the total for PayPal
}

// Load cart when the page loads
const totalAmount = loadCart();

// Render the PayPal button
paypal.Buttons({
    createOrder: function (data, actions) {
        return actions.order.create({
            purchase_units: [{
                amount: {
                    value: totalAmount.toFixed(2) // Total amount from the cart
                }
            }]
        });
    },
    onApprove: function (data, actions) {
        return actions.order.capture().then(function (details) {
            alert('Payment successful! Thank you, ' + details.payer.name.given_name + '.');
            // Clear the cart after successful payment
            localStorage.removeItem('cart');
            window.location.href = './Home.html'; // Redirect to home page
        });
    },
    onError: function (err) {
        console.error('Payment failed:', err);
        alert('Payment failed. Please try again.');
    }
}).render('#paypal-button-container');