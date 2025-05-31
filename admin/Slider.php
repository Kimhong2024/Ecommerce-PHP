<?php
// Slider.php
require_once 'include/db.php';

class Slider {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    // Handle form submissions
    public function handleFormSubmission() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $id = $_POST['id'] ?? null;
            $title = trim($_POST['title']);
            $description1 = trim($_POST['description1']);
            $description2 = trim($_POST['description2']);
            $button_text = trim($_POST['button_text']);
            $button_link = trim($_POST['button_link']);
            $status = $_POST['status'] ?? 'active';

            // Validate input
            if (empty($title)) {
                return ['success' => false, 'message' => 'Slider title is required'];
            }

            // Handle image upload
            $image_path = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/sliders/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid('slider_') . '.' . $file_ext;
                $target_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    $image_path = $target_path;
                } else {
                    return ['success' => false, 'message' => 'Failed to upload image'];
                }
            }

            try {
                if ($_POST['action'] === 'add') {
                    // Add new slider
                    if (!$image_path) {
                        return ['success' => false, 'message' => 'Image is required for new slider'];
                    }
                    
                    $stmt = $this->db->prepare("INSERT INTO sliders (title, description1, description2, button_text, button_link, image_path, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$title, $description1, $description2, $button_text, $button_link, $image_path, $status]);
                    return ['success' => true, 'message' => 'Slider added successfully'];
                } elseif ($_POST['action'] === 'edit' && isset($_POST['id'])) {
                    // Update existing slider
                    if ($image_path) {
                        // Delete old image if new one is uploaded
                        $old_image = $this->getSliderImage($id);
                        if ($old_image && file_exists($old_image)) {
                            unlink($old_image);
                        }
                        
                        $stmt = $this->db->prepare("UPDATE sliders SET title = ?, description1 = ?, description2 = ?, button_text = ?, button_link = ?, image_path = ?, status = ? WHERE id = ?");
                        $stmt->execute([$title, $description1, $description2, $button_text, $button_link, $image_path, $status, $id]);
                    } else {
                        $stmt = $this->db->prepare("UPDATE sliders SET title = ?, description1 = ?, description2 = ?, button_text = ?, button_link = ?, status = ? WHERE id = ?");
                        $stmt->execute([$title, $description1, $description2, $button_text, $button_link, $status, $id]);
                    }
                    return ['success' => true, 'message' => 'Slider updated successfully'];
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
                // Delete image file first
                $image_path = $this->getSliderImage($id);
                if ($image_path && file_exists($image_path)) {
                    unlink($image_path);
                }
                
                $stmt = $this->db->prepare("DELETE FROM sliders WHERE id = ?");
                $stmt->execute([$id]);
                return ['success' => true, 'message' => 'Slider deleted successfully'];
            } catch (PDOException $e) {
                return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
        }
        return null;
    }

    // Fetch sliders with search
    public function getSliders($search = '') {
        try {
            $query = "SELECT * FROM sliders";
            
            if ($search) {
                $query .= " WHERE title LIKE :search OR description1 LIKE :search OR description2 LIKE :search";
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

    // Get a single slider by ID
    public function getSliderById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sliders WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    // Get slider image path
    private function getSliderImage($id) {
        $stmt = $this->db->prepare("SELECT image_path FROM sliders WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['image_path'] : null;
    }
}

// Initialize the Slider class
$sliderManager = new Slider();

// Handle form submissions
$formResult = $sliderManager->handleFormSubmission();
if (isset($formResult['success']) && $formResult['success']) {
    $_SESSION['success_message'] = $formResult['message'];
    echo "<script>window.location.href = 'index.php?p=Slider';</script>";
    exit;
} elseif (isset($formResult['success']) && !$formResult['success']) {
    $_SESSION['error_message'] = $formResult['message'];
}

// Handle delete action
$deleteResult = $sliderManager->handleDelete();
if (isset($deleteResult['success']) && $deleteResult['success']) {
    $_SESSION['success_message'] = $deleteResult['message'];
    echo "<script>window.location.href = 'index.php?p=Slider';</script>";
    exit;
} elseif (isset($deleteResult['success']) && !$deleteResult['success']) {
    $_SESSION['error_message'] = $deleteResult['message'];
}

// Get sliders for display
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sliders = $sliderManager->getSliders($search);
?>

<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Slider Management</h3>
            </div>
            <div class="ms-md-auto py-2 py-md-0">
                <button type="button" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#sliderModal">
                    <i class="fas fa-plus"></i> Add New Slider
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
                            <div class="card-title">Slider List</div>
                            <div class="card-tools">
                                <form method="GET" class="d-flex">
                                    <input type="hidden" name="p" value="Slider">
                                    <input type="text" name="search" class="form-control" placeholder="Search Slider..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary ms-2">Search</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="sliderTable">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Description 1</th>
                                        <th>Description 2</th>
                                        <th>Button Text</th>
                                        <th>Button Link</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($sliders)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No sliders found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($sliders as $slider): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($slider['image_path']): ?>
                                                        <img src="<?php echo htmlspecialchars($slider['image_path']); ?>" alt="Slider Image" style="max-width: 100px; max-height: 60px;">
                                                    <?php else: ?>
                                                        No Image
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($slider['title']); ?></td>
                                                <td><?php echo htmlspecialchars($slider['description1']); ?></td>
                                                <td><?php echo htmlspecialchars($slider['description2']); ?></td>
                                                <td><?php echo htmlspecialchars($slider['button_text']); ?></td>
                                                <td><?php echo htmlspecialchars($slider['button_link']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $slider['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($slider['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary edit-slider" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#sliderModal"
                                                            data-id="<?php echo $slider['id']; ?>"
                                                            data-title="<?php echo htmlspecialchars($slider['title']); ?>"
                                                            data-description1="<?php echo htmlspecialchars($slider['description1']); ?>"
                                                            data-description2="<?php echo htmlspecialchars($slider['description2']); ?>"
                                                            data-button_text="<?php echo htmlspecialchars($slider['button_text']); ?>"
                                                            data-button_link="<?php echo htmlspecialchars($slider['button_link']); ?>"
                                                            data-status="<?php echo $slider['status']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="index.php?p=Slider&action=delete&id=<?php echo $slider['id']; ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Are you sure you want to delete this slider?')">
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

<!-- Slider Modal -->
<div class="modal fade" id="sliderModal" tabindex="-1" aria-labelledby="sliderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sliderModalLabel">Add New Slider</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="sliderForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="sliderId">
                    <input type="hidden" name="action" id="sliderAction" value="add">
                    
                    <div class="form-group">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description1" class="form-label">Description Line 1</label>
                        <input type="text" class="form-control" id="description1" name="description1">
                    </div>
                    
                    <div class="form-group">
                        <label for="description2" class="form-label">Description Line 2</label>
                        <input type="text" class="form-control" id="description2" name="description2">
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
                        <label for="image" class="form-label">Slider Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="text-muted">Only for new slider or when changing image</small>
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
                    <button type="submit" class="btn btn-primary">Save Slider</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#sliderTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 10,
        "language": {
            "search": "Search sliders:",
            "lengthMenu": "Show _MENU_ sliders per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ sliders",
            "infoEmpty": "No sliders found",
            "infoFiltered": "(filtered from _MAX_ total sliders)"
        }
    });

    // Handle edit button click
    $('.edit-slider').click(function() {
        var id = $(this).data('id');
        var title = $(this).data('title');
        var description1 = $(this).data('description1');
        var description2 = $(this).data('description2');
        var button_text = $(this).data('button_text');
        var button_link = $(this).data('button_link');
        var status = $(this).data('status');

        $('#sliderId').val(id);
        $('#title').val(title);
        $('#description1').val(description1);
        $('#description2').val(description2);
        $('#button_text').val(button_text);
        $('#button_link').val(button_link);
        $('#status').val(status);
        $('#sliderModalLabel').text('Edit Slider');
        $('#sliderAction').val('edit');
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
    $('#sliderModal').on('hidden.bs.modal', function() {
        $('#sliderForm')[0].reset();
        $('#sliderId').val('');
        $('#sliderModalLabel').text('Add New Slider');
        $('#sliderAction').val('add');
        $('#image').attr('required', 'required');
        $('#currentImageContainer').addClass('d-none');
    });
});
</script>