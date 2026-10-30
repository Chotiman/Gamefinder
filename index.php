<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$genres   = fetch_genres($conn);
$allGames = fetch_games($conn);
$favIds   = get_favorite_ids($conn);

// ---------- รับค่าจากฟอร์ม (GET) ----------
$played    = array_values(array_unique(array_filter(array_map('intval', (array)($_GET['played'] ?? [])), fn($id) => isset($allGames[$id]))));
$selGenres = array_values(array_unique(array_filter(array_map('intval', (array)($_GET['genre'] ?? [])), fn($id) => isset($genres[$id]))));
$selPlat   = array_values(array_intersect((array)($_GET['platform'] ?? []), PLATFORMS));
$budget    = $_GET['budget'] ?? 'any';
if (!isset(BUDGETS[$budget])) $budget = 'any';
$sort      = $_GET['sort'] ?? 'recommend';
if (!in_array($sort, ['recommend', 'price_asc', 'price_desc', 'name'], true)) $sort = 'recommend';
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 9;

// ---------- น้ำหนักแนวเกมจากเกมที่เคยเล่น ----------
$playedWeight = [];
foreach ($played as $pid) {
    foreach ($allGames[$pid]['genre_ids'] as $gid) {
        $playedWeight[$gid] = ($playedWeight[$gid] ?? 0) + 1;
    }
}

// ---------- กรอง + ให้คะแนน ----------
$maxPrice = null;
if ($budget === 'free') $maxPrice = 0;
elseif ($budget !== 'any') $maxPrice = (int)$budget;

$results = [];
foreach ($allGames as $g) {
    if (in_array($g['id'], $played, true)) continue;                         // ไม่แนะนำเกมที่เคยเล่นแล้ว
    if ($maxPrice !== null && $g['price'] > $maxPrice) continue;             // งบประมาณ
    if ($selPlat && !array_intersect($selPlat, $g['platforms'])) continue;   // แพลตฟอร์ม
    if ($selGenres && !array_intersect($selGenres, $g['genre_ids'])) continue; // แนวที่เลือก

    $score = 0;
    foreach ($g['genre_ids'] as $gid) {
        if (in_array($gid, $selGenres, true)) $score += 3;
        $score += 2 * ($playedWeight[$gid] ?? 0);
    }
    $g['score'] = $score;
    $results[] = $g;
}

usort($results, function ($a, $b) use ($sort) {
    switch ($sort) {
        case 'price_asc':  return [$a['price'], $a['title']] <=> [$b['price'], $b['title']];
        case 'price_desc': return [$b['price'], $a['title']] <=> [$a['price'], $b['title']];
        case 'name':       return strcasecmp($a['title'], $b['title']);
        default:           return [$b['score'], $a['id']] <=> [$a['score'], $b['id']];
    }
});

$total      = count($results);
$totalPages = max(1, (int)ceil($total / $perPage));
$page       = min($page, $totalPages);
$pageItems  = array_slice($results, ($page - 1) * $perPage, $perPage);

function page_url($p) {
    $q = $_GET;
    $q['page'] = $p;
    return 'index.php?' . http_build_query($q) . '#results';
}

// ข้อมูลสำหรับช่องค้นหาเกมที่เคยเล่น (ส่งให้ JS)
$gameList = array_map(fn($g) => ['id' => $g['id'], 'title' => $g['title']], array_values($allGames));

$pageTitle = 'หาเกมที่ใช่สำหรับคุณ';
$active = 'index';
include 'includes/header.php';
?>
<section class="hero">
    <div class="hero-inner">
        <h1>หาเกมที่ใช่ <span class="accent">สำหรับคุณ</span></h1>
        <p>บอกเราเกี่ยวกับสิ่งที่คุณชอบ แล้วเราจะช่วยแนะนำเกมที่เหมาะกับคุณ</p>
        <div class="hero-steps">
            <span>🎮 เลือกจากเกมที่เคยเล่น</span>
            <span>⭐ ระบุแนวเกมที่ชอบ</span>
            <span>🪙 กำหนดงบประมาณได้</span>
        </div>
    </div>
</section>

