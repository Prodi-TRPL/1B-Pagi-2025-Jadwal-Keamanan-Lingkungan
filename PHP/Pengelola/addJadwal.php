<?php
session_start();
include "../Config.php";

$rw = $_SESSION['rw'];
$date = $_POST['customDate'];
$routeName = $_POST['route'];
$nikArray = $_POST['nik'];

// ==================== 1. Ambil routeId ====================
$sqlRoute = "SELECT routeId FROM route WHERE routeName='$routeName'";
$resRoute = mysqli_query($conn, $sqlRoute);

$route = mysqli_fetch_assoc($resRoute);
$routeId = $route['routeId'];

// ==================== 2. Ambil scheduleId ====================
$sqlCheck = "SELECT scheduleId FROM schedule 
             WHERE date='$date'AND route='$routeName' AND rw='$rw'";
$resCheck = mysqli_query($conn, $sqlCheck);

if (!$resCheck) {
    die("Query Schedule Error: " . mysqli_error($conn) . " | SQL: " . $sqlCheck);
}

if (mysqli_num_rows($resCheck) == 0) {
    $sqlInsert = "INSERT INTO schedule (date, route, rw) 
                  VALUES ('$date', '$routeName', '$rw')";
    if (!mysqli_query($conn, $sqlInsert)) {
        die("Insert Schedule Error: " . mysqli_error($conn) . " | SQL: " . $sqlInsert);
    }
    $scheduleId = mysqli_insert_id($conn);
} else {
    $schedule = mysqli_fetch_assoc($resCheck);
    $scheduleId = $schedule['scheduleId'];
}

// ==================== 3. Hapus attendance lama ====================
$sqlDelete = "DELETE FROM attendances WHERE scheduleId='$scheduleId'";
if (!mysqli_query($conn, $sqlDelete)) {
    die("Delete Attendance Error: " . mysqli_error($conn) . " | SQL: " . $sqlDelete);
}

// ==================== 4. Insert attendance baru ====================
if (!empty($nikArray)) {
    foreach ($nikArray as $nik) {
        $sqlInsertAtt = "INSERT INTO attendances (scheduleId, nik, routeId, attendanceStatus)
                         VALUES ('$scheduleId', '$nik', '$routeId', 'Absen')";
        if (!mysqli_query($conn, $sqlInsertAtt)) {
            die("Insert Attendance Error: " . mysqli_error($conn) . " | SQL: " . $sqlInsertAtt);
        }
    }
}

header("Location: ../../UI/Pengelola/Jadwal.php");
exit;
?>
