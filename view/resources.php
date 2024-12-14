<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

$page_title = 'Resources';
$current_page = 'resources';
$error_message = '';
$resources = [];

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM resources WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    log_message("Error in resources page: " . $e->getMessage(), 'ERROR');
    $error_message = "An error occurred while loading resources.";
}

ob_start();
?>

<div class="split-container">
    <div class="resources-list">
        <div class="resources-header">
            <h1>Study Resources</h1>
            <a href="../actions/upload_resource.php" class="add-resource-btn">
                <i class='bx bx-plus'></i> Add Resource
            </a>
        </div>

        <div class="resources-grid">
            <?php foreach ($resources as $resource): ?>
                <div class="resource-card">
                    <h3><?php echo htmlspecialchars($resource['title']); ?></h3>
                    <span class="resource-type"><?php echo htmlspecialchars($resource['category']); ?></span>
                    <p><?php echo htmlspecialchars($resource['description']); ?></p>
                    <div class="resource-meta">
                        <span class="file-type">
                            <i class='bx bx-file'></i> <?php echo htmlspecialchars($resource['file_type']); ?>
                        </span>
                    </div>
                    <div class="resource-actions">
                        <button class="preview-btn" 
                                onclick="previewResource('<?php echo htmlspecialchars($resource['file_path']); ?>', 
                                                       '<?php echo htmlspecialchars($resource['file_type']); ?>', 
                                                       '<?php echo htmlspecialchars($resource['title']); ?>')">
                            <i class='bx bx-show'></i> Preview
                        </button>
                        <a href="download_resource.php?id=<?php echo $resource['resource_id']; ?>" class="download-btn">
                            <i class='bx bx-download'></i> Download
                        </a>
                    </div>
                    <div class="resource-stats">
                        <span><i class='bx bx-download'></i> <?php echo $resource['downloads']; ?></span>
                        <span><i class='bx bx-star'></i> <?php echo number_format($resource['rating'], 1); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
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

<style>
.split-container {
    display: flex;
    gap: 20px;
    height: calc(100vh - 100px);
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

.resources-container {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.resources-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.resources-header h1 {
    color: #333;
    font-size: 2rem;
}

.add-resource-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #2196F3;
    color: white;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-size: 1rem;
    transition: background 0.3s ease;
}

.add-resource-btn:hover {
    background: #1976D2;
}

.resources-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.resource-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.resource-title {
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: start;
}

.resource-title h3 {
    margin: 0;
    color: #333;
    font-size: 1.25rem;
}

.resource-type {
    background: #E3F2FD;
    color: #1976D2;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
}

.resource-description {
    padding: 1.5rem;
}

.resource-description p {
    color: #666;
    margin-bottom: 1rem;
}

.resource-meta {
    padding: 1.5rem;
    background: #f8f9fa;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.file-type {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    font-size: 0.875rem;
}

.resource-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
}

.preview-btn, .download-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: opacity 0.3s ease;
}

.preview-btn {
    background: #E3F2FD;
    color: #2196F3;
}

.download-btn {
    background: #2196F3;
    color: white;
}

.preview-btn:hover, .download-btn:hover {
    opacity: 0.9;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.empty-state p {
    color: #666;
    margin: 0.5rem 0;
}

.empty-state p:first-child {
    font-size: 1.25rem;
    color: #333;
}

@media (max-width: 768px) {
    .resources-container {
        padding: 1rem;
    }

    .resources-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .add-resource-btn {
        width: 100%;
        justify-content: center;
    }
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.modal-content {
    position: relative;
    background: white;
    margin: 2rem auto;
    padding: 2rem;
    width: 90%;
    max-width: 800px;
    border-radius: 12px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #666;
}

.preview-btn {
    background: #E3F2FD;
    color: #2196F3;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.preview-btn:hover {
    background: #BBDEFB;
}
</style>

<script>
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

<?php
$content = ob_get_clean();
require_once 'layouts/main_layout.php';
?>
