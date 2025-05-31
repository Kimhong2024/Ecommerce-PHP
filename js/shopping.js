function loadCart() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartItems = document.getElementById("cartItems");
    cartItems.innerHTML = ""; // Clear existing items

    // Create a table element
    const table = document.createElement('table');
    table.classList.add('min-w-full', 'bg-white', 'shadow-md', 'rounded-lg', 'overflow-hidden');

    // Create the table header
    const thead = document.createElement('thead');
    thead.innerHTML = `
        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
            <th scope="col" class="py-3 px-6 text-left">Product</th>
            <th scope="col" class="py-3 px-6 text-left">Quantity</th>
            <th scope="col" class="py-3 px-6 text-left">Price</th>
            <th scope="col" class="py-3 px-6 text-left">Total</th>
            <th scope="col" class="py-3 px-6 text-left">Actions</th>
        </tr>
    `;
    table.appendChild(thead);

    // Create the table body
    const tbody = document.createElement('tbody');
    let subtotal = 0;

    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;

        const row = document.createElement('tr');
        row.classList.add('border-b', 'border-gray-200', 'hover:bg-gray-100');

        row.innerHTML = `
            <td class="py-3 px-6 text-left">
                <div class="flex items-center">
                    <img src="${item.image}" alt="${item.name}"
                        class="w-12 h-12 object-cover rounded-md mr-4"
                        onerror="this.src='./images/placeholder.jpg'">
                    <span class="font-medium">${item.name}</span>
                </div>
            </td>
            <td class="py-3 px-6 text-left">
                <div class="flex items-center">
                    <button class="px-2 py-1 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition decrease-quantity"
                        data-index="${index}">
                        -
                    </button>
                    <span class="mx-2">${item.quantity}</span>
                    <button class="px-2 py-1 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition increase-quantity"
                        data-index="${index}">
                        +
                    </button>
                </div>
            </td>
            <td class="py-3 px-6 text-left">$${item.price.toFixed(2)}</td>
            <td class="py-3 px-6 text-left">$${itemTotal.toFixed(2)}</td>
            <td class="py-3 px-6 text-left">
                <button class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition remove-item"
                    data-index="${index}">
                    Remove
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    table.appendChild(tbody);
    cartItems.appendChild(table);

    // Calculate and display subtotal, tax, and total
    const taxRate = 0.08; // 8% tax
    const tax = subtotal * taxRate;
    const total = subtotal + tax;

    document.getElementById("subtotal").textContent = `$${subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}`;
    document.getElementById("tax").textContent = `$${tax.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}`;
    document.getElementById("cartTotal").textContent = `$${total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}`;
}

function updateQuantity(index, quantity) {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    if (cart[index]) {
        cart[index].quantity = Math.max(1, parseInt(quantity)); // Ensure quantity is at least 1
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCart();
    }
}

function removeFromCart(index) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart.splice(index, 1);
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart();
}

// Event delegation for quantity and remove buttons
document.getElementById('cartItems').addEventListener('click', (event) => {
    if (event.target.classList.contains('remove-item')) {
        const index = event.target.getAttribute('data-index');
        removeFromCart(index);
    } else if (event.target.classList.contains('decrease-quantity')) {
        const index = event.target.getAttribute('data-index');
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        if (cart[index] && cart[index].quantity > 1) {
            cart[index].quantity -= 1;
            localStorage.setItem('cart', JSON.stringify(cart));
            loadCart();
        }
    } else if (event.target.classList.contains('increase-quantity')) {
        const index = event.target.getAttribute('data-index');
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        if (cart[index]) {
            cart[index].quantity += 1;
            localStorage.setItem('cart', JSON.stringify(cart));
            loadCart();
        }
    }
});

window.onload = loadCart; // Call loadCart when the page loads

document.getElementById("checkoutButton").addEventListener("click", function () {
    console.log("Checkout button clicked"); // Debugging statement
    window.location.href = "index.php?p=CheckOut";
});