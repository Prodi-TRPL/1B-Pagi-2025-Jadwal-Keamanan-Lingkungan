<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

$email = $_POST['email'];

$otp = strval(rand(100000, 999999));
$_SESSION['email_otp'] = $otp;
$_SESSION['new_email'] = $email;
$_SESSION['otp_expire'] = time() + 300;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'treepohonhijau@gmail.com';
    $mail->Password   = 'websxiwknrkbdfbv';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('treepohonhijau@gmail.com', 'TREE');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Kode Verifikasi Email';
    $mail->Body    = "
        <h2>Kode OTP Anda</h2>
        <h1>$otp</h1>
        <h5>Kode ini bersifat rahasia dan hanya berlaku selama 5 menit.
            Jangan bagikan kode ini kepada siapa pun.</h5>
        ";

    $mail->send();
    echo "OTP_SENT";

} catch (Exception $e) {
    echo "FAILED: " . $mail->ErrorInfo;
}
