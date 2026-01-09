<?php
header("Content-Type: application/json");
session_start();
include "../PHP/Config.php";

if (!isset($_SESSION['rw'])) {
    echo json_encode(["error" => "Session RW tidak ditemukan"]);
    exit;
}

$rw = $_SESSION['rw'];

/* ================= ABSENSI ================= */
$absensi = [];
$sql = "
SELECT DATE(s.date) AS tgl,
       SUM(CASE WHEN a.attendanceStatus='Hadir' THEN 1 ELSE 0 END) AS hadir,
       SUM(CASE WHEN a.attendanceStatus='Absen' THEN 1 ELSE 0 END) AS tidak_hadir
FROM attendances a
JOIN schedule s ON a.scheduleId = s.scheduleId
WHERE s.rw = '$rw'
GROUP BY DATE(s.date)
ORDER BY DATE(s.date) ASC";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc())
    $absensi[] = $row;

/* ================= LAPORAN ================= */
$laporan = [];
$sql = "
SELECT DATE(time) AS tgl,
SUM(type='pencurian') AS pencurian,
SUM(type='pembunuhan') AS pembunuhan,
SUM(type='kecelakaan') AS kecelakaan,
SUM(type='kebakaran') AS kebakaran,
SUM(type='lainnya') AS lainnya
FROM reports
WHERE rw = '$rw'
GROUP BY DATE(time)
ORDER BY DATE(time) ASC";
$res = $conn->query($sql);
while ($r = $res->fetch_assoc()) {
    $r["total"] = $r["pencurian"] + $r["pembunuhan"] + $r["kecelakaan"] + $r["kebakaran"] + $r["lainnya"];
    $laporan[] = $r;
}

/* ================= STATUS ================= */
$status = [];
$lastStatus = "AMAN";

$sql = "
SELECT DATE(date) AS tgl, status
FROM envistatus
WHERE rw = '$rw'
ORDER BY date ASC";
$res = $conn->query($sql);
while ($r = $res->fetch_assoc()) {
    $angka = ($r['status'] == "BAHAYA" ? 1 : ($r['status'] == "WASPADA" ? 2 : 3));
    $lastStatus = $r['status'];
    $status[] = ["tgl" => $r['tgl'], "status_angka" => $angka, "status_teks" => $r['status']];
}

/* ================= PENGAJUAN ================= */
$pengajuan = [];
$sql = "
SELECT DATE(date) AS tgl,
SUM(type='izin_ikut') AS ikut,
SUM(type='izin_tidak_ikut') AS tidak_ikut
FROM submission
WHERE rw = '$rw'
GROUP BY DATE(date)
ORDER BY DATE(date) ASC";
$res = $conn->query($sql);
while ($r = $res->fetch_assoc()) {
    $r["total"] = $r["ikut"] + $r["tidak_ikut"];
    $pengajuan[] = $r;
}

/* ================= OUTPUT ================= */
echo json_encode([
    "absensi" => $absensi,
    "laporan" => $laporan,
    "status" => $status,
    "lastStatus" => $lastStatus,
    "pengajuan" => $pengajuan
]);
exit;

?>