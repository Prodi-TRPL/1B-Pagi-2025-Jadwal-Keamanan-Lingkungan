<?php
session_start();
include "Config.php";

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
    $user_id = $_SESSION['admin_id'];

    mysqli_query($conn,
        "UPDATE admin SET email = '$email', lastEmailChange = NOW() WHERE adminId = '$user_id'"
    );

    unset($_SESSION['email_otp']);
    unset($_SESSION['new_email']);
    unset($_SESSION['otp_expire']);

    echo "SUCCESS";
} else {
    echo "INVALID";
}
