<?php
session_start();
require_once 'config.php';

// Handle search and filter
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? 'all';

// Build query
$query = "SELECT * FROM images WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (title ILIKE ? OR description ILIKE ? OR category ILIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

if ($category !== 'all') {
    $query .= " AND category = ?";
    $params[] = $category;
}

$query .= " ORDER BY uploaded_at DESC";

// Prepare and execute
$stmt = $conn->prepare($query);
$stmt->execute($params);
$result = $stmt->fetchAll();

// Get all categories for filter
$categories_result = $conn->query("SELECT DISTINCT category FROM images WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
$categories = $categories_result->fetchAll(PDO::FETCH_COLUMN);

// Get total images count
$total_images = (int) $conn->query("SELECT COUNT(*) AS total FROM images")->fetchColumn();
$total_views = (int) $conn->query("SELECT COALESCE(SUM(views), 0) AS total_views FROM images")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ART HUB</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1>Kalamansig Municipal Tourism ART HUB</h1>
            <p class="subtitle">Municipal Tourism Office</p>
        </header>

        <!-- Upload Section -->
        <div class="upload-section">
            <h2><i class="fas fa-cloud-upload-alt"></i> Upload New Image</h2>
            <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <div class="file-upload">
                        <input type="file" name="image" id="image" accept="image/*" required>
                        <label for="image" class="file-label">
                            <i class="fas fa-file-image"></i>
                            <span>Choose an image</span>
                        </label>
                        <div class="file-info" id="fileInfo">No file selected</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <input type="text" name="title" placeholder="Image Title (optional)" class="form-input">
                </div>
                
                <div class="form-group">
                    <textarea name="description" placeholder="Image Description (optional)" class="form-textarea"></textarea>
                </div>
                
                <div class="form-group">
                    <input type="text" name="category" placeholder="Category (e.g., Nature, Travel, Art)" class="form-input">
                </div>
                
                <button type="submit" class="upload-btn">
                    <i class="fas fa-upload"></i> Upload Image
                </button>
                
                <div class="upload-progress" id="uploadProgress">
                    <div class="progress-bar" id="progressBar"></div>
                </div>
            </form>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-card">
                <i class="fas fa-images"></i>
                <h3><?php echo $total_images; ?></h3>
                <p>Total Images</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-folder"></i>
                <h3><?php echo count($categories); ?></h3>
                <p>Categories</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-eye"></i>
                <h3 id="totalViewsCount"><?php echo $total_views; ?></h3>
                <p>Total Views</p>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="filter-section">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search images..." value="<?php echo htmlspecialchars($search); ?>">
                <button id="searchBtn" class="search-btn">Search</button>
            </div>
            
            <div class="category-filter">
                <select id="categoryFilter" class="category-select">
                    <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>All Categories</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button id="resetFilter" class="reset-btn">
                <i class="fas fa-redo"></i> Reset Filters
            </button>
        </div>

        <!-- Gallery -->
        <div class="gallery-section">
            <h2><i class="fas fa-th"></i> Image Gallery</h2>
            
            <?php if (!empty($result)): ?>
                <div class="gallery-grid" id="galleryGrid">
                    <?php foreach ($result as $row): 
                        $image_path = storagePath('images', $row['filename']);
                        $thumb_path = storagePath('thumbs', $row['filename']);
                        $image_url = publicAssetPath('images', $row['filename']);
                        $thumb_url = publicAssetPath('thumbs', $row['filename']);
                        
                        // Create thumbnail if it doesn't exist
                        if (!file_exists($thumb_path) && file_exists($image_path)) {
                            createThumbnail($image_path, $thumb_path, 300, 200);
                        }
                    ?>
                        <div class="gallery-item" data-category="<?php echo htmlspecialchars($row['category'] ?? 'Uncategorized'); ?>">
                            <div class="image-container">
                                <a href="<?php echo htmlspecialchars($image_url); ?>"
                                   data-lightbox="gallery"
                                   data-title="<?php echo htmlspecialchars($row['title'] ?? 'Untitled'); ?>"
                                   class="gallery-link"
                                   data-image-id="<?php echo $row['id']; ?>">
                                    <img src="<?php echo htmlspecialchars(file_exists($thumb_path) ? $thumb_url : $image_url); ?>" 
                                         alt="<?php echo htmlspecialchars($row['title'] ?? 'Image'); ?>"
                                         loading="lazy">
                                    <div class="image-overlay">
                                        <i class="fas fa-search-plus"></i>
                                    </div>
                                </a>
                                <div class="image-actions">
                                    <button type="button"
                                            class="view-btn"
                                            data-image-id="<?php echo $row['id']; ?>"
                                            onclick="incrementViews(<?php echo $row['id']; ?>, this)">
                                        <i class="fas fa-eye"></i> <?php echo $row['views']; ?>
                                    </button>
                                    <button type="button"
                                            class="delete-btn"
                                            onclick="deleteImage(event, <?php echo $row['id']; ?>, '<?php echo $row['filename']; ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="image-info">
                                <h3 class="image-title"><?php echo htmlspecialchars($row['title'] ?? 'Untitled'); ?></h3>
                                <?php if (!empty($row['description'])): ?>
                                    <p class="image-desc"><?php echo htmlspecialchars($row['description']); ?></p>
                                <?php endif; ?>
                                <div class="image-meta">
                                    <span class="image-category"><?php echo htmlspecialchars($row['category'] ?? 'Uncategorized'); ?></span>
                                    <span class="image-date"><?php echo date('M d, Y', strtotime($row['uploaded_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-image"></i>
                    <h3>No images found</h3>
                    <p>Upload your first image to get started!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p>FERNANDO & ZAMORA &copy; <?php echo date('Y'); ?> | BSIT 4</p>
            <p class="footer-links">
                <a href="#"><i class="fab fa-github"></i> MESSENGER</a>
                <a href="#"><i class="fas fa-question-circle"></i> Help</a>
                <a href="#"><i class="fas fa-envelope"></i> Contact</a>
            </p>
        </footer>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Deletion</h3>
            <p>Are you sure you want to delete this image? This action cannot be undone.</p>
            <div class="modal-actions">
                <button id="confirmDelete" class="btn-danger">Delete</button>
                <button id="cancelDelete" class="btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    <script src="script.js"></script>
</body>
</html>

<?php
// Thumbnail creation function
function createThumbnail($source, $destination, $width, $height) {
    $info = getimagesize($source);
    $mime = $info['mime'];
    
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }
    
    $src_w = imagesx($image);
    $src_h = imagesy($image);

    if ($src_w <= 0 || $src_h <= 0) {
        imagedestroy($image);
        return false;
    }
    
    // Calculate aspect ratio
    $src_ratio = $src_w / $src_h;
    $dst_ratio = $width / $height;
    
    if ($dst_ratio > $src_ratio) {
        $dst_h = $height;
        $dst_w = $height * $src_ratio;
    } else {
        $dst_w = $width;
        $dst_h = $width / $src_ratio;
    }

    $dst_w = max(1, (int) round($dst_w));
    $dst_h = max(1, (int) round($dst_h));
    $dst_x = (int) round(($width - $dst_w) / 2);
    $dst_y = (int) round(($height - $dst_h) / 2);
    
    $thumbnail = imagecreatetruecolor($width, $height);
    
    // Add white background for transparent images
    $white = imagecolorallocate($thumbnail, 255, 255, 255);
    imagefill($thumbnail, 0, 0, $white);
    
    imagecopyresampled($thumbnail, $image, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
    
    // Save thumbnail
    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($thumbnail, $destination, 85);
            break;
        case 'image/png':
            imagepng($thumbnail, $destination, 8);
            break;
        case 'image/gif':
            imagegif($thumbnail, $destination);
            break;
    }
    
    imagedestroy($image);
    imagedestroy($thumbnail);
    
    return true;
}
?>
