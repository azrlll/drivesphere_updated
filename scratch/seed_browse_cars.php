<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getDBConnection();

$newVehicles = [
    [
        'category_id' => 2,
        'make' => 'Toyota',
        'model' => 'Vios 1.5 G',
        'tag_label' => 'City Economy Sedan',
        'plate_number' => 'NDM 1520',
        'daily_rate' => 1800.00,
        'weekly_rate' => 11500.00,
        'transmission' => 'Automatic (CVT 7-Speed)',
        'fuel_type' => '1.5L Petrol Dual VVT-i',
        'seats' => 5,
        'status' => 'available',
        'image_url' => 'images/browse_vios.png',
        'description' => 'Agile, fuel-efficient daily city commuter with Dual VVT-i and touchscreen infotainment.'
    ],
    [
        'category_id' => 3,
        'make' => 'Toyota',
        'model' => 'Innova 2.8 V',
        'tag_label' => 'Family MPV',
        'plate_number' => 'NBQ 2800',
        'daily_rate' => 2800.00,
        'weekly_rate' => 17500.00,
        'transmission' => 'Automatic (6-Speed)',
        'fuel_type' => '2.8L Common Rail Diesel',
        'seats' => 8,
        'status' => 'available',
        'image_url' => 'images/browse_innova.png',
        'description' => 'Spacious 8-passenger family MPV with versatile luggage space and rear climate controls.'
    ],
    [
        'category_id' => 3,
        'make' => 'Hyundai',
        'model' => 'Staria Premium',
        'tag_label' => 'VIP Lounge MPV',
        'plate_number' => 'NCS 5200',
        'daily_rate' => 5200.00,
        'weekly_rate' => 32000.00,
        'transmission' => 'Automatic (8-Speed)',
        'fuel_type' => '2.2L CRDi Diesel Turbo',
        'seats' => 7,
        'status' => 'rented',
        'image_url' => 'images/browse_staria.png',
        'description' => 'Futuristic VIP luxury lounge MPV with premium relaxation captain seats and dual sunroofs.'
    ]
];

foreach ($newVehicles as $v) {
    $exists = $pdo->prepare("SELECT id FROM vehicles WHERE make = ? AND model = ?");
    $exists->execute([$v['make'], $v['model']]);
    if (!$exists->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO vehicles (category_id, make, model, tag_label, plate_number, daily_rate, weekly_rate, transmission, fuel_type, seats, status, image_url, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $v['category_id'], $v['make'], $v['model'], $v['tag_label'], $v['plate_number'],
            $v['daily_rate'], $v['weekly_rate'], $v['transmission'], $v['fuel_type'],
            $v['seats'], $v['status'], $v['image_url'], $v['description']
        ]);
        echo "Inserted: " . $v['make'] . " " . $v['model'] . "\n";
    } else {
        echo "Already exists: " . $v['make'] . " " . $v['model'] . "\n";
    }
}
