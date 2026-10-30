# GameFinder (PHP + MySQL)

เว็บค้นหา/แนะนำเกม ตามเกมที่เคยเล่น แนวเกม งบประมาณ และแพลตฟอร์ม

## วิธีติดตั้ง
1. phpMyAdmin → เลือกฐานข้อมูลของคุณ (เช่น `s68160188`) → แท็บ **Import** → เลือก `database.sql` → Go
2. แก้ `config/db.php` ใส่ host / user / password / ชื่อฐานข้อมูลให้ตรง
3. อัปโหลดทั้งโฟลเดอร์ขึ้นเซิร์ฟเวอร์ผ่าน FileZilla (เช่นไปที่ `public_html/gamefinder/`)
4. เปิด `angsila.cs.buu.ac.th/~รหัสนักศึกษา/gamefinder/`

## รูปปกเกม
ใส่ไฟล์รูปไว้ที่ `assets/images/games/` ชื่อไฟล์ต้องตรงกับคอลัมน์ `cover` ในตาราง `games` เช่น `valorant.jpg`, `apex-legends.jpg`
- ชื่อไฟล์ **ตัวพิมพ์เล็ก/ใหญ่มีผล** บนเซิร์ฟเวอร์ Linux
- แนะนำ `.jpg` / `.png` / `.webp` (หลีกเลี่ยง `.jfif`)
- ถ้ายังไม่มีไฟล์รูป เว็บจะแสดงกล่องสีพร้อมตัวอักษรย่อแทน ไม่ทำให้หน้าพัง
- เปลี่ยนชื่อไฟล์ได้ที่ phpMyAdmin → ตาราง `games` → คอลัมน์ `cover`

## โครงสร้าง
```
database.sql        ไฟล์ import (ตาราง + ข้อมูลเกม 24 เกม)
config/db.php       เชื่อมต่อฐานข้อมูล
includes/           functions.php, header.php, footer.php
index.php           หน้าหลัก: ตัวกรอง + ผลแนะนำ + แบ่งหน้า
game.php            รายละเอียดเกม + เกมที่คล้ายกัน
favorites.php       รายการโปรด
favorite.php        API กดหัวใจ (JSON)
about.php           เกี่ยวกับเรา
assets/             css, js, images/games
```

## ตาราง
`genres`, `games`, `game_genre`, `game_platform`, `favorites`

## ต้องการ
PHP 7.4+ พร้อม mysqli และ mbstring
