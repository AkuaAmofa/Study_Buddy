<?php
function log_message($message, $type = 'INFO') {
    $log_file = __DIR__ . '/../logs/app.log';
    $log_dir = dirname($log_file);

    // Create logs directory if it doesn't exist
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0777, true);
    }

    // Format the log message
    $timestamp = date('Y-m-d H:i:s');
    $formatted_message = "[$timestamp] [$type] $message" . PHP_EOL;

    // Append to log file
    file_put_contents($log_file, $formatted_message, FILE_APPEND);
}
?> 