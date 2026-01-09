<?php
session_start();
include "../Config.php";

// Pastikan RW ada di session
if (!isset($_SESSION['rw'])) {
    die("ERROR: RW tidak ditemukan di session. Silakan login ulang.");
}

$rwSession = $_SESSION['rw']; // RW pengelola

$year   = $_GET['year']   ?? date('Y');
$month  = $_GET['month']  ?? date('m');
$week   = $_GET['week']   ?? '';
$hari   = $_GET['hari']   ?? '';
$route  = $_GET['route']  ?? '';


// --- KONVERSI MINGGU ---
$whereWeek = "";
if ($week !== '') {

    $weekNumber = intval(str_replace('Minggu ', '', $week));

    // Hitung rentang minggu ISO
    $startDate = date("Y-m-d", strtotime($year . "W" . str_pad($weekNumber, 2, "0", STR_PAD_LEFT)));
    $endDate   = date("Y-m-d", strtotime($startDate . " +6 days"));

    $whereWeek = " AND attendances.date BETWEEN '$startDate' AND '$endDate' ";
}


// --- QUERY FINAL ---
$query = "
    SELECT 
        inhabitants.name,
        attendances.attendanceStatus,
        attendances.date,
        schedule.route
    FROM attendances
    JOIN inhabitants ON attendances.nik = inhabitants.nik
    JOIN schedule ON attendances.scheduleId = schedule.scheduleId
    WHERE schedule.rw = '$rwSession'              -- ⬅ hanya data RW login
      AND inhabitants.rw = '$rwSession'           -- ⬅ warga RW login saja
      AND YEAR(attendances.date) = '$year'
      AND MONTH(attendances.date) = '$month'
      $whereWeek
";

if ($hari !== '') {
    $query .= " AND DATE_FORMAT(attendances.date, '%W') = '$hari' ";
}

if ($route !== '') {
    $query .= " AND schedule.route = '$route' ";
}

$query .= " ORDER BY schedule.route, attendances.date ASC ";

$result = mysqli_query($conn, $query);


// --- OUTPUT ---
echo "<tr><td>";

while ($row = mysqli_fetch_assoc($result)) {

    $name   = $row['name'];
    $status = $row['attendanceStatus'];

    echo "
        <div style='display:flex; justify-content:space-between;'>
            <span>$name</span>
            <span style='color:#9ED2C6; font-weight:bold;'>$status</span>
        </div>
    ";
}

echo "</td></tr>";
?>
