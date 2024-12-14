<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

$page_title = 'Upload Resource';
$current_page = 'resources';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../view/login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate form data
        if (!isset($_POST['title']) || !isset($_POST['category']) || !isset($_POST['description']) || !isset($_FILES['file'])) {
            throw new Exception('Missing required fields');
        }

        $title = trim($_POST['title']);
        $category = trim($_POST['category']);
        $description = trim($_POST['description']);
        $file = $_FILES['file'];

        // Validate file upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed');
        }

        // Create upload directory if it doesn't exist
        $upload_dir = '../uploads/resources/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $file_name = uniqid() . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            throw new Exception('Failed to move uploaded file');
        }

        // Insert into database with all fields
        $conn = get_db_connection();
        $stmt = $conn->prepare("
            INSERT INTO resources (
                user_id, 
                title, 
                description, 
                file_path, 
                file_type, 
                category, 
                downloads, 
                rating
            ) VALUES (
                :user_id, 
                :title, 
                :description, 
                :file_path, 
                :file_type, 
                :category, 
                0, 
                0.00
            )
        ");

        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'title' => $title,
            'description' => $description,
            'file_path' => $file_name,
            'file_type' => $file_extension,
            'category' => $category
        ]);

        // Log success
        log_message("Resource uploaded successfully: {$title}", 'INFO');

        // Redirect after successful upload
        header('Location: ../view/resources.php?success=1');
        exit();

    } catch (Exception $e) {
        log_message("Error uploading resource: " . $e->getMessage(), 'ERROR');
        $error_message = $e->getMessage();
    }
}

ob_start();
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1>Upload Resource</h1>
    </div>

    <div class="upload-form-container">
        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">Select a category</option>
                    <option value="Textbook">Textbook</option>
                    <option value="Notes">Notes</option>
                    <option value="Assignment">Assignment</option>
                    <option value="Practice Questions">Practice Questions</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="file">File</label>
                <input type="file" id="file" name="file" required accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.jpeg,.png">
                <p class="file-info">Allowed types: PDF, DOC, DOCX, PPT, PPTX, TXT, JPG, JPEG, PNG</p>
                <div id="preview-container" class="preview-container"></div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-outline">Upload Resource</button>
                <a href="../view/resources.php" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.upload-form-container {
    max-width: 600px;
    margin: 0 auto;
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.upload-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 500;
    color: #333;
}

.form-group input[type="text"],
.form-group select,
.form-group textarea {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.file-info {
    font-size: 0.875rem;
    color: #666;
    margin-top: 0.25rem;
}

.preview-container {
    margin-top: 1rem;
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    display: none;
}

.preview-container.active {
    display: block;
}

.file-preview {
    max-width: 100%;
    margin-top: 1rem;
}

.file-preview img {
    max-width: 100%;
    max-height: 300px;
    object-fit: contain;
}

.file-info-preview {
    margin-top: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.file-info-preview p {
    margin: 0.5rem 0;
    color: #666;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.btn-outline {
    background: white;
    color: #2196F3;
    border: 2px solid #2196F3;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-outline:hover {
    background: #E3F2FD;
}
</style>

<script>
document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewContainer = document.getElementById('preview-container');
    
    if (!file) {
        previewContainer.style.display = 'none';
        return;
    }

    previewContainer.innerHTML = '';
    previewContainer.style.display = 'block';

    // Add file information
    const fileInfo = document.createElement('div');
    fileInfo.className = 'file-info-preview';
    fileInfo.innerHTML = `
        <p><strong>File Name:</strong> ${file.name}</p>
        <p><strong>File Type:</strong> ${file.type || 'Unknown'}</p>
        <p><strong>Size:</strong> ${(file.size / (1024 * 1024)).toFixed(2)} MB</p>
    `;
    previewContainer.appendChild(fileInfo);

    // Preview for images
    if (file.type.startsWith('image/')) {
        const img = document.createElement('img');
        img.className = 'file-preview';
        const reader = new FileReader();
        
        reader.onload = function(e) {
            img.src = e.target.result;
        };
        
        reader.readAsDataURL(file);
        previewContainer.appendChild(img);
    }
    // Preview for PDFs
    else if (file.type === 'application/pdf') {
        const preview = document.createElement('div');
        preview.innerHTML = `
            <p><i class='bx bxs-file-pdf' style='color: #ff0000'></i> PDF Document</p>
        `;
        previewContainer.appendChild(preview);
    }
    // Preview for other document types
    else {
        const preview = document.createElement('div');
        preview.innerHTML = `
            <p><i class='bx bxs-file-doc'></i> Document</p>
        `;
        previewContainer.appendChild(preview);
    }
});
</script>

<?php
$content = ob_get_clean();
require_once '../view/layouts/main_layout.php';
?>