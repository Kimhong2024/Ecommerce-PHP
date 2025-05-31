<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
        <div class="flex justify-between">
            <!-- Logo -->
            <div class="flex space-x-7">
            <a href="#" class="flex items-center py-4 px-2">
                <span class="font-semibold text-gray-500 text-lg">PHONE SHOP</span>
            </a>
            </div>

            <!-- Primary Navbar items -->
            <div class="hidden md:flex items-center space-x-4">
                    <a href="index.php?p=Home" class="flex items-center space-x-2 text-gray-500 hover:text-green-500 transition">
                        <i class="fas fa-home"></i>
                        <p>Home</p>
                    </a>
                    <a href="index.php?p=Shop" class="flex items-center space-x-2 text-gray-500 hover:text-green-500 transition">
                        <i class="fas fa-store"></i>
                        <p>Shop</p>
                    </a>
                    <a href="index.php?p=Contact" class="flex items-center space-x-2 text-gray-500 hover:text-green-500 transition">
                        <i class="fas fa-envelope"></i>
                        <p>Contact</p>
                    </a>
                    <a href="index.php?p=About" class="flex items-center space-x-2 text-gray-500 hover:text-green-500 transition">
                        <i class="fas fa-info-circle"></i>
                        <p>About</p>
                    </a>
                </div>

            <!-- Search Bar and Shopping Cart -->
            <div class="flex items-center space-x-3">
            <!-- Search Bar -->
            <div class="hidden md:flex items-center bg-gray-100 rounded-full p-2">
                <input
                type="text"
                placeholder="Search..."
                class="bg-transparent outline-none w-64 px-2"
                />
                <button class="text-gray-500 hover:text-green-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                </button>
            </div>
            
            <!-- Customer Profile/Login -->
            <?php if (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in']): ?>
                <div class="relative">
                    <button id="profileDropdown" class="flex items-center space-x-2 text-gray-500 hover:text-green-500 transition duration-300">
                        <i class="fas fa-user"></i>
                        <span><?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                        <a href="index.php?p=CustomerProfile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user mr-2"></i>My Profile
                        </a>
                        <a href="index.php?p=CustomerOrders" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-shopping-bag mr-2"></i>My Orders
                        </a>
                        <a href="index.php?p=Login&action=logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="index.php?p=Login" class="flex items-center space-x-2 text-gray-500 hover:text-green-500 transition duration-300">
                    <i class="fas fa-sign-in-alt"></i>
                    <p>Login</p>
                </a>
            <?php endif; ?>
            
            <!-- Shopping Cart Icon -->
            <a href="index.php?p=Shoppingcart" class="relative py-2 px-2 text-gray-500 hover:text-green-500 transition duration-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <!-- Cart Item Counter -->
                <span id="cartCounter" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full cart-count">0</span>
            </a>
            <!-- Mobile menu button -->
            <button class="md:hidden outline-none mobile-menu-button">
                <svg class="w-6 h-6 text-gray-500 hover:text-green-500" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            </div>
        </div>
        </div>
        <!-- Mobile Menu -->
        <div class="hidden mobile-menu">
        <ul class="">
            <li><a href="index.php?p=Home" class="block text-sm px-2 py-4 text-white bg-green-500 font-semibold">Home</a></li>
            <li><a href="index.php?p=Shop" class="block text-sm px-2 py-4 hover:bg-green-500 transition duration-300">Shop</a></li>
            <li><a href="index.php?p=About" class="block text-sm px-2 py-4 hover:bg-green-500 transition duration-300">About</a></li>
            <li><a href="index.php?p=Contact" class="block text-sm px-2 py-4 hover:bg-green-500 transition duration-300">Contact</a></li>
            <?php if (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in']): ?>
                <li><a href="index.php?p=CustomerProfile" class="block text-sm px-2 py-4 hover:bg-green-500 transition duration-300">Profile</a></li>
                <li><a href="index.php?p=Login&action=logout" class="block text-sm px-2 py-4 hover:bg-green-500 transition duration-300">Logout</a></li>
                <?php else: ?>
                <li><a href="index.php?p=Login" class="block text-sm px-2 py-4 hover:bg-green-500 transition duration-300">Login</a></li>
            <?php endif; ?>
        </ul>
        </div>
    </nav>

    <script>
        // Toggle profile dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const profileDropdown = document.getElementById('profileDropdown');
            const dropdownMenu = document.getElementById('dropdownMenu');
            
            if (profileDropdown && dropdownMenu) {
                profileDropdown.addEventListener('click', function() {
                    dropdownMenu.classList.toggle('hidden');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(event) {
                    if (!profileDropdown.contains(event.target)) {
                        dropdownMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>