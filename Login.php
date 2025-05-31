<?php
// Session is already started in nav.php, so we don't need to start it again

require_once 'admin/include/db.php';

// Handle logout action
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Destroy all session data
    session_destroy();
    // Start a new session for any success messages
    session_start();
    $_SESSION['success_message'] = "You have been logged out successfully.";
    // Use JavaScript redirect since headers already sent
    echo "<script>window.location.href = 'index.php?p=Login';</script>";
    exit;
}

// Handle login form submission
if ($_POST && isset($_POST['login'])) {
    try {
        $db = (new Database())->connect();
        
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        // Validate input
        if (empty($email) || empty($password)) {
            throw new Exception("Please fill in all fields");
        }
        
        // Check customer credentials
        $query = "SELECT * FROM customers WHERE email = ? AND status = 'active'";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($customer && password_verify($password, $customer['password'])) {
            // Login successful
            $_SESSION['customer_logged_in'] = true;
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];
            $_SESSION['customer_email'] = $customer['email'];
            
            // Use JavaScript redirect since headers already sent
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php?p=Home';
            echo "<script>window.location.href = '$redirect';</script>";
            exit;
        } else {
            throw new Exception("Invalid email or password");
        }
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Handle registration form submission
if ($_POST && isset($_POST['register'])) {
    try {
        $db = (new Database())->connect();
        
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address'] ?? '');
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Validate input
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            throw new Exception("Please fill in all required fields");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address");
        }
        
        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters long");
        }
        
        if ($password !== $confirmPassword) {
            throw new Exception("Passwords do not match");
        }
        
        // Check if email already exists
        $checkQuery = "SELECT id FROM customers WHERE email = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$email]);
        
        if ($checkStmt->fetch()) {
            throw new Exception("Email already registered. Please use a different email.");
        }
        
        // Hash password and create account
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $insertQuery = "INSERT INTO customers (first_name, last_name, email, phone, address, password, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([$firstName, $lastName, $email, $phone, $address, $hashedPassword]);
        
        // Get the newly created customer ID
        $customerId = $db->lastInsertId();
        
        // Automatically log in the user
        $_SESSION['customer_logged_in'] = true;
        $_SESSION['customer_id'] = $customerId;
        $_SESSION['customer_name'] = $firstName . ' ' . $lastName;
        $_SESSION['customer_email'] = $email;
        
        // Use JavaScript redirect since headers already sent
        echo "<script>window.location.href = 'index.php?p=CustomerProfile';</script>";
        exit;
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// If user is already logged in, redirect to profile
if (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in']) {
    echo "<script>window.location.href = 'index.php?p=CustomerProfile';</script>";
    exit;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto">
        <!-- Display success/error messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($_SESSION['success_message'] ?? ''); unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($error_message ?? ''); ?>
            </div>
        <?php endif; ?>
        
        <!-- Login/Register Toggle -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="flex">
                <button id="loginTab" class="flex-1 py-3 px-4 text-center font-semibold bg-blue-600 text-white cursor-pointer border-b-2 border-blue-600">
                    Login
                </button>
                <button id="registerTab" class="flex-1 py-3 px-4 text-center font-semibold bg-gray-100 text-gray-600 cursor-pointer border-b-2 border-transparent hover:bg-gray-200">
                    Register
                </button>
            </div>
            
            <!-- Login Form -->
            <div id="loginForm" class="p-6">
                <h2 class="text-2xl font-bold text-center mb-6">Login to Your Account</h2>
                
                <form method="POST" action="index.php?p=Login">
                    <input type="hidden" name="login" value="1">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" name="email" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?php echo isset($_POST['email']) && isset($_POST['login']) ? htmlspecialchars($_POST['email'] ?? '') : ''; ?>">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" name="password" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300">
                        Login
                    </button>
                </form>
            </div>
            
            <!-- Register Form -->
            <div id="registerForm" class="p-6 hidden">
                <h2 class="text-2xl font-bold text-center mb-6">Create Your Account</h2>
                
                <form method="POST" action="index.php?p=Login">
                    <input type="hidden" name="register" value="1">
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <input type="text" name="first_name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   value="<?php echo isset($_POST['first_name']) && isset($_POST['register']) ? htmlspecialchars($_POST['first_name'] ?? '') : ''; ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input type="text" name="last_name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   value="<?php echo isset($_POST['last_name']) && isset($_POST['register']) ? htmlspecialchars($_POST['last_name'] ?? '') : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" name="email" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?php echo isset($_POST['email']) && isset($_POST['register']) ? htmlspecialchars($_POST['email'] ?? '') : ''; ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" name="phone" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?php echo isset($_POST['phone']) && isset($_POST['register']) ? htmlspecialchars($_POST['phone'] ?? '') : ''; ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea name="address" rows="2" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        ><?php echo isset($_POST['address']) && isset($_POST['register']) ? htmlspecialchars($_POST['address'] ?? '') : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" name="password" required minlength="6"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <small class="text-gray-600">Minimum 6 characters</small>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                        <input type="password" name="confirm_password" required minlength="6"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition duration-300">
                        Create Account
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Additional Links -->
        <div class="text-center mt-6">
            <a href="index.php?p=Home" class="text-blue-600 hover:text-blue-800">
                ← Back to Home
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginTab = document.getElementById('loginTab');
    const registerTab = document.getElementById('registerTab');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    // Switch to login form
    loginTab.addEventListener('click', function() {
        loginTab.classList.remove('bg-gray-100', 'text-gray-600');
        loginTab.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
        registerTab.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
        registerTab.classList.add('bg-gray-100', 'text-gray-600', 'border-transparent');
        
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
    });
    
    // Switch to register form
    registerTab.addEventListener('click', function() {
        registerTab.classList.remove('bg-gray-100', 'text-gray-600', 'border-transparent');
        registerTab.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
        loginTab.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
        loginTab.classList.add('bg-gray-100', 'text-gray-600', 'border-transparent');
        
        registerForm.classList.remove('hidden');
        loginForm.classList.add('hidden');
    });
    
    // Check if there was a registration attempt and switch to register tab
    <?php if ($_POST && isset($_POST['register'])): ?>
        registerTab.click();
    <?php endif; ?>
});
</script> 