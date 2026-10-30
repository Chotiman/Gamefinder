<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$genres = fetch_genres($conn);
$all = fetch_games($conn);
$game = $all[$id] ?? null;
$favIds = get_favorite_ids($conn);

$pageTitle = $game ? $game['title'] : 'ไม่พบเกม';
$active = 'index';
include 'includes/header.php';
?>
<main class="detail-wrap">
<?php if (!$game): ?>
    <div class="empty">ไม่พบเกมที่ต้องการ <a href="index.php">← กลับหน้าหลัก</a></div>
<?php else:
    $isFav = in_array($game['id'], $favIds, true);
    // เกมที่คล้ายกัน = มีแนวร่วมกัน
    $similar = [];
    foreach ($all as $g) {
        if ($g['id'] === $game['id']) continue;
        $common = count(array_intersect($g['genre_ids'], $game['genre_ids']));
        if ($common > 0) { $g['common'] = $common; $similar[] = $g; }
    }
    usort($similar, fn($a, $b) => $b['common'] <=> $a['common']);
    $similar = array_slice($similar, 0, 3);
?>
    <p><a class="back" href="javascript:history.back()">← ย้อนกลับ</a></p>
    <div class="detail card-box">
        <div class="detail-cover"><?php echo cover_html($game, 'cover big'); ?></div>
        <div class="detail-info">
            <h1><?php echo e($game['title']); ?></h1>
            <div class="chips">
                <?php foreach ($game['genre_ids'] as $gid): if (!isset($genres[$gid])) continue; ?>
                    <span class="chip chip-tag"><?php echo e($genres[$gid]['icon'] . ' ' . $genres[$gid]['name']); ?></span>
                <?php endforeach; ?>
            </div>
            <p class="price-big <?php echo $game['price'] <= 0 ? 'free' : ''; ?>"><?php echo e(price_label($game['price'])); ?></p>
            <p class="desc-big"><?php echo nl2br(e($game['description'])); ?></p>
            <div class="plats"><?php echo platform_chips($game['platforms']); ?></div>
            <p>
                <button type="button" class="fav-btn wide <?php echo $isFav ? 'active' : ''; ?>" data-id="<?php echo $game['id']; ?>">♥ รายการโปรด</button>
            </p>
        </div>
    </div>

    <?php if ($similar): ?>
        <h2 class="sec-title">เกมที่คล้ายกัน</h2>
        <div class="grid">
            <?php foreach ($similar as $g) render_card($g, $favIds, $genres); ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
</main>
<?php
include 'includes/footer.php';
mysqli_close($conn);
