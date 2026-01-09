<?php
session_start();
include "config.php";

if (!isset($_SESSION['email_otp'], $_SESSION['otp_expire'])) {
    echo "SESSION_LOST";
    exit;
}

$otp = $_POST['otp'];

if (
    $_SESSION['email_otp'] === $otp &&
    time() < $_SESSION['otp_expire']
) {
    $email   = $_SESSION['new_email'];
    $user_id = $_SESSION['inhabitants_id'];

    mysqli_query($conn,
        "UPDATE inhabitants SET email = '$email', lastEmailChange = NOW() WHERE inhabitantId = '$user_id'"
    );

    unset($_SESSION['email_otp']);
    unset($_SESSION['new_email']);
    unset($_SESSION['otp_expire']);

    echo "SUCCESS";
} else {
    echo "INVALID";
}
