<?php
// กดหัวใจ: เพิ่ม/ลบรายการโปรด (เรียกด้วย fetch จาก assets/js/app.js) คืนค่าเป็น JSON
require_once 'config/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'POST only']);
    exit;
}

$gameId = (int)($_POST['game_id'] ?? 0);
$token  = $_SESSION['gf_token'];

// เช็คว่าเกมมีอยู่จริง
$stmt = mysqli_prepare($conn, 'SELECT id FROM games WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $gameId);
mysqli_stmt_execute($stmt);
$exists = mysqli_stmt_get_result($stmt)->num_rows > 0;
mysqli_stmt_close($stmt);
if (!$exists) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'game not found']);
    exit;
}

// มีอยู่แล้ว -> ลบ / ยังไม่มี -> เพิ่ม
$stmt = mysqli_prepare($conn, 'SELECT id FROM favorites WHERE session_token = ? AND game_id = ?');
mysqli_stmt_bind_param($stmt, 'si', $token, $gameId);
mysqli_stmt_execute($stmt);
$has = mysqli_stmt_get_result($stmt)->num_rows > 0;
mysqli_stmt_close($stmt);

if ($has) {
    $stmt = mysqli_prepare($conn, 'DELETE FROM favorites WHERE session_token = ? AND game_id = ?');
} else {
    $stmt = mysqli_prepare($conn, 'INSERT INTO favorites (session_token, game_id) VALUES (?, ?)');
}
mysqli_stmt_bind_param($stmt, 'si', $token, $gameId);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo json_encode(['ok' => true, 'favorited' => !$has]);
mysqli_close($conn);
