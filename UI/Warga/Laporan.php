<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION["inhabitants_id"]) || !isset($_SESSION["rw"])) {
  echo "<script>alert('Session tidak ditemukan! Silakan login ulang'); window.location.href='../Masuk.php';</script>";
  exit();
}

include "../../PHP/Config.php";

$userId = $_SESSION["inhabitants_id"];
$rw = $_SESSION["rw"];

// Ambil semua laporan user yg login
$query = $conn->prepare("SELECT * FROM reports WHERE inhabitantId = ? ORDER BY time DESC");
$query->bind_param("i", $userId);
$query->execute();
$reports = $query->get_result();
?>


<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TREE</title>
  <link rel="icon" type="image/png" href="../../Asset/Image/Logo.png">

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <link rel="stylesheet" href="../../CSS/Main.css">
  <link rel="stylesheet" href="../../CSS/Lapor.css">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../../JavaScript/Lapor.js"></script>
</head>

<body>

  <div class="transition transition1 is_active"></div>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-left">
      <div class="profile-icon">
        <img src="../../Asset/Image/Logo.png">
        <p>TREE</p>
      </div>
    </div>

    <div class="hamburger" onclick="toggleMenu()">
      <span></span>
      <span></span>
      <span></span>
    </div>

    <div class="navbar-right" id="navbarMenu">
      <a href="Beranda.php" class="unselected">Beranda</a>
      <a href="Jadwal.php" class="unselected">Jadwal</a>
      <a href="#" class="selected">Lapor</a>
      <a href="../Profil.php" class="unselected">Profil</a>
    </div>
  </nav>

  <section class="section">
    <!-- Kanan-Form Laporan -->
    <div class="container1">
      <h2>Lapor Insiden</h2>
      <p>Beri tahu kami tentang insiden yang ingin Anda laporkan. Informasi yang dibagikan hanya akan digunakan untuk
        merespons laporan Anda.</p>

      <form id="Form-Laporan" action="../../PHP/SubmitLaporan.php" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="rw" value="<?php echo $_SESSION['rw']; ?>">

        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">

        <label for="kategori">Kategori <span>*</span></label>
        <select id="kategori" name="kategori" required>
          <option value="" disabled selected hidden>Pilih opsi</option>
          <option value="pencurian">Pencurian</option>
          <option value="pembunuhan">Pembunuhan</option>
          <option value="kecelakaan">Kecelakaan</option>
          <option value="kebakaran">Kebakaran</option>
          <option value="lainnya">Lainnya</option>
        </select>

        <label for="nama">Judul <span>*</span></label>
        <input type="text" id="judul" name="judul" placeholder="Judul laporan" required>

        <label for="lokasi">Lokasi <span>*</span></label>
        <div id="map"></div>
        <button type="button" id="lokasi" class="loc_button" onclick="getLocation();">Tetapkan Lokasi</button>

        <label for="detail">Detail insiden <span>*</span></label>
        <textarea id="detail" name="detail" placeholder="Ceritakan lebih lanjut (min. 50 karakter)" required></textarea>

        <label>Lampiran (Opsional)</label>
        <p style="font-size:13px; color:#000000;">Jenis file: MP4, JPG (maks 1 file)</p>
        <input type="file" name="lampiran">

        <div class="checkbox">
          <input type="checkbox" required>
          <p> Data saya benar.</p>
        </div>

        <div class="checkbox">
          <input type="checkbox" required>
          <p> Saya setuju kebijakan TREE.</p>
        </div>

        <button type="submit" class="submit-btn">Kirim</button>
      </form>

    </div>
    <!--Kiri-Histori laporan -->
    <div class="container2">
      <div class="top-container">
        <h2>Laporan Saya</h2>
        <select id="filterStatus">
          <option value="Semua" selected>Semua</option>
          <option value="Pencurian">Pencurian</option>
          <option value="Pembunuhan">Pembunuhan</option>
          <option value="Kecelakaan">Kecelakaan</option>
          <option value="Kebakaran">Kebakaran</option>
        </select>
      </div>

      <div class="card-container" id="cardContainer">
        <?php if ($reports->num_rows === 0): ?>
          <p style="color: var(--Text2); text-align:center;">Belum ada laporan.</p>
        <?php else: ?>
          <?php while ($row = $reports->fetch_assoc()): ?>
            <div class="card">
              <div style="display: flex; flex-direction:row; gap:20px;">
                <img src="../../Asset/Image/<?php echo ucfirst($row['type']); ?>.png" style="height:65px;">
                <div>
                  <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                  <p><b>Kategori:</b> <?php echo htmlspecialchars($row['type']); ?></p>
                  <p><b>Tanggal:</b> <?php echo htmlspecialchars($row['time']); ?></p>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>


    </div>
  </section>

  <script>
    function toggleMenu() {
      const menu = document.getElementById('navbarMenu');
      menu.classList.toggle('show');
    }

    const leafletmap = document.getElementById("map");

    function getLocation() {
      leafletmap.style.height = "200px";
      leafletmap.style.width = "100%";
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition, showError);
      } else {
        alert("Geolokasi tidak didukung oleh browser ini.");
      }
    }

    function showPosition(position) {
      const latitude = position.coords.latitude;
      const longitude = position.coords.longitude;

      document.getElementById("latitude").value = latitude;
      document.getElementById("longitude").value = longitude;

      const map = L.map('map', { zoomControl: false, attributionControl: false })
        .setView([latitude, longitude], 18);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
      }).addTo(map);

      const marker = L.marker([latitude, longitude], { draggable: true }).addTo(map);

      marker.on('dragend', function (event) {
        const newPos = event.target.getLatLng();
        document.getElementById("latitude").value = newPos.lat;
        document.getElementById("longitude").value = newPos.lng;
      });
    }


    function showError(error) {
      switch (error.code) {
        case error.PERMISSION_DENIED:
          alert("Aktifkan lokasi.");
          break;
        case error.POSITION_UNAVAILABLE:
          alert("Informasi lokasi tidak tersedia.");
          break;
        case error.TIMEOUT:
          alert("Tidak dapat terhubung ke server.");
          break;
        case error.UNKNOWN_ERROR:
          alert("Terjadi kesalahan yang tidak diketahui.");
          break;
      }
    }

    document.getElementById("filterStatus").addEventListener("change", function () {
      if (this.value === "Semua") {
        renderCards(laporanDummy);
      } else {
        const filtered = laporanDummy.filter(lapor => lapor.kategori === this.value);
        renderCards(filtered);
      }
    });
  </script>
</body>

</html>