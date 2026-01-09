<?php
session_start();
include "config.php";

use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

$email = $_POST['email'] ?? '';

if (!$email) {
    echo "Email tidak valid";
    exit;
}

$token  = bin2hex(random_bytes(32));
$expire = date("Y-m-d H:i:s", time() + 900); // 15 menit

$userId = null;
$role   = null;

/* =========================
   CEK TABEL ADMIN
========================= */
$qAdmin = $conn->prepare("
    SELECT adminId 
    FROM admin 
    WHERE email = ?
    LIMIT 1
");
$qAdmin->bind_param("s", $email);
$qAdmin->execute();
$resAdmin = $qAdmin->get_result();

if ($resAdmin->num_rows > 0) {
    $data   = $resAdmin->fetch_assoc();
    $userId = $data['adminId'];
    $role   = 'Admin';

    $upd = $conn->prepare("
        UPDATE admin 
        SET resetToken = ?, resetExpire = ?
        WHERE adminId = ?
    ");
    $upd->bind_param("ssi", $token, $expire, $userId);
    $upd->execute();
}

/* =========================
   CEK TABEL INHABITANTS
========================= */
if (!$userId) {
    $qUser = $conn->prepare("
        SELECT inhabitantId 
        FROM inhabitants 
        WHERE email = ?
        LIMIT 1
    ");
    $qUser->bind_param("s", $email);
    $qUser->execute();
    $resUser = $qUser->get_result();

    if ($resUser->num_rows > 0) {
        $data   = $resUser->fetch_assoc();
        $userId = $data['inhabitantId'];
        $role   = 'user';

        $upd = $conn->prepare("
            UPDATE inhabitants 
            SET resetToken = ?, resetExpire = ?
            WHERE inhabitantId = ?
        ");
        $upd->bind_param("ssi", $token, $expire, $userId);
        $upd->execute();
    }
}

/* =========================
   JIKA EMAIL TIDAK ADA
========================= */
if (!$userId) {
    echo "Email tidak terdaftar";
    exit;
}

/* =========================
   KIRIM EMAIL
========================= */
$link = "http://localhost/TREE/PHP/LoginByEmail.php?token=$token&role=$role";

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'treepohonhijau@gmail.com';
$mail->Password   = 'websxiwknrkbdfbv';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;

$mail->setFrom('treepohonhijau@gmail.com', 'TREE App');
$mail->addAddress($email);
$mail->isHTML(true);
$mail->Subject = 'Login ke akun TREE';
$mail->Body = "
    <h3 style='font-family: Arial, sans-serif;'>Login ke akun TREE</h3>
    <p style='font-family: Arial, sans-serif;'>
    Klik tombol di bawah ini untuk masuk:
    </p>

    <table width='100%' cellpadding='0' cellspacing='0' role='presentation'>
    <tr>
        <td align='center' style='padding: 20px 0;''>
        <a href='$link'
            style='
            display: inline-block;
            padding: 12px 20px;
            background-color: #2e7d32;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-family: Arial, sans-serif;
            '>
            Masuk ke Akun
        </a>
        </td>
    </tr>
    </table>

    <p style='font-family: Arial, sans-serif; font-size: 12px; color: #555;'>
    Link berlaku 15 menit
    </p>
    ";

$mail->send();
echo "SENT";
