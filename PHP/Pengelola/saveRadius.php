<?php
session_start();
include "../Config.php";

// Ambil RW dari session
if (!isset($_SESSION['rw'])) {
    echo json_encode(['success' => false, 'message' => 'Session RW tidak ditemukan']);
    exit;
}

$rw = $_SESSION['rw']; // ← RW fix dari sesi, tidak bisa diubah user

// Get raw JSON
$data = json_decode(file_get_contents('php://input'), true);

// Ambil data lain (tanpa RW karena sudah dari session)
$lat    = $data['lat'] ?? null;
$lng    = $data['lng'] ?? null;
$radius = $data['radius'] ?? null;
$minAge = $data['minAge'] ?? null;
$maxAge = $data['maxAge'] ?? null;
$timeStart = $data['timeStart'] ?? null;
$timeEnd = $data['timeEnd'] ?? null;

// Validasi
if (!$lat || !$lng || !$radius) {
    echo json_encode(['success' => false, 'message' => 'Data radius tidak lengkap']);
    exit;
}
if ($minAge === null || $maxAge === null) {
    echo json_encode(['success' => false, 'message' => 'Data umur tidak lengkap']);
    exit;
}

/* =====================
   SIMPAN DATA RADIUS
===================== */
$stmt = $conn->prepare("
    INSERT INTO radius (rw, latitude, longitude, radius)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        latitude = VALUES(latitude),
        longitude = VALUES(longitude),
        radius = VALUES(radius)
");
$stmt->bind_param("sddi", $rw, $lat, $lng, $radius);
$stmt->execute();
$stmt->close();

/* =====================
   SIMPAN DATA PENGATURAN
===================== */
$stmt2 = $conn->prepare("
    INSERT INTO requirements (rw, minAge, maxAge, timeStart, timeEnd)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        minAge = VALUES(minAge),
        maxAge = VALUES(maxAge),
        timeStart = VALUES(timeStart),
        timeEnd = VALUES(timeEnd)
");
$stmt2->bind_param("iiiss", $rw, $minAge, $maxAge, $timeStart, $timeEnd);
$stmt2->execute();

/* ============================
   AUTO PURGE ATTENDANCE
============================ */

// Ambil semua attendance RW ini
$purge = $conn->query("
    SELECT a.attendanceId, a.nik, i.dateBirth
    FROM attendances a
    JOIN schedule s ON a.scheduleId = s.scheduleId
    JOIN inhabitants i ON a.nik = i.nik
    WHERE s.rw = '$rw'
");

$toDelete = [];

while ($r = $purge->fetch_assoc()) {
    $age = date_diff(date_create($r['dateBirth']), date_create('today'))->y;
    if ($age < $minAge || $age > $maxAge) {
        $toDelete[] = $r['attendanceId'];
    }
}

if (count($toDelete) > 0) {
    $ids = implode(',', $toDelete);
    $conn->query("DELETE FROM attendances WHERE attendanceId IN ($ids)");
}


$stmt2->close();



$conn->close();

echo json_encode(['success' => true]);
