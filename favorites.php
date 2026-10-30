<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$genres = fetch_genres($conn);
$all = fetch_games($conn);
$favIds = get_favorite_ids($conn);

$pageTitle = 'รายการโปรด';
$active = 'favorites';
include 'includes/header.php';
?>
<main class="detail-wrap">
    <h2 class="sec-title">♥ รายการโปรดของคุณ</h2>
    <?php if (!$favIds): ?>
        <div class="empty">ยังไม่มีเกมในรายการโปรด ลองกดหัวใจที่เกมที่ถูกใจใน<a href="index.php">หน้าหลัก</a>ดูนะ</div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($favIds as $fid) { if (isset($all[$fid])) render_card($all[$fid], $favIds, $genres); } ?>
        </div>
    <?php endif; ?>
</main>
<?php
include 'includes/footer.php';
mysqli_close($conn);
