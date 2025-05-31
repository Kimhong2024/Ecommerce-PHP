<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'admin/include/db.php';
?>


    <!-- Header - can include your existing header here -->
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Shopping Cart</h1>
            <a href="index.php?p=Shop" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Continue Shopping
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div id="cart-items" class="space-y-4">
                        <!-- Cart items will be populated by JavaScript -->
                    </div>
                    <div id="empty-cart" class="text-center py-8 hidden">
                        <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
                        <h2 class="text-2xl font-semibold text-gray-600 mb-2">Your cart is empty</h2>
                        <p class="text-gray-500 mb-6">Looks like you haven't added anything to your cart yet.</p>
                        <a href="index.php?p=Shop" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Start Shopping
                        </a>
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Shipping:</span>
                            <span id="shipping">$10.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tax (8%):</span>
                            <span id="tax">$0.00</span>
                        </div>
                        <hr class="my-3">
                        <div class="flex justify-between text-lg font-semibold">
                            <span>Total:</span>
                            <span id="total">$0.00</span>
                        </div>
                    </div>
                    <button id="checkout-btn" class="w-full mt-6 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Proceed to Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadCart();
            
            // Load cart from localStorage
            function loadCart() {
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                const cartItemsContainer = document.getElementById('cart-items');
                const emptyCart = document.getElementById('empty-cart');
                
                if (cart.length === 0) {
                    cartItemsContainer.classList.add('hidden');
                    emptyCart.classList.remove('hidden');
                    return;
                }
                
                cartItemsContainer.classList.remove('hidden');
                emptyCart.classList.add('hidden');
                
                cartItemsContainer.innerHTML = '';
                
                cart.forEach((item, index) => {
                    const cartItem = createCartItemHTML(item, index);
                    cartItemsContainer.appendChild(cartItem);
                });
                
                updateCartSummary();
            }
            
            // Create cart item HTML
            function createCartItemHTML(item, index) {
                const div = document.createElement('div');
                div.className = 'flex items-center space-x-4 p-4 border border-gray-200 rounded-lg';
                
                div.innerHTML = `
                    <img src="admin/${item.image || 'uploads/default-product.jpg'}" 
                         alt="${item.name}" 
                         class="w-20 h-20 object-cover rounded-md">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold">${item.name}</h3>
                        <p class="text-gray-600">$${parseFloat(item.price).toFixed(2)}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="updateQuantity(${index}, -1)" 
                                class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300">
                            <i class="fas fa-minus text-sm"></i>
                        </button>
                        <span class="quantity w-12 text-center font-semibold">${item.quantity}</span>
                        <button onclick="updateQuantity(${index}, 1)" 
                                class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300">
                            <i class="fas fa-plus text-sm"></i>
                        </button>
                    </div>
                    <div class="text-lg font-semibold">
                        $${(parseFloat(item.price) * item.quantity).toFixed(2)}
                    </div>
                    <button onclick="removeItem(${index})" 
                            class="text-red-500 hover:text-red-700 p-2">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                
                return div;
            }
            
            // Update quantity
            window.updateQuantity = function(index, change) {
                let cart = JSON.parse(localStorage.getItem('cart')) || [];
                
                cart[index].quantity += change;
                
                if (cart[index].quantity <= 0) {
                    cart.splice(index, 1);
                }
                
                localStorage.setItem('cart', JSON.stringify(cart));
                loadCart();
                showToast('Cart updated');
            };
            
            // Remove item
            window.removeItem = function(index) {
                let cart = JSON.parse(localStorage.getItem('cart')) || [];
                cart.splice(index, 1);
                localStorage.setItem('cart', JSON.stringify(cart));
                loadCart();
                showToast('Item removed from cart');
            };
            
            // Update cart summary
            function updateCartSummary() {
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                let subtotal = 0;
                
                cart.forEach(item => {
                    subtotal += parseFloat(item.price) * item.quantity;
                });
                
                const shipping = subtotal > 50 ? 0 : 10;
                const tax = subtotal * 0.08;
                const total = subtotal + shipping + tax;
                
                document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
                document.getElementById('shipping').textContent = shipping === 0 ? 'Free' : `$${shipping.toFixed(2)}`;
                document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
                document.getElementById('total').textContent = `$${total.toFixed(2)}`;
            }
            
            // Checkout button
            document.getElementById('checkout-btn').addEventListener('click', function() {
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                if (cart.length === 0) {
                    showToast('Your cart is empty', 'error');
                    return;
                }
                window.location.href = 'index.php?p=CheckOut';
            });
            
            // Toast notification
            function showToast(message, type = 'success') {
                const toast = document.createElement('div');
                const bgColor = type === 'error' ? 'bg-red-500' : 'bg-green-500';
                toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg flex items-center opacity-0 transform translate-y-4 transition-all duration-300`;
                toast.innerHTML = `
                    <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'} mr-2"></i>
                    <span>${message}</span>
                `;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.style.opacity = '1';
                    toast.style.transform = 'translateY(0)';
                }, 10);
                
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(1rem)';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }
        });
    </script>
</body>
</html>
