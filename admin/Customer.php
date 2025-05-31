<?php
// Customer.php
require_once 'include/db.php';

class Customer {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    // Handle form submissions
    public function handleFormSubmission() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $id = $_POST['id'] ?? null;
            $firstName = trim($_POST['first_name']);
            $lastName = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $address = trim($_POST['address']);
            $password = trim($_POST['password']);

            // Validate input
            if (empty($firstName) || empty($lastName)) {
                return ['success' => false, 'message' => 'First name and last name are required'];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }

            // Check if email already exists (for new customers)
            if (empty($id)) {
                $stmt = $this->db->prepare("SELECT id FROM customers WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    return ['success' => false, 'message' => 'Email already exists'];
                }
            }

            try {
                if ($_POST['action'] === 'add') {
                    // Add new customer
                    if (empty($password)) {
                        return ['success' => false, 'message' => 'Password is required for new customers'];
                    }
                    $stmt = $this->db->prepare("INSERT INTO customers (first_name, last_name, email, phone, address, password, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$firstName, $lastName, $email, $phone, $address, password_hash($password, PASSWORD_DEFAULT)]);
                    return ['success' => true, 'message' => 'Customer added successfully'];
                } elseif ($_POST['action'] === 'edit' && isset($_POST['id'])) {
                    // Update existing customer
                    if (!empty($password)) {
                        $stmt = $this->db->prepare("UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?");
                        $stmt->execute([$firstName, $lastName, $email, $phone, $address, password_hash($password, PASSWORD_DEFAULT), $id]);
                    } else {
                        $stmt = $this->db->prepare("UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
                        $stmt->execute([$firstName, $lastName, $email, $phone, $address, $id]);
                    }
                    return ['success' => true, 'message' => 'Customer updated successfully'];
                }
            } catch (PDOException $e) {
                return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
        }
        return null;
    }

    // Handle delete action
    public function handleDelete() {
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            try {
                $stmt = $this->db->prepare("DELETE FROM customers WHERE id = ?");
                $stmt->execute([$id]);
                return ['success' => true, 'message' => 'Customer deleted successfully'];
            } catch (PDOException $e) {
                return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
        }
        return null;
    }

    // Fetch customers with search
    public function getCustomers($search = '') {
        try {
            $query = "SELECT *, CONCAT(first_name, ' ', last_name) as full_name FROM customers";
            
            if ($search) {
                $query .= " WHERE first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR phone LIKE :search";
                $stmt = $this->db->prepare($query);
                $stmt->execute(['search' => "%$search%"]);
            } else {
                $stmt = $this->db->query($query);
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Get a single customer by ID
    public function getCustomerById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
}

// Initialize the Customer class
$customerManager = new Customer();

// Handle form submissions
$formResult = $customerManager->handleFormSubmission();
if (isset($formResult['success']) && $formResult['success']) {
    $_SESSION['success_message'] = $formResult['message'] ?? 'Operation completed successfully';
    echo "<script>window.location.href = 'index.php?p=Customer';</script>";
    exit;
} elseif (isset($formResult['success']) && !$formResult['success']) {
    $_SESSION['error_message'] = $formResult['message'] ?? 'An error occurred';
}

// Handle delete action
$deleteResult = $customerManager->handleDelete();
if (isset($deleteResult['success']) && $deleteResult['success']) {
    $_SESSION['success_message'] = $deleteResult['message'] ?? 'Customer deleted successfully';
    echo "<script>window.location.href = 'index.php?p=Customer';</script>";
    exit;
} elseif (isset($deleteResult['success']) && !$deleteResult['success']) {
    $_SESSION['error_message'] = $deleteResult['message'] ?? 'Error deleting customer';
}

// Get customers for display
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$customers = $customerManager->getCustomers($search);
?>

<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Customer Management</h3>
            </div>
            <div class="ms-md-auto py-2 py-md-0">
                <button type="button" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#customerModal">
                    <i class="fas fa-plus"></i> Add New Customer
                </button>
            </div>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-head-row">
                            <div class="card-title">Customer List</div>
                            <div class="card-tools">
                                <form method="GET" class="d-flex">
                                    <input type="text" name="search" class="form-control" placeholder="Search Customer..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                                    <button type="submit" class="btn btn-primary ms-2">Search</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="customerTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($customers)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No customers found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($customers as $customer): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($customer['id'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($customer['full_name'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($customer['email'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($customer['phone'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($customer['address'] ?? ''); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($customer['status'] ?? '') == 'active' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($customer['status'] ?? 'inactive'); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($customer['created_at'] ?? 'now')); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary edit-customer" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#customerModal"
                                                            data-id="<?php echo $customer['id']; ?>"
                                                            data-first_name="<?php echo htmlspecialchars($customer['first_name'] ?? ''); ?>"
                                                            data-last_name="<?php echo htmlspecialchars($customer['last_name'] ?? ''); ?>"
                                                            data-email="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>"
                                                            data-phone="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>"
                                                            data-address="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>"
                                                            data-status="<?php echo $customer['status'] ?? 'active'; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="index.php?p=Customer&action=delete&id=<?php echo $customer['id']; ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Are you sure you want to delete this customer?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
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

<!-- Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="customerForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="customerId">
                    <input type="hidden" name="action" id="customerAction" value="add">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="text-muted">Leave blank to keep current password when editing</small>
                    </div>
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#customerTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 10,
        "language": {
            "search": "Search customers:",
            "lengthMenu": "Show _MENU_ customers per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ customers",
            "infoEmpty": "No customers found",
            "infoFiltered": "(filtered from _MAX_ total customers)"
        }
    });

    // Handle edit button click
    $('.edit-customer').click(function() {
        var id = $(this).data('id');
        var firstName = $(this).data('first_name');
        var lastName = $(this).data('last_name');
        var email = $(this).data('email');
        var phone = $(this).data('phone');
        var address = $(this).data('address');
        var status = $(this).data('status');

        $('#customerId').val(id);
        $('#first_name').val(firstName);
        $('#last_name').val(lastName);
        $('#email').val(email);
        $('#phone').val(phone);
        $('#address').val(address);
        $('#status').val(status);
        $('#customerModalLabel').text('Edit Customer');
        $('#customerAction').val('edit');
        $('#password').removeAttr('required');
    });

    // Reset form when modal is closed
    $('#customerModal').on('hidden.bs.modal', function() {
        $('#customerForm')[0].reset();
        $('#customerId').val('');
        $('#customerModalLabel').text('Add New Customer');
        $('#customerAction').val('add');
        $('#password').attr('required', 'required');
    });
});
</script>


