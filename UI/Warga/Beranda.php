<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

// Jika belum login, redirect
if (!isset($_SESSION["inhabitants_id"]) || !isset($_SESSION["rw"])) {
  echo "<script>alert('Session tidak ditemukan! Silakan login ulang'); window.location.href='../Masuk.php';</script>";
  exit;
}

include "../../PHP/Config.php";

// Ambil RW user
$rw = $_SESSION["rw"];

// Ambil status terbaru berdasarkan RW
// Ambil status terbaru berdasarkan RW
$query = $conn->prepare("SELECT `status`, `date` FROM envistatus WHERE rw = ? ORDER BY date DESC LIMIT 1");

if (!$query) {
  die("QUERY ERROR: " . $conn->error);
}

$query->bind_param("i", $rw);
$query->execute();
$result = $query->get_result()->fetch_assoc();

$status = $result["status"] ?? "AMAN";

// Jika tidak ada data, gunakan waktu sekarang
$update_date = isset($result["date"])
  ? date("d M Y, H:i", strtotime($result["date"]))
  : date("d M Y, H:i");

$today = date("Y-m-d");
$staffToday = $conn->prepare("
SELECT 
    i.nik,
    i.name,
    GROUP_CONCAT(DISTINCT r.routeName SEPARATOR ', ') AS routes
FROM inhabitants i
LEFT JOIN attendances a ON a.nik = i.nik
LEFT JOIN schedule s ON a.scheduleId = s.scheduleId
LEFT JOIN route r ON s.route = r.routeName
WHERE DATE(s.date) = ?
GROUP BY i.nik, i.name
ORDER BY i.name ASC
");

$staffToday->bind_param("s", $today);
$staffToday->execute();
$staffToday = $staffToday->get_result();

// Ambil laporan berdasarkan RW
$queryReports = $conn->prepare("
    SELECT title, type, description, time, file, latitude, longitude
    FROM reports
    WHERE rw = ? AND status ='diterima'
    ORDER BY time DESC
");
$queryReports->bind_param("i", $rw);
$queryReports->execute();
$reports = $queryReports->get_result();



?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TREE</title>
  <link rel="icon" type="image/png" href="../../Asset/Image/Logo.png">

  <link rel="stylesheet" href="../../CSS/Main.css" />
  <link rel="stylesheet" href="../../CSS/Beranda-Warga.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

  <style>
    .status-circle.aman {
      border-color: #00ff40;
    }

    .status-circle.waspada {
      border-color: #fffb00;
    }

    .status-circle.bahaya {
      border-color: #ff0000;
    }

    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 15px;
      margin-top: 15px;
    }

    .pagination button {
      background: #0a4d23;
      border-radius: 6px;
      border: none;
      padding: 6px 12px;
      font-size: 20px;
      color: #fff;
      cursor: pointer;
      transition: 0.2s;
    }

    .pagination button:hover {
      background: #0d6c30;
    }

    .no-data {
      text-align: center;
      width: 100%;
      color: #666;
      font-size: 15px;
      font-style: italic;
      padding: 12px 0;
    }

    .nama {
      padding: 15px;
    }
    #staff-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
  </style>
</head>

<body>

  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-left">
      <div class="profile-icon">
        <img src="../../Asset/Image/Logo.png">
        <p>TREE</p>
      </div>
    </div>
    <div class="navbar-right">
      <a href="#" class="selected">Beranda</a>
      <a href="Jadwal.php" class="unselected">Jadwal</a>
      <a href="Laporan.php" class="unselected">Lapor</a>
      <a href="../Profil.php" class="unselected">Profil</a>
    </div>
  </nav>

  <section class="hero">
    <h1 class="welcome">Selamat Datang!</h1>
    <div class="container">

      <div class="kiri">
        <h2>Petugas Hari Ini</h2>

        <div id="staff-list">
          <?php if ($staffToday->num_rows < 1): ?>
            <p class="no-data">Tidak ada petugas hari ini</p>
          <?php else: ?>
            <?php while ($row = $staffToday->fetch_assoc()): ?>
              <div class="nama">
                <p><strong><?= htmlspecialchars($row["name"]) ?></strong></p>
              </div>
            <?php endwhile; ?>
          <?php endif; ?>
        </div>

        <div class="pagination">
          <button onclick="prevStaffPage()">‹</button>
          <span id="staff-page-info"></span>
          <button onclick="nextStaffPage()">›</button>
        </div>
      </div>





      <div class="tengah">
        <h2>Pemberitahuan</h2>
        <div class="scroll-container">

          <div class="scroll-container">

            <?php if ($reports->num_rows < 1): ?>
              <p class="no-data">Tidak ada pemberitahuan</p>
            <?php else: ?>
              <?php while ($row = $reports->fetch_assoc()): ?>
                <button class="Pemberitahuan-list next-btn" data-title="<?= htmlspecialchars($row['title']) ?>"
                  data-type="<?= htmlspecialchars($row['type']) ?>" data-time="<?= date('H:i', strtotime($row['time'])) ?>"
                  data-date="<?= date('d-m-Y', strtotime($row['time'])) ?>"
                  data-description="<?= htmlspecialchars($row['description']) ?>"
                  data-file="../../Uploads/<?= htmlspecialchars($row['file']) ?>"
                  data-lat="<?= htmlspecialchars($row['latitude']) ?>"
                  data-lng="<?= htmlspecialchars($row['longitude']) ?>">
                  <div class="details">
                    <span><?= htmlspecialchars($row['title']) ?></span>
                    <p><?= htmlspecialchars($row['type']) ?></p>
                  </div>
                  <span>&gt;</span>
                </button>
              <?php endwhile; ?>
            <?php endif; ?>


          </div>
          <div class="pagination">
            <button onclick="prevReportPage()">‹</button>
            <span id="report-page-info"></span>
            <button onclick="nextReportPage()">›</button>
          </div>



        </div>
      </div>
      <!-- Popup Detail Pemberitahuan-->
      <div id="popup-laporan" class="popup">
        <div class="popup-content">
          <span class="close-btn">&times;</span>
          <h1>Lala Lepas</h1>
          <div class="date-time">
            <p>22:00</p>
            <p>|</p>
            <p>20-12-25</p>
          </div>

          <div class="popup-container">
            <div class="popup-leftcontainer">
              <img id="image" alt="Image" src="/Asset/Image/Laporan.jpg">
              <video id="video" controls
                style="display:none; width:100%; height:350px; border-radius:12px; object-fit:cover;"></video>
              <div id="map"></div>
              <div class="buttons">
                <button id="gambar" class="active" onclick="changetoimg()">Gambar</button>
                <button id="lokasi" onclick="changetoloc()">Lokasi</button>
              </div>
            </div>
            <div class="popup-rightcontainer">
              <p class="Title"><strong>Kategori:</strong> Lainnya</p>
              <p>Lala adalah seorang manusia yang dilaporkan lepas dari pengawasan pada waktu yang belum ditentukan
                secara pasti. Berdasarkan informasi yang diterima, Lala terakhir kali terlihat berada di area sekitar
                tempat tinggalnya sebelum dinyatakan tidak diketahui keberadaannya.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="kanan">
        <h2>Status Lingkungan</h2>
        <div class="kanan1">
          <?php
          $circleClass = strtolower($status); // aman / waspada / bahaya
          ?>
          <div class="status-circle <?= $circleClass ?>">
            <span class="status-text"><?= strtoupper($status) ?></span>
          </div>

          <p class="update-time">
            Terakhir diperbarui: <?= $update_date ?>
          </p>

        </div>

      </div>
    </div>
  </section>

  <footer>
    <p>© 2025 Portal Warga. Semua Hak Dilindungi.</p>
  </footer>

  <script src="https://kit.fontawesome.com/a2e8f5e6a5.js" crossorigin="anonymous"></script>
  <script>
    // Ambil elemen-elemen popup
    const popupLaporan = document.getElementById("popup-laporan");

    const closeBtn = document.querySelector(".close-btn");
    const nextBtns = document.querySelectorAll(".next-btn");

    const gambarBtn = document.getElementById("gambar");
    const lokasiBtn = document.getElementById("lokasi");
    const img = document.getElementById('image');
    const leafletmap = document.getElementById('map');
    leafletmap.style.display = "none";

    let currentLat = null;
    let currentLng = null;


    // Saat tombol ">" diklik → tampilkan popup
    nextBtns.forEach((btn) => {
      btn.addEventListener("click", () => {

        const judul = btn.dataset.title;
        const kategori = btn.dataset.type;
        const waktu = btn.dataset.time;
        const tanggal = btn.dataset.date;
        const deskripsi = btn.dataset.description;
        const gambar = btn.dataset.file;

        currentLat = parseFloat(btn.dataset.lat);
        currentLng = parseFloat(btn.dataset.lng);

        document.querySelector("#popup-laporan h1").textContent = judul;
        document.querySelector(".popup-rightcontainer .Title").innerHTML = `<strong>Kategori:</strong> ${kategori}`;
        document.querySelector(".popup-rightcontainer p:nth-child(2)").textContent = deskripsi;

        document.querySelector(".date-time p:nth-child(1)").textContent = waktu;
        document.querySelector(".date-time p:nth-child(3)").textContent = tanggal;

        const video = document.getElementById("video");

        // Reset display
        img.style.display = "none";
        video.style.display = "none";

        // Cek extension
        const ext = gambar.split('.').pop().toLowerCase();

        if (["mp4", "webm", "ogg"].includes(ext)) {
          video.src = gambar;
          video.style.display = "block";
          img.style.display = "none";
        } else {
          img.src = gambar;
          img.style.display = "block";
          video.style.display = "none";
        }


        popupLaporan.style.display = "flex";
      });
    });



    // Tutup popup saat tombol X atau Tutup ditekan
    closeBtn.addEventListener("click", () =>
      popupLaporan.style.display = "none");


    // Tutup popup kalau klik di luar konten
    window.addEventListener("click", (e) => {
      if (e.target === popupLaporan) popupLaporan.style.display = "none";
    });

    function changetoloc() {
      if (gambarBtn.classList.contains("active")) {
        lokasiBtn.classList.add('active')
        gambarBtn.classList.remove('active')

        leafletmap.style.height = "380px";
        leafletmap.style.display = "flex";

        img.style.display = "none";
        const video = document.getElementById("video");
        video.style.display = "none";

        if (navigator.geolocation) {
          showIncidentLocation();
        } else {
          alert("Geolokasi tidak didukung oleh browser ini.");
        }
      }
    }


    function changetoimg() {
      if (lokasiBtn.classList.contains("active")) {
        gambarBtn.classList.add('active');
        lokasiBtn.classList.remove('active');

        const video = document.getElementById("video");

        // Tampilkan gambar/video sesuai yang aktif
        if (video.src && video.src !== "") {
          video.style.display = "block";
          img.style.display = "none";
        } else {
          img.style.display = "block";
          video.style.display = "none";
        }

        leafletmap.style.height = "0";
        leafletmap.style.display = "none";
      }
    }


    function showIncidentLocation() {
      if (!currentLat || !currentLng) {
        alert("Lokasi laporan tidak tersedia.");
        return;
      }

      const map = L.map('map', {
        zoomControl: false,
        attributionControl: false
      }).setView([currentLat, currentLng], 17);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
      }).addTo(map);

      L.marker([currentLat, currentLng]).addTo(map)
        .bindPopup('Lokasi Kejadian')
        .openPopup();
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

    // ===== Pagination Pemberitahuan ===== //
    const itemsPerPageReport = 4;
    let currentPageReport = 1;

    function renderReports() {
      const reportItems = document.querySelectorAll(".next-btn");
      const totalPages = Math.ceil(reportItems.length / itemsPerPageReport);

      // Sembunyikan pagination jika tidak perlu
      const pagination = document.querySelector(".tengah .pagination");
      pagination.style.display = totalPages <= 1 ? "none" : "flex";

      reportItems.forEach((item, index) => {
        item.style.display =
          index >= (currentPageReport - 1) * itemsPerPageReport &&
            index < currentPageReport * itemsPerPageReport
            ? "flex"
            : "none";
      });

      if (totalPages > 0) {
        document.getElementById("report-page-info").textContent = `${currentPageReport}/${totalPages}`;
      }
    }


    function nextReportPage() {
      const reportItems = document.querySelectorAll(".next-btn");
      const totalPages = Math.ceil(reportItems.length / itemsPerPageReport);

      if (currentPageReport < totalPages) {
        currentPageReport++;
        renderReports();
      }
    }

    function prevReportPage() {
      if (currentPageReport > 1) {
        currentPageReport--;
        renderReports();
      }
    }

    renderReports();

    if (document.querySelectorAll(".next-btn").length === 0) {
      document.querySelector(".tengah .pagination").style.display = "none";
    }

    // ===== Pagination Petugas Hari Ini ===== //
    const itemsPerPageStaff = 5;
    let currentPageStaff = 1;

    function renderStaff() {
      const staffItems = document.querySelectorAll(".kiri .nama");
      const totalPages = Math.ceil(staffItems.length / itemsPerPageStaff);

      // Sembunyikan pagination jika tidak perlu
      const pagination = document.querySelector(".kiri .pagination");
      pagination.style.display = totalPages <= 1 ? "none" : "flex";

      staffItems.forEach((item, index) => {
        item.style.display =
          index >= (currentPageStaff - 1) * itemsPerPageStaff &&
            index < currentPageStaff * itemsPerPageStaff
            ? "flex"
            : "none";
      });

      if (totalPages > 0) {
        document.getElementById("staff-page-info").textContent = `${currentPageStaff}/${totalPages}`;
      }
    }


    function nextStaffPage() {
      const staffItems = document.querySelectorAll(".kiri .nama");
      const totalPages = Math.ceil(staffItems.length / itemsPerPageStaff);

      if (currentPageStaff < totalPages) {
        currentPageStaff++;
        renderStaff();
      }
    }

    function prevStaffPage() {
      if (currentPageStaff > 1) {
        currentPageStaff--;
        renderStaff();
      }
    }

    renderStaff();

    if (document.querySelectorAll(".kiri .nama").length === 0) {
      const noStaff = document.createElement("p");
      noStaff.className = "no-data";
      document.querySelector(".kiri").appendChild(noStaff);

      document.querySelector(".kiri .pagination").style.display = "none";
    }





  </script>

</body>

</html>