<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION["inhabitants_id"])) {
  header("Location: Masuk.php");
  exit;
}

include "../PHP/Config.php";

$user_id = $_SESSION["inhabitants_id"];



$query = $conn->prepare("SELECT * FROM inhabitants WHERE inhabitantId = ?");

if (!$query) {
  die("QUERY ERROR: " . $conn->error);
}

$query->bind_param("i", $user_id);

$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();


$role = strtolower($user["status"]); // pastikan lowercase agar mudah dibanding

$isPengelola = ($role === "pengelola");

$avatar = strtoupper(substr($user["name"], 0, 2));

function timeAgo($time)
{

  if (empty($time) || $time === "0000-00-00 00:00:00") {
    return "Belum pernah diubah";
  }

  try {

    $past = new DateTime($time);
  } catch (Exception $e) {

    return "Waktu tidak tersedia";
  }

  $now = new DateTime();
  $diff = $now->diff($past);

  if ($diff->y > 0) {
    return $diff->y . " tahun lalu";
  }
  if ($diff->m > 0) {
    return $diff->m . " bulan lalu";
  }
  if ($diff->d > 0) {
    return $diff->d . " hari lalu";
  }
  if ($diff->h > 0) {
    return $diff->h . " jam lalu";
  }
  if ($diff->i > 0) {
    return $diff->i . " menit lalu";
  }
  return "Baru saja";
}


$passwordTime = timeAgo($user["lastPasswordChange"]);
$emailTime = timeAgo($user["lastEmailChange"]);
$phoneTime = timeAgo($user["lastPhoneChange"]);

$firstName = explode(" ", trim($user["name"]))[0];

?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TREE</title>
  <link rel="icon" type="image/png" href="../Asset/Image/Logo.png">

  <link rel="stylesheet" href="../CSS/Main.css">
  <link rel="stylesheet" href="../CSS/Profil.css">
  <script src="https://kit.fontawesome.com/8eb0a590d4.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../JavaScript/Profil.js"></script>
</head>

<body>

  <nav class="navbar">
    <div class="navbar-left">
      <div class="profile-icon">
        <img src="../Asset/Image/Logo.png">
        <p>TREE</p>
      </div>
    </div>

    <div class="hamburger" onclick="toggleMenu()">
      <span></span>
      <span></span>
      <span></span>
    </div>

    <div class="navbar-right" id="navbarMenu">
      <?php if ($isPengelola): ?>
        <a href="Pengelola/Beranda.php" class="unselected">Beranda</a>
        <a href="Pengelola/Jadwal.php" class="unselected">Jadwal</a>
        <a href="Pengelola/Data-Warga.php" class="unselected">Data Warga</a>
        <a href="Pengelola/Laporan.php" class="unselected">Laporan</a>
        <a href="#" class="selected">Profil</a>
      <?php else: ?>
        <a href="Warga/Beranda.php" class="unselected">Beranda</a>
        <a href="Warga/Jadwal.php" class="unselected">Jadwal</a>
        <a href="Warga/Laporan.php" class="unselected">Laporan</a>
        <a href="#" class="selected">Profil</a>
      <?php endif; ?>
    </div>

  </nav>

  <section class="section">
    <div class="container1">
      <div class="profil-header">
        <div class="profil-info">
          <div class="profil-avatar"><?= $avatar ?></div>
          <div class="profil-text">
            <h2><?= $firstName ?></h2>
            <p><?= $user["status"] ?></p>
            <p><b>ID Akun:</b> <?= $user["inhabitantId"] ?></p>
          </div>
        </div>
      </div>

      <div class="detail-grid">
        <div class="detail-card">
          <p>Nomor Telepon</p>
          <p><?= $user["phone"] ?></p>
        </div>
        <div class="detail-card">
          <p>Email</p>
          <p><?= $user["email"] ?></p>
        </div>
        <div class="detail-card">
          <p>Alamat</p>
          <p><?= $user["address"] ?></p>
        </div>
      </div>

      <div class="detail-grid-new">
        <div class="security">
          <div>
            <p class="Upper">Nomor Telepon</p>
            <p class="Lower">Terakhir diubah <?= $phoneTime ?></p>
          </div>
          <button type="button" onclick="Phone()"><i class="fa-regular fa-pen-to-square"></i></button>
        </div>


        <div class="security">
          <div>
            <p class="Upper">Alamat Email</p>
            <p class="Lower">Terakhir diubah <?= $emailTime ?></p>
          </div>
          <button type="button" onclick="Email()"><i class="fa-regular fa-pen-to-square"></i></button>
        </div>
      </div>

      <div class="security">
        <div>
          <p class="Upper">Keamanan Akun</p>
          <p class="Lower">Terakhir diubah <?= $passwordTime ?></p>
        </div>
        <button type="button" onclick="Password()"><i class="fa-regular fa-pen-to-square"></i></button>
      </div>


      <div class="actions">
        <button type="button" class="logout" onclick="Keluar()">Keluar</button>
      </div>

    </div>
  </section>

</body>

</html>