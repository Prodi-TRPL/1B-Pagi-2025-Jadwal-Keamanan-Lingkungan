<?php
session_start();
include "../Config.php";

// Pastikan RW ada di session
if (!isset($_SESSION['rw'])) {
    die("ERROR: RW tidak ditemukan di session. Silakan login ulang.");
}

$rwSession = $_SESSION['rw']; // RW pengelola

// Ambil rute berdasarkan RW dari tabel route
$ruteQuery = mysqli_query(
    $conn,
    "SELECT DISTINCT routeName 
     FROM route 
     WHERE rw = '$rwSession'
     ORDER BY routeName ASC"
);

while ($r = mysqli_fetch_assoc($ruteQuery)) {
    echo "<th>" . htmlspecialchars($r['routeName']) . "</th>";
}
?>