<main class="layout" id="finder">
    <!-- ================= แผงตัวกรอง ================= -->
    <form class="panel" method="get" action="index.php#results" id="filterForm">
        <input type="hidden" name="sort" value="<?php echo e($sort); ?>">

        <div class="step">
            <div class="step-head"><span class="num">1</span>
                <div><h2>เกมที่คุณเคยเล่น</h2><p>เลือกเกมที่คุณเล่นแล้ว เพื่อให้เราแนะนำเกมใหม่ที่เหมาะกับคุณ</p></div>
            </div>
            <div class="search-box">
                <input type="text" id="playedSearch" placeholder="ค้นหาเกม..." autocomplete="off">
                <ul id="playedSuggest" class="suggest" hidden></ul>
            </div>
            <div id="playedChips" class="chips-row">
                <?php foreach ($played as $pid): ?>
                    <span class="played-chip" data-id="<?php echo $pid; ?>">
                        <?php echo e($allGames[$pid]['title']); ?>
                        <button type="button" class="x" aria-label="ลบ">×</button>
                        <input type="hidden" name="played[]" value="<?php echo $pid; ?>">
                    </span>
                <?php endforeach; ?>
            </div>
            <button type="button" class="link-btn" id="toggleAllGames">ดูเกมทั้งหมด ⌄</button>
            <div id="allGames" class="all-games" hidden>
                <?php foreach ($allGames as $g): ?>
                    <button type="button" class="pick-game" data-id="<?php echo $g['id']; ?>"><?php echo e($g['title']); ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="step">
            <div class="step-head"><span class="num">2</span>
                <div><h2>แนวเกมที่ชอบ</h2><p>เลือกได้หลายแนว หรือไม่เลือกก็ได้</p></div>
            </div>
            <div class="genre-grid">
                <?php foreach ($genres as $gid => $gen): ?>
                    <label class="pill">
                        <input type="checkbox" name="genre[]" value="<?php echo $gid; ?>" <?php echo in_array($gid, $selGenres, true) ? 'checked' : ''; ?>>
                        <span><?php echo e($gen['icon']); ?> <?php echo e($gen['name']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="step">
            <div class="step-head"><span class="num">3</span>
                <div><h2>งบประมาณ</h2><p>เลือกราคาเกมที่ต้องการ</p></div>
            </div>
            <div class="radio-grid">
                <?php foreach (BUDGETS as $key => $label): ?>
                    <label class="radio">
                        <input type="radio" name="budget" value="<?php echo e($key); ?>" <?php echo $budget === (string)$key ? 'checked' : ''; ?>>
                        <span><?php echo e($label); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="step">
            <div class="step-head"><span class="num">4</span>
                <div><h2>แพลตฟอร์ม</h2><p>เลือกแพลตฟอร์มที่คุณเล่นได้</p></div>
            </div>
            <div class="plat-grid">
                <?php foreach (PLATFORMS as $p): ?>
                    <label class="pill">
                        <input type="checkbox" name="platform[]" value="<?php echo e($p); ?>" <?php echo in_array($p, $selPlat, true) ? 'checked' : ''; ?>>
                        <span><?php echo e($p); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="btn-primary">🔍 ค้นหาเกมที่ใช่</button>
        <a href="index.php" class="reset-link">ล้างตัวเลือกทั้งหมด</a>
    </form>

    <!-- ================= ผลลัพธ์ ================= -->
    <section class="results" id="results">
        <div class="results-head">
            <div>
                <h2>🎮 เกมที่แนะนำสำหรับคุณ</h2>
                <p>เราได้คัดเลือกเกมที่ตรงกับความชอบและเงื่อนไขของคุณ</p>
            </div>
            <form method="get" action="index.php#results" class="sort-form">
                <?php foreach ($played as $pid): ?><input type="hidden" name="played[]" value="<?php echo $pid; ?>"><?php endforeach; ?>
                <?php foreach ($selGenres as $gid): ?><input type="hidden" name="genre[]" value="<?php echo $gid; ?>"><?php endforeach; ?>
                <?php foreach ($selPlat as $p): ?><input type="hidden" name="platform[]" value="<?php echo e($p); ?>"><?php endforeach; ?>
                <input type="hidden" name="budget" value="<?php echo e($budget); ?>">
                <label>เรียงตาม :
                    <select name="sort" onchange="this.form.submit()">
                        <option value="recommend" <?php echo $sort === 'recommend' ? 'selected' : ''; ?>>แนะนำสำหรับคุณ</option>
                        <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>ราคา: ต่ำ → สูง</option>
                        <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>ราคา: สูง → ต่ำ</option>
                        <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>ชื่อเกม A-Z</option>
                    </select>
                </label>
            </form>
        </div>

        <?php if ($total === 0): ?>
            <div class="empty">😢 ไม่พบเกมที่ตรงกับเงื่อนไข ลองปรับงบประมาณหรือลดตัวเลือกลงดูนะ</div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($pageItems as $g) render_card($g, $favIds, $genres); ?>
            </div>
        <?php endif; ?>

        <div class="results-foot">
            <span>🎮 พบทั้งหมด <?php echo $total; ?> เกม</span>
            <?php if ($totalPages > 1): ?>
                <nav class="pager">
                    <?php if ($page > 1): ?><a href="<?php echo e(page_url($page - 1)); ?>">‹</a><?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="<?php echo e(page_url($i)); ?>" class="<?php echo $i === $page ? 'cur' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?><a href="<?php echo e(page_url($page + 1)); ?>">›</a><?php endif; ?>
                </nav>
            <?php endif; ?>
            <small>* ราคาอาจมีการเปลี่ยนแปลงตามแพลตฟอร์มและโปรโมชัน</small>
        </div>
    </section>
</main>

<script>window.GAME_LIST = <?php echo json_encode($gameList, JSON_UNESCAPED_UNICODE); ?>;</script>
<?php
include 'includes/footer.php';
mysqli_close($conn);
