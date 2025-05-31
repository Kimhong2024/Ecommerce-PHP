<?php
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if customer is logged in, if not redirect to login
if (!isset($_SESSION['customer_logged_in']) || !$_SESSION['customer_logged_in']) {
    $_SESSION['redirect_after_login'] = 'index.php?p=CheckOut';
    header("Location: index.php?p=Login");
    exit;
}

require_once 'admin/include/db.php';

// Handle form submission
if ($_POST && !isset($_POST['verify_paypal_payment'])) {
    try {
        $db = (new Database())->connect();
        
        // Get form data with proper validation
        $customerName = $_POST['customer_name'] ?? '';
        $customerEmail = $_POST['customer_email'] ?? '';
        $customerPhone = $_POST['customer_phone'] ?? '';
        $shippingAddress = $_POST['shipping_address'] ?? '';
        $city = $_POST['city'] ?? '';
        $zipCode = $_POST['zip_code'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? '';
        
        // Check required fields
        if (empty($customerName) || empty($customerEmail) || empty($paymentMethod)) {
            throw new Exception('Required fields are missing');
        }
        
        // Get cart data from POST (sent via JavaScript)
        $cartData = isset($_POST['cart_data']) ? json_decode($_POST['cart_data'], true) : [];
        
        if (empty($cartData)) {
            throw new Exception('Cart is empty');
        }
        
        // Calculate totals
        $subtotal = 0;
        foreach ($cartData as $item) {
            $subtotal += floatval($item['price'] ?? 0) * intval($item['quantity'] ?? 1);
        }
        $shipping = $subtotal > 50 ? 0 : 10;
        $tax = $subtotal * 0.08;
        $total = $subtotal + $shipping + $tax;
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Generate order reference for KHQR
            $orderReference = 'ORD-' . date('Ymd') . '-' . uniqid();
            
            // Insert order
            $orderQuery = "INSERT INTO orders (order_reference, customer_name, customer_email, customer_phone, shipping_address, city, zip_code, subtotal, shipping, tax, total, payment_method, status, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $orderStmt = $db->prepare($orderQuery);
            
            // Set initial status based on payment method
            $initialStatus = ($paymentMethod === 'khqr') ? 'pending_payment' : 'pending';
            
            $orderStmt->execute([$orderReference, $customerName, $customerEmail, $customerPhone, $shippingAddress, $city, $zipCode, $subtotal, $shipping, $tax, $total, $paymentMethod, $initialStatus]);
            
            $orderId = $db->lastInsertId();
            
            // Insert order items
            $orderItemQuery = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total) VALUES (?, ?, ?, ?, ?, ?)";
            $orderItemStmt = $db->prepare($orderItemQuery);
            
            foreach ($cartData as $item) {
                // Validate required item data exists
                if (!isset($item['id']) || !isset($item['name']) || !isset($item['price']) || !isset($item['quantity'])) {
                    // Skip invalid items or use default values
                    continue;
                }
                
                $productId = isset($item['id']) ? $item['id'] : null;
                $productName = isset($item['name']) ? $item['name'] : 'Unknown Product';
                $productPrice = isset($item['price']) ? floatval($item['price']) : 0;
                $productQuantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
                
                $itemTotal = $productPrice * $productQuantity;
                $orderItemStmt->execute([$orderId, $productId, $productName, $productPrice, $productQuantity, $itemTotal]);
            }
            
            // Commit transaction
            $db->commit();
            
            // Set success message
            $_SESSION['order_success'] = "Order placed successfully! Order #$orderId";
            
            // Redirect based on payment method
            if ($paymentMethod === 'paypal') {
                header("Location: index.php?p=CheckOut&paypal_payment=1&order_id=$orderId&total=$total&reference=$orderReference");
            } else {
                header("Location: index.php?p=CheckOut&success=1&order_id=$orderId");
            }
            exit;
        } catch (Exception $e) {
            // Only rollback if transaction is active
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e; // Re-throw to be caught by the outer try-catch
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Handle PayPal payment verification
if (isset($_POST['verify_paypal_payment'])) {
    try {
        $db = (new Database())->connect();
        $orderId = isset($_POST['order_id']) ? $_POST['order_id'] : null;
        $paypalPaymentId = $_POST['paypal_payment_id'] ?? null;
        
        if (empty($orderId)) {
            throw new Exception('Order ID is required');
        }
        
        // Begin transaction
        $db->beginTransaction();
        
        try {
            // Check if paypal_payment_id column exists in the orders table
            $columnExists = false;
            try {
                $checkColumn = $db->query("SHOW COLUMNS FROM orders LIKE 'paypal_payment_id'");
                $columnExists = ($checkColumn && $checkColumn->rowCount() > 0);
            } catch (Exception $e) {
                // Column doesn't exist, continue with basic update
            }
            
            // Update order status to paid
            if ($columnExists && $paypalPaymentId) {
                $updateQuery = "UPDATE orders SET status = 'paid', payment_verified_at = NOW(), paypal_payment_id = ? WHERE id = ?";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->execute([$paypalPaymentId, $orderId]);
            } else {
                $updateQuery = "UPDATE orders SET status = 'paid', payment_verified_at = NOW() WHERE id = ?";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->execute([$orderId]);
            }
            
            // Commit the transaction
            $db->commit();
            
            $_SESSION['order_success'] = "Payment verified successfully!";
            header("Location: index.php?p=CheckOut&success=1&order_id=$orderId");
            exit;
        } catch (Exception $e) {
            // Rollback if transaction is active
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e; // Re-throw for outer catch
        }
    } catch (Exception $e) {
        $error_message = "Payment verification failed: " . $e->getMessage();
    }
}

// Check if this is KHQR payment page
// Check if this is PayPal payment page
$isPaypalPayment = isset($_GET['paypal_payment']) && $_GET['paypal_payment'] == 1;
$isSuccess = isset($_GET['success']) && $_GET['success'] == 1;
$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : null;
$total = isset($_GET['total']) ? $_GET['total'] : 0;
$orderReference = isset($_GET['reference']) ? $_GET['reference'] : '';

// Get customer data for pre-filling
try {
    $db = (new Database())->connect();
    
    // Check if customer_id exists in session
    if (isset($_SESSION['customer_id'])) {
        $query = "SELECT * FROM customers WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['customer_id']]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Fallback to email if customer_id is not set
        $query = "SELECT * FROM customers WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['customer_email'] ?? '']);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Store customer_id in session if found
        if ($customer && isset($customer['id'])) {
            $_SESSION['customer_id'] = $customer['id'];
        }
    }
    
    // Initialize customer data with defaults if not found
    if (!$customer) {
        $customer = [
            'first_name' => '',
            'last_name' => '',
            'email' => $_SESSION['customer_email'] ?? '',
            'phone' => '',
            'address' => '',
            'city' => '',
            'zip_code' => ''
        ];
    }
} catch (Exception $e) {
    // Handle error
    $customer = [
        'first_name' => '',
        'last_name' => '',
        'email' => $_SESSION['customer_email'] ?? '',
        'phone' => '',
        'address' => '',
        'city' => '',
        'zip_code' => ''
    ];
}
?>


    <div class="container mx-auto px-4 py-8">
    <?php if ($isPaypalPayment): ?>
            <!-- PayPal Payment Page -->
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-lg shadow-md p-8">
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fab fa-paypal text-3xl text-blue-600"></i>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">PayPal Payment</h1>
                        <p class="text-gray-600">Pay securely with PayPal</p>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-gray-600">Order Reference:</span>
                            <span class="font-semibold"><?php echo htmlspecialchars($orderReference); ?></span>
                        </div>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-gray-600">Total Amount:</span>
                            <span class="text-2xl font-bold text-green-600">$<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                    
                    <!-- PayPal Payment Button -->
                    <div class="text-center mb-6">
                        <div id="paypal-button-container" class="max-w-md mx-auto"></div>
                    </div>
                    
                    <!-- Payment Instructions -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h3 class="text-blue-800 font-semibold mb-2">Payment Instructions:</h3>
                        <ol class="text-blue-700 text-sm space-y-1">
                            <li>1. Click the PayPal button above</li>
                            <li>2. Log in to your PayPal account or pay as guest</li>
                            <li>3. Review your order details</li>
                            <li>4. Confirm payment</li>
                        </ol>
                    </div>
                    
                    <!-- Manual Payment Verification (for demo) -->
                    <div class="text-center space-y-4">
                        <form method="POST" action="index.php?p=CheckOut" id="verify-payment-form">
                            <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                            <input type="hidden" name="paypal_payment_id" id="paypal_payment_id_input" value="">
                            <input type="hidden" name="verify_paypal_payment" value="1">
                            <button type="submit" class="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                                I've Completed Payment
                            </button>
                        </form>
                        
                        <div class="text-sm text-gray-500">
                            <p>Payment not working? <a href="index.php?p=CheckOut" class="text-blue-600 hover:underline">Try another payment method</a></p>
                        </div>
                    </div>
                    
                    <!-- Security Notice -->
                    <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-shield-alt text-green-600 mr-2"></i>
                            <span class="text-sm text-green-700">
                                Your payment is secured by PayPal's industry-leading encryption
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($isSuccess): ?>
            <!-- Success Page -->
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check text-3xl text-green-600"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-4">Order Confirmed!</h1>
                    <p class="text-gray-600 mb-2">Thank you for your purchase.</p>
                    <p class="text-gray-600 mb-6">Your order <span class="font-semibold">#<?php echo $orderId; ?></span> has been placed successfully.</p>
                    <div class="space-y-3">
                        <a href="index.php?p=Shop" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition mr-4">
                            Continue Shopping
                        </a>
                        <a href="index.php" class="inline-block px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                            Back to Home
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Checkout Form -->
            <div class="max-w-6xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-800 mb-8">Checkout</h1>
                
                <?php if (isset($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Checkout Form -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold mb-6">Billing Information</h2>
                        <form id="checkout-form" method="POST" action="index.php?p=CheckOut">
                            <input type="hidden" name="cart_data" id="cart_data_input">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                    <input type="text" name="customer_name" value="<?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                    <input type="email" name="customer_email" value="<?php echo htmlspecialchars($customer['email']); ?>" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                                <input type="tel" name="customer_phone" value="<?php echo htmlspecialchars($customer['phone']); ?>" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Address *</label>
                                <textarea name="shipping_address" required rows="3"
                                         class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($customer['address']); ?></textarea>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                                    <input type="text" name="city" value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ZIP Code *</label>
                                    <input type="text" name="zip_code" value="<?php echo htmlspecialchars($customer['zip_code'] ?? ''); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                                <div class="space-y-3">
                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                        <input type="radio" name="payment_method" value="khqr" required class="mr-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-qrcode text-blue-600 text-xl mr-3"></i>
                                            <div>
                                                <span class="font-semibold">KHQR</span>
                                                <p class="text-sm text-gray-500">Pay instantly with KHQR</p>
                                            </div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                        <input type="radio" name="payment_method" value="credit_card" required class="mr-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-credit-card text-green-600 text-xl mr-3"></i>
                                            <span>Credit Card</span>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                        <input type="radio" name="payment_method" value="paypal" required class="mr-3">
                                        <div class="flex items-center">
                                            <i class="fab fa-paypal text-blue-500 text-xl mr-3"></i>
                                            <span>PayPal</span>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                        <input type="radio" name="payment_method" value="cash_on_delivery" required class="mr-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-money-bill text-green-500 text-xl mr-3"></i>
                                            <span>Cash on Delivery</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="w-full mt-8 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                                Place Order
                            </button>
                        </form>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold mb-6">Order Summary</h2>
                        <div id="checkout-items" class="space-y-4 max-h-60 overflow-y-auto">
                            <!-- Items will be populated by JavaScript -->
                        </div>
                        <hr class="my-4">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span id="checkout-subtotal">$0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Shipping:</span>
                                <span id="checkout-shipping">$10.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Tax (8%):</span>
                                <span id="checkout-tax">$0.00</span>
                            </div>
                            <hr class="my-2">
                            <div class="flex justify-between text-lg font-semibold">
                                <span>Total:</span>
                                <span id="checkout-total">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
      <!-- PayPal SDK -->
      <script src="https://www.paypal.com/sdk/js?client-id="></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if this is PayPal payment page
            const isPaypalPayment = <?php echo $isPaypalPayment ? 'true' : 'false'; ?>;
            
            if (isPaypalPayment) {
                // Initialize PayPal
                initializePayPal();
            } else if (!<?php echo $isSuccess ? 'true' : 'false'; ?>) {
                // Load cart data on checkout page
                loadCheckoutData();
                
                // Handle form submission
                document.getElementById('checkout-form').addEventListener('submit', function(e) {
                    const cart = JSON.parse(localStorage.getItem('cart')) || [];
                    if (cart.length === 0) {
                        e.preventDefault();
                        alert('Your cart is empty!');
                        return;
                    }
                    
                    // Validate cart items before submission
                    const validCart = cart.filter(item => {
                        return item && item.id && item.name && item.price && item.quantity;
                    });
                    
                    // Check if any items were filtered out
                    if (validCart.length === 0) {
                        e.preventDefault();
                        alert('Your cart contains invalid items. Please try adding products again.');
                        return;
                    }
                    
                    if (validCart.length < cart.length) {
                        // Some invalid items were removed
                        localStorage.setItem('cart', JSON.stringify(validCart));
                    }
                    
                    // Add cart data to hidden input
                    document.getElementById('cart_data_input').value = JSON.stringify(validCart);
                });
            } else {
                // Clear cart after successful order
                localStorage.removeItem('cart');
            }
            
            function initializePayPal() {
                const orderRef = '<?php echo $orderReference; ?>';
                const amount = '<?php echo $total; ?>';
                const orderId = '<?php echo $orderId; ?>';
                
                paypal.Buttons({
                    createOrder: function(data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                reference_id: orderRef,
                                amount: {
                                    value: amount,
                                    currency_code: 'USD'
                                },
                                description: `Order #${orderRef} - E-PHP Shop`
                            }]
                        });
                    },
                    onApprove: function(data, actions) {
                        return actions.order.capture().then(function(details) {
                            // Payment successful
                            console.log('Payment completed by ' + details.payer.name.given_name);
                            
                            // Check if the payment ID input field exists
                            const paymentIdInput = document.getElementById('paypal_payment_id_input');
                            if (paymentIdInput) {
                                paymentIdInput.value = data.orderID || '';
                            }
                            
                            // Auto-submit verification form
                            document.getElementById('verify-payment-form').submit();
                        });
                    },
                    onError: function(err) {
                        console.error('PayPal error:', err);
                        alert('Payment failed. Please try again.');
                    },
                    onCancel: function(data) {
                        console.log('Payment cancelled');
                        alert('Payment was cancelled. You can try again or choose a different payment method.');
                    }
                }).render('#paypal-button-container');
            }
            
            function loadCheckoutData() {
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                const checkoutItems = document.getElementById('checkout-items');
                
                if (cart.length === 0) {
                    checkoutItems.innerHTML = '<p class="text-gray-500">Your cart is empty</p>';
                    return;
                }
                
                checkoutItems.innerHTML = '';
                
                // Validate cart data and fix any missing IDs
                const validatedCart = cart.map(item => {
                    // Ensure item has an id property
                    if (!item.id && item.product_id) {
                        item.id = item.product_id;
                    } else if (!item.id) {
                        // Generate a fallback ID if none exists
                        item.id = 'product-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
                    }
                    return item;
                });
                
                // Save the validated cart back to localStorage
                localStorage.setItem('cart', JSON.stringify(validatedCart));
                
                validatedCart.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'flex items-center justify-between p-3 border-b';
                    div.innerHTML = `
                        <div class="flex items-center space-x-3">
                            <img src="admin/${item.image || 'uploads/default-product.jpg'}" 
                                 alt="${item.name}" 
                                 class="w-12 h-12 object-cover rounded">
                            <div>
                                <h4 class="font-semibold">${item.name}</h4>
                                <p class="text-sm text-gray-600">Qty: ${item.quantity}</p>
                            </div>
                        </div>
                        <span class="font-semibold">$${(parseFloat(item.price) * item.quantity).toFixed(2)}</span>
                    `;
                    checkoutItems.appendChild(div);
                });
                
                updateCheckoutSummary();
            }
            
            function updateCheckoutSummary() {
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                let subtotal = 0;
                
                // Only process valid items with price and quantity
                cart.forEach(item => {
                    if (item && item.price && item.quantity) {
                        subtotal += parseFloat(item.price) * parseInt(item.quantity, 10);
                    }
                });
                
                const shipping = subtotal > 50 ? 0 : 10;
                const tax = subtotal * 0.08;
                const total = subtotal + shipping + tax;
                
                document.getElementById('checkout-subtotal').textContent = `$${subtotal.toFixed(2)}`;
                document.getElementById('checkout-shipping').textContent = shipping === 0 ? 'Free' : `$${shipping.toFixed(2)}`;
                document.getElementById('checkout-tax').textContent = `$${tax.toFixed(2)}`;
                document.getElementById('checkout-total').textContent = `$${total.toFixed(2)}`;
            }
        });
    </script>

<?php
?>

