<?php
// Temporary script to update company logos - DELETE AFTER USE
require_once __DIR__ . '/../src/bootstrap.php';

$ts = 1700000000;
$updates = [
    1 => "logo_1_{$ts}.png",   // FPT Software
    3 => "logo_3_{$ts}.png",   // Tiki
    4 => "logo_4_{$ts}.png",   // Shopee
    5 => "logo_5_{$ts}.png",   // Grab
    6 => "logo_6_{$ts}.png",   // MoMo
    7 => "logo_7_{$ts}.png",   // VNPT
    8 => "logo_8_{$ts}.png",   // Viettel
];

$pdo = db();
$stmt = $pdo->prepare("UPDATE companies SET logo = ? WHERE id = ?");
foreach ($updates as $id => $logo) {
    $stmt->execute([$logo, $id]);
    echo "Updated company $id → $logo (rows: {$stmt->rowCount()})<br>\n";
}

// Add logo column to employer_requests if not exists
try {
    $pdo->exec("ALTER TABLE employer_requests ADD COLUMN company_logo VARCHAR(255) NULL AFTER company_website");
    echo "Added company_logo column to employer_requests<br>\n";
} catch (PDOException $e) {
    echo "Column may already exist: " . $e->getMessage() . "<br>\n";
}

echo "<br><strong>Done! Delete this file now.</strong>";
