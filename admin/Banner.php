<?php
require_once 'include/db.php';

class Banner {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function handleFormSubmission() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $id = $_POST['id'] ?? null;
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $button_text = trim($_POST['button_text']);
            $button_link = trim($_POST['button_link']);
            $position = trim($_POST['position']);
            $status = $_POST['status'] ?? 'active';

            // Validate input
            if (empty($title)) {
                return ['success' => false, 'message' => 'Banner title is required'];
            }

            // Handle image upload
            $image_path = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/banners/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid('banner_') . '.' . $file_ext;
                $target_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    $image_path = $target_path;
                } else {
                    return ['success' => false, 'message' => 'Failed to upload image'];
                }
            }

            try {
                if ($_POST['action'] === 'add') {
                    // Add new banner
                    if (!$image_path) {
                        return ['success' => false, 'message' => 'Image is required for new banner'];
                    }
                    
                    $stmt = $this->db->prepare("INSERT INTO banners (title, description, button_text, button_link, image_path, position, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$title, $description, $button_text, $button_link, $image_path, $position, $status]);
                    return ['success' => true, 'message' => 'Banner added successfully'];
                } elseif ($_POST['action'] === 'edit' && isset($_POST['id'])) {
                    // Update existing banner
                    if ($image_path) {
                        // Delete old image if new one is uploaded
                        $old_image = $this->getBannerImage($id);
                        if ($old_image && file_exists($old_image)) {
                            unlink($old_image);
                        }
                        
                        $stmt = $this->db->prepare("UPDATE banners SET title = ?, description = ?, button_text = ?, button_link = ?, image_path = ?, position = ?, status = ? WHERE id = ?");
                        $stmt->execute([$title, $description, $button_text, $button_link, $image_path, $position, $status, $id]);
                    } else {
                        $stmt = $this->db->prepare("UPDATE banners SET title = ?, description = ?, button_text = ?, button_link = ?, position = ?, status = ? WHERE id = ?");
                        $stmt->execute([$title, $description, $button_text, $button_link, $position, $status, $id]);
                    }
                    return ['success' => true, 'message' => 'Banner updated successfully'];
                }
            } catch (PDOException $e) {
                return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
        }
        return null;
    }

    public function handleDelete() {
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            try {
                // Delete image file first
                $image_path = $this->getBannerImage($id);
                if ($image_path && file_exists($image_path)) {
                    unlink($image_path);
                }
                
                $stmt = $this->db->prepare("DELETE FROM banners WHERE id = ?");
                $stmt->execute([$id]);
                return ['success' => true, 'message' => 'Banner deleted successfully'];
            } catch (PDOException $e) {
                return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
        }
        return null;
    }

    public function getBanners($search = '') {
        try {
            $query = "SELECT * FROM banners";
            
            if ($search) {
                $query .= " WHERE title LIKE :search OR description LIKE :search";
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

    public function getBannerById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM banners WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    private function getBannerImage($id) {
        $stmt = $this->db->prepare("SELECT image_path FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['image_path'] : null;
    }
}

// Initialize the Banner class
$bannerManager = new Banner();

// Handle form submissions
$formResult = $bannerManager->handleFormSubmission();
if (isset($formResult['success']) && $formResult['success']) {
    $_SESSION['success_message'] = $formResult['message'];
    echo "<script>window.location.href = 'index.php?p=Banner';</script>";
    exit;
} elseif (isset($formResult['success']) && !$formResult['success']) {
    $_SESSION['error_message'] = $formResult['message'];
}

// Handle delete action
$deleteResult = $bannerManager->handleDelete();
if (isset($deleteResult['success']) && $deleteResult['success']) {
    $_SESSION['success_message'] = $deleteResult['message'];
    echo "<script>window.location.href = 'index.php?p=Banner';</script>";
    exit;
} elseif (isset($deleteResult['success']) && !$deleteResult['success']) {
    $_SESSION['error_message'] = $deleteResult['message'];
}

// Get banners for display
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$banners = $bannerManager->getBanners($search);
?>

<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Banner Management</h3>
            </div>
            <div class="ms-md-auto py-2 py-md-0">
                <button type="button" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#bannerModal">
                    <i class="fas fa-plus"></i> Add New Banner
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
                            <div class="card-title">Banner List</div>
                            <div class="card-tools">
                                <form method="GET" class="d-flex">
                                    <input type="hidden" name="p" value="Banner">
                                    <input type="text" name="search" class="form-control" placeholder="Search Banner..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary ms-2">Search</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="bannerTable">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Button Text</th>
                                        <th>Button Link</th>
                                        <th>Position</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($banners)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No banners found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($banners as $banner): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($banner['image_path']): ?>
                                                        <img src="<?php echo htmlspecialchars($banner['image_path']); ?>" alt="Banner Image" style="max-width: 100px; max-height: 60px;">
                                                    <?php else: ?>
                                                        No Image
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($banner['title']); ?></td>
                                                <td><?php echo htmlspecialchars($banner['description']); ?></td>
                                                <td><?php echo htmlspecialchars($banner['button_text']); ?></td>
                                                <td><?php echo htmlspecialchars($banner['button_link']); ?></td>
                                                <td><?php echo htmlspecialchars($banner['position']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $banner['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($banner['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary edit-banner" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#bannerModal"
                                                            data-id="<?php echo $banner['id']; ?>"
                                                            data-title="<?php echo htmlspecialchars($banner['title']); ?>"
                                                            data-description="<?php echo htmlspecialchars($banner['description']); ?>"
                                                            data-button_text="<?php echo htmlspecialchars($banner['button_text']); ?>"
                                                            data-button_link="<?php echo htmlspecialchars($banner['button_link']); ?>"
                                                            data-position="<?php echo $banner['position']; ?>"
                                                            data-status="<?php echo $banner['status']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="index.php?p=Banner&action=delete&id=<?php echo $banner['id']; ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Are you sure you want to delete this banner?')">
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

<!-- Banner Modal -->
<div class="modal fade" id="bannerModal" tabindex="-1" aria-labelledby="bannerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bannerModalLabel">Add New Banner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="bannerForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="bannerId">
                    <input type="hidden" name="action" id="bannerAction" value="add">
                    
                    <div class="form-group">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="button_text" class="form-label">Button Text</label>
                        <input type="text" class="form-control" id="button_text" name="button_text">
                    </div>
                    
                    <div class="form-group">
                        <label for="button_link" class="form-label">Button Link</label>
                        <input type="text" class="form-control" id="button_link" name="button_link">
                    </div>
                    
                    <div class="form-group">
                        <label for="position" class="form-label">Position</label>
                        <select class="form-control" id="position" name="position" required>
                            <option value="left">Left</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="image" class="form-label">Banner Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="text-muted">Only for new banner or when changing image</small>
                        <div id="currentImageContainer" class="mt-2 d-none">
                            <p>Current Image:</p>
                            <img id="currentImagePreview" src="" style="max-width: 100%; max-height: 150px;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#bannerTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 10,
        "language": {
            "search": "Search banners:",
            "lengthMenu": "Show _MENU_ banners per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ banners",
            "infoEmpty": "No banners found",
            "infoFiltered": "(filtered from _MAX_ total banners)"
        }
    });

    // Handle edit button click
    $('.edit-banner').click(function() {
        var id = $(this).data('id');
        var title = $(this).data('title');
        var description = $(this).data('description');
        var button_text = $(this).data('button_text');
        var button_link = $(this).data('button_link');
        var position = $(this).data('position');
        var status = $(this).data('status');

        $('#bannerId').val(id);
        $('#title').val(title);
        $('#description').val(description);
        $('#button_text').val(button_text);
        $('#button_link').val(button_link);
        $('#position').val(position);
        $('#status').val(status);
        $('#bannerModalLabel').text('Edit Banner');
        $('#bannerAction').val('edit');
        $('#image').removeAttr('required');
        
        // Show current image preview
        var currentImage = $(this).closest('tr').find('img').attr('src');
        if (currentImage) {
            $('#currentImagePreview').attr('src', currentImage);
            $('#currentImageContainer').removeClass('d-none');
        } else {
            $('#currentImageContainer').addClass('d-none');
        }
    });

    // Reset form when modal is closed
    $('#bannerModal').on('hidden.bs.modal', function() {
        $('#bannerForm')[0].reset();
        $('#bannerId').val('');
        $('#bannerModalLabel').text('Add New Banner');
        $('#bannerAction').val('add');
        $('#image').attr('required', 'required');
        $('#currentImageContainer').addClass('d-none');
    });
});
</script>