<!-- Promotions and Discounts Section -->
<section id="promotions" class="container mx-auto my-12 p-8 rounded-lg ">
        <h2 class="text-3xl font-bold text-center mb-8">Promotions & Discounts</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Promotion Card 1: Winter Sale -->
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow text-center">
                <!-- Snowflake Icon -->
                <div class="flex justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2 text-blue-500">Winter Sale</h3>
                <p class="text-gray-500 mb-4">Up to <span class="text-red-500 font-bold">50% off</span> on selected items!</p>
                <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    Shop Now
                </button>
            </div>
    
            <!-- Promotion Card 2: Buy One Get One Free -->
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow text-center">
                <!-- Gift Icon -->
                <div class="flex justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2 text-blue-500">Buy One Get One Free</h3>
                <p class="text-gray-500 mb-4">Applicable on our new arrivals collection.</p>
                <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    View Collection
                </button>
            </div>
    
            <!-- Promotion Card 3: Free Shipping -->
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow text-center">
                <!-- Truck Icon -->
                <div class="flex justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM12 6h2m-2 4h2m-2 4h2m-2 4h2m-6-8h2m-2 4h2m-2 4h2m-2 4h2m6-16v2m0 4v2m0 4v2m0 4v2M3 7h18M3 11h18M3 15h18M3 19h18" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2 text-blue-500">Free Shipping</h3>
                <p class="text-gray-500 mb-4">On orders above <span class="font-bold">$50</span>!</p>
                <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    Start Shopping
                </button>
            </div>
        </div>
</section>


    <!--Category Collections Section -->
    <section id="collections" class="container mx-auto my-12 px-4">
        <h2 class="text-3xl font-bold text-center mb-8">Collections</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
            <?php
            require_once 'admin/include/db.php';
            $db = (new Database())->connect();
            $stmt = $db->query("SELECT * FROM categories ORDER BY id DESC");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($categories)) {
                echo '<div class="col-span-full text-center text-gray-500">No categories available</div>';
            } else {
                foreach ($categories as $category) {
                    ?>
                    <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                        <?php if (!empty($category['image'])): ?>
                            <img src="admin/<?php echo htmlspecialchars($category['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                 class="w-full h-48 object-cover rounded-md mb-4">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 rounded-md mb-4 flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                            </div>
                        <?php endif; ?>
                        <h3 class="text-lg font-semibold mb-2 text-center"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p class="text-gray-500 mb-4 text-center"><?php echo htmlspecialchars($category['description']); ?></p>
                        <a href="products.php?category=<?php echo $category['id']; ?>" 
                           class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-center block">
                            Shop Now
                        </a>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </section>


    <!-- Banner Begin -->
    <div class="bg-gray-100 py-16">
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap -mx-2">
                <?php
                require_once 'admin/include/db.php';
                $db = (new Database())->connect();
                $stmt = $db->query("SELECT * FROM banners WHERE status = 'active' ORDER BY position, id DESC");
                $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($banners)) {
                    // Default banners if none are found in database
                    ?>
                    <!-- Left Banner Section -->
                    <div class="w-full lg:w-1/2 md:w-1/2 sm:w-full px-2 mb-4">
                        <div class="relative overflow-hidden rounded-lg shadow-lg">
                            <img src="./images/bannerContact.jpg" alt="Summer Sale" class="w-full h-auto object-cover">
                            <div class="absolute inset-0 flex flex-col justify-center items-start p-6 bg-black bg-opacity-0">
                                <h2 class="text-white text-3xl font-extrabold mb-3">Summer Sale!</h2>
                                <p class="text-white text-lg mb-5">Get up to <span class="font-semibold text-yellow-300">50% off</span> on selected items. Limited time only!</p>
                                <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                                    Shop Now
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right Banner Section -->
                    <div class="w-full lg:w-1/2 md:w-1/2 sm:w-full px-2 mb-4">
                        <div class="relative overflow-hidden rounded-lg shadow-lg">
                            <img src="./images/bannerabout.jpg" alt="New Arrivals" class="w-full h-auto object-cover">
                            <div class="absolute inset-0 flex flex-col justify-center items-start p-6 bg-black bg-opacity-0">
                                <h2 class="text-white text-3xl font-extrabold mb-3">New Arrivals!</h2>
                                <p class="text-white text-lg mb-5">Discover the latest in tech and gadgets</p>
                                <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                                    Shop Now
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php
                } else {
                    foreach ($banners as $banner) {
                        ?>
                        <div class="w-full lg:w-1/2 md:w-1/2 sm:w-full px-2 mb-4">
                            <div class="relative overflow-hidden rounded-lg shadow-lg">
                                <img src="admin/<?php echo htmlspecialchars($banner['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($banner['title']); ?>" 
                                     class="w-full h-auto object-cover">
                                <div class="absolute inset-0 flex flex-col justify-center items-start p-6 bg-black bg-opacity-0">
                                    <h2 class="text-white text-3xl font-extrabold mb-3"><?php echo htmlspecialchars($banner['title']); ?></h2>
                                    <p class="text-white text-lg mb-5"><?php echo htmlspecialchars($banner['description']); ?></p>
                                    <?php if ($banner['button_text'] && $banner['button_link']): ?>
                                        <a href="<?php echo htmlspecialchars($banner['button_link']); ?>" 
                                           class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                                            <?php echo htmlspecialchars($banner['button_text']); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
    <!-- Banner End -->
    <!-- Featured Products Section -->
    <section id="featured-products" class="container mx-auto my-12">
        <h2 class="text-3xl font-bold text-center mb-8">Top Ordered Products</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            <?php
            require_once 'admin/include/db.php';
            
            // Fetch top ordered products from database
            $db = (new Database())->connect();
            $stmt = $db->query("SELECT p.*, c.name as category_name, COUNT(oi.product_id) as order_count 
                               FROM products p 
                               LEFT JOIN categories c ON p.category_id = c.id 
                               LEFT JOIN order_items oi ON p.id = oi.product_id
                               GROUP BY p.id
                               ORDER BY order_count DESC 
                               LIMIT 4");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($products)) {
                echo '<div class="col-span-full text-center text-gray-500">No products available</div>';
            } else {
                foreach ($products as $product) {
                    ?>
                    <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow product-card">
                        <?php if (!empty($product['image'])): ?>
                            <img src="admin/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="w-full h-48 object-cover rounded-md mb-4">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 rounded-md mb-4 flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                            </div>
                        <?php endif; ?>
                        <h3 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-gray-500 mb-2"><?php echo htmlspecialchars(substr($product['description'], 0, 60)) . '...'; ?></p>
                        <div class="flex items-center justify-between">
                            <span class="text-blue-500 font-bold">$<?php echo number_format($product['price'], 2); ?></span>
                            <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition add-to-cart"
                                    data-id="<?php echo $product['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-price="<?php echo $product['price']; ?>"
                                    data-image="<?php echo htmlspecialchars($product['image']); ?>">
                                <span class="flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                                    </svg>
                                    Add to Cart
                                </span>
                            </button>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </section>