<?php
include "../PHP/Config.php";
$id = $_POST['id'];
$sql = "UPDATE reports SET status='Ditolak' WHERE reportId=$id";
$conn->query($sql);
echo "ok";
?>
