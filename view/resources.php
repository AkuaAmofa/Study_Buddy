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
                <div class="resource-card" data-resource-id="<?php echo $resource['resource_id']; ?>">
                    <div class="resource-title">
                        <h3><?php echo htmlspecialchars($resource['title']); ?></h3>
                        <span class="resource-type"><?php echo htmlspecialchars($resource['category']); ?></span>
                    </div>
                    <div class="resource-description">
                        <p><?php echo htmlspecialchars($resource['description']); ?></p>
                    </div>
                    <div class="resource-meta">
                        <span class="file-type">
                            <i class='bx bx-file'></i> <?php echo htmlspecialchars($resource['file_type']); ?>
                        </span>
                    </div>
                    <div class="resource-actions">
                        <button class="preview-btn" 
                                onclick="previewResource('<?php echo $resource['resource_id']; ?>', 
                                                      '<?php echo htmlspecialchars($resource['file_type']); ?>', 
                                                      '<?php echo htmlspecialchars($resource['title']); ?>')">
                            <i class='bx bx-show'></i> Preview
                        </button>
                        <button class="delete-btn" 
                                onclick="deleteResource('<?php echo $resource['resource_id']; ?>', 
                                                     '<?php echo htmlspecialchars(addslashes($resource['title'])); ?>')">
                            <i class='bx bx-trash'></i> Delete
                        </button>
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
    overflow: hidden;
}
.preview-content {
    flex: 1;
    overflow: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 4px;
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
    justify-content: flex-start;
    align-items: center;
    gap: 1rem;
    margin-top: 1rem;
}
.preview-btn, .delete-btn {
    flex: 0 0 auto;
}
.preview-btn {
    background: #E3F2FD;
    color: #2196F3;
}
.delete-btn {
    background: #ff4444;
    color: white;
}
.preview-btn:hover, .delete-btn:hover {
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
.preview-image {
    max-width: 100%;
    max-height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.preview-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.preview-pdf {
    width: 100%;
    height: 100%;
}
.preview-pdf iframe {
    border: none;
    background: white;
}
.preview-placeholder {
    text-align: center;
    color: #666;
    padding: 20px;
}
.preview-placeholder .download-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 10px;
    padding: 8px 16px;
    background: #2196F3;
    color: white;
    border-radius: 4px;
    text-decoration: none;
}
.preview-placeholder .download-btn:hover {
    background: #1976D2;
}
.preview-content {
    width: 100%;
    height: 100%;
    overflow: auto;
    padding: 20px;
}

.preview-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.preview-pdf,
.preview-doc,
.preview-excel,
.preview-powerpoint {
    width: 100%;
    height: 100%;
}

.preview-text {
    width: 100%;
    height: 100%;
    overflow: auto;
    background: #f5f5f5;
    padding: 20px;
}

.preview-text pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}

