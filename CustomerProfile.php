<?php
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if customer is logged in
if (!isset($_SESSION['customer_logged_in']) || !$_SESSION['customer_logged_in']) {
    header("Location: index.php?p=Login");
    exit;
}

require_once 'admin/include/db.php';

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    try {
        $db = (new Database())->connect();
        
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $city = $_POST['city'];
        $zipCode = $_POST['zip_code'];
        
        // Update customer profile
        $updateQuery = "UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, city = ?, zip_code = ? WHERE id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$firstName, $lastName, $email, $phone, $address, $city, $zipCode, $_SESSION['customer_id']]);
        
        // Update session
        $_SESSION['customer_name'] = $firstName . ' ' . $lastName;
        $_SESSION['customer_email'] = $email;
        
        $success_message = "Profile updated successfully!";
        
    } catch (Exception $e) {
        $error_message = "Update error: " . $e->getMessage();
    }
}

// Handle password change
if ($_POST && isset($_POST['change_password'])) {
    try {
        $db = (new Database())->connect();
        
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Verify current password
        $verifyQuery = "SELECT password FROM customers WHERE id = ?";
        $verifyStmt = $db->prepare($verifyQuery);
        $verifyStmt->execute([$_SESSION['customer_id']]);
        $customer = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!password_verify($currentPassword, $customer['password'])) {
            throw new Exception("Current password is incorrect");
        }
        
        if ($newPassword !== $confirmPassword) {
            throw new Exception("New passwords do not match");
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE customers SET password = ? WHERE id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$hashedPassword, $_SESSION['customer_id']]);
        
        $success_message = "Password changed successfully!";
        
    } catch (Exception $e) {
        $error_message = "Password change error: " . $e->getMessage();
    }
}

// Fetch customer data
try {
    $db = (new Database())->connect();
    $query = "SELECT * FROM customers WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['customer_id']]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error_message = "Error fetching profile: " . $e->getMessage();
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">My Profile</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Profile Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold mb-6">Profile Information</h2>
                
                <form method="POST" action="index.php?p=CustomerProfile">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($customer['first_name'] ?? ''); ?>" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($customer['last_name'] ?? ''); ?>" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea name="address" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <input type="text" name="city" value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ZIP Code</label>
                            <input type="text" name="zip_code" value="<?php echo htmlspecialchars($customer['zip_code'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300">
                        Update Profile
                    </button>
                </form>
            </div>
            
            <!-- Change Password -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold mb-6">Change Password</h2>
                
                <form method="POST" action="index.php?p=CustomerProfile">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                        <input type="password" name="current_password" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" name="new_password" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition duration-300">
                        Change Password
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-8">
            <h2 class="text-2xl font-semibold mb-6">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="index.php?p=CustomerOrders" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-shopping-bag text-blue-600 text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-semibold">My Orders</h3>
                        <p class="text-sm text-gray-600">View your order history</p>
                    </div>
                </a>
                <a href="index.php?p=Shop" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-store text-green-600 text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-semibold">Continue Shopping</h3>
                        <p class="text-sm text-gray-600">Browse our products</p>
                    </div>
                </a>
                <a href="index.php?p=Shoppingcart" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-shopping-cart text-orange-600 text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-semibold">Shopping Cart</h3>
                        <p class="text-sm text-gray-600">Review your cart</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div> 