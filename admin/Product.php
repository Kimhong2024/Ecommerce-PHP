<?php
// Product.php
require_once 'include/db.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

   

    // Handle form submissions
    public function handleFormSubmission() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $stock = intval($_POST['stock']);
            $category_id = intval($_POST['category_id']);
            
            // Validate input
            if (empty($name)) {
                return ['success' => false, 'message' => 'Product name is required'];
            }
            
            if ($price <= 0) {
                return ['success' => false, 'message' => 'Price must be greater than zero'];
            }
            
            if ($stock < 0) {
                return ['success' => false, 'message' => 'Stock cannot be negative'];
            }
            
            // Handle image upload
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploadDir = 'uploads/products/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($fileExtension, $allowedExtensions)) {
                    return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.'];
                }
                
                $fileName = uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image = $targetPath;
                } else {
                    return ['success' => false, 'message' => 'Failed to upload image'];
                }
            }
            
            if ($_POST['action'] === 'add') {
                try {
                    $stmt = $this->db->prepare("INSERT INTO products (name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $price, $stock, $category_id, $image]);
                    return ['success' => true, 'message' => 'Product added successfully'];
                } catch (PDOException $e) {
                    return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
                }
            } elseif ($_POST['action'] === 'edit' && isset($_POST['id'])) {
                $id = (int)$_POST['id'];
                try {
                    if ($image) {
                        // Get the old image to delete it
                        $oldImage = $this->getProductImage($id);
                        $stmt = $this->db->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ? WHERE id = ?");
                        $stmt->execute([$name, $description, $price, $stock, $category_id, $image, $id]);
                        
                        // Delete old image if it exists
                        if ($oldImage && file_exists($oldImage)) {
                            unlink($oldImage);
                        }
                    } else {
                        $stmt = $this->db->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ? WHERE id = ?");
                        $stmt->execute([$name, $description, $price, $stock, $category_id, $id]);
                    }
                    return ['success' => true, 'message' => 'Product updated successfully'];
                } catch (PDOException $e) {
                    return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
                }
            }
        }
        return null;
    }

    // Get product image path
    private function getProductImage($id) {
        $stmt = $this->db->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['image'] : null;
    }

    // Handle delete action
    public function handleDelete() {
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            try {
                // Get the image to delete it
                $image = $this->getProductImage($id);
                
                $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$id]);
                
                // Delete the image file if it exists
                if ($image && file_exists($image)) {
                    unlink($image);
                }
                
                // Check if this was the last product
                $stmt = $this->db->query("SELECT COUNT(*) FROM products");
                $count = $stmt->fetchColumn();
                
                // If no products left, reset the auto-increment counter
                if ($count == 0) {
                    $this->db->exec("ALTER TABLE products AUTO_INCREMENT = 1");
                }
                
                return ['success' => true, 'message' => 'Product deleted successfully'];
            } catch (PDOException $e) {
                return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
        }
        return null;
    }

    // Fetch products with search
    public function getProducts($search = '') {
        try {
            $query = "SELECT p.*, c.name as category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id";
            
            if ($search) {
                $query .= " WHERE p.name LIKE :search OR p.description LIKE :search OR c.name LIKE :search";
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
    
    // Get a single product by ID
    public function getProductById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    // Get all categories for dropdown
    public function getCategories() {
        try {
            $stmt = $this->db->query("SELECT id, name FROM categories ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}

// Initialize the Product class
$productManager = new Product();

// Handle form submissions and get result
$formResult = $productManager->handleFormSubmission();
if (isset($formResult['success']) && $formResult['success']) {
    $_SESSION['success_message'] = $formResult['message'];
    // Use a more reliable redirect method
    echo "<script>window.location.href = 'index.php?p=Product';</script>";
    exit;
} elseif (isset($formResult['success']) && !$formResult['success']) {
    $_SESSION['error_message'] = $formResult['message'];
}

// Handle delete action and get result
$deleteResult = $productManager->handleDelete();
if (isset($deleteResult['success']) && $deleteResult['success']) {
    $_SESSION['success_message'] = $deleteResult['message'];
    // Use a more reliable redirect method
    echo "<script>window.location.href = 'index.php?p=Product';</script>";
    exit;
} elseif (isset($deleteResult['success']) && !$deleteResult['success']) {
    $_SESSION['error_message'] = $deleteResult['message'];
}

// Get products for display
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$products = $productManager->getProducts($search);
$categories = $productManager->getCategories();
?>

<div class="container">
  <div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
      <div>
        <h3 class="fw-bold mb-3">Product Management</h3>
      </div>
      <div class="ms-md-auto py-2 py-md-0">
                <button id="addProductBtn" class="btn btn-primary btn-round">
          <i class="fas fa-plus"></i> Add Product
        </button>
      </div>
    </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

    <!-- Product Table -->
    <div class="row">
      <div class="col-md-12">
        <div class="card card-round">
          <div class="card-header">
            <div class="card-head-row">
              <div class="card-title">Product List</div>
              <div class="card-tools">
                                <form method="GET" action="index.php" class="d-flex">
                                    <input type="hidden" name="p" value="Product">
                                    <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary ms-2">Search</button>
                                </form>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover" id="productTable">
                <thead>
                  <tr>
                    <th>ID</th>
                                        <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                                    <?php if (empty($products)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No products found</td>
                                        </tr>
                                    <?php else: ?>
                  <?php foreach ($products as $product): ?>
                    <tr>
                                                <td><?php echo htmlspecialchars($product['id']); ?></td>
                                                <td>
                                                    <?php if (!empty($product['image'])): ?>
                                                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" style="max-width: 50px;">
                                                    <?php else: ?>
                                                        <i class="fas fa-image text-muted"></i>
                                                    <?php endif; ?>
                      </td>
                                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                      <td>$<?php echo number_format($product['price'], 2); ?></td>
                                                <td><?php echo htmlspecialchars($product['stock']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-product" 
                                                            data-id="<?php echo $product['id']; ?>" 
                                                            data-name="<?php echo htmlspecialchars($product['name']); ?>" 
                                                            data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                                            data-price="<?php echo $product['price']; ?>"
                                                            data-stock="<?php echo $product['stock']; ?>"
                                                            data-category="<?php echo $product['category_id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="index.php?p=Product&action=delete&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">
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

<!-- Add/Edit Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productModalLabel">Add Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="productForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="productId" name="id">
                    <input type="hidden" id="action" name="action" value="add">
                    
                    <div class="row">
                        <div class="col-md-6">
          <div class="form-group">
            <label for="productName">Product Name</label>
                                <input type="text" class="form-control" id="productName" name="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="productDescription">Description</label>
                                <textarea class="form-control" id="productDescription" name="description" rows="3"></textarea>
          </div>
                            
          <div class="form-group">
            <label for="productCategory">Category</label>
                                <select class="form-control" id="productCategory" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
            </select>
          </div>
                        </div>
                        
                        <div class="col-md-6">
          <div class="form-group">
                                <label for="productPrice">Price ($)</label>
                                <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0.01" required>
          </div>
                            
          <div class="form-group">
            <label for="productStock">Stock</label>
                                <input type="number" class="form-control" id="productStock" name="stock" min="0" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="productImage">Product Image</label>
                                <div class="image-upload-container">
                                    <div class="image-preview" id="imagePreview" style="width: 200px; height: 200px; border: 2px dashed #ddd; border-radius: 8px; overflow: hidden; position: relative; margin-bottom: 10px;">
                                        <img src="" alt="Image Preview" class="image-preview__image" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                        <span class="image-preview__default-text" style="display: flex; align-items: center; justify-content: center; height: 100%; color: #999;">
                                            <i class="fas fa-image fa-3x"></i>
                                        </span>
                                    </div>
                                  
                                    <input type="file" class="form-control" id="productImage" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <div id="imageError" class="text-danger mt-2" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveProductBtn">Save Product</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
        // Add Product Button Click
        document.getElementById('addProductBtn').addEventListener('click', function() {
            document.getElementById('productModalLabel').textContent = 'Add Product';
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('action').value = 'add';
            document.querySelector('.image-preview__image').style.display = 'none';
            document.querySelector('.image-preview__default-text').style.display = 'flex';
            
            // Show the modal using Bootstrap 5 syntax
            var productModal = new bootstrap.Modal(document.getElementById('productModal'));
            productModal.show();
        });

        // Edit Product Button Click
        document.querySelectorAll('.edit-product').forEach(function(button) {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const description = this.getAttribute('data-description');
                const price = this.getAttribute('data-price');
                const stock = this.getAttribute('data-stock');
                const category = this.getAttribute('data-category');
                
                document.getElementById('productModalLabel').textContent = 'Edit Product';
                document.getElementById('productId').value = id;
                document.getElementById('productName').value = name;
                document.getElementById('productDescription').value = description;
                document.getElementById('productPrice').value = price;
                document.getElementById('productStock').value = stock;
                document.getElementById('productCategory').value = category;
                document.getElementById('action').value = 'edit';
                
                // Show the modal using Bootstrap 5 syntax
                var productModal = new bootstrap.Modal(document.getElementById('productModal'));
                productModal.show();
            });
        });

        // Save Product Button Click
        document.getElementById('saveProductBtn').addEventListener('click', function() {
            // Prevent double submission
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            
            // Submit the form
            document.getElementById('productForm').submit();
        });

        // Image Preview with validation
        document.getElementById('productImage').addEventListener('change', function() {
            const file = this.files[0];
            const errorDiv = document.getElementById('imageError');
            const maxSize = 2 * 1024 * 1024; // 2MB in bytes
            
            // Reset error message
            errorDiv.style.display = 'none';
            errorDiv.textContent = '';
            
            if (file) {
                // Validate file size
                if (file.size > maxSize) {
                    errorDiv.textContent = 'File size exceeds 2MB limit';
                    errorDiv.style.display = 'block';
                    this.value = ''; // Clear the file input
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    errorDiv.textContent = 'Only JPG, PNG, GIF, and WebP files are allowed';
                    errorDiv.style.display = 'block';
                    this.value = ''; // Clear the file input
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.image-preview__image');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    document.querySelector('.image-preview__default-text').style.display = 'none';
                }
                reader.readAsDataURL(file);
            } else {
                // Reset preview if no file selected
                const preview = document.querySelector('.image-preview__image');
                preview.style.display = 'none';
                document.querySelector('.image-preview__default-text').style.display = 'flex';
            }
        });
});
</script>
</body>
</html>


