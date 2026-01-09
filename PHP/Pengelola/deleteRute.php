<?php
session_start();
include "../Config.php";

/* ===================== AMBIL RW DARI SESSION ===================== */
if (!isset($_SESSION['rw'])) {
    echo json_encode(['success' => false, 'message' => 'RW tidak ditemukan dalam session.']);
    exit;
}
$rw = $_SESSION['rw'];

/* ===================== VALIDASI routeId ===================== */
$routeId = $_POST['routeId'] ?? null;

if (!$routeId) {
    echo json_encode(['success' => false, 'message' => 'ID rute tidak ditemukan']);
    exit;
}

/* ===================== AMBIL routeName & VALIDASI RW ===================== */
$stmt = $conn->prepare("SELECT routeName, rw FROM route WHERE routeId = ?");
$stmt->bind_param("i", $routeId);
$stmt->execute();
$stmt->bind_result($routeName, $routeRw);

if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Rute tidak ditemukan']);
    exit;
}
$stmt->close();

/* ===================== CEK : RUTE HARUS SESUAI RW USER ===================== */
if ($routeRw != $rw) {
    echo json_encode(['success' => false, 'message' => 'Anda tidak berhak menghapus rute RW lain.']);
    exit;
}

/* ===================== HAPUS attendance berdasarkan schedule ===================== */
$stmt = $conn->prepare("
    DELETE a 
    FROM attendances a
    INNER JOIN schedule s ON a.scheduleId = s.scheduleId
    WHERE s.route = ? AND s.rw = ?
");
$stmt->bind_param("si", $routeName, $rw);
$stmt->execute();
$stmt->close();

/* ===================== HAPUS SCHEDULE dari rute ini ===================== */
$stmt = $conn->prepare("DELETE FROM schedule WHERE route = ? AND rw = ?");
$stmt->bind_param("si", $routeName, $rw);
$stmt->execute();
$stmt->close();

/* ===================== HAPUS ROUTE ===================== */
$stmt = $conn->prepare("DELETE FROM route WHERE routeId = ? AND rw = ?");
$stmt->bind_param("ii", $routeId, $rw);

if ($stmt->execute()) {
    $stmt->close();

    /* ===================== REDIRECT TANPA RW PADA URL ===================== */
    $year  = $_POST['year'] ?? date('Y');
    $month = $_POST['month'] ?? date('m');
    $week  = $_POST['week'] ?? 1;
    $day   = $_POST['day'] ?? 'Monday';

    header("Location: ../../UI/Pengelola/Jadwal.php?year=$year&month=$month&week=$week&day=$day");
    exit;
} else {
    echo "Gagal menghapus rute: " . $conn->error;
}
?>
