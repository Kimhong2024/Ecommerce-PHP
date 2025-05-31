<?php
// MyProfile.php
require_once 'include/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ./auth/login.php');
    exit();
}

// Initialize variables
$db = new Database();
$conn = $db->connect();

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$showSuccessMessage = false; // Flag to control redirects

// Load user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'update_email') {
        $email = trim($_POST['email']);
        
        try {
            $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$email, $userId]);
            
            // Update session
            $_SESSION['email'] = $email;
            
            $_SESSION['success_message'] = 'Email updated successfully!';
            // Don't redirect - it causes an infinite loop
            // Use a flag to show the success message instead
            $showSuccessMessage = true;
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error updating email: ' . $e->getMessage();
        }
    }
    else if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            $_SESSION['error_message'] = 'Current password is incorrect';
        } else if ($newPassword !== $confirmPassword) {
            $_SESSION['error_message'] = 'New passwords do not match';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            try {
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $userId]);
                
                $_SESSION['success_message'] = 'Password changed successfully!';
                // Don't redirect - it causes an infinite loop
                // Use a flag to show the success message instead
                $showSuccessMessage = true;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Error changing password: ' . $e->getMessage();
            }
        }
    }
    else if ($action === 'upload_avatar') {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = $_FILES['avatar']['type'];
            
            if (!in_array($fileType, $allowedTypes)) {
                $_SESSION['error_message'] = 'Only JPG, PNG and GIF images are allowed';
            } else {
                $maxSize = 2 * 1024 * 1024; // 2MB
                if ($_FILES['avatar']['size'] > $maxSize) {
                    $_SESSION['error_message'] = 'Image size should not exceed 2MB';
                } else {
                    $fileName = 'user_' . $userId . '_' . time() . '.' . pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                    $targetPath = 'uploads/avatars/' . $fileName;
                    
                    // Create directory if it doesn't exist
                    if (!file_exists('uploads/avatars')) {
                        mkdir('uploads/avatars', 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
                        try {
                            // Update avatar in database - check if avatar column exists first
                            $stmt = $conn->prepare("
                                SELECT COUNT(*) as column_exists 
                                FROM information_schema.COLUMNS 
                                WHERE TABLE_SCHEMA = 'e-php' 
                                AND TABLE_NAME = 'users' 
                                AND COLUMN_NAME = 'avatar'
                            ");
                            $stmt->execute();
                            $result = $stmt->fetch();
                            
                            if ($result['column_exists'] > 0) {
                                $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                                $stmt->execute([$fileName, $userId]);
                                
                                $_SESSION['success_message'] = 'Profile picture updated successfully!';
                            } else {
                                $_SESSION['error_message'] = 'Avatar column does not exist in the database';
                            }
                            
                            // Don't redirect - it causes an infinite loop
                            // Use a flag to show the success message instead
                            $showSuccessMessage = true;
                        } catch (PDOException $e) {
                            $_SESSION['error_message'] = 'Error updating profile picture: ' . $e->getMessage();
                        }
                    } else {
                        $_SESSION['error_message'] = 'Failed to upload image';
                    }
                }
            }
        } else {
            $_SESSION['error_message'] = 'Please select an image to upload';
        }
    }
}
?>

