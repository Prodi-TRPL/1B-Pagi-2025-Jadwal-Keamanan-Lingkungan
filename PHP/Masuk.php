<?php
session_start();
header('Content-Type: application/json');
include "Config.php";

// =========================
// BACA JSON DARI FETCH
// =========================
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['nik']) || empty($data['password'])) {
    echo json_encode([
        "status" => "failed",
        "message" => "NIK atau password kosong"
    ]);
    exit;
}

$nik = trim($data['nik']);
$password = $data['password'];

/* =========================
   CEK INHABITANTS
========================= */
$q = $conn->prepare("SELECT * FROM inhabitants WHERE nik = ?");
$q->bind_param("s", $nik);
$q->execute();
$r = $q->get_result();

if ($r->num_rows > 0) {
    $user = $r->fetch_assoc();

    if ($password !== $user['password']) {
        echo json_encode(["status" => "failed", "message" => "Password salah"]);
        exit;
    }

    $_SESSION['inhabitants_id'] = $user['inhabitantId'];
    $_SESSION['nik'] = $user['nik'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['status'];
    $_SESSION['rw'] = $user['rw'];
    $_SESSION['ward'] = $user['ward'];

    $finalTarget = match ($user['status']) {
        'Pengelola' => '../../UI/Pengelola/Beranda.php',
        'Warga' => '../../UI/Warga/Beranda.php',
        default => '../Error/NoRole.php'
    };

    echo json_encode([
        "status" => "success",
        "redirect" => "../UI/Splash/TREE.php?go=" . urlencode($finalTarget)
    ]);
    exit;
}

/* =========================
   CEK ADMIN
========================= */
$q = $conn->prepare("SELECT * FROM admin WHERE nik = ?");
$q->bind_param("s", $nik);
$q->execute();
$r = $q->get_result();

if ($r->num_rows > 0) {
    $admin = $r->fetch_assoc();

    if ($password !== $admin['password']) {
        echo json_encode(["status" => "failed", "message" => "Password salah"]);
        exit;
    }

    $_SESSION['admin_id'] = $admin['adminId'];
    $_SESSION['nik'] = $admin['nik'];
    $_SESSION['admin_ward'] = $admin['ward'];
    $_SESSION['status'] = 'Admin';

    echo json_encode([
        "status" => "success",
        "redirect" => "../UI/Splash/TREE.php?go=" . urlencode("../../UI/Admin/Beranda.php")
    ]);
    exit;
}

// =========================
// TIDAK DITEMUKAN
// =========================
echo json_encode([
    "status" => "failed",
    "message" => "Akun tidak ditemukan"
]);
