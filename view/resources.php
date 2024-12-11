<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Initialize variables
$resources = [];
$categories = [];
$error_message = '';
$success_message = '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';

$page_title = 'Study Resources';
$current_page = 'resources';

ob_start(); // Start output buffering to capture content
$content = ob_get_clean(); // Capture the content

require_once 'layouts/main_layout.php'; // Include the main layout


// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $conn = get_db_connection();
    
    // Fetch resource categories
    $stmt = $conn->prepare("
        SELECT DISTINCT category 
        FROM resources 
        WHERE user_id = :user_id 
        ORDER BY category
    ");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Build query based on filters
    $query = "
        SELECT 
            r.resource_id,
            r.title,
            r.description,
            r.category,
            r.file_path,
            r.file_type,
            r.uploaded_at,
            r.downloads,
            r.rating,
            u.username as uploaded_by
        FROM resources r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.user_id = :user_id
    ";
    
    $params = ['user_id' => $_SESSION['user_id']];
    
    if (!empty($search)) {
        $query .= " AND (r.title LIKE :search OR r.description LIKE :search)";
        $params['search'] = "%$search%";
    }
    
    if (!empty($category_filter)) {
        $query .= " AND r.category = :category";
        $params['category'] = $category_filter;
    }
    
    $query .= " ORDER BY r.uploaded_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    log_message("Error in resources page: " . $e->getMessage(), 'ERROR');
    $error_message = "An error occurred while loading resources.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Resources</title>
    <style>
        .resources-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 20px;
        }

        .search-filter {
            flex: 1;
            display: flex;
            gap: 10px;
        }

        .search-filter input,
        .search-filter select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .upload-btn {
            background: #4CAF50;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .resource-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .resource-card h3 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .resource-meta {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 10px;
        }

        .resource-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .resource-actions button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .download-btn {
            background: #2196F3;
            color: white;
        }

        .delete-btn {
            background: #f44336;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 500px;
            margin: 50px auto;
        }

        .error-message {
            color: #f44336;
            padding: 10px;
            margin-bottom: 20px;
            background: #ffebee;
            border-radius: 4px;
        }

        .success-message {
            color: #4CAF50;
            padding: 10px;
            margin-bottom: 20px;
            background: #e8f5e9;
            border-radius: 4px;
        }

        .split-container {
            display: flex;
            gap: 20px;
            height: calc(100vh - 100px); /* Adjust based on your header height */
        }

        .resources-list {
            flex: 1;
            overflow-y: auto;
            padding-right: 20px;
        }

        .preview-pane {
            flex: 1;
            position: sticky;
            top: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 140px);
        }

        .preview-content {
            flex: 1;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-content iframe,
        .preview-content img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .preview-header {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .preview-placeholder {
            color: #666;
            text-align: center;
            font-size: 1.1em;
        }

        .preview-btn {
            background: #2196F3;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }

        /* Adjust the existing resources grid for the split view */
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
    </style>
</head>
<body>
    <div class="resources-container">
        <h1>Study Resources</h1>
        
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <div class="controls">
            <div class="search-filter">
                <input type="text" id="search" placeholder="Search resources..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <select id="category-filter">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>"
                                <?php echo $category_filter === $category ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button onclick="applyFilters()">Apply Filters</button>
            </div>
            <button class="upload-btn" onclick="showUploadModal()">Upload Resource</button>
        </div>

        <div class="split-container">
            <div class="resources-list">
                <div class="resources-grid">
                    <?php if (empty($resources)): ?>
                        <p>No resources found. Start by uploading study materials!</p>
                    <?php else: ?>
                        <?php foreach ($resources as $resource): ?>
                            <div class="resource-card">
                                <h3><?php echo htmlspecialchars($resource['title']); ?></h3>
                                <div class="resource-meta">
                                    <p>Category: <?php echo htmlspecialchars($resource['category']); ?></p>
                                    <p>Uploaded: <?php echo date('M d, Y', strtotime($resource['uploaded_at'])); ?></p>
                                    <p>Downloads: <?php echo htmlspecialchars($resource['downloads']); ?></p>
                                    <p>Rating: <?php echo number_format($resource['rating'], 1); ?>/5.0</p>
                                </div>
                                <p><?php echo htmlspecialchars($resource['description']); ?></p>
                                <div class="resource-actions">
                                    <button class="preview-btn" 
                                            onclick="previewResource('<?php echo htmlspecialchars($resource['file_path']); ?>', 
                                                                   '<?php echo htmlspecialchars($resource['file_type']); ?>', 
                                                                   '<?php echo htmlspecialchars($resource['title']); ?>')">
                                        Preview
                                    </button>
                                    <button class="download-btn" 
                                            onclick="downloadResource(<?php echo $resource['resource_id']; ?>)">
                                        Download
                                    </button>
                                    <button class="delete-btn" 
                                            onclick="deleteResource(<?php echo $resource['resource_id']; ?>)">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="preview-pane">
                <div class="preview-header">
                    <h2 id="preview-title">Resource Preview</h2>
                </div>
                <div class="preview-content" id="preview-content">
                    <div class="preview-placeholder">
                        Select a resource to preview
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <h2>Upload Resource</h2>
            <form id="uploadForm" enctype="multipart/form-data">
                <div>
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div>
                    <label for="category">Category:</label>
                    <input type="text" id="category" name="category" required>
                </div>
                <div>
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <div>
                    <label for="file">File:</label>
                    <input type="file" id="file" name="file" required>
                </div>
                <button type="submit">Upload</button>
                <button type="button" onclick="hideUploadModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function applyFilters() {
            const search = document.getElementById('search').value;
            const category = document.getElementById('category-filter').value;
            window.location.href = `resources.php?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}`;
        }

        function showUploadModal() {
            document.getElementById('uploadModal').style.display = 'block';
        }

        function hideUploadModal() {
            document.getElementById('uploadModal').style.display = 'none';
        }

        function downloadResource(resourceId) {
            window.location.href = `download_resource.php?id=${resourceId}`;
        }

        function deleteResource(resourceId) {
            if (confirm('Are you sure you want to delete this resource?')) {
                fetch('../actions/delete_resource.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ resource_id: resourceId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting resource: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting resource');
                });
            }
        }

        // Handle form submission
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../actions/upload_resource.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error uploading resource: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error uploading resource');
            });
        });

        function previewResource(filePath, fileType, title) {
            const previewContent = document.getElementById('preview-content');
            const previewTitle = document.getElementById('preview-title');
            previewTitle.textContent = title;

            // Clear previous content
            previewContent.innerHTML = '';

            // Determine file type and create appropriate preview
            const fileExtension = fileType.toLowerCase();
            const previewUrl = `../uploads/resources/${filePath}`;

            if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                // Image preview
                const img = document.createElement('img');
                img.src = previewUrl;
                img.alt = title;
                previewContent.appendChild(img);
            } else if (fileExtension === 'pdf') {
                // PDF preview
                const iframe = document.createElement('iframe');
                iframe.src = previewUrl;
                iframe.style.width = '100%';
                iframe.style.height = '100%';
                iframe.style.border = 'none';
                previewContent.appendChild(iframe);
            } else {
                // Unsupported file type
                previewContent.innerHTML = `
                    <div class="preview-placeholder">
                        <p>Preview not available for this file type (${fileExtension})</p>
                        <p>Please download to view the content</p>
                    </div>
                `;
            }
        }
    </script>
</body>
</html>
