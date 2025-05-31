<?php
// Product.php
require_once 'admin/include/db.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    // Fetch all products with category information
    public function getAllProducts() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  ORDER BY p.id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch products by category
    public function getProductsByCategory($category_id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.category_id = ? 
                  ORDER BY p.id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$category_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all categories
    public function getAllCategories() {
        $query = "SELECT * FROM categories ORDER BY name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all products grouped by category
    public function getProductsGroupedByCategory() {
        $query = "SELECT p.*, c.name as category_name, c.id as category_id 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  ORDER BY c.name ASC, p.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $groupedProducts = [];
        foreach ($results as $product) {
            $categoryId = $product['category_id'] ?? 0;
            $categoryName = $product['category_name'] ?? 'Uncategorized';
            
            if (!isset($groupedProducts[$categoryId])) {
                $groupedProducts[$categoryId] = [
                    'name' => $categoryName,
                    'products' => []
                ];
            }
            
            $groupedProducts[$categoryId]['products'][] = $product;
        }
        
        return $groupedProducts;
    }
}

// Initialize Product class
$product = new Product();

// Get category ID from URL if present
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Determine if we're showing products grouped by category or filtered by a single category
$viewMode = isset($_GET['view']) ? $_GET['view'] : 'grouped';

if ($category_id) {
    // If a specific category is requested, show only that category
    $products = $product->getProductsByCategory($category_id);
    $categories = [];
    $groupedProducts = [];
} elseif ($viewMode === 'all') {
    // Show all products in a single grid
    $products = $product->getAllProducts();
    $categories = [];
    $groupedProducts = [];
} else {
    // Group products by category (default view)
    $products = [];
    $categories = $product->getAllCategories();
    $groupedProducts = $product->getProductsGroupedByCategory();
}
?>

<!-- Products Section -->
<section id="products" class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-12">
            <div class="mb-4 md:mb-0">
                <h2 class="text-4xl font-bold text-gray-800">
                    <?php 
                    if ($category_id) {
                        echo "Products in " . htmlspecialchars($products[0]['category_name'] ?? 'Category');
                    } else {
                        echo "Our Products";
                    }
                    ?>
                </h2>
                <p class="text-gray-600 mt-2">Discover our amazing collection of products</p>
            </div>
            <div class="flex gap-4">
                <a href="Shop.php?view=grouped" class="px-6 py-3 <?php echo ($viewMode === 'grouped' && !$category_id) ? 'bg-blue-600' : 'bg-gray-600'; ?> text-white rounded-lg hover:bg-blue-700 transition-all duration-300 shadow-md hover:shadow-lg">
                    <i class="fas fa-layer-group mr-2"></i>By Category
                </a>
                <a href="Shop.php?view=all" class="px-6 py-3 <?php echo ($viewMode === 'all') ? 'bg-blue-600' : 'bg-gray-600'; ?> text-white rounded-lg hover:bg-blue-700 transition-all duration-300 shadow-md hover:shadow-lg">
                    <i class="fas fa-th-large mr-2"></i>All Products
                </a>
                <a href="Category.php" class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-all duration-300 shadow-md hover:shadow-lg">
                    <i class="fas fa-tags mr-2"></i>Categories
                </a>
            </div>
        </div>

        <?php if ($viewMode === 'grouped' && !$category_id): ?>
            <!-- Products Grouped by Category -->
            <?php if (empty($groupedProducts)): ?>
                <div class="text-center py-12">
                    <div class="bg-white p-8 rounded-lg shadow-lg">
                        <i class="fas fa-box-open text-6xl text-gray-400 mb-4"></i>
                        <h3 class="text-2xl font-semibold text-gray-700 mb-2">No Products Found</h3>
                        <p class="text-gray-500">Please check back later or browse our other categories.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($groupedProducts as $categoryId => $category): ?>
                    <div class="mb-16">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-2xl font-bold text-gray-800">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </h3>
                            <a href="Shop.php?category=<?php echo $categoryId; ?>" class="text-blue-600 hover:text-blue-800 flex items-center">
                                View All <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                            <?php 
                            // Show up to 4 products per category
                            $productsToShow = array_slice($category['products'], 0, 4);
                            foreach ($productsToShow as $product): 
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
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php else: ?>
            <!-- Products Grid (All Products or Single Category View) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            <?php if (empty($products)): ?>
                <div class="col-span-full text-center py-12">
                    <div class="bg-white p-8 rounded-lg shadow-lg">
                        <i class="fas fa-box-open text-6xl text-gray-400 mb-4"></i>
                        <h3 class="text-2xl font-semibold text-gray-700 mb-2">No Products Found</h3>
                        <p class="text-gray-500">Please check back later or browse our other categories.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
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
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.product-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.add-to-cart {
    transition: all 0.3s ease;
}

.add-to-cart:hover {
    transform: scale(1.05);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add to Cart functionality
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function() {
                const product = {
                    id: this.getAttribute('data-id'),
                    name: this.getAttribute('data-name'),
                    price: parseFloat(this.getAttribute('data-price')),
                    image: this.getAttribute('data-image'),
                    quantity: 1
                };
                
                addToCart(product);
                
                // Visual feedback
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check mr-2"></i>Added!';
                this.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                this.classList.add('bg-green-500', 'hover:bg-green-600');
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('bg-green-500', 'hover:bg-green-600');
                    this.classList.add('bg-blue-600', 'hover:bg-blue-700');
                }, 2000);
            });
        });
        
        // Cart functions
        function addToCart(product) {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            
            // Check if product already exists in cart
            const existingItemIndex = cart.findIndex(item => item.id.toString() === product.id.toString());
            
            if (existingItemIndex !== -1) {
                // If product exists, increment quantity
                cart[existingItemIndex].quantity += 1;
                showToast(`${product.name} quantity updated (${cart[existingItemIndex].quantity})`);
            } else {
                // If product doesn't exist, add it to cart
                cart.push(product);
                showToast(`${product.name} added to cart`);
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
        }
        
        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
            
            // Update cart count in header
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(el => {
                el.textContent = totalItems;
                el.style.display = totalItems > 0 ? 'block' : 'none';
            });
        }
        
        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-4 right-4 px-6 py-3 bg-green-500 text-white rounded-lg shadow-lg flex items-center';
            toast.innerHTML = `
                <i class="fas fa-check-circle mr-2"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            }, 10);
            
            // Remove after 3 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(20px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        // Initialize cart count on page load
        updateCartCount();
    });
    </script>