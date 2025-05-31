<?php
// Order.php
require 'include/db.php';

class Order {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    // Fetch all customers
    public function getAllCustomers() {
        try {
            $query = "SELECT id, CONCAT(first_name, ' ', last_name) as name, email FROM customers ORDER BY first_name ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching customers: " . $e->getMessage());
            throw new Exception("Failed to fetch customers");
        }
    }

    // Fetch all products
        // Fetch all products
        public function getAllProducts() {
            try {
                $query = "SELECT id, name, price FROM products WHERE status = 'active' ORDER BY name ASC";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Error fetching products: " . $e->getMessage());
                throw new Exception("Failed to fetch products");
            }
        }
    

    // Add a new order
    public function addOrder($customerName, $customerEmail, $customerPhone, $shippingAddress, $city, $zipCode, $paymentMethod, $items) {
        if (empty($customerName) || empty($customerEmail) || empty($items)) {
            throw new Exception("Required fields are missing");
        }

        $this->db->beginTransaction();

        try {
            // Calculate totals
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += floatval($item['price']) * intval($item['quantity']);
            }
            $shipping = $subtotal > 50 ? 0 : 10;
            $tax = $subtotal * 0.08;
            $total = $subtotal + $shipping + $tax;

            // Generate order reference
            $orderReference = 'ORD-' . date('Ymd') . '-' . uniqid();

            // Insert the order
            $query = "INSERT INTO orders (order_reference, customer_name, customer_email, customer_phone, shipping_address, city, zip_code, subtotal, shipping, tax, total, payment_method, status, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$orderReference, $customerName, $customerEmail, $customerPhone, $shippingAddress, $city, $zipCode, $subtotal, $shipping, $tax, $total, $paymentMethod]);
            $orderId = $this->db->lastInsertId();

            // Insert order items
            foreach ($items as $item) {
                if (!isset($item['productId']) || !isset($item['quantity']) || !isset($item['price'])) {
                    throw new Exception("Invalid item data");
                }

                $itemTotal = floatval($item['price']) * intval($item['quantity']);
                $query = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total) 
                         VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$orderId, $item['productId'], $item['productName'], $item['price'], $item['quantity'], $itemTotal]);
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error adding order: " . $e->getMessage());
            throw new Exception("Failed to add order: " . $e->getMessage());
        }
    }

    // Fetch all orders
    public function getAllOrders() {
        try {
            $query = "SELECT o.id, o.order_reference, o.customer_name, o.created_at AS date, o.status, o.total 
                     FROM orders o 
                     ORDER BY o.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching orders: " . $e->getMessage());
            throw new Exception("Failed to fetch orders");
        }
    }

    // Fetch a single order by ID
    public function getOrderById($id) {
        try {
            $query = "SELECT * FROM orders WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($order) {
                // Fetch order items
                $itemQuery = "SELECT * FROM order_items WHERE order_id = ?";
                $itemStmt = $this->db->prepare($itemQuery);
                $itemStmt->execute([$id]);
                $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $order;
        } catch (PDOException $e) {
            error_log("Error fetching order: " . $e->getMessage());
            throw new Exception("Failed to fetch order");
        }
    }

    // Update an order status
    public function updateOrderStatus($id, $status) {
        if (empty($id) || empty($status)) {
            throw new Exception("Required fields are missing");
        }

        try {
            $query = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$status, $id]);
            return true;
        } catch (Exception $e) {
            error_log("Error updating order: " . $e->getMessage());
            throw new Exception("Failed to update order: " . $e->getMessage());
        }
    }

    // Delete an order
    public function deleteOrder($id) {
        if (empty($id)) {
            throw new Exception("Order ID is required");
        }

        $this->db->beginTransaction();

        try {
            // Delete order items (will be automatically deleted due to foreign key cascade)
            // But we'll do it explicitly for clarity
            $query = "DELETE FROM order_items WHERE order_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);

            // Delete the order
            $query = "DELETE FROM orders WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error deleting order: " . $e->getMessage());
            throw new Exception("Failed to delete order: " . $e->getMessage());
        }
    }

    // Search orders
    public function searchOrders($searchTerm) {
        try {
            $query = "SELECT o.id, o.order_reference, o.customer_name, o.created_at AS date, o.status, o.total 
                     FROM orders o 
                     WHERE o.customer_name LIKE ? OR o.order_reference LIKE ? OR o.status LIKE ? 
                     ORDER BY o.created_at DESC";
            $stmt = $this->db->prepare($query);
            $searchTerm = "%$searchTerm%";
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching orders: " . $e->getMessage());
            throw new Exception("Failed to search orders");
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $order = new Order();
        
        if (isset($_POST['action']) && $_POST['action'] === 'updateStatus') {
            // Handle status update
            if (!isset($_POST['orderId']) || !isset($_POST['status'])) {
                throw new Exception('Required fields are missing');
            }
            
            if ($order->updateOrderStatus($_POST['orderId'], $_POST['status'])) {
                echo json_encode(['success' => true, 'message' => 'Order status updated successfully!']);
            } else {
                throw new Exception('Failed to update order status');
            }
        } else {
            // Handle new order creation
            if (!isset($_POST['customerName']) || !isset($_POST['customerEmail'])) {
                throw new Exception('Required fields are missing');
            }

            $items = isset($_POST['items']) ? json_decode($_POST['items'], true) : [];
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid items data: ' . json_last_error_msg());
            }

            $orderId = $order->addOrder(
                $_POST['customerName'],
                $_POST['customerEmail'],
                $_POST['customerPhone'] ?? '',
                $_POST['shippingAddress'] ?? '',
                $_POST['city'] ?? '',
                $_POST['zipCode'] ?? '',
                $_POST['paymentMethod'] ?? 'cash_on_delivery',
                $items
            );
            
            if ($orderId) {
                echo json_encode(['success' => true, 'message' => 'Order added successfully!', 'orderId' => $orderId]);
            } else {
                throw new Exception('Failed to add order');
            }
        }
    } catch (Exception $e) {
        error_log('Order Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle delete request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $order = new Order();
        if ($order->deleteOrder($_GET['id'])) {
            echo json_encode(['success' => true, 'message' => 'Order deleted successfully!']);
        } else {
            throw new Exception('Failed to delete order');
        }
    } catch (Exception $e) {
        error_log('Delete Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle search request
if (isset($_GET['action']) && $_GET['action'] === 'search' && isset($_GET['searchTerm'])) {
    try {
        $order = new Order();
        $orders = $order->searchOrders($_GET['searchTerm']);
        echo json_encode($orders);
    } catch (Exception $e) {
        error_log('Search Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle get order by ID request
if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
    try {
        $order = new Order();
        $orderData = $order->getOrderById($_GET['id']);
        if ($orderData) {
            echo json_encode($orderData);
        } else {
            throw new Exception('Order not found');
        }
    } catch (Exception $e) {
        error_log('Get Order Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Fetch all orders to display
try {
    $order = new Order();
    $orders = $order->getAllOrders();
    $customers = $order->getAllCustomers();
    $products = $order->getAllProducts();
} catch (Exception $e) {
    error_log('Fetch Error: ' . $e->getMessage());
    $error = $e->getMessage();
}
?>

<!-- Main Content -->
<div class="container">
    <!-- Message Container -->
    <div id="message" class="mb-3"></div>

    <div class="page-inner">
        <!-- Header -->
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Order Management</h3>
            </div>
            <div class="ms-md-auto py-2 py-md-0">
                <button id="addOrderBtn" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#orderModal">
                    <i class="fas fa-plus"></i> Add Order
                </button>
            </div>
        </div>

        <!-- Order Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-head-row">
                            <div class="card-title">Order List</div>
                            <div class="card-tools">
                                <input type="text" id="searchOrder" class="form-control" placeholder="Search orders...">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="orderTable">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Reference</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($orders) && !empty($orders)): ?>
                                        <?php foreach ($orders as $orderRow): ?>
                                            <tr data-order-id="<?php echo $orderRow['id']; ?>">
                                                <td><?php echo $orderRow['id']; ?></td>
                                                <td><?php echo htmlspecialchars($orderRow['order_reference']); ?></td>
                                                <td><?php echo htmlspecialchars($orderRow['customer_name']); ?></td>
                                                <td><?php echo date('Y-m-d H:i', strtotime($orderRow['date'])); ?></td>
                                                <td>$<?php echo number_format($orderRow['total'] ?? 0, 2); ?></td>
                                                <td>
                                                    <select class="form-select form-select-sm status-select" data-order-id="<?php echo $orderRow['id']; ?>">
                                                        <option value="pending" <?php echo $orderRow['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="confirmed" <?php echo $orderRow['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                        <option value="shipped" <?php echo $orderRow['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                        <option value="delivered" <?php echo $orderRow['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                        <option value="cancelled" <?php echo $orderRow['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" onclick="viewOrder(<?php echo $orderRow['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteOrder(<?php echo $orderRow['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No orders found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Add Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel">Add New Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="orderForm" method="POST">
                    <!-- Customer Information -->
                    <div class="row mb-3">
                    <div class="col-md-6">
                            <div class="form-group">
                                <label for="customerName">Customer Name</label>
                                <select class="form-control" id="customerName" name="customerName" required onchange="handleCustomerSelection(this)">
                                    <option value="">Select Customer</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo htmlspecialchars($customer['name']); ?>" 
                                                data-email="<?php echo htmlspecialchars($customer['email']); ?>">
                                            <?php echo htmlspecialchars($customer['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customerEmail">Customer Email</label>
                                <input type="email" class="form-control" id="customerEmail" name="customerEmail" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customerPhone">Phone</label>
                                <input type="text" class="form-control" id="customerPhone" name="customerPhone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="paymentMethod">Payment Method</label>
                                <select class="form-control" id="paymentMethod" name="paymentMethod">
                                    <option value="cash_on_delivery">Cash on Delivery</option>
                                    <option value="khqr">KHQR</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="paypal">PayPal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-group">
                            <label for="shippingAddress">Shipping Address</label>
                            <textarea class="form-control" id="shippingAddress" name="shippingAddress" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" class="form-control" id="city" name="city">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="zipCode">ZIP Code</label>
                                <input type="text" class="form-control" id="zipCode" name="zipCode">
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="order-items mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Order Items</h6>
                            <button type="button" class="btn btn-sm btn-secondary" id="addOrderItem">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table" id="orderItemsTable">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="orderItemsBody"></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Order Total:</td>
                                        <td id="orderTotal">$0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="orderForm" class="btn btn-primary">Save Order</button>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function to get status color
function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'confirmed':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>

<script>
// Function to show message
function showMessage(message, type = 'success') {
    let messageDiv = document.getElementById('message');
    messageDiv.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`;
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        const alert = messageDiv.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}
// Function to handle customer selection
function handleCustomerSelection(select) {
    const selectedOption = select.options[select.selectedIndex];
    const emailInput = document.getElementById('customerEmail');
    const phoneInput = document.getElementById('customerPhone');
    
    if (selectedOption.value) {
        emailInput.value = selectedOption.getAttribute('data-email') || '';
        phoneInput.value = selectedOption.getAttribute('data-phone') || '';
    } else {
        emailInput.value = '';
        phoneInput.value = '';
    }
}

// Function to handle product selection
// Function to handle product selection
function handleProductSelection(select) {
    const row = select.closest('tr');
    const selectedOption = select.options[select.selectedIndex];
    const priceInput = row.querySelector('.price');
    const quantityInput = row.querySelector('.quantity');
    const totalInput = row.querySelector('.total');

    const price = selectedOption.getAttribute('data-price') || '0.00';
    priceInput.value = price;
    updateRowTotal(quantityInput);
}

// Function to add order items dynamically
function addOrderItem() {
    const tbody = document.getElementById('orderItemsBody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <select class="form-control product-select" required onchange="handleProductSelection(this)">
                <option value="">Select Product</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?php echo $product['id']; ?>" 
                            data-price="<?php echo $product['price']; ?>"
                            data-name="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php echo htmlspecialchars($product['name']); ?> - $<?php echo number_format($product['price'], 2); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="number" class="form-control quantity" min="1" value="1" required onchange="updateRowTotal(this)"></td>
        <td><input type="text" class="form-control price" value="0.00" readonly></td>
        <td><input type="text" class="form-control total" value="0.00" disabled></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeOrderItem(this)">Remove</button></td>
    `;
    tbody.appendChild(row);
}

// Function to update row total
function updateRowTotal(input) {
    const row = input.closest('tr');
    const priceInput = row.querySelector('.price');
    const totalInput = row.querySelector('.total');
    
    const quantity = parseFloat(input.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    const total = quantity * price;
    totalInput.value = total.toFixed(2);
    updateOrderTotal();
}

// Function to update the order total
function updateOrderTotal() {
    const rows = document.querySelectorAll('#orderItemsBody tr');
    let orderTotal = 0;

    rows.forEach(row => {
        const totalInput = row.querySelector('.total');
        if (totalInput) {
            const total = parseFloat(totalInput.value) || 0;
            orderTotal += total;
        }
    });

    // Add shipping and tax
    const shipping = orderTotal > 50 ? 0 : 10;
    const tax = orderTotal * 0.08;
    const finalTotal = orderTotal + shipping + tax;
    
    document.getElementById('orderTotal').textContent = `$${finalTotal.toFixed(2)}`;
}

// Function to remove an order item
function removeOrderItem(button) {
    const row = button.closest('tr');
    if (row) {
        row.remove();
        updateOrderTotal();
    }
}

// Function to view order details
function viewOrder(id) {
    fetch(`Order.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data && !data.success) {
                const orderContent = document.getElementById('orderDetailsContent');
                orderContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <table class="table table-borderless">
                                <tr><td><strong>Order Reference:</strong></td><td>${data.order_reference}</td></tr>
                                <tr><td><strong>Customer:</strong></td><td>${data.customer_name}</td></tr>
                                <tr><td><strong>Email:</strong></td><td>${data.customer_email}</td></tr>
                                <tr><td><strong>Phone:</strong></td><td>${data.customer_phone || 'N/A'}</td></tr>
                                <tr><td><strong>Payment Method:</strong></td><td>${data.payment_method}</td></tr>
                                <tr><td><strong>Status:</strong></td><td><span class="badge bg-${getStatusColor(data.status)}">${data.status}</span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Shipping Information</h6>
                            <table class="table table-borderless">
                                <tr><td><strong>Address:</strong></td><td>${data.shipping_address || 'N/A'}</td></tr>
                                <tr><td><strong>City:</strong></td><td>${data.city || 'N/A'}</td></tr>
                                <tr><td><strong>ZIP Code:</strong></td><td>${data.zip_code || 'N/A'}</td></tr>
                            </table>
                            <h6>Order Summary</h6>
                            <table class="table table-borderless">
                                <tr><td><strong>Subtotal:</strong></td><td>$${parseFloat(data.subtotal).toFixed(2)}</td></tr>
                                <tr><td><strong>Shipping:</strong></td><td>$${parseFloat(data.shipping).toFixed(2)}</td></tr>
                                <tr><td><strong>Tax:</strong></td><td>$${parseFloat(data.tax).toFixed(2)}</td></tr>
                                <tr><td><strong>Total:</strong></td><td><strong>$${parseFloat(data.total).toFixed(2)}</strong></td></tr>
                            </table>
                        </div>
                    </div>
                    <h6>Order Items</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.items.map(item => `
                                    <tr>
                                        <td>${item.product_name}</td>
                                        <td>${item.quantity}</td>
                                        <td>$${parseFloat(item.price).toFixed(2)}</td>
                                        <td>$${parseFloat(item.total).toFixed(2)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
                const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
                modal.show();
            } else {
                throw new Error('Order not found');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage(error.message, 'danger');
        });
}

// Function to delete order
function deleteOrder(id) {
    if (confirm('Are you sure you want to delete this order?')) {
        fetch(`Order.php?action=delete&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message);
                    const row = document.querySelector(`tr[data-order-id="${id}"]`);
                    if (row) {
                        row.remove();
                    }
                } else {
                    throw new Error(data.message || 'Failed to delete order');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage(error.message, 'danger');
            });
    }
}

// Handle status change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('status-select')) {
        const orderId = e.target.getAttribute('data-order-id');
        const newStatus = e.target.value;
        
        const formData = new FormData();
        formData.append('action', 'updateStatus');
        formData.append('orderId', orderId);
        formData.append('status', newStatus);
        
        fetch('Order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message);
            } else {
                throw new Error(data.message || 'Failed to update status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage(error.message, 'danger');
            // Revert the select back to original value if needed
            location.reload();
        });
    }
});

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener to the "Add Item" button
    const addOrderItemBtn = document.getElementById('addOrderItem');
    if (addOrderItemBtn) {
        addOrderItemBtn.addEventListener('click', addOrderItem);
    }

    // Handle form submission
    const orderForm = document.getElementById('orderForm');
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            try {
                const items = [];
                const rows = document.querySelectorAll('#orderItemsBody tr');
                
                if (rows.length === 0) {
                    showMessage('Please add at least one item to the order', 'danger');
                    return;
                }

                rows.forEach(row => {
                    const productSelect = row.querySelector('.product-select');
                    const quantityInput = row.querySelector('.quantity');
                    const priceInput = row.querySelector('.price');
                    
                    if (!productSelect.value) {
                        throw new Error('Please select a product for all items');
                    }

                    if (!quantityInput.value || quantityInput.value <= 0) {
                        throw new Error('Please enter a valid quantity for all items');
                    }

                    const selectedOption = productSelect.options[productSelect.selectedIndex];
                    const productName = selectedOption.getAttribute('data-name');

                    items.push({
                        productId: productSelect.value,
                        productName: productName,
                        quantity: quantityInput.value,
                        price: priceInput.value
                    });
                });

                const formData = new FormData(this);
                formData.append('items', JSON.stringify(items));

                fetch('Order.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message);
                        const modal = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
                        if (modal) {
                            modal.hide();
                        }
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        throw new Error(data.message || 'An error occurred');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage(error.message, 'danger');
                });
            } catch (error) {
                console.error('Error:', error);
                showMessage(error.message, 'danger');
            }
        });
    }

    // Add search functionality
    const searchInput = document.getElementById('searchOrder');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            if (searchTerm.length > 0) {
                fetch(`Order.php?action=search&searchTerm=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.json())
                    .then(data => {
                        const tbody = document.querySelector('#orderTable tbody');
                        tbody.innerHTML = '';
                        
                        if (data.length > 0) {
                            data.forEach(order => {
                                const row = document.createElement('tr');
                                row.setAttribute('data-order-id', order.id);
                                row.innerHTML = `
                                    <td>${order.id}</td>
                                    <td>${order.order_reference}</td>
                                    <td>${order.customer_name}</td>
                                    <td>${new Date(order.date).toLocaleString()}</td>
                                    <td>$${parseFloat(order.total).toFixed(2)}</td>
                                    <td>
                                        <select class="form-select form-select-sm status-select" data-order-id="${order.id}">
                                            <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                                            <option value="confirmed" ${order.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                                            <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>Shipped</option>
                                            <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>Delivered</option>
                                            <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                        </select>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewOrder(${order.id})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteOrder(${order.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                `;
                                tbody.appendChild(row);
                            });
                        } else {
                            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No orders found</td></tr>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('Error searching orders', 'danger');
                    });
            } else {
                // Reload the page to show all orders
                window.location.reload();
            }
        });
    }
});

function getStatusColor(status) {
    switch (status) {
        case 'pending':
            return 'warning';
        case 'confirmed':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}
</script>
