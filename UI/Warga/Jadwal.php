<?php
session_start();

if (!isset($_SESSION["inhabitants_id"]) || !isset($_SESSION["rw"])) {
  echo "<script>alert('Session tidak ditemukan! Silakan login ulang'); window.location.href='../Masuk.php';</script>";
  exit;
}

include "../../PHP/Config.php";

$userId = $_SESSION['inhabitants_id'];

// ambil data user
$uRes = $conn->query("SELECT dateBirth, gender, rw FROM inhabitants WHERE inhabitantId=$userId");
if (!$uRes || !$uRes->num_rows)
  die("User tidak ditemukan");
$u = $uRes->fetch_assoc();

// ambil requirement sesuai RW user
$rRes = $conn->query("SELECT minAge, maxAge FROM requirements WHERE rw='{$u['rw']}' LIMIT 1");
if (!$rRes || !$rRes->num_rows)
  die("Requirement RW {$u['rw']} belum diset");
$r = $rRes->fetch_assoc();

// hitung umur
$age = date_diff(date_create($u['dateBirth']), date_create('today'))->y;

// cek eligibility
$isEligible = (
  $age >= $r['minAge'] &&
  $age <= $r['maxAge'] &&
  $u['gender'] === "L"
);

$routes = $conn->query("
  SELECT routeId, routeName 
  FROM route 
  WHERE rw =  {$u['rw']}
  ORDER BY routeName ASC
");

?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TREE</title>
  <link rel="icon" type="image/png" href="../../Asset/Image/Logo.png">

  <link rel="stylesheet" href="../../CSS/Main.css">
  <link rel="stylesheet" href="../../CSS/Jadwal-Warga.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

  <script src="https://kit.fontawesome.com/8eb0a590d4.js" crossorigin="anonymous"></script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    .next-btn.disabled,
    .pengajuan-btn.disabled {
      background: #999;
      cursor: not-allowed;
      opacity: 0.6;
    }

    .scrollable {
      height: 100%;
      min-height: 300px;
      overflow-y: auto;
    }

    .list-left h3 {
      margin: 0;
      font-size: 15px;
      color: var(--Text2);
    }

    .list-left small {
      color: #ffffffff;
      opacity: .2;
      font-size: 15px;
      float: left;
    }
  </style>
</head>

<body>

  <div class="navbar">
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

    <div class="navbar-right">
      <a href="Beranda.php" class="unselected">Beranda</a>
      <a href="#" class="selected">Jadwal</a>
      <a href="Laporan.php" class="unselected">Lapor</a>
      <a href="../Profil.php" class="unselected">Profil</a>
    </div>
  </div>

  <div class="main">
    <div class="container-left">
      <div class="schedule-container">
        <div class="table">
          <div class="top row">
            <input type="text" placeholder="Cari" class="search-input">
            <!-- FILTER BARU -->
            <input type="date" class="select-input date-filter">

            <select class="select-input route-filter" id="routeFilter">
              <option value="">Semua Rute</option>

              <?php while ($r = $routes->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($r['routeName']) ?>">
                  <?= htmlspecialchars($r['routeName']) ?>
                </option>
              <?php endwhile; ?>
            </select>

          </div>

          <div id="attendanceList" class="scrollable">



          </div>
        </div>
      </div>
    </div>
    <div class="container-right">
      <div class="card absensi-card">
        <img src="../../Asset/Image/Location.png">
        <div class="card-details">
          <h2>Absensi</h2>
          <p>Warga yang bertugas dapat mengisi presensi ronda malam dengan menekan tombol dibawah ini.</p>
          <?php if ($isEligible): ?>
            <button class="next-btn" onclick="mapping();">Isi Absensi</button>
          <?php else: ?>
            <button class="next-btn disabled" disabled title="Tidak memenuhi syarat usia / gender / RW">
              Tidak Memenuhi Syarat
            </button>
          <?php endif; ?>

        </div>
      </div>
      <div class="card pengajuan-card">
        <div class="card-details">
          <h2>Pengajuan</h2>
          <p>Warga dapat mengajukan diri untuk mengikuti ronda malam ataupun bagi warga yang bertugas dapat mengajukan
            izin tidak ikut ronda malam.</p>
          <?php if ($isEligible): ?>
            <button class="pengajuan-btn">Ajukan</button>
          <?php else: ?>
            <button class="pengajuan-btn disabled" disabled title="Tidak memenuhi syarat usia / gender / RW">
              Tidak Memenuhi Syarat
            </button>
          <?php endif; ?>

        </div>
        <img src="../../Asset/Image/Berkas.png">
      </div>
    </div>

    <div id="popup-laporan" class="popup">
      <div class="popup-content">
        <span class="close-btn">&times;</span>
        <h1>Absensi</h1>
        <div class="date-time">
          <p id="current-time"></p>
          <p>|</p>
          <p id="current-date"></p>
        </div>


        <div class="popup-container">
          <div class="popup-leftcontainer">
            <div id="map"></div>
          </div>
        </div>
      </div>
    </div>

    <div id="popup-pengajuan" class="popup">
      <div class="popup-content">
        <span class="close-btn-pengajuan">&times;</span>
        <h1>Pengajuan</h1>

        <div class="popup-container">
          <form class="FormPengajuan" id="pengajuanForm" onsubmit="Submit(event)">
            <select name="type" required>
              <option value="IkutRonda" selected>Ikut Ronda</option>
              <option value="TidakIkutRonda">Tidak Ikut Ronda</option>
            </select>

            <div class="box-content">
              <label>Hari/Tanggal:</label>
              <input type="date" name="date" required>
            </div>

            <div class="box-content">
              <label>Alasan:</label>
              <textarea name="description" placeholder="Min. 50 karakter" required></textarea>
            </div>

            <button type="submit" class="save-btn">Ajukan</button>
          </form>

        </div>
      </div>
    </div>

  </div>

</body>
<script>

  function toggleMenu() {
    const menu = document.querySelector('.navbar-right');
    menu.classList.toggle('show');
  }

  // Ambil elemen-elemen popup
  const popupLaporan = document.getElementById("popup-laporan");
  const popupPengajuan = document.getElementById("popup-pengajuan");
  const formPengajuan = document.getElementById("pengajuanForm")
  const NIK = 4342501053;

  const closeBtn = document.querySelector(".close-btn");
  const closePengajuanBtn = document.querySelector(".close-btn-pengajuan")
  const nextBtns = document.querySelectorAll(".next-btn");
  const pengajuanbtn = document.querySelectorAll(".pengajuan-btn")

  const gambarBtn = document.getElementById("gambar");
  const lokasiBtn = document.getElementById("lokasi");
  const img = document.getElementById('image');
  const leafletmap = document.getElementById('map');

  // Saat tombol ">" diklik → tampilkan popup
  nextBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      popupLaporan.style.display = "flex";
    });
  });

  pengajuanbtn.forEach((btn) => {
    btn.addEventListener("click", () => {
      popupPengajuan.style.display = "flex"
    });
  });

  // Tutup popup saat tombol X atau Tutup ditekan
  closeBtn.addEventListener("click", () =>
    popupLaporan.style.display = "none");

  closePengajuanBtn.addEventListener("click", () => {
    popupPengajuan.style.display = "none";
    formPengajuan.reset();
  });


  // Tutup popup kalau klik di luar konten
  window.addEventListener("click", (e) => {
    if (e.target === popupLaporan) popupLaporan.style.display = "none";
  });

  function mapping() {
    popupLaporan.style.display = "flex";
    updateDateTime();


    // Tunggu popup render → baru load map (mencegah container size = 0)
    setTimeout(() => {

      // === 2. Cek apakah user punya jadwal hari ini ===
      fetch("../../PHP/CheckAttendance.php")
        .then(res => res.json())
        .then(data => {

          if (data.status === "notFound") {
            Swal.fire({
              icon: "error",
              title: "Tidak Ada Jadwal",
              text: "Anda tidak terdaftar sebagai petugas ronda hari ini."
            });
            return;
          }

          if (data.status === "closed") {
            Swal.fire({
              icon: "info",
              iconColor: "var(--Text4)",
              title: "Gagal",
              color: "var(--Text1)",
              text: "Absensi hanya dapat dilakukan pada jam ronda malam.",
              confirmButtonColor: "var(--Text4)"
            });
            return;
          }

          const attendanceId = data.attendanceId;

          if (data.attendanceStatus === "Hadir") {
            Swal.fire({
              icon: "info",
              iconColor: 'var(--Text4)',
              title: "Sudah Absen",
              color: 'var(--Text1)',
              confirmButtonColor: 'var(--Text4)',
              text: "Anda sudah absen hari ini."
            });
            return;
          }

          // Lanjut ambil radius


          // === 3. Ambil radius lokasi RW ===
          fetch("../../PHP/GetRadius.php")
            .then(res => res.json())
            .then(radius => {

              if (radius.status !== "ok") {
                Swal.fire("Error", radius.message || "Lokasi RW belum disetting", "error");
                return;
              }

              const dbLat = Number(radius.latitude);
              const dbLng = Number(radius.longitude);
              const dbRadius = Number(radius.radius);

              if (!dbLat || !dbLng) {
                Swal.fire("Error", "Koordinat RW tidak valid", "error");
                return;
              }


              // === 4. Ambil lokasi GPS user ===
              navigator.geolocation.getCurrentPosition(pos => {
                const userLat = pos.coords.latitude;
                const userLng = pos.coords.longitude;

                // === 5. Inisialisasi map (HANYA SATU KALI) ===
                const map = L.map('map', {
                  zoomControl: false,
                  attributionControl: false
                }).setView([userLat, userLng], 17);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png')
                  .addTo(map);

                const userMarker = L.marker([userLat, userLng]).addTo(map);

                const circle = L.circle([dbLat, dbLng], {
                  color: 'red',
                  fillColor: '#f03',
                  fillOpacity: 0.4,
                  radius: dbRadius
                }).addTo(map);

                const distance = userMarker.getLatLng().distanceTo(circle.getLatLng());

                // === 6. Validasi absensi ===
                if (distance <= dbRadius) {

                  // Update attendance status di DB
                  fetch("../../PHP/UpdateAttendance.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "id=" + attendanceId
                  })

                    .then(r => r.json())
                    .then(res => {

                      if (res.status === "success") {
                        Swal.fire({
                          icon: "success",
                          iconColor: 'var(--Text4)',
                          title: "Berhasil",
                          color: 'var(--Text1)',
                          confirmButtonColor: 'var(--Text4)',
                          text: "Absensi berhasil dicatat!"
                        });
                      } else {
                        Swal.fire({
                          icon: "error",
                          iconColor: 'red',
                          title: "Gagal",
                          color: 'var(--Text1)',
                          confirmButtonColor: 'var(--Text4)',
                          text: "Gagal menyimpan absensi."
                        });
                      }
                    });

                } else {
                  Swal.fire({
                    icon: "info",
                    iconColor: 'var(--Text4)',
                    color: 'var(--Text1)',
                    confirmButtonColor: 'var(--Text4)',
                    title: "Gagal",
                    text: "Anda berada di luar radius lokasi ronda!"
                  });
                }
              });

            });

        });

    }, 200); // delay kecil agar popup render dulu
  }





  function showPosition(position) {
    const latitude = position.coords.latitude
    const longitude = position.coords.longitude

    const map = L.map('map', {
      zoomControl: false,
      attributionControl: false,
      dragging: true,
      scrollWheelZoom: true,
      doubleClickZoom: false,
      boxZoom: false,
      keyboard: false,
      touchZoom: false
    }).setView([latitude, longitude], 17);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 17,
    }).addTo(map);

    const marker = L.marker([latitude, longitude], { draggable: false })
      .addTo(map)
      .bindPopup('Lokasi Anda')
      .openPopup();

    var circle = L.circle([1.1221, 104.0465], {
      color: 'red',
      fillColor: '#f03',
      fillOpacity: 0.5,
      radius: 300
    }).addTo(map);

    // Define coordinates
    const markerLatLng = marker.getLatLng();
    const circleCenter = circle.getLatLng();
    const circleRadius = circle.getRadius(); // in meters

    // Calculate the distance between marker and circle center
    const distance = markerLatLng.distanceTo(circleCenter);

    if (NIK === 4342501053) {
      if (distance <= circleRadius) {
        Swal.fire({
          icon: "success",
          title: "Berhasil",
          text: "Kehadiran Anda sudah dicatat oleh sistem!",
          color: "var(--Text1)",
          showConfirmButton: false,
          timer: 2000
        })
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal",
          text: "Anda tidak berada diradius GPS Absensi!",
          color: "var(--Text1)",
          showConfirmButton: false,
          timer: 2000
        })
      }
    }
    else {
      Swal.fire({
        icon: "error",
        title: "Gagal",
        text: "Anda tidak terdaftar sebagai Peronda!",
        color: "var(--Text1)",
        showConfirmButton: false,
        timer: 2000
      })
    }
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

  function Submit(event) {
    event.preventDefault();

    const form = document.getElementById('pengajuanForm');
    const formData = new FormData(form);

    fetch("../../PHP/SubmitPengajuan.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.status === "success") {
          Swal.fire({
            icon: "success",
            title: "Berhasil",
            text: "Pengajuan berhasil dikirim!",
            color: "var(--Text1)",
            confirmButtonColor: "var(--Text4)"
          }).then(() => {
            popupPengajuan.style.display = "none";
            form.reset();
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Gagal",
            text: data.message,
            color: "var(--Text1)",
            confirmButtonColor: "var(--Text4)"
          });
        }
      });
  }


  function updateDateTime() {
    const now = new Date();

    // Format jam
    const hours = now.getHours().toString().padStart(2, "0");
    const minutes = now.getMinutes().toString().padStart(2, "0");

    // Format tanggal DD-MM-YY
    const day = now.getDate().toString().padStart(2, "0");
    const month = (now.getMonth() + 1).toString().padStart(2, "0");
    const year = now.getFullYear().toString().slice(-2);

    document.getElementById("current-time").textContent = `${hours}:${minutes}`;
    document.getElementById("current-date").textContent = `${day}-${month}-${year}`;
  }
  const dateFilter = document.querySelector(".date-filter");
  const routeFilter = document.querySelector(".route-filter");
  const searchInput = document.querySelector(".search-input");

  // LOAD DATA SAAT PAGE DIBUKA
  document.addEventListener("DOMContentLoaded", () => {
    // Set default date hari ini
    if (!dateFilter.value) {
      dateFilter.value = new Date().toISOString().split("T")[0];
    }
    loadAttendance();
  });

  // Event Listener filter
  dateFilter.addEventListener("change", loadAttendance);
  routeFilter.addEventListener("change", loadAttendance);
  searchInput.addEventListener("keyup", loadAttendance);




  function loadAttendance() {
    const scrollable = document.getElementById("attendanceList");

    const date = dateFilter.value;
    const route = routeFilter.value || "";
    const search = searchInput.value || "";

    fetch(`../../PHP/GetAttendanceList.php?date=${date}&route=${route}&search=${search}`)
      .then(r => r.json())
      .then(data => {

        console.log("SERVER:", data);
        scrollable.innerHTML = "";

        if (!Array.isArray(data) || data.length === 0) {
          scrollable.innerHTML = `<p class="empty">Tidak ada petugas.</p>`;
          return;
        }

        data.forEach(d => {
          scrollable.innerHTML += `
    <div class="list-nama">
      <div class="list-left">
        <h3>${d.full_name}</h3>
        <small>${d.phone}</small>
      </div>
    </div>
  `;
        });
      });

  }

  document.addEventListener("DOMContentLoaded", loadAttendance);




</script>

</html>