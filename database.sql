-- GameFinder database
-- วิธีใช้: เปิด phpMyAdmin -> เลือกฐานข้อมูลของคุณ (เช่น s68160188) -> แท็บ Import -> เลือกไฟล์นี้ -> Go
-- (ไม่มีคำสั่ง CREATE DATABASE เพราะจะ import เข้าฐานข้อมูลที่เลือกอยู่)

SET NAMES utf8mb4;

DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS game_platform;
DROP TABLE IF EXISTS game_genre;
DROP TABLE IF EXISTS games;
DROP TABLE IF EXISTS genres;

CREATE TABLE genres (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  icon VARCHAR(10) NOT NULL DEFAULT '🎮'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE games (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  price INT NOT NULL DEFAULT 0,            -- 0 = เล่นฟรี (หน่วยบาท)
  tag VARCHAR(40) DEFAULT NULL,            -- ป้ายเล็ก ๆ เช่น Co-op, เล่นกับเพื่อน
  description TEXT,
  cover VARCHAR(255) DEFAULT NULL          -- ชื่อไฟล์ใน assets/images/games/
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE game_genre (
  game_id INT NOT NULL,
  genre_id INT NOT NULL,
  PRIMARY KEY (game_id, genre_id),
  FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
  FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE game_platform (
  game_id INT NOT NULL,
  platform ENUM('PC','PlayStation','Xbox') NOT NULL,
  PRIMARY KEY (game_id, platform),
  FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE favorites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_token VARCHAR(64) NOT NULL,
  game_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_fav (session_token, game_id),
  FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- แนวเกม ----------
INSERT INTO genres (id, name, icon) VALUES
(1, 'FPS / Shooter', '🎯'),
(2, 'RPG / Open World', '🗡️'),
(3, 'Action / Adventure', '⚔️'),
(4, 'Horror', '👻'),
(5, 'Simulation / Casual', '🎮'),
(6, 'Sports / Racing', '🏎️');

-- ---------- เกม ----------
INSERT INTO games (id, title, price, tag, description, cover) VALUES
(1,  'Valorant', 0, 'ทีม 5v5', 'เกมยิงแนวยุทธวิธี 5v5 ที่ผสมทักษะการเล็งเข้ากับสกิลของเอเจนต์', 'valorant.jpg'),
(2,  'Apex Legends', 0, 'เล่นกับเพื่อน', 'เกม Battle Royale ที่เน้นการเคลื่อนที่และการทำงานเป็นทีม', 'apex-legends.jpg'),
(3,  'Minecraft', 899, 'สร้างสรรค์', 'โลกบล็อกที่ให้คุณสำรวจ สร้าง และเอาชีวิตรอดได้อย่างอิสระ', 'minecraft.jpg'),
(4,  'GTA V', 590, 'โลกเปิด', 'โลกเปิดสุดอลังการในลอสซานโตส พร้อมเนื้อเรื่องและโหมดออนไลน์', 'gta-v.jpg'),
(5,  'Overwatch 2', 0, 'ฮีโร่ชูตเตอร์', 'เกมยิงแบบทีมที่มีฮีโร่และความสามารถแตกต่างกัน', 'overwatch-2.jpg'),
(6,  'The Finals', 0, 'ทำลายฉาก', 'เกมยิงที่สนามรบทำลายสภาพแวดล้อมได้ และมีความมันส์ในการเล่นสูง', 'the-finals.jpg'),
(7,  'Elden Ring', 1590, 'ท้าทาย', 'ผจญภัยในโลกแฟนตาซีอันกว้างใหญ่ ต่อสู้กับบอสสุดโหด', 'elden-ring.jpg'),
(8,  'Stardew Valley', 315, 'ผ่อนคลาย', 'สร้างฟาร์ม สนิทสนมกับชาวบ้าน และใช้ชีวิตในชนบทแบบสบาย ๆ', 'stardew-valley.jpg'),
(9,  'Phasmophobia', 369, 'Co-op', 'ร่วมทีมสืบสวนปรากฏการณ์เหนือธรรมชาติสุดหลอน', 'phasmophobia.jpg'),
(10, 'Baldur''s Gate 3', 1499, 'เนื้อเรื่อง', 'เกม RPG ที่คุณเลือกเส้นทางเอง เนื้อเรื่องลึก ตัดสินใจได้ทุกฉาก', 'baldurs-gate-3.jpg'),
(11, 'Rocket League', 0, 'เล่นกับเพื่อน', 'ฟุตบอลผสมรถยนต์ เล่นง่ายแต่เก่งยาก สนุกกับเพื่อนมาก', 'rocket-league.jpg'),
(12, 'Hades', 385, 'Roguelike', 'แอ็กชันสุดเร็ว ต่อสู้ฝ่าเขาวงกตนรกพร้อมเนื้อเรื่องที่เล่นซ้ำได้', 'hades.jpg'),
(13, 'Counter-Strike 2', 0, 'แข่งขัน', 'เกม FPS สายแข่งขันระดับตำนาน เน้นการเล็งและการทำงานเป็นทีม', 'counter-strike-2.jpg'),
(14, 'Fortnite', 0, 'เล่นกับเพื่อน', 'Battle Royale ที่สร้างสิ่งก่อสร้างได้ พร้อมอีเวนต์ใหม่ ๆ สม่ำเสมอ', 'fortnite.jpg'),
(15, 'Cyberpunk 2077', 1490, 'เนื้อเรื่อง', 'RPG โลกเปิดในมหานครไซเบอร์พังก์ เลือกสายการเล่นได้หลากหลาย', 'cyberpunk-2077.jpg'),
(16, 'The Witcher 3', 599, 'เนื้อเรื่อง', 'ผจญภัยกับพ่อมดนักล่าสัตว์ประหลาด โลกกว้างและภารกิจน่าจดจำ', 'the-witcher-3.jpg'),
(17, 'Hollow Knight', 245, 'อินดี้', 'ผจญภัยแนว Metroidvania ในอาณาจักรแมลงใต้ดินที่งดงามและลึกลับ', 'hollow-knight.jpg'),
(18, 'Terraria', 199, 'Co-op', 'ขุด สร้าง สู้บอส ผจญภัยแบบ 2D ที่เล่นกับเพื่อนได้', 'terraria.jpg'),
(19, 'Forza Horizon 5', 1790, 'ขับรถ', 'เกมขับรถโลกเปิดในเม็กซิโก ภาพสวยและรถให้เลือกเยอะมาก', 'forza-horizon-5.jpg'),
(20, 'Rainbow Six Siege', 399, 'ทีม 5v5', 'เกมยิงยุทธวิธีที่ต้องวางแผนบุกและป้องกัน ทำลายฉากได้', 'rainbow-six-siege.jpg'),
(21, 'Resident Evil Village', 990, 'เนื้อเรื่อง', 'สยองขวัญแอ็กชันในหมู่บ้านลึกลับ บรรยากาศหลอนและระทึก', 'resident-evil-village.jpg'),
(22, 'Red Dead Redemption 2', 1590, 'โลกเปิด', 'ชีวิตคาวบอยในโลกเปิดที่ละเอียดมาก พร้อมเนื้อเรื่องทรงพลัง', 'red-dead-redemption-2.jpg'),
(23, 'Fall Guys', 0, 'เล่นกับเพื่อน', 'เกมวิ่งฝ่าด่านสุดป่วน แข่งกับผู้เล่นหลายคนในแบบสนุก ๆ', 'fall-guys.jpg'),
(24, 'Dead by Daylight', 499, 'Co-op', 'เกมสยองขวัญ 4 ต่อ 1 ผู้รอดชีวิตต้องหนีจากฆาตกร', 'dead-by-daylight.jpg');

-- ---------- เกม <-> แนว ----------
INSERT INTO game_genre (game_id, genre_id) VALUES
(1,1),(2,1),(3,5),(3,3),(4,3),(5,1),(6,1),(7,2),(7,3),(8,5),(9,4),(10,2),
(11,6),(12,3),(13,1),(14,1),(14,3),(15,2),(15,3),(16,2),(17,3),(18,5),(18,3),
(19,6),(20,1),(21,4),(21,3),(22,3),(22,2),(23,6),(23,5),(24,4);

-- ---------- เกม <-> แพลตฟอร์ม ----------
INSERT INTO game_platform (game_id, platform) VALUES
(1,'PC'),
(2,'PC'),(2,'PlayStation'),(2,'Xbox'),
(3,'PC'),(3,'PlayStation'),(3,'Xbox'),
(4,'PC'),(4,'PlayStation'),(4,'Xbox'),
(5,'PC'),(5,'PlayStation'),(5,'Xbox'),
(6,'PC'),(6,'PlayStation'),(6,'Xbox'),
(7,'PC'),(7,'PlayStation'),(7,'Xbox'),
(8,'PC'),
(9,'PC'),
(10,'PC'),(10,'PlayStation'),(10,'Xbox'),
(11,'PC'),(11,'PlayStation'),(11,'Xbox'),
(12,'PC'),(12,'PlayStation'),(12,'Xbox'),
(13,'PC'),
(14,'PC'),(14,'PlayStation'),(14,'Xbox'),
(15,'PC'),(15,'PlayStation'),(15,'Xbox'),
(16,'PC'),(16,'PlayStation'),(16,'Xbox'),
(17,'PC'),(17,'PlayStation'),(17,'Xbox'),
(18,'PC'),(18,'PlayStation'),(18,'Xbox'),
(19,'PC'),(19,'Xbox'),
(20,'PC'),(20,'PlayStation'),(20,'Xbox'),
(21,'PC'),(21,'PlayStation'),(21,'Xbox'),
(22,'PC'),(22,'PlayStation'),(22,'Xbox'),
(23,'PC'),(23,'PlayStation'),(23,'Xbox'),
(24,'PC'),(24,'PlayStation'),(24,'Xbox');
