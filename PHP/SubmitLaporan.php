<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION["inhabitants_id"]) || !isset($_SESSION["rw"])) {
    echo "<script>alert('Session tidak ditemukan! Silakan login ulang'); window.location.href='../Masuk.php';</script>";
    exit();
}

include "Config.php";

$userId = $_SESSION["inhabitants_id"];
$rw = $_SESSION["rw"];  // <-- ambil RW dari session

$kategori = $_POST["kategori"];
$judul = $_POST["judul"];
$detail = $_POST["detail"];
$latitude = floatval($_POST["latitude"]);
$longitude = floatval($_POST["longitude"]);

// Handle file upload
$fileName = NULL;
if (isset($_FILES["lampiran"]) && $_FILES["lampiran"]["error"] == 0) {
    $fileName = time() . "_" . basename($_FILES["lampiran"]["name"]);
    move_uploaded_file($_FILES["lampiran"]["tmp_name"], "../Uploads/" . $fileName);
}

$query = $conn->prepare("
    INSERT INTO reports (type, title, description, latitude, longitude, file, inhabitantId, rw)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$query->bind_param(
    "sssddsii",
    $kategori,
    $judul,
    $detail,
    $latitude,
    $longitude,
    $fileName,
    $userId,
    $rw
);

if ($query->execute()) {
    echo "<script>alert('Laporan berhasil dikirim!'); window.location.href='../UI/Warga/Laporan.php';</script>";
} else {
    echo "<script>alert('Gagal mengirim laporan!');</script>";
}
?>
