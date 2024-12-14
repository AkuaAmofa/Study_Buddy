<?php
// Remove or comment out session_start() since it's already called in the page files
// session_start();

// Check if these variables are set, if not set defaults
if (!isset($page_title)) {
    $page_title = 'Study Buddy';
}
if (!isset($current_page)) {
    $current_page = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Study Buddy</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>
    
    <main class="main-content">
        <?php echo $content; ?>
    </main>

</body>
</html> 