<?php
session_start();
include "Config.php";

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Token tidak valid");
}

/* =========================
   1️⃣ CEK INHABITANTS
========================= */
$q = $conn->prepare("
    SELECT inhabitantId, status, rw
    FROM inhabitants
    WHERE resetToken = ?
      AND resetExpire > NOW()
    LIMIT 1
");
$q->bind_param("s", $token);
$q->execute();
$r = $q->get_result();

if ($r->num_rows > 0) {
    $user = $r->fetch_assoc();

    $_SESSION['inhabitants_id'] = $user['inhabitantId'];
    $_SESSION['rw'] = $user['rw'];
    $_SESSION['role'] = $user['status'];

    $del = $conn->prepare("
        UPDATE inhabitants
        SET resetToken = NULL, resetExpire = NULL
        WHERE inhabitantId = ?
    ");
    $del->bind_param("i", $user['inhabitantId']);
    $del->execute();

    $redirect = $user['status'];
    $status = "success";
}


/* =========================
   2️⃣ CEK ADMIN
========================= */
$q = $conn->prepare("
    SELECT adminId, ward
    FROM admin
    WHERE resetToken = ?
      AND resetExpire > NOW()
    LIMIT 1
");
$q->bind_param("s", $token);
$q->execute();
$r = $q->get_result();

if ($r->num_rows > 0) {
    $admin = $r->fetch_assoc();

    $_SESSION['admin_id'] = $admin['adminId'];
    $_SESSION['admin_ward'] = $admin['ward'];
    $_SESSION['role'] = 'Admin';

    $del = $conn->prepare("
        UPDATE admin
        SET resetToken = NULL, resetExpire = NULL
        WHERE adminId = ?
    ");
    $del->bind_param("i", $admin['adminId']);
    $del->execute();

    $redirect = "Admin";
    $status = "success";
}


/* =========================
   3️⃣ TOKEN TIDAK VALID
========================= */
if (!isset($status)) {
    $status = "invalid";
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>TREE</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../CSS/Main.css">
    <style>
        * {
            font-family: 'inter', 'sans';
        }

        body {
            background-color: var(--Text4);
        }

    </style>
</head>

<body>

    <script>
        const status = "<?= $status ?>";
        const role = "<?= $redirect ?? '' ?>";

        if (status === "success") {
            Swal.fire({
                icon: 'success',
                iconColor: 'var(--Text4)',
                showConfirmButton: false,
                timer: 1300,
                title: "Berhasil!",
                text: "Login berhasil!",
                color: "var(--Text1)"
            }).then(() => {
                window.location.href = "../UI/Splash/TREE.php?go=../" + role + "/Beranda.php";
            });
        }

        if (status === "invalid") {
            Swal.fire({
                icon: 'error',
                iconColor: 'red',
                title: 'Gagal',
                text: 'Link login sudah tidak berlaku.',
                color: 'var(--Text1)',
                confirmButtonText: 'Kembali',
                confirmButtonColor: "var(--Text4)"
            }).then(() => {
                window.location.href = "../UI/Masuk.php";
            });
        }
    </script>

</body>

</html>