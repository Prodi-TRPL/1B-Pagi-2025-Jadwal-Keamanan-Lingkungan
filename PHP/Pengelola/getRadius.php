<?php
session_start();
include "../Config.php";

// Pastikan RW tersedia di session
if (!isset($_SESSION['rw'])) {
    echo json_encode([]);
    exit;
}

$rw = $_SESSION['rw']; // RW dari session pengguna login

/* =====================
   AMBIL DATA RADIUS
===================== */
$stmt = $conn->prepare("SELECT latitude, longitude, radius FROM radius WHERE rw = ?");
$stmt->bind_param("s", $rw);
$stmt->execute();
$result = $stmt->get_result();
$radiusData = $result->fetch_assoc() ?? [];
$stmt->close();

/* =====================
   AMBIL DATA
===================== */
$stmt2 = $conn->prepare("SELECT minAge, maxAge, timeStart, timeEnd FROM requirements WHERE rw = ?");
$stmt2->bind_param("s", $rw);
$stmt2->execute();
$result2 = $stmt2->get_result();
$ageData = $result2->fetch_assoc() ?? [];
$stmt2->close();

$conn->close();

/* =====================
   GABUNGKAN DAN KIRIM
===================== */
echo json_encode(array_merge($radiusData, $ageData));
