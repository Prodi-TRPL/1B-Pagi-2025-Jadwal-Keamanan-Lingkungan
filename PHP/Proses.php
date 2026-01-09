<?php
include 'Config.php';

$action = $_POST['action'] ?? '';

if ($_POST['action'] == "insert") {
    $nik = $_POST['nik'];
    $name = $_POST['nama'];
    $address = $_POST['alamat'];
    $gender = $_POST['jenis_kelamin'];
    $datebirth = $_POST['tanggal_lahir'];
    $phone = $_POST['no_telp'];
    $status = $_POST['status'];
    $rw = $_POST['rw'];
    $ward = $_POST['ward'];

    $query = "INSERT INTO inhabitants (nik, name, address, gender, dateBirth, password, phone, status, rw, ward)
              VALUES ('$nik', '$name', '$address', '$gender', '$datebirth','$nik','$phone', '$status', '$rw', '$ward')";

    if (mysqli_query($conn, $query)) {
        echo "Data berhasil ditambahkan!";
    } else {
        echo "Gagal menambah data: " . mysqli_error($conn);
    }
    exit;
}


if ($action == "update") {
    $id = $_POST['id'];
    $nik = $_POST['nik'];
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $jk = $_POST['jenis_kelamin'];
    $datebirth = $_POST['tanggal_lahir'];
    $telp = $_POST['no_telp'];
    $status = $_POST['status'];

    $query = "UPDATE inhabitants SET 
        nik='$nik', name='$nama', address='$alamat',
        gender='$jk', dateBirth='$datebirth', phone='$telp', status='$status'
        WHERE inhabitantId='$id'";

    mysqli_query($conn, $query);
    echo "Data berhasil diperbarui!";
}

if ($action == "delete") {
    $id = $_POST['id'];
    mysqli_query($conn, "DELETE FROM inhabitants WHERE inhabitantId='$id'");
    echo "Data berhasil dihapus!";
}
?>