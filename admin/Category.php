<?php
require_once 'include/db.php';

class Category {
  private $db;

  public function __construct() {
      $this->db = (new Database())->connect();
  }



  // Handle form submissions
  public function handleFormSubmission() {
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
          $name = trim($_POST['name']);
          $description = trim($_POST['description']);
          
          // Validate input
          if (empty($name)) {
              return ['success' => false, 'message' => 'Category name is required'];
          }
          
          // Handle image upload
          $image = '';
          if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
              $uploadDir = 'uploads/categories/';
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
                  $stmt = $this->db->prepare("INSERT INTO categories (name, description, image) VALUES (?, ?, ?)");
                  $stmt->execute([$name, $description, $image]);
                  return ['success' => true, 'message' => 'Category added successfully'];
              } catch (PDOException $e) {
                  return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
              }
          } elseif ($_POST['action'] === 'edit' && isset($_POST['id'])) {
              $id = (int)$_POST['id'];
              try {
                  if ($image) {
                      // Get the old image to delete it
                      $oldImage = $this->getCategoryImage($id);
                      $stmt = $this->db->prepare("UPDATE categories SET name = ?, description = ?, image = ? WHERE id = ?");
                      $stmt->execute([$name, $description, $image, $id]);
                      
                      // Delete old image if it exists
                      if ($oldImage && file_exists($oldImage)) {
                          unlink($oldImage);
                      }
                  } else {
                      $stmt = $this->db->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
                      $stmt->execute([$name, $description, $id]);
                  }
                  return ['success' => true, 'message' => 'Category updated successfully'];
              } catch (PDOException $e) {
                  return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
              }
          }
      }
     
  }

  // Get category image path
  private function getCategoryImage($id) {
      $stmt = $this->db->prepare("SELECT image FROM categories WHERE id = ?");
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
              $image = $this->getCategoryImage($id);
              
              $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
              $stmt->execute([$id]);
              
              // Delete the image file if it exists
              if ($image && file_exists($image)) {
                  unlink($image);
              }
              
              // Check if this was the last category
              $stmt = $this->db->query("SELECT COUNT(*) FROM categories");
              $count = $stmt->fetchColumn();
              
              // If no categories left, reset the auto-increment counter
              if ($count == 0) {
                  $this->db->exec("ALTER TABLE categories AUTO_INCREMENT = 1");
              }
              
              return ['success' => true, 'message' => 'Category deleted successfully'];
          } catch (PDOException $e) {
              return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
          }
      }

  }

  // Fetch categories with search
  public function getCategories($search = '') {
      try {
          $query = "SELECT * FROM categories";
          if ($search) {
              $query .= " WHERE name LIKE :search OR description LIKE :search";
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
  
  // Get a single category by ID
  public function getCategoryById($id) {
      try {
          $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
          $stmt->execute([$id]);
          return $stmt->fetch(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
          return null;
      }
  }
}

// Initialize the Category class
$categoryManager = new Category();


// Handle form submissions and get result
$formResult = $categoryManager->handleFormSubmission();
if (isset($formResult['success']) && $formResult['success']) {
    $_SESSION['success_message'] = $formResult['message'];
    // Use a more reliable redirect method
    echo "<script>window.location.href = 'index.php?p=Category';</script>";
    exit;
} elseif (isset($formResult['success']) && !$formResult['success']) {
    $_SESSION['error_message'] = $formResult['message'];
}

// Handle delete action and get result
$deleteResult = $categoryManager->handleDelete();
if (isset($deleteResult['success']) && $deleteResult['success']) {
    $_SESSION['success_message'] = $deleteResult['message'];
    // Use a more reliable redirect method
    echo "<script>window.location.href = 'index.php?p=Category';</script>";
    exit;
} elseif (isset($deleteResult['success']) && !$deleteResult['success']) {
    $_SESSION['error_message'] = $deleteResult['message'];
}

// Get categories for display
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categories = $categoryManager->getCategories($search);
?>

<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Category Management</h3>
            </div>
            <div class="ms-md-auto py-2 py-md-0">
                <button id="addCategoryBtn" class="btn btn-primary btn-round">
                    <i class="fas fa-plus"></i> Add Category
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

        <!-- Category Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-head-row">
                            <div class="card-title">Category List</div>
                            <div class="card-tools">
                                <form method="GET" action="index.php" class="d-flex">
                                    <input type="hidden" name="p" value="Category">
                                    <input type="text" name="search" class="form-control" placeholder="Search categories..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary ms-2">Search</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="categoryTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categories)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No categories found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($category['id']); ?></td>
                                                <td>
                                                    <?php if (!empty($category['image'])): ?>
                                                        <img src="<?php echo htmlspecialchars($category['image']); ?>" alt="Category Image" style="max-width: 50px;">
                                                    <?php else: ?>
                                                        <i class="fas fa-image text-muted"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                <td><?php echo htmlspecialchars($category['description']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-category" data-id="<?php echo $category['id']; ?>" data-name="<?php echo htmlspecialchars($category['name']); ?>" data-description="<?php echo htmlspecialchars($category['description']); ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="index.php?p=Category&action=delete&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?')">
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

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="categoryId" name="id">
                    <input type="hidden" id="action" name="action" value="add">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="categoryName">Category Name</label>
                                <input type="text" class="form-control" id="categoryName" name="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="categoryDescription">Description</label>
                                <textarea class="form-control" id="categoryDescription" name="description" rows="5" required></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="categoryImage">Category Image</label>
                                <div class="image-upload-container">
                                    <div class="image-preview" id="imagePreview" style="width: 200px; height: 200px; border: 2px dashed #ddd; border-radius: 8px; overflow: hidden; position: relative; margin-bottom: 10px;">
                                        <img src="" alt="Image Preview" class="image-preview__image" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                        <span class="image-preview__default-text" style="display: flex; align-items: center; justify-content: center; height: 100%; color: #999;">
                                            <i class="fas fa-image fa-3x"></i>
                                        </span>
                                    </div>
                                  
                                    <input type="file" class="form-control" id="categoryImage" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <div id="imageError" class="text-danger mt-2" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveCategoryBtn">Save Category</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add Category Button Click
        document.getElementById('addCategoryBtn').addEventListener('click', function() {
            document.getElementById('categoryModalLabel').textContent = 'Add Category';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryId').value = '';
            document.getElementById('action').value = 'add';
            document.querySelector('.image-preview__image').style.display = 'none';
            document.querySelector('.image-preview__default-text').style.display = 'flex';
            
            // Show the modal using Bootstrap 5 syntax
            var categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
            categoryModal.show();
        });

        // Edit Category Button Click
        document.querySelectorAll('.edit-category').forEach(function(button) {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const description = this.getAttribute('data-description');
                
                document.getElementById('categoryModalLabel').textContent = 'Edit Category';
                document.getElementById('categoryId').value = id;
                document.getElementById('categoryName').value = name;
                document.getElementById('categoryDescription').value = description;
                document.getElementById('action').value = 'edit';
                
                // Show the modal using Bootstrap 5 syntax
                var categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
                categoryModal.show();
            });
        });

        // Save Category Button Click
        document.getElementById('saveCategoryBtn').addEventListener('click', function() {
            // Prevent double submission
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            
            // Submit the form
            document.getElementById('categoryForm').submit();
        });

        // Image Preview with validation
        document.getElementById('categoryImage').addEventListener('change', function() {
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

