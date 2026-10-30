<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$count = 0;
$res = mysqli_query($conn, 'SELECT COUNT(*) AS c FROM games');
if ($res) { $count = (int)mysqli_fetch_assoc($res)['c']; }

$pageTitle = 'เกี่ยวกับเรา';
$active = 'about';
include 'includes/header.php';
?>
<main class="detail-wrap">
    <div class="card-box about">
        <h1>เกี่ยวกับ GameFinder</h1>
        <p>GameFinder เป็นเว็บช่วยค้นหาและแนะนำเกมให้เหมาะกับผู้เล่น จากเกมที่เคยเล่น แนวเกมที่ชอบ งบประมาณ และแพลตฟอร์ม</p>
        <p>ตอนนี้ในระบบมีเกมทั้งหมด <strong><?php echo $count; ?></strong> เกม</p>
        <h3>ระบบแนะนำทำงานอย่างไร</h3>
        <ul>
            <li>ตัดเกมที่คุณเคยเล่นแล้วออก</li>
            <li>กรองตามงบประมาณและแพลตฟอร์มที่เลือก</li>
            <li>ให้คะแนนเกมที่มีแนวตรงกับแนวที่เลือก และแนวเดียวกับเกมที่เคยเล่น แล้วเรียงจากคะแนนมากไปน้อย</li>
        </ul>
        <p>สร้างด้วย PHP + MySQL (mysqli)</p>
    </div>
</main>
<?php
include 'includes/footer.php';
mysqli_close($conn);