.preview-video,
.preview-audio {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.preview-placeholder {
    text-align: center;
    padding: 20px;
}

.preview-placeholder .download-btn {
    display: inline-block;
    margin-top: 15px;
    padding: 10px 20px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

.preview-placeholder .download-btn:hover {
    background: #0056b3;
}

.preview-pdf,
.preview-office {
    width: 100%;
    height: 100%;
    min-height: 500px;
}

.preview-pdf object,
.preview-office iframe {
    width: 100%;
    height: 100%;
    border: none;
}

.preview-image {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    padding: 20px;
}

.preview-image img {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
}

.preview-text {
    padding: 20px;
    background: #fff;
    height: 100%;
    overflow: auto;
    border: 1px solid #eee;
    border-radius: 4px;
}

.preview-text pre {
    white-space: pre-wrap;
    word-wrap: break-word;
    margin: 0;
    font-family: monospace;
}

.delete-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #ff4444;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: background 0.3s ease;
}

.delete-btn:hover {
    background: #cc0000;
}

.resource-actions {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 1rem;
    margin-top: 1rem;
}
</style>
<script>
function previewResource(resourceId, fileType, title) {
    const previewContent = document.getElementById('preview-content');
    const previewTitle = document.getElementById('preview-title');
    previewTitle.textContent = title;

    previewContent.innerHTML = '<div class="preview-placeholder">Loading preview...</div>';

    // Debug log
    console.log('Preview request:', { resourceId, fileType, title });

    fetch(`../actions/get_resource.php?id=${resourceId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Error loading preview');
            }

            const resource = data.resource;
            const fileExtension = fileType.toLowerCase();
            const previewUrl = `../uploads/resources/${resource.file_path}`;

            console.log('File type:', fileExtension); // Debug log

            // Handle different file types
            if (['jpg', 'jpeg', 'png'].includes(fileExtension)) {
                // Images
                previewContent.innerHTML = `
                    <div class="preview-image">
                        <img src="${previewUrl}" alt="${title}" 
                             onerror="this.onerror=null; this.parentElement.innerHTML='Error loading image'"/>
                    </div>`;
            } 
            else if (fileExtension === 'pdf') {
                // PDFs
                previewContent.innerHTML = `
                    <div class="preview-pdf">
                        <object data="${previewUrl}" type="application/pdf" width="100%" height="100%">
                            <p>Unable to display PDF. <a href="${previewUrl}" target="_blank">Download</a> instead.</p>
                        </object>
                    </div>`;
            }
            else if (['doc', 'docx', 'ppt', 'pptx'].includes(fileExtension)) {
                // Microsoft Office Documents
                const fullUrl = window.location.origin + previewUrl;
                const encodedUrl = encodeURIComponent(fullUrl);
                previewContent.innerHTML = `
                    <div class="preview-office">
                        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src=${encodedUrl}" 
                                width="100%" height="100%" frameborder="0">
                        </iframe>
                    </div>`;
            }
            else if (fileExtension === 'txt') {
                // Text files
                fetch(previewUrl)
                    .then(response => response.text())
                    .then(text => {
                        previewContent.innerHTML = `
                            <div class="preview-text">
                                <pre>${text}</pre>
                            </div>`;
                    })
                    .catch(() => {
                        throw new Error('Failed to load text content');
                    });
            }
            else {
                // Unsupported files
                previewContent.innerHTML = `
                    <div class="preview-placeholder">
                        <p>Preview not available for ${fileExtension.toUpperCase()} files</p>
                        <a href="../view/download_resource.php?id=${resourceId}" class="download-btn">
                            <i class='bx bx-download'></i> Download to view
                        </a>
                    </div>`;
            }
        })
        .catch(error => {
            console.error('Preview error:', error); // Debug log
            previewContent.innerHTML = `
                <div class="preview-placeholder">
                    <p>Error loading preview</p>
                    <p>${error.message}</p>
                    <a href="../view/download_resource.php?id=${resourceId}" class="download-btn">
                        <i class='bx bx-download'></i> Download File
                    </a>
                </div>`;
        });
}

function deleteResource(resourceId, title) {
    if (confirm(`Are you sure you want to delete "${title}"?`)) {
        fetch('../actions/delete_resource.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                resource_id: resourceId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Find and remove the resource card
                const resourceCard = document.querySelector(`.resource-card[data-resource-id="${resourceId}"]`);
                if (resourceCard) {
                    resourceCard.remove();
                }
                
                // Clear preview if the deleted resource was being previewed
                const previewContent = document.getElementById('preview-content');
                const previewTitle = document.getElementById('preview-title');
                if (previewTitle.textContent === title) {
                    previewContent.innerHTML = '<div class="preview-placeholder">Select a resource to preview</div>';
                    previewTitle.textContent = 'Resource Preview';
                }
                
                // Show success message
                alert('Resource deleted successfully');
                
                // Optionally refresh the page
                // window.location.reload();
            } else {
                alert('Error deleting resource: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting resource. Please try again.');
        });
    }
}
</script>
<?php
$content = ob_get_clean();
require_once 'layouts/main_layout.php';
?>