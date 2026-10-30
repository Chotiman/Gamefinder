<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['gf_token'])) {
    $_SESSION['gf_token'] = bin2hex(random_bytes(16));
}

const PLATFORMS = ['PC', 'PlayStation', 'Xbox'];
const BUDGETS = [
    'any'  => 'ทุกงบประมาณ',
    'free' => 'ฟรี',
    '500'  => 'ไม่เกิน 500 บาท',
    '1000' => 'ไม่เกิน 1,000 บาท',
    '2000' => 'ไม่เกิน 2,000 บาท',
];

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function price_label($price) {
    return ((int)$price <= 0) ? 'ฟรี' : number_format((int)$price) . ' บาท';
}

/** ดึงแนวเกมทั้งหมด คืนค่าเป็น [id => row] */
function fetch_genres($conn) {
    $genres = [];
    $res = mysqli_query($conn, 'SELECT id, name, icon FROM genres ORDER BY id');
    while ($res && $row = mysqli_fetch_assoc($res)) {
        $genres[(int)$row['id']] = $row;
    }
    return $genres;
}

/** ดึงเกมทั้งหมดพร้อมแนวและแพลตฟอร์ม */
function fetch_games($conn) {
    $sql = "SELECT g.*,
              (SELECT GROUP_CONCAT(genre_id) FROM game_genre WHERE game_id = g.id) AS genre_ids,
              (SELECT GROUP_CONCAT(platform) FROM game_platform WHERE game_id = g.id) AS platforms
            FROM games g ORDER BY g.id";
    $games = [];
    $res = mysqli_query($conn, $sql);
    while ($res && $row = mysqli_fetch_assoc($res)) {
        $row['id']        = (int)$row['id'];
        $row['price']     = (int)$row['price'];
        $row['genre_ids'] = $row['genre_ids'] !== null && $row['genre_ids'] !== '' ? array_map('intval', explode(',', $row['genre_ids'])) : [];
        $row['platforms'] = $row['platforms'] !== null && $row['platforms'] !== '' ? explode(',', $row['platforms']) : [];
        $games[$row['id']] = $row;
    }
    return $games;
}

function fetch_game($conn, $id) {
    $all = fetch_games($conn);
    return $all[$id] ?? null;
}

function get_favorite_ids($conn) {
    $ids = [];
    $stmt = mysqli_prepare($conn, 'SELECT game_id FROM favorites WHERE session_token = ? ORDER BY created_at DESC');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $_SESSION['gf_token']);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $ids[] = (int)$row['game_id'];
        }
        mysqli_stmt_close($stmt);
    }
    return $ids;
}

/** ภาพปก: ถ้าไม่มีไฟล์ในโฟลเดอร์ จะแสดงกล่องสีพร้อมตัวอักษรแทน (ไม่ให้ภาพแตก) */
function cover_html($game, $class = 'cover') {
    $file = $game['cover'] ?? '';
    $path = __DIR__ . '/../assets/images/games/' . $file;
    if ($file !== '' && is_file($path)) {
        return '<img class="' . e($class) . '" src="assets/images/games/' . e(rawurlencode($file)) . '" alt="' . e($game['title']) . '" loading="lazy">';
    }
    $hue = crc32($game['title']) % 360;
    $initials = mb_strtoupper(mb_substr($game['title'], 0, 2, 'UTF-8'), 'UTF-8');
    return '<div class="' . e($class) . ' cover-ph" style="--h:' . $hue . '"><span>' . e($initials) . '</span></div>';
}

function platform_chips($platforms) {
    $out = '';
    foreach ($platforms as $p) {
        $out .= '<span class="plat">' . e($p) . '</span>';
    }
    return $out;
}

function render_card($g, $favIds, $genres) {
    $isFav = in_array($g['id'], $favIds, true);
    $mainGenre = isset($g['genre_ids'][0], $genres[$g['genre_ids'][0]]) ? $genres[$g['genre_ids'][0]]['name'] : '';
    ?>
    <article class="game-card">
        <a class="cover-wrap" href="game.php?id=<?php echo $g['id']; ?>"><?php echo cover_html($g); ?></a>
        <div class="game-info">
            <h3><?php echo e($g['title']); ?></h3>
            <p class="genre-text"><?php echo e($mainGenre); ?></p>
            <div class="chips">
                <span class="chip <?php echo $g['price'] <= 0 ? 'chip-free' : 'chip-paid'; ?>"><?php echo e(price_label($g['price'])); ?></span>
                <?php if (!empty($g['tag'])): ?><span class="chip chip-tag"><?php echo e($g['tag']); ?></span><?php endif; ?>
            </div>
            <p class="desc"><?php echo e($g['description']); ?></p>
            <div class="plats"><?php echo platform_chips($g['platforms']); ?></div>
            <div class="card-actions">
                <a class="btn-outline" href="game.php?id=<?php echo $g['id']; ?>">ดูรายละเอียด</a>
                <button type="button" class="fav-btn <?php echo $isFav ? 'active' : ''; ?>" data-id="<?php echo $g['id']; ?>" aria-label="เพิ่มในรายการโปรด">♥</button>
            </div>
        </div>
    </article>
    <?php
}
