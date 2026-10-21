-- =====================================================================
-- DriveSphere database setup AND upgrade script (safe to run more than once).
-- Import in phpMyAdmin > Import.  Works on a new database or on your
-- existing "drivesphere" database: existing rows are NOT deleted.
-- Requires XAMPP's MariaDB (supports ADD COLUMN IF NOT EXISTS).
-- =====================================================================
CREATE DATABASE IF NOT EXISTS `drivesphere` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `drivesphere`;

-- ---------- 1. Original tables (created only if missing) ----------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('customer','admin') DEFAULT 'customer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `make` VARCHAR(50) NOT NULL,
  `model` VARCHAR(100) NOT NULL,
  `tag_label` VARCHAR(100) DEFAULT NULL,
  `plate_number` VARCHAR(20) NOT NULL UNIQUE,
  `daily_rate` DECIMAL(10,2) NOT NULL,
  `weekly_rate` DECIMAL(10,2) DEFAULT NULL,
  `transmission` VARCHAR(30) NOT NULL,
  `fuel_type` VARCHAR(30) NOT NULL,
  `seats` INT NOT NULL,
  `status` ENUM('available','rented','maintenance') DEFAULT 'available',
  `image_url` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `reservations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reference_code` VARCHAR(30) NOT NULL UNIQUE,
  `user_id` INT DEFAULT NULL,
  `vehicle_id` INT NOT NULL,
  `pickup_location` VARCHAR(100) NOT NULL,
  `return_location` VARCHAR(100) NOT NULL,
  `pickup_date` DATETIME NOT NULL,
  `return_date` DATETIME NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `category` VARCHAR(50) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reviewer_name` VARCHAR(100) NOT NULL,
  `reviewer_title` VARCHAR(100) NOT NULL,
  `rating` DECIMAL(2,1) NOT NULL DEFAULT 5.0,
  `vehicle_model` VARCHAR(100) NOT NULL,
  `service_type` VARCHAR(50) NOT NULL,
  `review_text` TEXT NOT NULL,
  `location_tag` VARCHAR(100) NOT NULL,
  `perk_tag` VARCHAR(100) NOT NULL,
  `tag_type` ENUM('corporate','self','verified') DEFAULT 'verified',
  `review_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- 2. New tables ----------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,                       -- NULL = for all admins
  `audience` ENUM('customer','admin') NOT NULL,
  `type` ENUM('booking','payment','security','fleet') NOT NULL DEFAULT 'booking',
  `title` VARCHAR(150) NOT NULL,
  `message` VARCHAR(255) NOT NULL,
  `link` VARCHAR(150) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `merchant_profile` (
  `id` INT PRIMARY KEY,
  `merchant_name` VARCHAR(120) NOT NULL,
  `contact_name` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` VARCHAR(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- 3. Upgrades to existing tables ----------
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `license_number` VARCHAR(50) DEFAULT NULL;

ALTER TABLE `vehicles`
  ADD COLUMN IF NOT EXISTS `odometer` INT NOT NULL DEFAULT 12000;

-- widen the status list first, convert old rows, then keep the final list
ALTER TABLE `reservations` MODIFY `status`
  ENUM('pending','confirmed','reserved','en_route','returned','completed','rejected','cancelled') NOT NULL DEFAULT 'pending';
UPDATE `reservations` SET `status`='reserved' WHERE `status`='confirmed';
ALTER TABLE `reservations` MODIFY `status`
  ENUM('pending','reserved','en_route','returned','completed','rejected','cancelled') NOT NULL DEFAULT 'pending';

ALTER TABLE `reservations`
  ADD COLUMN IF NOT EXISTS `days` INT NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS `base_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `vat_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `deposit_amount` DECIMAL(10,2) NOT NULL DEFAULT 5000,
  ADD COLUMN IF NOT EXISTS `driver_name` VARCHAR(100) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `driver_phone` VARCHAR(30) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `driver_license` VARCHAR(50) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `rejection_reason` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `approved_at` DATETIME DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `returned_at` DATETIME DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `odometer_out` INT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `odometer_in` INT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `fuel_level` VARCHAR(20) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `damage_description` TEXT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `damage_severity` VARCHAR(20) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `damage_charge` DECIMAL(10,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `discount` DECIMAL(10,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `final_total` DECIMAL(10,2) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `settled_at` DATETIME DEFAULT NULL;

ALTER TABLE `reviews` ADD UNIQUE KEY IF NOT EXISTS `uq_review` (`reviewer_name`, `review_date`);

-- ---------- 4. Seed data (INSERT IGNORE = never duplicates) ----------
INSERT IGNORE INTO `categories` (`id`,`slug`,`name`,`description`) VALUES
(1,'suv','7-Seater Premium SUV','High ground clearance and rugged luxury'),
(2,'sedan','Sport & Executive Sedan','Aerodynamic urban efficiency'),
(3,'van','Executive Van & MPV','First-class group travel shuttles');

INSERT IGNORE INTO `vehicles` (`id`,`category_id`,`make`,`model`,`tag_label`,`plate_number`,`daily_rate`,`weekly_rate`,`transmission`,`fuel_type`,`seats`,`status`,`image_url`,`description`) VALUES
(1,1,'Toyota','Fortuner LTD 2.8L','4x4 LTD Flagship','NQM 5520',4200.00,26500.00,'Automatic (6-Speed)','2.8L Diesel Turbo',7,'available','https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&q=80&w=800','Top-tier performance with premium leather captain seats and JBL audio system.'),
(2,2,'Honda','Civic RS Turbo','RS Turbo Edition','CBX 1092',3500.00,22000.00,'Automatic (CVT Sport)','1.5L Turbo Petrol',5,'available','https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?auto=format&fit=crop&q=80&w=800','Agile handling with Honda SENSING suite, wireless CarPlay, and sports paddle shifters.'),
(3,1,'Mitsubishi','Montero Sport GT','Family Adventure SUV','ABC 8891',3900.00,24500.00,'Automatic (8-Speed)','2.4L MIVEC Diesel',7,'available','https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&q=80&w=800','High ground clearance, 8-speed automatic, and spacious 3-row comfort for long road trips.'),
(4,3,'Toyota','HiAce Grandia Elite','Executive VIP Van','NQD 4018',5800.00,36000.00,'Automatic (6-Speed)','2.8L 1GD-FTV Diesel',10,'available','https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&q=80&w=800','First-class individual reclining Ottoman seats, dual power sliding doors, whisper-quiet cabin.'),
(5,2,'Mazda','3 Fastback Premium','Hybrid Compact Sedan','XYZ 3310',3200.00,20000.00,'Automatic (SKYACTIV)','2.0L e-SkyactivG Petrol',5,'available','https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80&w=800','Japanese mastercraft interior, heads-up display, and Bose 12-speaker surround sound.'),
(6,1,'Ford','Everest Titanium+ 4x4','Luxury Off-Road SUV','FED 9942',4600.00,29000.00,'Automatic (10-Speed)','2.0L Bi-Turbo Diesel',7,'available','https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&q=80&w=800','Bi-Turbo power with panoramic moonroof, 12-inch portrait SYNC display, and 360 camera.');

-- Demo accounts. Password for all three is: password123  (change it after the demo)
INSERT IGNORE INTO `users` (`id`,`first_name`,`last_name`,`email`,`phone`,`password`,`role`) VALUES
(1,'Carlos','Alvarez','carlos@drivesphere.ph','+63 917 123 4567','$2b$12$i3p0RX.uiFmcDFt.0xxGy.Or1So83VNHhlKdUHE4OR9G5DRdaAzbq','customer'),
(2,'System','Admin','admin@drivesphere.ph','+63 917 888 0000','$2b$12$i3p0RX.uiFmcDFt.0xxGy.Or1So83VNHhlKdUHE4OR9G5DRdaAzbq','admin'),
(3,'Maria','Santos','maria@drivesphere.ph','+63 917 555 0101','$2b$12$i3p0RX.uiFmcDFt.0xxGy.Or1So83VNHhlKdUHE4OR9G5DRdaAzbq','customer'),
(4,'Eduardo','Reyes','eduardo@drivesphere.ph','+63 917 554 9021','$2b$12$i3p0RX.uiFmcDFt.0xxGy.Or1So83VNHhlKdUHE4OR9G5DRdaAzbq','customer');

-- The old seed hash was not a real hash of "password123"; replace it.
UPDATE `users` SET `password`='$2b$12$i3p0RX.uiFmcDFt.0xxGy.Or1So83VNHhlKdUHE4OR9G5DRdaAzbq' WHERE `password` LIKE '$2y$10$e81qg8Q/O1vGTh10S3vO8e5%';

INSERT IGNORE INTO `merchant_profile` (`id`,`merchant_name`,`contact_name`,`phone`,`email`,`address`) VALUES
(1,'DriveSphere Mobility','System Admin','+63 2 8888 3748','support@drivesphere.ph','BGC, Taguig City, Philippines');

INSERT IGNORE INTO `reviews` (`reviewer_name`,`reviewer_title`,`rating`,`vehicle_model`,`service_type`,`review_text`,`location_tag`,`perk_tag`,`tag_type`,`review_date`) VALUES
('Atty. Rafael Mendoza','Managing Partner, Makati Law Firm',5.0,'Toyota HiAce Super Grandia Elite','Chauffeur','DriveSphere provided our visiting ASEAN delegates with flawless chauffeur transit from NAIA Terminal 3 to BGC and Makati. The van was immaculate and driver was punctual.','NAIA Terminal 3 Handover','Verified Corporate Contract','corporate','2026-05-10'),
('Maria Cristina Santos','Tech Founder, BGC',5.0,'Toyota Fortuner LTD 2.8 4x4','Digital Key','Rented the Fortuner for a 4-day corporate expedition to Clark and Subic. Contactless handover took under 3 minutes via digital agreement. Full tank and zero hidden fees.','Clark Freeport Corridor','Zero Surcharge Confirmed','self','2026-05-06');

-- Demo rentals so every dashboard page has data (dates are relative to today)
INSERT IGNORE INTO `reservations`
 (`reference_code`,`user_id`,`vehicle_id`,`pickup_location`,`return_location`,`pickup_date`,`return_date`,`days`,`base_amount`,`vat_amount`,`total_price`,`deposit_amount`,`driver_name`,`driver_phone`,`driver_license`,`status`,`odometer_out`)
VALUES
('DS-RN-2026-0001',1,1,'BGC Flagship Station','BGC Flagship Station',DATE_SUB(NOW(),INTERVAL 1 DAY),DATE_ADD(NOW(),INTERVAL 2 DAY),3,12600,1512,14112,5000,'Carlos Alvarez','+63 917 123 4567','N02-14-048291','en_route',12000),
('DS-RN-2026-0002',1,2,'NAIA Terminal 3','NAIA Terminal 3',DATE_ADD(NOW(),INTERVAL 5 DAY),DATE_ADD(NOW(),INTERVAL 8 DAY),3,10500,1260,11760,5000,'Carlos Alvarez','+63 917 123 4567','N02-14-048291','reserved',NULL),
('DS-RN-2026-0003',1,3,'Makati Central Hub','Makati Central Hub',DATE_SUB(NOW(),INTERVAL 20 DAY),DATE_SUB(NOW(),INTERVAL 17 DAY),3,11700,1404,13104,5000,'Carlos Alvarez','+63 917 123 4567','N02-14-048291','completed',12000),
('DS-RN-2026-0004',1,4,'BGC Flagship Station','BGC Flagship Station',DATE_ADD(NOW(),INTERVAL 10 DAY),DATE_ADD(NOW(),INTERVAL 12 DAY),2,11600,1392,12992,5000,'Carlos Alvarez','+63 917 123 4567','N02-14-048291','pending',NULL),
('DS-RN-2026-0005',3,6,'Clark Freeport','Clark Freeport',DATE_SUB(NOW(),INTERVAL 4 DAY),DATE_SUB(NOW(),INTERVAL 2 DAY),2,9200,1104,10304,5000,'Maria Santos','+63 917 555 0101','N03-18-120044','returned',12000),
('DS-RN-2026-0006',4,5,'Ortigas Center','Ortigas Center',DATE_ADD(NOW(),INTERVAL 3 DAY),DATE_ADD(NOW(),INTERVAL 5 DAY),2,6400,768,7168,5000,'Eduardo Reyes','+63 917 554 9021','N02-14-048291','pending',NULL);

UPDATE `reservations` SET `returned_at`=DATE_SUB(NOW(),INTERVAL 17 DAY), `odometer_in`=12450, `fuel_level`='Full (8/8)',
  `damage_description`='Minor surface scuff on rear bumper.', `damage_severity`='Minor', `damage_charge`=1850, `discount`=0,
  `final_total`=14954, `settled_at`=DATE_SUB(NOW(),INTERVAL 17 DAY)
  WHERE `reference_code`='DS-RN-2026-0003' AND `final_total` IS NULL;
UPDATE `reservations` SET `returned_at`=DATE_SUB(NOW(),INTERVAL 2 DAY), `odometer_in`=12380, `fuel_level`='Full (8/8)'
  WHERE `reference_code`='DS-RN-2026-0005' AND `returned_at` IS NULL;
UPDATE `vehicles` SET `status`='rented' WHERE `id`=1 AND `status`='available'
  AND EXISTS (SELECT 1 FROM `reservations` WHERE `reference_code`='DS-RN-2026-0001' AND `status`='en_route');

INSERT INTO `notifications` (`user_id`,`audience`,`type`,`title`,`message`,`link`)
SELECT 1,'customer','booking','Booking Confirmed — Toyota Fortuner LTD 2.8L','Your reservation DS-RN-2026-0001 has been approved by DriveSphere Fleet Command.','customer/receipt.php?ref=DS-RN-2026-0001'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `notifications`);
INSERT INTO `notifications` (`user_id`,`audience`,`type`,`title`,`message`,`link`)
SELECT NULL,'admin','booking','Rental Request Awaiting Approval','Eduardo Reyes requested the Mazda 3 Fastback Premium (DS-RN-2026-0006).','admin/requests.php'
FROM DUAL WHERE (SELECT COUNT(*) FROM `notifications` WHERE `audience`='admin')=0;
