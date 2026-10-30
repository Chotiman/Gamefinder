<?php
// ตั้งค่าการเชื่อมต่อฐานข้อมูล: แก้ 4 ค่านี้ให้ตรงกับของคุณ (ใช้ค่าเดียวกับโปรเจกต์ resume ได้เลย)
$db_host = 'localhost';
$db_user = 's68160188';
$db_pass = 'YOUR_PASSWORD';
$db_name = 's68160188';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
    die('เชื่อมต่อฐานข้อมูลไม่ได้: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');
