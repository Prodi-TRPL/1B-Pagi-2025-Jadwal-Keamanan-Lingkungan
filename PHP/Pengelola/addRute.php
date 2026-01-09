<?php
session_start();
include "../Config.php";

$route = $_POST['routeName'] ?? '';
$rw = $_SESSION['rw'];  // ✅ otomatis pakai RW login

if ($route != '') {
    $cek = mysqli_query($conn, 
        "SELECT * FROM route WHERE routeName='$route' AND rw='$rw' LIMIT 1"
    );

    if (mysqli_num_rows($cek) == 0) {
        mysqli_query($conn, 
            "INSERT INTO route (routeName, rw) VALUES ('$route','$rw')"
        );
    }
}

header("Location: ../../UI/Pengelola/Jadwal.php");
exit;
?>
