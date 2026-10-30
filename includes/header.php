<?php
// ตัวแปรที่ใช้: $pageTitle (ชื่อหน้า), $active (index | favorites | about)
$pageTitle = $pageTitle ?? 'GameFinder';
$active = $active ?? 'index';
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle); ?> | GameFinder</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <a class="brand" href="index.php">
        <span class="brand-icon">🎮</span>
        <span><strong>GameFinder</strong><small>Find Your Next Game</small></span>
    </a>
    <div class="nav-links">
        <a href="index.php" class="<?php echo $active === 'index' ? 'on' : ''; ?>">หน้าหลัก</a>
        <a href="index.php#finder">ค้นหาเกม</a>
        <a href="favorites.php" class="<?php echo $active === 'favorites' ? 'on' : ''; ?>">รายการโปรด</a>
        <a href="about.php" class="<?php echo $active === 'about' ? 'on' : ''; ?>">เกี่ยวกับเรา</a>
    </div>
</nav>
