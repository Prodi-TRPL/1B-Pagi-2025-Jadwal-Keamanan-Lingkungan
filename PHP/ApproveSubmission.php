<?php
include "../PHP/Config.php";
$id = $_POST['id'];
$sql = "UPDATE submission SET submissionStatus='Diterima' WHERE submissionId=$id";
$conn->query($sql);
echo "ok";
?>
