<?php
session_start();
include "../Config.php";

/* ===================== VALIDASI SESSION ===================== */
if (!isset($_SESSION['rw'])) {
    die("ERROR: RW tidak ditemukan di session. Harap login ulang.");
}
$rw = $_SESSION['rw'];

/* ===================== AMBIL DATA POST ===================== */
$customDate = $_POST['customDate'] ?? '';
$routeName  = $_POST['route'] ?? ''; // DARI FORM berupa routeName
$niks       = $_POST['nik'] ?? [];

/* ===================== VALIDASI ===================== */
if (!$customDate || !$routeName) {
    die("ERROR: Data tidak lengkap!");
}

/* ===================== 1. AMBIL routeId dari tabel route ===================== */
$sqlRoute = "
    SELECT routeId 
    FROM route 
    WHERE routeName='" . mysqli_real_escape_string($conn, $routeName) . "'
";
$resRoute = mysqli_query($conn, $sqlRoute);

if (!$resRoute || mysqli_num_rows($resRoute) == 0) {
    die("ERROR: routeName tidak ditemukan di tabel route!");
}

$route = mysqli_fetch_assoc($resRoute);
$routeId = $route['routeId'];

/* ===================== 2. AMBIL ATAU BUAT scheduleId ===================== */
$sqlSched = "
    SELECT scheduleId 
    FROM schedule 
    WHERE route='$routeName' 
      AND DATE(date)='" . mysqli_real_escape_string($conn, $customDate) . "'
      AND rw='$rw'
";

$resSched = mysqli_query($conn, $sqlSched);

if (!$resSched) {
    die("ERROR QUERY schedule: " . mysqli_error($conn));
}

if (mysqli_num_rows($resSched) == 0) {
    // buat baru
    $sqlInsertSched = "
        INSERT INTO schedule (route, date, rw)
        VALUES ('$routeName', '$customDate', '$rw')
    ";

    if (!mysqli_query($conn, $sqlInsertSched)) {
        die("ERROR INSERT schedule: " . mysqli_error($conn));
    }

    $scheduleId = mysqli_insert_id($conn);

} else {
    // sudah ada
    $data = mysqli_fetch_assoc($resSched);
    $scheduleId = $data['scheduleId'];
}

/* ===================== 3. HAPUS attendances LAMA ===================== */
$sqlDelete = "DELETE FROM attendances WHERE scheduleId='$scheduleId'";
mysqli_query($conn, $sqlDelete);

/* ===================== 4. INSERT attendances BARU ===================== */
foreach ($niks as $nik) {

    $sqlInsertAtt = "
        INSERT INTO attendances (scheduleId, nik, routeId, attendanceStatus)
        VALUES ('$scheduleId', '" . mysqli_real_escape_string($conn, $nik) . "', '$routeId', 'Absen')
    ";

    if (!mysqli_query($conn, $sqlInsertAtt)) {
        die("ERROR INSERT attendance: " . mysqli_error($conn));
    }
}

/* ===================== 5. REDIRECT ===================== */
$year  = date('Y', strtotime($customDate));
$month = date('m', strtotime($customDate));
$day   = date('l', strtotime($customDate));

header("Location: ../../UI/Pengelola/Jadwal.php?year=$year&month=$month&week=1&day=$day");
exit;
?>
