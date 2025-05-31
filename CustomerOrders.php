<?php
// Session is already started in nav.php, so we don't need to start it again

// Check if customer is logged in
if (!isset($_SESSION['customer_logged_in']) || !$_SESSION['customer_logged_in']) {
    echo "<script>window.location.href = 'index.php?p=Login';</script>";
    exit;
}

require_once 'admin/include/db.php';

// Fetch customer orders - Only show orders for the logged-in customer
try {
    $db = (new Database())->connect();
    
    // Query orders using customer email since there's no customer_id
    $query = "SELECT o.*, 
                     o.total as total_amount,
                     o.order_reference,
                     o.status as order_status,
                     o.payment_method,
                     'paid' as payment_status,
                     o.shipping_address,
                     (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
              FROM orders o 
              WHERE o.customer_email = ? 
              ORDER BY o.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['customer_email']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error_message = "Error fetching orders: " . $e->getMessage();
    $orders = [];
}

// Function to get order items for the specific order
function getOrderItems($db, $orderId) {
    try {
        // Check if order_items table exists
        $checkTable = $db->query("SHOW TABLES LIKE 'order_items'");
        $tableExists = $checkTable->fetch();
        
        if (!$tableExists) {
            return [];
        }
        
        // Direct query approach for simplicity and reliability
        $query = "SELECT oi.*,
                       COALESCE(oi.product_name, p.name) as display_name,
                       p.image as product_image
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?";
                
        $stmt = $db->prepare($query);
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $items;
    } catch (Exception $e) {
        // Log error for debugging
        error_log("Error fetching order items: " . $e->getMessage());
        return [];
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">My Orders</h1>
            <p class="text-sm text-gray-600">
                Welcome, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>!
            </p>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($orders)): ?>
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <i class="fas fa-shopping-bag text-gray-400 text-6xl mb-4"></i>
                <h2 class="text-2xl font-semibold text-gray-600 mb-2">No Orders Yet</h2>
                <p class="text-gray-500 mb-6">You haven't placed any orders yet. Start shopping to see your orders here.</p>
                <a href="index.php?p=Shop" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition duration-300">
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <div class="flex flex-wrap justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">
                                        Order #<?php echo htmlspecialchars($order['order_reference']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Placed on <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Customer: <?php echo htmlspecialchars($order['customer_name']); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-semibold text-gray-800">
                                        $<?php echo number_format($order['total_amount'], 2); ?>
                                    </p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php 
                                        switch(strtolower($order['order_status'])) {
                                            case 'pending':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'confirmed':
                                                echo 'bg-blue-100 text-blue-800';
                                                break;
                                            case 'shipped':
                                                echo 'bg-purple-100 text-purple-800';
                                                break;
                                            case 'delivered':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'cancelled':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                                <div>
                                    <span class="font-medium">Items:</span> <?php echo $order['item_count']; ?>
                                </div>
                                <div>
                                    <span class="font-medium">Payment:</span> <?php echo ucfirst($order['payment_method']); ?>
                                </div>
                                <div>
                                    <span class="font-medium">Status:</span> <?php echo ucfirst($order['payment_status']); ?>
                                </div>
                            </div>
                            
                            <!-- Order Summary -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600">Subtotal:</span>
                                        <p class="font-medium">$<?php echo number_format($order['subtotal'], 2); ?></p>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Shipping:</span>
                                        <p class="font-medium">$<?php echo number_format($order['shipping'], 2); ?></p>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Tax:</span>
                                        <p class="font-medium">$<?php echo number_format($order['tax'], 2); ?></p>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Total:</span>
                                        <p class="font-semibold text-lg">$<?php echo number_format($order['total'], 2); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Toggle button for order details -->
                            <div class="mt-4">
                                <button onclick="toggleOrderDetails(<?php echo $order['id']; ?>)" 
                                        class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                    <span id="toggle-text-<?php echo $order['id']; ?>">View Details</span>
                                    <i id="toggle-icon-<?php echo $order['id']; ?>" class="fas fa-chevron-down ml-1"></i>
                                </button>
                            </div>
                            
                            <!-- Order Details (Hidden by default) -->
                            <div id="order-details-<?php echo $order['id']; ?>" class="hidden mt-4 pt-4 border-t border-gray-200">
                                <h4 class="font-semibold text-gray-800 mb-3">Order Items:</h4>
                                <div class="space-y-3">
                                    <?php 
                                    $orderItems = getOrderItems($db, $order['id']);
                                    
                                    // Debug order items
                                    echo "<!-- Debug order items: " . json_encode($orderItems) . " -->";
                                    ?>
                                    
                                    <?php if (empty($orderItems)): ?>
                                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                            <p class="text-yellow-700 text-sm">No items found for this order.</p>
                                        </div>
                                    <?php else:
                                        foreach ($orderItems as $item): 
                                            // Debug current item
                                            echo "<!-- Debug item: " . json_encode($item) . " -->";
                                    ?>
                                        <div class="flex items-center space-x-4 bg-gray-50 p-4 rounded-lg">
                                            <div class="w-16 h-16 bg-gray-200 rounded-md flex-shrink-0">
                                                <?php 
                                                // Debug product image info
                                                echo "<!-- Image info: " . 
                                                     "isset: " . (isset($item['product_image']) ? 'yes' : 'no') . 
                                                     ", empty: " . (empty($item['product_image']) ? 'yes' : 'no') . 
                                                     ", value: " . ($item['product_image'] ?? 'null') . 
                                                     " -->";
                                                
                                                // We know the iPhone 13 mini exists in the directory with a space in the name
                                                if ($item['display_name'] == 'iPhone 13 mini' || strpos($item['display_name'], 'iPhone 13') !== false) {
                                                    $imagePath = 'admin/uploads/iphone13 mini.jpg';
                                                } elseif ($item['display_name'] == 'iPhone 14 Pro' || strpos($item['display_name'], 'iPhone 14') !== false) {
                                                    $imagePath = 'admin/uploads/iPhone14 pro.jpg';
                                                } elseif ($item['display_name'] == 'iPhone 15 Pro' || strpos($item['display_name'], 'iPhone 15') !== false) {
                                                    $imagePath = 'admin/uploads/iphone15 pro.jpg';
                                                } elseif ($item['display_name'] == 'iPhone 16 Pro' || strpos($item['display_name'], 'iPhone 16') !== false) {
                                                    $imagePath = 'admin/uploads/iphone 16 pro.jpg';
                                                } elseif ($item['display_name'] == 'Air Max' || strpos($item['display_name'], 'Air Max') !== false) {
                                                    $imagePath = 'admin/uploads/air max.jpg';
                                                } elseif (stripos($item['display_name'], 'Samsung Galaxy A54') !== false) {
                                                    $imagePath = 'admin/uploads/Samsung Galaxy A54.jpg';
                                                } elseif (stripos($item['display_name'], 'Samsung Galaxy S21 FE') !== false) {
                                                    $imagePath = 'admin/uploads/Samsung Galaxy S21 FE.jpg';
                                                } elseif (stripos($item['display_name'], 'Samsung Galaxy S23 Ultra') !== false) {
                                                    $imagePath = 'admin/uploads/Samsung Galaxy S23 Ultra.jpg';
                                                } elseif (stripos($item['display_name'], 'Samsung Galaxy Z Fold 5') !== false) {
                                                    $imagePath = 'admin/uploads/Samsung Galaxy Z Fold 5.jpg';
                                                } elseif (stripos($item['display_name'], 'Samsung Galaxy') !== false) {
                                                    // Generic fallback for any Samsung Galaxy model
                                                    $imagePath = 'admin/uploads/Samsung Galaxy A54.jpg';
                                                } elseif (stripos($item['display_name'], 'Vivo V29') !== false) {
                                                    $imagePath = 'admin/uploads/Vivo V29.jpg';
                                                } elseif (stripos($item['display_name'], 'Vivo X70 Pro') !== false) {
                                                    $imagePath = 'admin/uploads/Vivo X70 Pro.jpg';
                                                } elseif (stripos($item['display_name'], 'Vivo X90 Pro') !== false) {
                                                    // Use X70 Pro as fallback since X90 Pro image doesn't exist
                                                    $imagePath = 'admin/uploads/Vivo X70 Pro.jpg';
                                                } elseif (stripos($item['display_name'], 'Vivo Y56') !== false) {
                                                    $imagePath = 'admin/uploads/Vivo Y56.jpg';
                                                } elseif (stripos($item['display_name'], 'Vivo') !== false) {
                                                    // Generic fallback for any Vivo model
                                                    $imagePath = 'admin/uploads/Vivo V29.jpg';
                                                } elseif (isset($item['product_image']) && !empty($item['product_image'])) {
                                                    $imagePath = 'admin/uploads/' . $item['product_image'];
                                                } else {
                                                    // For any other product, try to find by name from our known image files
                                                    $foundMatch = false;
                                                    $knownImages = [
                                                        'air.jpg' => ['air', 'nike air'],
                                                        'air3.jpg' => ['air3', 'nike air 3'],
                                                        'm1.jpg' => ['m1', 'macbook', 'macbook m1'],
                                                        'm3.jpg' => ['m3', 'macbook m3'],
                                                        'ma19.jpg' => ['ma19', 'macbook air', 'macbook air 2019'],
                                                        'max2.jpg' => ['max2', 'macbook max', 'pro max'],
                                                        'Samsung Galaxy A54.jpg' => ['samsung', 'galaxy', 'samsung galaxy'],
                                                        'Vivo V29.jpg' => ['vivo', 'vivo phone']
                                                    ];
                                                    
                                                    foreach ($knownImages as $img => $keywords) {
                                                        foreach ($keywords as $keyword) {
                                                            if (stripos($item['display_name'], $keyword) !== false) {
                                                                $imagePath = 'admin/uploads/' . $img;
                                                                $foundMatch = true;
                                                                break 2;
                                                            }
                                                        }
                                                    }
                                                    
                                                    if (!$foundMatch) {
                                                        $imagePath = '';
                                                    }
                                                }
                                                
                                                if (!empty($imagePath)):
                                                ?>
                                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product_name'] ?? $item['original_product_name'] ?? ''); ?>"
                                                         class="w-full h-full object-cover rounded-md"
                                                         onerror="console.log('Image failed to load: <?php echo htmlspecialchars($imagePath); ?>'); this.onerror=null; this.src='admin/uploads/default-product.jpg';">
                                                <?php else: ?>
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <i class="fas fa-image text-gray-400"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-1">
                                                <h5 class="font-medium text-gray-800">
                                                    <?php echo htmlspecialchars($item['display_name'] ?? 'Product Name Not Available'); ?>
                                                </h5>
                                                <p class="text-sm text-gray-600">
                                                    Quantity: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['price'], 2); ?>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-semibold text-gray-800">
                                                    $<?php echo number_format($item['total'] ?? ($item['quantity'] * $item['price']), 2); ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; 
                                    endif; ?>
                                </div>
                                
                                <!-- Shipping Information -->
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <h4 class="font-semibold text-gray-800 mb-2">Shipping Information:</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <p class="text-gray-600">Name: <span class="font-medium"><?php echo htmlspecialchars($order['customer_name']); ?></span></p>
                                            <p class="text-gray-600">Email: <span class="font-medium"><?php echo htmlspecialchars($order['customer_email']); ?></span></p>
                                            <p class="text-gray-600">Phone: <span class="font-medium"><?php echo htmlspecialchars($order['customer_phone']); ?></span></p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600">Address:</p>
                                            <p class="font-medium"><?php echo htmlspecialchars($order['shipping_address']); ?></p>
                                            <p class="font-medium"><?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['zip_code']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Summary Stats -->
            <div class="mt-8 bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Order Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                    <div>
                        <p class="text-2xl font-bold text-blue-600"><?php echo count($orders); ?></p>
                        <p class="text-sm text-gray-600">Total Orders</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-600">
                            $<?php echo number_format(array_sum(array_column($orders, 'total_amount')), 2); ?>
                        </p>
                        <p class="text-sm text-gray-600">Total Spent</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-purple-600">
                            <?php echo count(array_filter($orders, function($order) { 
                                return strtolower($order['order_status']) === 'delivered'; 
                            })); ?>
                        </p>
                        <p class="text-sm text-gray-600">Delivered</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-orange-600">
                            <?php echo count(array_filter($orders, function($order) { 
                                return strtolower($order['order_status']) === 'pending'; 
                            })); ?>
                        </p>
                        <p class="text-sm text-gray-600">Pending</p>
                    </div>
                </div>
            </div>
            
            <!-- Back to Profile Button -->
            <div class="mt-8 text-center">
                <a href="index.php?p=CustomerProfile" class="text-blue-600 hover:text-blue-800 font-medium">
                    ← Back to Profile
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleOrderDetails(orderId) {
    const details = document.getElementById(`order-details-${orderId}`);
    const toggleText = document.getElementById(`toggle-text-${orderId}`);
    const toggleIcon = document.getElementById(`toggle-icon-${orderId}`);
    
    if (details.classList.contains('hidden')) {
        details.classList.remove('hidden');
        toggleText.textContent = 'Hide Details';
        toggleIcon.classList.remove('fa-chevron-down');
        toggleIcon.classList.add('fa-chevron-up');
    } else {
        details.classList.add('hidden');
        toggleText.textContent = 'View Details';
        toggleIcon.classList.remove('fa-chevron-up');
        toggleIcon.classList.add('fa-chevron-down');
    }
}
</script>