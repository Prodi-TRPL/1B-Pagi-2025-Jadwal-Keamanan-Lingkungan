<?php
session_start();
session_destroy();
header("Location: ../UI/LandingPage.php");
exit;
?>
