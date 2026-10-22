-- ==========================================================================
-- DriveSphere Fleet Management System
-- 30 Realistic Philippine Vehicles Database Migration & Seeder
-- Suitable for direct import into phpMyAdmin
-- ==========================================================================

-- 1. Ensure categories table includes all 6 primary vehicle categories
INSERT IGNORE INTO `categories` (`id`, `slug`, `name`, `description`) VALUES
(1, 'suv', 'SUV & Crossover', 'Rugged 7-Seater SUVs and Compact Crossovers'),
(2, 'sedan', 'Sedan', 'Fuel-Efficient Urban and Executive Sedans'),
(3, 'van', 'Van', 'High-Capacity Passenger Shuttles and Vans'),
(4, 'hatchback', 'Hatchback', 'Economical and Agile City Hatchbacks'),
(5, 'mpv', 'MPV', 'Multi-Purpose and 7-Seater Family Vehicles'),
(6, 'pickup', 'Pickup', 'Heavy-Duty 4x4 Utility Pickups')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`);

-- 2. Ensure vehicles table has all required columns
ALTER TABLE `vehicles`
  ADD COLUMN IF NOT EXISTS `brand` VARCHAR(50) NOT NULL DEFAULT '' AFTER `category_id`,
  ADD COLUMN IF NOT EXISTS `year` INT(4) NOT NULL DEFAULT 2024 AFTER `model`,
  ADD COLUMN IF NOT EXISTS `category` VARCHAR(50) NOT NULL DEFAULT 'Sedan' AFTER `year`,
  ADD COLUMN IF NOT EXISTS `seating_capacity` INT(11) NOT NULL DEFAULT 5 AFTER `fuel_type`,
  ADD COLUMN IF NOT EXISTS `image_path` VARCHAR(255) NOT NULL DEFAULT '' AFTER `daily_rate`;

-- 3. Insert or Update all 30 Philippine Vehicles
INSERT INTO `vehicles` (
  `id`, `category_id`, `brand`, `make`, `model`, `year`, `category`,
  `tag_label`, `plate_number`, `daily_rate`, `image_path`, `weekly_rate`,
  `transmission`, `fuel_type`, `seating_capacity`, `seats`, `status`,
  `image_url`, `description`, `odometer`
) VALUES
(1, 1, 'Toyota', 'Toyota', 'Fortuner 2.8 4x4 LTD', 2024, 'SUV', '4x4 Flagship SUV', 'NQM 5520', 4200.00, 'assets/images/vehicles/toyota-fortuner.jpg', 26500.00, 'Automatic', 'Diesel', 7, 7, 'available', 'assets/images/vehicles/toyota-fortuner.jpg', 'Flagship 7-seater midsize SUV with 2.8L turbo-diesel engine, leather captain chairs, JBL premium audio, and rugged off-road readiness.', 12500),
(2, 2, 'Honda', 'Honda', 'City 1.5 RS', 2024, 'Sedan', 'Executive Sport Sedan', 'NAK 5821', 2200.00, 'assets/images/vehicles/honda-city.jpg', 14000.00, 'Automatic', 'Gasoline', 5, 5, 'available', 'assets/images/vehicles/honda-city.jpg', 'Sophisticated executive styling with Honda SENSING driver-assist features, leather-trimmed cabin, and agile VTEC performance.', 8400),
(3, 1, 'Mitsubishi', 'Mitsubishi', 'Montero Sport GT 4WD', 2024, 'SUV', 'Family Adventure SUV', 'ABC 8891', 3900.00, 'assets/images/vehicles/mitsubishi-montero-sport.jpg', 24500.00, 'Automatic', 'Diesel', 7, 7, 'available', 'assets/images/vehicles/mitsubishi-montero-sport.jpg', 'Dynamic shield design with 8-speed automatic transmission, Super Select 4WD-II, and spacious 3-row family comfort.', 14100),
(4, 3, 'Toyota', 'Toyota', 'HiAce GL Grandia', 2024, 'Van', 'Executive Passenger Van', 'NEE 9012', 4800.00, 'assets/images/vehicles/toyota-hiace.jpg', 30000.00, 'Automatic', 'Diesel', 12, 12, 'available', 'assets/images/vehicles/toyota-hiace.jpg', 'Spacious 12-passenger van with high roof, rear individual AC vents, wide sliding door, and heavy-duty 2.8L turbo-diesel engine.', 19300),
(5, 4, 'Suzuki', 'Suzuki', 'Swift 1.2 GL', 2024, 'Hatchback', 'Sport Compact Hatch', 'NCU 9481', 1700.00, 'assets/images/vehicles/suzuki-swift.jpg', 10800.00, 'Automatic', 'Gasoline', 5, 5, 'available', 'assets/images/vehicles/suzuki-swift.jpg', 'European-inspired sporty hatchback with a wide stance, sharp road feedback, and energetic driving dynamics.', 7600),
(6, 1, 'Ford', 'Ford', 'Everest Titanium+ 4x4', 2024, 'SUV', 'Luxury 7-Seater 4x4', 'NFG 7392', 4500.00, 'assets/images/vehicles/ford-everest.jpg', 28500.00, 'Automatic', 'Diesel', 7, 7, 'available', 'assets/images/vehicles/ford-everest.jpg', 'Luxurious 7-seater SUV equipped with Bi-Turbo diesel, 12-inch SYNC 4A screen, panoramic moonroof, and adaptive cruise control.', 11200),
(7, 1, 'Nissan', 'Nissan', 'Terra 2.5 VL 4x2', 2023, 'SUV', 'Executive 7-Seater', 'NEU 8104', 3800.00, 'assets/images/vehicles/nissan-terra.jpg', 24000.00, 'Automatic', 'Diesel', 7, 7, 'available', 'assets/images/vehicles/nissan-terra.jpg', 'Zero Gravity seats, rear flip-down monitor for kids, and Bose 8-speaker audio system for serene family road trips.', 16500),
(8, 2, 'Toyota', 'Toyota', 'Vios 1.5 G', 2024, 'Sedan', 'City Economy Sedan', 'NQM 2419', 1800.00, 'assets/images/vehicles/toyota-vios.jpg', 11500.00, 'Automatic', 'Gasoline', 5, 5, 'available', 'assets/images/vehicles/toyota-vios.jpg', 'The Philippines\' most popular subcompact sedan. Renowned for supreme fuel economy, smooth CVT, and reliable air conditioning.', 9200),
(9, 2, 'Mitsubishi', 'Mitsubishi', 'Mirage G4 GLS', 2023, 'Sedan', 'Compact City Sedan', 'DAZ 7193', 1600.00, 'assets/images/vehicles/mitsubishi-mirage-g4.jpg', 10200.00, 'Automatic', 'Gasoline', 5, 5, 'available', 'assets/images/vehicles/mitsubishi-mirage-g4.jpg', 'Highly economical sedan with generous rear legroom and large trunk space, ideal for city commuting and budget airport transfers.', 18400),
(10, 2, 'Nissan', 'Nissan', 'Almera 1.0 VL Turbo', 2024, 'Sedan', 'Turbocharged Sedan', 'NEB 4902', 2000.00, 'assets/images/vehicles/nissan-almera.jpg', 12800.00, 'Automatic', 'Gasoline', 5, 5, 'available', 'assets/images/vehicles/nissan-almera.jpg', 'Punchy 1.0L turbocharged engine with Nissan Intelligent Mobility 360-degree camera, wireless Apple CarPlay, and plush seating.', 6100),
(11, 2, 'Suzuki', 'Suzuki', 'Dzire GL+ AGS', 2023, 'Sedan', 'Efficient Subcompact', 'CAO 8312', 1500.00, 'assets/images/vehicles/suzuki-dzire.jpg', 9500.00, 'Automatic', 'Gasoline', 5, 5, 'available', 'assets/images/vehicles/suzuki-dzire.jpg', 'Ultra-efficient compact sedan featuring Suzuki Auto Gear Shift technology and rear AC vents for passenger comfort.', 15200),
(12, 2, 'Hyundai', 'Hyundai', 'Accent 1.6 CRDi', 2022, 'Sedan', 'Diesel Commuter Sedan', 'NBS 6284', 1900.00, 'assets/images/vehicles/hyundai-accent.jpg', 12000.00, 'Automatic', 'Diesel', 5, 5, 'available', 'assets/images/vehicles/hyundai-accent.jpg', 'Durable turbo-diesel sedan delivering high torque, low highway fuel consumption, and comfortable long-distance cruising.', 28400),
(13, 4, 'Toyota', 'Toyota', 'Wigo 1.0 G', 2024, 'Hatchback', 'Urban Micro Hatch', 'DAR 3910', 1400.00, 'assets/images/vehicles/toyota-wigo.jpg', 8900.00, 'Automatic', 'Gasoline', 5, 5, 'available', 'assets/images/vehicles/toyota-wigo.jpg', 'Modern urban hatchback featuring sharp styling, easy maneuverability in tight city alleys, and touchscreen infotainment.', 5300),
(14, 4, 'Mitsubishi', 'Mitsubishi', 'Mirage GLX', 2023, 'Hatchback', 'Fuel-Saver Hatch', 'CBC 1928', 1350.00, 'assets/images/vehicles/mitsubishi-mirage.jpg', 8600.00, 'Automatic', 'Gasoline', 5, 5, 'available', 'assets/images/vehicles/mitsubishi-mirage.jpg', 'Nimble 5-door hatchback with tight turning radius, responsive steering, and impressive kilometer-per-liter city numbers.', 17100),
(15, 4, 'Honda', 'Honda', 'Brio 1.2 RS Black Top', 2024, 'Hatchback', 'Sport City Hatch', 'NDL 3025', 1750.00, 'assets/images/vehicles/honda-brio.jpg', 11200.00, 'Automatic', 'Gasoline', 5, 5, 'available', 'assets/images/vehicles/honda-brio.jpg', 'Class-leading 4-cylinder 1.2L i-VTEC engine with sporty RS body kit, sharp handling, and reliable Honda build quality.', 8100),
(16, 4, 'Suzuki', 'Suzuki', 'S-Presso GL AGS', 2023, 'Hatchback', 'Mini Crossover Hatch', 'NDY 7741', 1200.00, 'assets/images/vehicles/suzuki-s-presso.jpg', 7600.00, 'Automatic', 'Gasoline', 5, 5, 'available', 'assets/images/vehicles/suzuki-s-presso.jpg', 'Micro-SUV hatchback with 180mm high ground clearance, commanding seating position, and pocket-friendly fuel consumption.', 12900),
(17, 1, 'Toyota', 'Toyota', 'Corolla Cross 1.8 HEV GR-S', 2024, 'SUV', 'Hybrid Compact Crossover', 'NFD 1948', 3200.00, 'assets/images/vehicles/toyota-corolla-cross.jpg', 20000.00, 'Automatic', 'Hybrid', 5, 5, 'available', 'assets/images/vehicles/toyota-corolla-cross.jpg', 'Self-charging hybrid crossover offering ultra-quiet electric drive modes, 23+ km/L fuel efficiency, and Toyota Safety Sense.', 6400),
(18, 1, 'Honda', 'Honda', 'HR-V 1.5 V Turbo', 2024, 'SUV', 'Turbo Compact SUV', 'NGD 6183', 3300.00, 'assets/images/vehicles/honda-hr-v.jpg', 21000.00, 'Automatic', 'Gasoline', 5, 5, 'available', 'assets/images/vehicles/honda-hr-v.jpg', 'Coupe-inspired compact crossover featuring ULT versatile folding rear seats, turbo acceleration, and sleek minimalist interior.', 9100),
(19, 1, 'Geely', 'Geely', 'Coolray Sport 1.5T', 2023, 'SUV', 'Sport Crossover', 'NAN 2850', 2800.00, 'assets/images/vehicles/geely-coolray.jpg', 17800.00, 'Automatic', 'Gasoline', 5, 5, 'available', 'assets/images/vehicles/geely-coolray.jpg', 'High-tech subcompact crossover with automated parking assist, 360-degree camera, panoramic sunroof, and sporty red interior accents.', 14800),
(20, 1, 'Hyundai', 'Hyundai', 'Tucson 2.0 CRDi GLS', 2024, 'SUV', 'Modern Diesel Crossover', 'NHF 7729', 3500.00, 'assets/images/vehicles/hyundai-tucson.jpg', 22000.00, 'Automatic', 'Diesel', 5, 5, 'available', 'assets/images/vehicles/hyundai-tucson.jpg', 'Futuristic geometric exterior design with push-button shifter, heated/ventilated seats, and potent 2.0L diesel engine.', 8900),
(21, 5, 'Toyota', 'Toyota', 'Innova 2.8 V', 2024, 'MPV', 'Preferred Family MPV', 'NFZ 9182', 3000.00, 'assets/images/vehicles/toyota-innova.jpg', 19000.00, 'Automatic', 'Diesel', 7, 7, 'available', 'assets/images/vehicles/toyota-innova.jpg', 'The undisputed king of Philippine family transport. Second-row captain chairs, high-output diesel torque, and unmatched dependability.', 13400),
(22, 5, 'Toyota', 'Toyota', 'Avanza 1.5 G', 2024, 'MPV', '7-Seater City MPV', 'NBL 4019', 2300.00, 'assets/images/vehicles/toyota-avanza.jpg', 14500.00, 'Automatic', 'Gasoline', 7, 7, 'available', 'assets/images/vehicles/toyota-avanza.jpg', 'Front-wheel drive modern 7-seater MPV with long sofa mode seating configuration and smooth CVT transmission.', 10200),
(23, 5, 'Mitsubishi', 'Mitsubishi', 'Xpander Cross', 2024, 'MPV', 'Crossover MPV', 'CAU 3820', 2600.00, 'assets/images/vehicles/mitsubishi-xpander.jpg', 16500.00, 'Automatic', 'Gasoline', 7, 7, 'available', 'assets/images/vehicles/mitsubishi-xpander.jpg', 'Robust crossover MPV styling with 225mm ground clearance, Active Yaw Control (AYC), and multiple USB charging ports across all 3 rows.', 11900),
(24, 5, 'Suzuki', 'Suzuki', 'Ertiga 1.5 GLX Hybrid', 2024, 'MPV', 'Smart Hybrid MPV', 'DAQ 5194', 2400.00, 'assets/images/vehicles/suzuki-ertiga.jpg', 15200.00, 'Automatic', 'Hybrid', 7, 7, 'available', 'assets/images/vehicles/suzuki-ertiga.jpg', 'Smart Hybrid 7-seater family MPV with integrated starter generator, regenerative braking, and lightweight aerodynamic body.', 8700),
(25, 5, 'Toyota', 'Toyota', 'Veloz 1.5 V', 2024, 'MPV', 'Premium 7-Seater MPV', 'NHR 8271', 2800.00, 'assets/images/vehicles/toyota-veloz.jpg', 17800.00, 'Automatic', 'Gasoline', 7, 7, 'available', 'assets/images/vehicles/toyota-veloz.jpg', 'Upmarket 7-seater with ambient lighting, electronic parking brake with auto hold, wireless charging pad, and 360-degree panoramic view monitor.', 7900),
(26, 5, 'Honda', 'Honda', 'BR-V 1.5 VX SENSING', 2024, 'MPV', 'Safety 7-Seater MPV', 'NCS 1953', 2900.00, 'assets/images/vehicles/honda-br-v.jpg', 18500.00, 'Automatic', 'Gasoline', 7, 7, 'available', 'assets/images/vehicles/honda-br-v.jpg', 'Most powerful engine in its class (121 PS), equipped with Honda SENSING suite, leather interior, and exceptional crash safety ratings.', 9800),
(27, 3, 'Nissan', 'Nissan', 'NV350 Urvan Premium', 2024, 'Van', '15-Seater Group Shuttle', 'NDJ 6610', 4500.00, 'assets/images/vehicles/nissan-urvan.jpg', 28000.00, 'Manual', 'Diesel', 15, 15, 'available', 'assets/images/vehicles/nissan-urvan.jpg', 'High-capacity group carrier featuring 15 individual seats, 14 rear air vents, and high-tensile body construction for corporate shuttles.', 21000),
(28, 6, 'Toyota', 'Toyota', 'Hilux Conquest 4x4', 2024, 'Pickup', 'Heavy-Duty 4x4 Pickup', 'NAU 4190', 3800.00, 'assets/images/vehicles/toyota-hilux.jpg', 24000.00, 'Automatic', 'Diesel', 5, 5, 'available', 'assets/images/vehicles/toyota-hilux.jpg', 'Indestructible double-cab utility pickup with Conquest styling, sports bar, bedliner, 4x4 differential lock, and 1-ton payload capacity.', 14500),
(29, 6, 'Ford', 'Ford', 'Ranger Wildtrak 4x4', 2024, 'Pickup', 'Next-Gen 4x4 Pickup', 'NHB 5028', 4200.00, 'assets/images/vehicles/ford-ranger.jpg', 26500.00, 'Automatic', 'Diesel', 5, 5, 'available', 'assets/images/vehicles/ford-ranger.jpg', 'Next-Gen pickup featuring 2.0L Bi-Turbo diesel, 10-speed e-Shifter, digital instrument cluster, 12-inch touchscreen, and zone lighting.', 11800),
(30, 6, 'Mitsubishi', 'Mitsubishi', 'Strada Athlete 4WD', 2023, 'Pickup', 'Dynamic 4WD Pickup', 'CBK 7241', 3600.00, 'assets/images/vehicles/mitsubishi-strada.jpg', 22800.00, 'Automatic', 'Diesel', 5, 5, 'available', 'assets/images/vehicles/mitsubishi-strada.jpg', 'Aggressive orange/black styling, Super Select 4WD, forward collision mitigation, and comfortable coil-spring ride quality.', 15600)
ON DUPLICATE KEY UPDATE
  `category_id` = VALUES(`category_id`),
  `brand` = VALUES(`brand`),
  `make` = VALUES(`make`),
  `model` = VALUES(`model`),
  `year` = VALUES(`year`),
  `category` = VALUES(`category`),
  `tag_label` = VALUES(`tag_label`),
  `plate_number` = VALUES(`plate_number`),
  `daily_rate` = VALUES(`daily_rate`),
  `image_path` = VALUES(`image_path`),
  `weekly_rate` = VALUES(`weekly_rate`),
  `transmission` = VALUES(`transmission`),
  `fuel_type` = VALUES(`fuel_type`),
  `seating_capacity` = VALUES(`seating_capacity`),
  `seats` = VALUES(`seats`),
  `status` = VALUES(`status`),
  `image_url` = VALUES(`image_url`),
  `description` = VALUES(`description`),
  `odometer` = VALUES(`odometer`);