<div class="container-fluid px-4">
    <div class="page-inner">
        <div class="page-header mb-4">
            <h1 class="page-title fw-bold">My Profile</h1>
            <span class="text-muted">Manage your account information and settings</span>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Overview Card -->
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="avatar-container mx-auto mb-3">
                                <?php if (isset($user['avatar']) && !empty($user['avatar'])): ?>
                                    <img src="uploads/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" class="avatar-img" alt="Profile Picture">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?php echo strtoupper(substr($username, 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($username); ?></h4>
                            <p class="text-muted mb-3">Administrator</p>
                            
                            <form method="POST" action="" enctype="multipart/form-data" class="mt-3 text-start">
                                <input type="hidden" name="action" value="upload_avatar">
                                <label for="avatar" class="form-label fw-medium">Change Profile Picture</label>
                                <div class="input-group mb-3">
                                    <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload"></i>
                                    </button>
                                </div>
                                <div class="form-text">Maximum file size: 2MB (JPG, PNG, GIF)</div>
                            </form>
                        </div>
                        
                        <hr>
                        
                        <h6 class="fw-bold mb-3">Account Information</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0 py-2 d-flex align-items-center border-0">
                                <i class="fas fa-user text-primary me-3"></i>
                                <div>
                                    <small class="d-block text-muted">Username</small>
                                    <span class="fw-medium"><?php echo htmlspecialchars($username); ?></span>
                                </div>
                            </li>
                            <li class="list-group-item px-0 py-2 d-flex align-items-center border-0">
                                <i class="fas fa-envelope text-primary me-3"></i>
                                <div>
                                    <small class="d-block text-muted">Email Address</small>
                                    <span class="fw-medium"><?php echo htmlspecialchars($email); ?></span>
                                </div>
                            </li>
                            <li class="list-group-item px-0 py-2 d-flex align-items-center border-0">
                                <i class="fas fa-shield-alt text-primary me-3"></i>
                                <div>
                                    <small class="d-block text-muted">Account Status</small>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </li>
                            <li class="list-group-item px-0 py-2 d-flex align-items-center border-0">
                                <i class="fas fa-clock text-primary me-3"></i>
                                <div>
                                    <small class="d-block text-muted">Last Login</small>
                                    <span class="fw-medium"><?php echo date('M d, Y H:i', time()); ?></span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Profile Settings -->
            <div class="col-lg-8 col-md-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0"><i class="fas fa-envelope me-2 text-primary"></i> Update Email Address</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_email">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                <div class="form-text">We'll never share your email with anyone else.</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Email
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0"><i class="fas fa-lock me-2 text-primary"></i> Change Password</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">
                            Strong passwords include a mix of uppercase and lowercase letters, numbers, and special characters.
                        </p>
                        
                        <form method="POST" action="index.php?p=MyProfile">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                        <i class="far fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                        <i class="far fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                                        <i class="far fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key me-2"></i>Change Password
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0"><i class="fas fa-shield-alt me-2 text-primary"></i> Security Settings</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center border-0">
                                <div>
                                    <h6 class="mb-1 fw-medium">Two-Factor Authentication</h6>
                                    <p class="text-muted mb-0 small">Add an extra layer of security to your account</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="twoFactorSwitch" disabled>
                                </div>
                            </div>
                            
                            <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center border-0">
                                <div>
                                    <h6 class="mb-1 fw-medium">Login Notifications</h6>
                                    <p class="text-muted mb-0 small">Get notified when someone logs into your account</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="loginNotifSwitch" checked>
                                </div>
                            </div>
                            
                            <div class="list-group-item px-0 py-3 border-0">
                                <h6 class="mb-3 fw-medium">Active Sessions</h6>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <i class="fas fa-desktop fs-4 text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-medium">Windows PC - Chrome</span>
                                            <span class="badge bg-success">Current</span>
                                        </div>
                                        <small class="text-muted"><?php echo $_SERVER['REMOTE_ADDR']; ?> - <?php echo date('M d, Y H:i'); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Profile styles */
    .avatar-container {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        overflow: hidden;
        background-color: #f8f9fa;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
    }
    
    .avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .avatar-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #0d6efd;
        color: white;
        font-size: 40px;
        font-weight: bold;
    }
    
    .list-group-item i {
        width: 20px;
        text-align: center;
    }
    
    .card {
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="far fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="far fa-eye"></i>';
            }
        });
    });
    
    // Preview image before upload
    const avatarInput = document.getElementById('avatar');
    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const avatarContainer = document.querySelector('.avatar-container');
                    
                    // Check if there's already an image or placeholder
                    if (avatarContainer.querySelector('.avatar-img')) {
                        avatarContainer.querySelector('.avatar-img').src = e.target.result;
                    } else if (avatarContainer.querySelector('.avatar-placeholder')) {
                        // Remove placeholder and create image
                        avatarContainer.innerHTML = `<img src="${e.target.result}" class="avatar-img" alt="Profile Picture">`;
                    }
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Password strength validation
    const newPassword = document.getElementById('new_password');
    if (newPassword) {
        newPassword.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 1;
            
            // Contains uppercase
            if (/[A-Z]/.test(password)) strength += 1;
            
            // Contains lowercase
            if (/[a-z]/.test(password)) strength += 1;
            
            // Contains number
            if (/[0-9]/.test(password)) strength += 1;
            
            // Contains special char
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Update feedback
            let feedbackText = "";
            let feedbackColor = "";
            
            switch(strength) {
                case 0:
                case 1:
                    feedbackText = "Very Weak";
                    feedbackColor = "#dc3545";
                    break;
                case 2:
                    feedbackText = "Weak";
                    feedbackColor = "#ffc107";
                    break;
                case 3:
                    feedbackText = "Medium";
                    feedbackColor = "#fd7e14";
                    break;
                case 4:
                    feedbackText = "Strong";
                    feedbackColor = "#20c997";
                    break;
                case 5:
                    feedbackText = "Very Strong";
                    feedbackColor = "#198754";
                    break;
            }
            
            // Update UI with feedback
            let feedbackEl = document.getElementById('password-strength-feedback');
            if (!feedbackEl) {
                feedbackEl = document.createElement('div');
                feedbackEl.id = 'password-strength-feedback';
                feedbackEl.classList.add('mt-2');
                this.parentNode.appendChild(feedbackEl);
            }
            
            feedbackEl.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="progress flex-grow-1" style="height: 5px;">
                        <div class="progress-bar" role="progressbar" style="width: ${strength * 20}%; background-color: ${feedbackColor};" 
                            aria-valuenow="${strength * 20}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <span class="ms-2" style="color: ${feedbackColor};">${feedbackText}</span>
                </div>
            `;
        });
    }
});
</script> 