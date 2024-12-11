<?php
// Define the path relative to your project root
$upload_path = __DIR__ . '/uploads/resources';

// Create directories if they don't exist
if (!file_exists($upload_path)) {
    // Create with full permissions (777) for development
    // For production, use more restrictive permissions
    if (mkdir($upload_path, 0777, true)) {
        echo "Successfully created uploads/resources directory\n";
    } else {
        echo "Failed to create uploads/resources directory\n";
    }
} else {
    echo "uploads/resources directory already exists\n";
}

// Verify permissions
if (is_writable($upload_path)) {
    echo "Directory is writable\n";
} else {
    echo "Warning: Directory is not writable\n";
} 