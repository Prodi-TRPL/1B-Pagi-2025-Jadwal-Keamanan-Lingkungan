<?php
session_start();
include "../../PHP/Config.php";

/* ===================== AMBIL RW DARI SESSION ===================== */
if (!isset($_SESSION['rw'])) {
    die("RW tidak ditemukan di session. Pastikan login menyimpan rw.");
}
$rw = $_SESSION['rw'];  // <-- RW pengelola login

/* ===================== FILTER (tahun, bulan, minggu, hari) ===================== */
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');
$week = $_GET['week'] ?? 1;
$day = $_GET['day'] ?? "Monday";

/* ===================== HITUNG RANGE TANGGAL MINGGU ===================== */
$firstDay = date("Y-m-01", strtotime("$year-$month"));
$weekStart = date("Y-m-d", strtotime("$firstDay +" . (($week - 1) * 7) . " days"));
$weekEnd = date("Y-m-d", strtotime("$weekStart +6 days"));

/* ===================== LOAD RUTE HANYA UNTUK RW INI ===================== */
$ruteQuery = mysqli_query($conn, "
    SELECT DISTINCT routeName 
    FROM route 
    WHERE rw='$rw'
    ORDER BY routeName ASC
");

/* ===================== PETUGAS SESUAI RW DAN USIA ===================== */
$reqQuery = mysqli_query($conn, "SELECT minAge, maxAge, timeStart, timeEnd FROM requirements WHERE rw='$rw'");
$reqData = mysqli_fetch_assoc($reqQuery);

$minAge = $reqData['minAge'] ?? 0;
$maxAge = $reqData['maxAge'] ?? 200;

$timeStart = $reqData['timeStart'] ?? 22;
$timeEnd = $reqData['timeEnd'] ?? 4;

$petugasQuery = mysqli_query($conn, "
    SELECT nik, name 
    FROM inhabitants 
    WHERE gender='L' 
      AND rw='$rw'
      AND TIMESTAMPDIFF(YEAR, dateBirth, CURDATE()) BETWEEN $minAge AND $maxAge
    ORDER BY name ASC
");

/* ===================== PETUGAS UNTUK EDIT (HANYA RW INI JUGA) ===================== */
$allPetugasQuery = mysqli_query($conn, "
    SELECT nik, name 
    FROM inhabitants 
    WHERE gender='L' AND rw='$rw'
    ORDER BY name ASC
");

$rwList = [$rw];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>TREE</title>
    <link rel="icon" type="image/png" href="../../Asset/Image/Logo.png">

    <!-- CSS -->
    <link rel="stylesheet" href="../../CSS/Jadwal-Pengelola.css">
    <link rel="stylesheet" href="../../CSS/Main.css">

    <!-- ICON -->
    <script src="https://kit.fontawesome.com/8eb0a590d4.js" crossorigin="anonymous"></script>

    <!-- LEAFLET -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        /* small inline styles needed for popup map / layout */
        #mapRadius {
            width: 100%;
            height: 300px;
            border-radius: 8px;
        }

        .popup-content {
            position: relative;
            z-index: 10000;
        }

        /* ensure popup radius two-column layout looks good (if not present in external CSS) */
        #popupRadius .popup-box {
            width: 760px !important;
            max-width: 95%;
            display: flex;
            flex-direction: column;
        }

        #popupRadius .popup-content {
            display: flex;
            gap: 20px;
            width: 100%;
        }

        #popupRadius .left-side,
        #popupRadius .right-side {
            width: 50%;
        }

        #popupRadius .right-side {
            border-left: 2px solid #ddd;
            padding-left: 18px;
        }

        #popupRadius h2 {
            font-size: 18px;
            color: #4F6F52;
            margin-bottom: 10px;
        }

        #popupRadius .age-grid {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        #popupRadius .age-field {
            width: 50%;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        #popupRadius .age-field input {
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        #popupRadius .popup-buttons {
            justify-content: center;
            margin-top: 12px;
        }

        .status-badge {
            float: right;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 6px;
            text-transform: capitalize;
        }

        /* Merah */
        .status-badge.absen {
            background: #ff4d4d;
            color: white;
        }

        /* Hijau */
        .status-badge.hadir {
            background: #1bbf4b;
            color: white;
        }

        .route-th {
            padding: 12px 14px;
        }

        .route-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .route-name {
            white-space: nowrap;
        }

        .route-header form {
            margin: 0;
        }

        .delete-route-btn {
            background: none;
            border: none;
            color: var(--Text2);
            font-size: 14px;
            cursor: pointer;
            line-height: 1;
            opacity: .2;
            transition: .1s ease;
            font-weight: 800;
        }

        .delete-route-btn:hover {
            scale: 1.2;
            color: red;
            opacity: 1;
        }
    </style>
</head>

<body>

    <!-- ===================== NAVBAR ===================== -->
    <nav class="navbar">
        <div class="navbar-left">
            <div class="profile-icon">
                <img src="../../Asset/Image/Logo.png">
                <p>TREE</p>
            </div>
        </div>

        <div class="navbar-right">
            <a href="Beranda.php" class="unselected">Beranda</a>
            <a href="#" class="selected">Jadwal</a>
            <a href="Data-Warga.php" class="unselected">Data Warga</a>
            <a href="Laporan.php" class="unselected">Laporan</a>
            <a href="../Profil.php" class="unselected">Profil</a>
        </div>
    </nav>

    <!-- ===================== CONTAINER ===================== -->
    <div class="container">

        <!-- ===================== FILTER ===================== -->
        <div class="filter-box">

            <div class="filter-left">
                <select id="filter-year" onchange="applyFilter()">
                    <option <?= $year == 2025 ? 'selected' : '' ?>>2025</option>
                    <option <?= $year == 2026 ? 'selected' : '' ?>>2026</option>
                </select>

                <select id="filter-month" onchange="applyFilter()">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $month == $m ? 'selected' : '' ?>>
                            <?= date("F", strtotime("2025-$m-01")) ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <select id="filter-week" onchange="applyFilter()">
                    <?php for ($w = 1; $w <= 5; $w++): ?>
                        <option value="<?= $w ?>" <?= $week == $w ? 'selected' : '' ?>>
                            Minggu <?= $w ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="filter-right">
                <button class="btn-add auto-btn" onclick="autoGenerateMonth()">Auto Jadwal</button>
                <button class="btn-add" onclick="openPopup()">Jadwal</button>
                <button class="btn-add" onclick="openRutePopup()">Rute</button>

                <button class="btn-add" onclick="openRadiusPopup()" title="Pengaturan">
                    <i class="fas fa-cog"></i>
                </button>

                <button class="btn-add email-btn" onclick="sendAllScheduleEmail()">
                    <i class="fas fa-envelope"></i> Kirim Notifikasi
                </button>
            </div>

        </div>

        <!-- ===================== TABEL JADWAL ===================== -->
        <div class="layout">

            <!-- DAYS -->
            <div class="day-list">
                <?php
                $days = [
                    "Monday" => "Senin",
                    "Tuesday" => "Selasa",
                    "Wednesday" => "Rabu",
                    "Thursday" => "Kamis",
                    "Friday" => "Jumat",
                    "Saturday" => "Sabtu",
                    "Sunday" => "Minggu"
                ];

                foreach ($days as $eng => $indo): ?>
                    <div class="day-item <?= $day == $eng ? 'active' : '' ?>" onclick="selectDay('<?= $eng ?>')">
                        <?= $indo ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- TABLE -->
            <div class="schedule-table">
                <table>
                    <thead>
                        <tr>
                            <?php
                            // Pastikan query rute mengambil routeId
                            $ruteQuery = mysqli_query($conn, "
    SELECT routeId, routeName 
    FROM route 
    WHERE rw='$rw'
    ORDER BY routeName ASC
");

                            mysqli_data_seek($ruteQuery, 0);

                            while ($r = mysqli_fetch_assoc($ruteQuery)):
                                $routeId = $r['routeId'];
                                $routeName = htmlspecialchars($r['routeName']);
                                ?>
                                <th class="route-th">
                                    <div class="route-header">
                                        <span class="route-name"><?= $routeName ?></span>

                                        <form action="../../PHP/Pengelola/deleterute.php" method="POST"
                                            onsubmit="return confirm('Hapus rute ini beserta semua jadwal & absensinya?')">
                                            <input type="hidden" name="routeId" value="<?= $routeId ?>">
                                            <input type="hidden" name="year" value="<?= $year ?>">
                                            <input type="hidden" name="month" value="<?= $month ?>">
                                            <input type="hidden" name="week" value="<?= $week ?>">
                                            <input type="hidden" name="day" value="<?= $day ?>">
                                            <button type="submit" class="delete-route-btn">✖</button>
                                        </form>
                                    </div>
                                </th>
                            <?php endwhile; ?>
                        </tr>
                    </thead>


                    <tbody>
                        <tr>
                            <?php
                            // Pastikan query rute sudah ada routeId
                            mysqli_data_seek($ruteQuery, 0);

                            while ($r = mysqli_fetch_assoc($ruteQuery)):
                                $routeId = $r['routeId'];
                                $route = $r['routeName'];

                                $sql = "
                                    SELECT i.name, a.attendanceStatus 
                                    FROM schedule s
                                    LEFT JOIN attendances a ON s.scheduleId = a.scheduleId
                                    LEFT JOIN inhabitants i ON a.nik = i.nik
                                    WHERE s.route='$route'
                                    AND DATE(s.date) BETWEEN '$weekStart' AND '$weekEnd'
                                    AND DAYNAME(s.date)='$day'
                                    ORDER BY i.name ASC
                                ";

                                $res = mysqli_query($conn, $sql);

                                echo "<td><div class='Box'>";

                                if (mysqli_num_rows($res) == 0) {
                                    echo "<p style='text-align:center;opacity:0.7'>Belum ada jadwal</p>";
                                } else {
                                    $hasData = false;

                                    while ($p = mysqli_fetch_assoc($res)) {

                                        // kalau tidak ada petugas → skip row kosong dari LEFT JOIN
                                        if ($p['name'] === null)
                                            continue;

                                        $hasData = true;

                                        $status = strtolower($p['attendanceStatus']);

                                        echo "<div class='per-data'>
        {$p['name']}
        <span class='status-badge $status'>
            {$p['attendanceStatus']}
        </span>
      </div>";
                                    }

                                    if (!$hasData) {
                                        echo "<p style='text-align:center;opacity:0.7'>Belum ada jadwal</p>";
                                    }

                                }

                                echo "</div></td>";

                            endwhile;
                            ?>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- ========================= POPUP JADWAL ========================= -->
    <div id="popupJadwal" class="popup">
        <div class="popup-box">
            <button class="close-x" onclick="closePopup()">✖</button>
            <h2>Atur Jadwal Ronda</h2>
            <form action="../../PHP/Pengelola/addJadwal.php" method="POST">
                <label>Tanggal</label>
                <input type="date" name="customDate" required value="<?= date('Y-m-d') ?>">
                <label>Rute</label>
                <select name="route" id="routeDropdown" required>
                    <option value="">--Pilih Rute--</option>
                    <?php mysqli_data_seek($ruteQuery, 0);
                    while ($r = mysqli_fetch_assoc($ruteQuery)): ?>
                        <option value="<?= $r['routeName'] ?>"><?= $r['routeName'] ?></option>
                    <?php endwhile; ?>
                </select>
                <label>Petugas</label>
                <select name="nik[]" id="petugasDropdown" multiple required>

                </select>
                <div class="popup-buttons">
                    <button type="submit" class="btn-save">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================= POPUP EDIT ========================= -->
    <div id="popupEdit" class="popup">
        <div class="popup-box">
            <button class="close-x" onclick="closeEditPopup()">✖</button>
            <h2 id="editTitle">Edit Jadwal / Rute</h2>

            <!-- Form untuk edit rute + jadwal -->
            <form action="../../PHP/Pengelola/editRute.php" method="POST" id="formEditRute">
                <!-- Hidden input untuk routeId -->
                <input type="hidden" name="routeId" id="routeIdEdit">
                <!-- Input nama rute -->
                <label>Nama Rute</label>
                <input type="text" name="routeName" id="routeNameEdit" required>

                <!-- Input untuk jadwal lama (tidak dihapus, tetap dipakai) -->
                <input type="hidden" name="route" id="routeEdit">
                <label>Tanggal</label>
                <input type="date" name="customDate" id="dateEdit" required value="<?= date('Y-m-d') ?>">
                <label>Petugas</label>
                <select name="nik[]" id="petugasEdit" multiple required></select>

                <div class="popup-buttons">
                    <button type="button" class="btn-delete" id="btnDeleteEdit">Hapus</button>
                    <button type="submit" class="btn-save">Simpan</button>
                </div>
            </form>

            <!-- Form delete tetap ada -->
            <form id="formDelete" action="../../PHP/Pengelola/deleteRute.php" method="POST" style="display:none;">
                <input type="hidden" name="routeId" id="routeIdDelete">
            </form>
        </div>
    </div>

    <!-- ========================= POPUP RUTE ========================= -->
    <div id="popupRute" class="popup">
        <div class="popup-box">
            <button class="close-x" onclick="closeRutePopup()">✖</button>
            <h2>Tambah Rute Baru</h2>
            <form action="../../PHP/Pengelola/addRute.php" method="POST">
                <input type="text" name="routeName" placeholder="Masukkan nama rute" required>
                <div class="popup-buttons">
                    <button type="submit" class="btn-save">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================= POPUP RADIUS (BARU - KIRI MAP, KANAN BATAS USIA) ========================= -->
    <div id="popupRadius" class="popup">
        <div class="popup-box">
            <button class="close-x" onclick="closeRadiusPopup()">✖</button>

            <!-- CONTENT (2 columns) -->
            <div class="popup-content">

                <!-- LEFT: map, RW select, radius -->
                <div class="left-side">
                    <h2>Atur Radius Absensi</h2>

                    <div id="mapRadius"></div>

                    <label>Radius (meter): <span id="radiusValue">500</span></label>
                    <input type="range" id="radiusSlider" min="50" max="2000" step="10" value="500"
                        style="width:100%; margin-bottom:10px;">
                </div>

                <!-- RIGHT: usia min & max -->
                <div class="right-side">
                    <h2>Pengaturan Ronda</h2>

                    <div class="age-grid">
                        <div class="age-field">
                            <label>Minimal Usia</label>
                            <input type="number" id="minAgeInput" min="15" max="99" value="20">
                        </div>

                        <div class="age-field">
                            <label>Maksimal Usia</label>
                            <input type="number" id="maxAgeInput" min="15" max="99" value="50">
                        </div>
                    </div>

                    <div class="age-grid">
                        <div class="age-field">
                            <label>Waktu Mulai</label>
                            <input type="time" id="timeStartInput" value="22:00" id="minTimeInput">
                        </div>

                        <div class="age-field">
                            <label>Waktu Akhir</label>
                            <input type="time" id="timeEndInput" value="04:00" id="maxTimeInpupt">
                        </div>
                    </div>

                </div>

            </div>

            <!-- BUTTONS inside popup-box -->
            <div class="popup-buttons popup-radius-buttons">
                <button type="button" class="btn-save" onclick="loadRadiusForSelectedRW()">Load RW</button>
                <button type="button" class="btn-save" onclick="saveRadius()">Simpan</button>
            </div>

        </div>
    </div>

    <!-- ========================= SCRIPTS ========================= -->
    <script>

        function deleteRoute(id) {
            if (confirm("Hapus rute ini? Semua jadwal di rute ini akan ikut terhapus!")) {
                window.location.href = "../../PHP/Pengelola/deleteRute.php?routeId=" + id;
            }
        }


        async function autoGenerateMonth() {
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = "Membuat...";

            const res = await fetch('../../PHP/autoGenerateMonth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'year=2025&month=12'
            });

            const data = await res.json();

            btn.innerHTML = "Auto Generate";
            btn.disabled = false;

            if (data.success) {
                alert("Jadwal 1 bulan berhasil dibuat!");
                location.reload();
            }
        }

        function sendAllScheduleEmail() {
            fetch("../../PHP/getAttendanceSchedule.php")
                .then(r => r.json())
                .then(data => {

                    const grouped = {};
                    data.forEach(j => {
                        if (!grouped[j.nik]) {
                            grouped[j.nik] = { name: j.full_name, email: j.email, list: [] };
                        }
                        grouped[j.nik].list.push(j);
                    });

                    const entries = Object.entries(grouped);
                    const batchSize = 50;
                    let i = 0;

                    function sendBatch() {
                        if (i >= entries.length) {
                            alert("SEMUA EMAIL TERKIRIM ✔️");
                            return;
                        }

                        const chunk = Object.fromEntries(entries.slice(i, i + batchSize));

                        fetch("../../PHP/sendScheduleEmail.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify(chunk)
                        }).then(() => { i += batchSize; sendBatch(); });
                    }

                    sendBatch();
                });
        }



        /* ========================= FILTER & DAY ========================= */
        function applyFilter() {
            const y = document.getElementById('filter-year').value;
            const m = document.getElementById('filter-month').value;
            const w = document.getElementById('filter-week').value;
            const d = "<?= $day ?>";
            window.location.href = `Jadwal.php?year=${y}&month=${m}&week=${w}&day=${d}`;
        }

        function selectDay(daySel) {
            const y = document.getElementById('filter-year').value;
            const m = document.getElementById('filter-month').value;
            const w = document.getElementById('filter-week').value;
            window.location.href = `Jadwal.php?year=${y}&month=${m}&week=${w}&day=${daySel}`;
        }

        /* ========================= POPUP UTILS ========================= */
        function openPopup() { document.getElementById('popupJadwal').classList.add('show') }
        function closePopup() { document.getElementById('popupJadwal').classList.remove('show'); }
        function openRutePopup() { document.getElementById('popupRute').classList.add('show'); }
        function closeRutePopup() { document.getElementById('popupRute').classList.remove('show'); }
        function closeEditPopup() { document.getElementById('popupEdit').style.display = "none"; }

        /* ========================= PETUGAS ========================= */
        function filterPetugasByRW() {
            const route = document.getElementById("routeDropdown").value;

            fetch(`../../PHP/Pengelola/getPetugas.php?route=${encodeURIComponent(route)}`)
                .then(res => res.json())
                .then(data => {
                    if (!Array.isArray(data)) {
                        console.error("API ERROR:", data);
                        return;
                    }

                    const sel = document.getElementById('petugasDropdown');
                    sel.innerHTML = '';

                    data.forEach(p => {
                        const o = document.createElement('option');
                        o.value = p.nik;
                        o.textContent = p.name;
                        sel.appendChild(o);
                    });
                });
        }

        function loadPetugasForRW() {
            const route = document.getElementById("routeEdit").value;

            fetch(`../../PHP/Pengelola/getPetugasByRoute.php?route=${encodeURIComponent(route)}`)
                .then(res => res.json())
                .then(data => {
                    if (!Array.isArray(data)) {
                        console.error("API ERROR:", data);
                        return;
                    }

                    const sel = document.getElementById('petugasEdit');
                    const selectedValues = Array.from(sel.selectedOptions).map(o => o.value);
                    sel.innerHTML = '';

                    data.forEach(p => {
                        const o = document.createElement('option');
                        o.value = p.nik;
                        o.textContent = p.name;
                        if (selectedValues.includes(p.nik)) o.selected = true;
                        sel.appendChild(o);
                    });
                });
        }


        function openEditPopup(routeName, date, routeId) {
            document.getElementById('routeEdit').value = routeName;
            document.getElementById('routeNameEdit').value = routeName; // nama rute
            document.getElementById('dateEdit').value = date;
            document.getElementById('routeIdDelete').value = routeId;

            // Tambahkan agar routeId dikirim ke editRute.php
            document.getElementById('routeIdEdit').value = routeId;

            // tombol hapus
            const btnDelete = document.getElementById('btnDeleteEdit');
            btnDelete.onclick = function () {
                if (confirm("Yakin ingin menghapus rute ini?")) {
                    document.getElementById('formDelete').submit();
                }
            };

            document.getElementById('popupEdit').classList.add('show');

            // load petugas
            fetch(`../../PHP/Pengelola/getPetugasByRoute.php?route=${encodeURIComponent(routeName)}&date=${encodeURIComponent(date)}&rw=<?= $_SESSION['rw'] ?>`)

                .then(res => res.json())
                .then(data => {
                    const sel = document.getElementById('petugasEdit');
                    sel.innerHTML = '';
                    data.forEach(p => {
                        const o = document.createElement('option');
                        o.value = p.nik;
                        o.textContent = p.name;
                        if (p.selected == 1) o.selected = true;
                        sel.appendChild(o);
                    });
                })
                .catch(err => { console.error(err); alert('Gagal memuat petugas.'); });
        }

        /* ========================= POPUP RADIUS ========================= */
        let mapRadius = null, markerRadius = null, circleRadius = null;
        let selectedLat = null, selectedLng = null;
        const defaultLat = -6.200000, defaultLng = 106.816666, defaultRadius = 500;

        function openRadiusPopup() {
            document.getElementById('popupRadius').classList.add('show');
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    pos => initMapRadius(pos.coords.latitude, pos.coords.longitude, defaultRadius),
                    () => initMapRadius(defaultLat, defaultLng, defaultRadius)
                );
            } else {
                initMapRadius(defaultLat, defaultLng, defaultRadius);
            }
        }

        function closeRadiusPopup() { document.getElementById('popupRadius').classList.remove('show'); }

        function initMapRadius(lat, lng, radius) {
            selectedLat = lat; selectedLng = lng;
            if (!mapRadius) {
                mapRadius = L.map('mapRadius', { zoomControl: true, attributionControl: false }).setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapRadius);
                markerRadius = L.marker([lat, lng], { draggable: true }).addTo(mapRadius);
                circleRadius = L.circle([lat, lng], { radius: radius, color: '#2b7af4', fillOpacity: 0.12 }).addTo(mapRadius);

                markerRadius.on('drag', e => {
                    const pos = e.target.getLatLng();
                    circleRadius.setLatLng(pos);
                    selectedLat = pos.lat; selectedLng = pos.lng;
                });

                document.getElementById('radiusSlider').addEventListener('input', function () {
                    const val = parseInt(this.value);
                    circleRadius.setRadius(val);
                    document.getElementById('radiusValue').textContent = val;
                });

                mapRadius.whenReady(() => { setTimeout(() => mapRadius.invalidateSize(), 120); });
            } else {
                markerRadius.setLatLng([lat, lng]);
                circleRadius.setLatLng([lat, lng]);
                circleRadius.setRadius(radius);
                document.getElementById('radiusSlider').value = radius;
                document.getElementById('radiusValue').textContent = radius;
                mapRadius.invalidateSize();
                mapRadius.panTo([lat, lng]);
            }
        }

        function loadRadiusForSelectedRW() {
            const rw = <?= $_SESSION['rw'] ?>;   // langsung dari PHP

            fetch(`../../PHP/Pengelola/getRadius.php?rw=${encodeURIComponent(rw)}`)
                .then(res => res.json())
                .then(data => {
                    const lat = data.latitude ?? defaultLat;
                    const lng = data.longitude ?? defaultLng;
                    const radius = data.radius ?? defaultRadius;

                    initMapRadius(lat, lng, radius);

                    if (data.minAge !== undefined) document.getElementById('minAgeInput').value = data.minAge;
                    if (data.maxAge !== undefined) document.getElementById('maxAgeInput').value = data.maxAge;
                    if (data.timeStart !== undefined) document.getElementById('timeStartInput').value = data.timeStart;
                    if (data.timeEnd !== undefined) document.getElementById('timeEndInput').value = data.timeEnd;
                })
                .catch(err => { console.error(err); alert('Gagal memuat data RW.'); initMapRadius(defaultLat, defaultLng, defaultRadius); });
        }

        function saveRadius() {
            if (!markerRadius || !circleRadius) { alert("Peta belum siap."); return; }

            const rw = "<?php echo $_SESSION['rw']; ?>";

            if (!rw) { alert("Pilih RW terlebih dahulu!"); return; }

            const latlng = markerRadius.getLatLng();
            const radius = Math.round(circleRadius.getRadius());
            const minAge = parseInt(document.getElementById('minAgeInput').value || 0);
            const maxAge = parseInt(document.getElementById('maxAgeInput').value || 0);
            const timeStart = document.getElementById('timeStartInput').value
            const timeEnd = document.getElementById('timeEndInput').value

            if (isNaN(minAge) || isNaN(maxAge) || minAge <= 0 || maxAge <= 0) { alert("Masukkan batas usia yang valid."); return; }
            if (minAge > maxAge) { alert("Minimal usia tidak boleh lebih besar dari maksimal usia."); return; }

            if (timeStart < timeEnd) { alert("waktu mulai ronda tidak boleh lebih besar dari waktu selesai."); return; }

            fetch('../../PHP/Pengelola/saveRadius.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ rw, lat: latlng.lat, lng: latlng.lng, radius, minAge, maxAge, timeStart, timeEnd })
            })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        alert('Berhasil menyimpan pengaturan.');

                        closeRadiusPopup();

                        // 🔥 RELOAD JADWAL REALTIME
                        if (typeof reloadJadwalRealtime === "function") {
                            reloadJadwalRealtime();
                        } else {
                            location.reload(); // fallback kalau function belum ada
                        }
                    } else {
                        alert('Gagal menyimpan: ' + (result.message || 'unknown'));
                    }
                })
        }

        function reloadJadwalRealtime() {
            const url = window.location.href;

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const newContent = doc.querySelector(".jadwal-container");
                    const oldContent = document.querySelector(".jadwal-container");

                    if (newContent && oldContent) {
                        oldContent.innerHTML = newContent.innerHTML;
                    } else {
                        location.reload(); // fallback
                    }
                });
        }



        /* ========================= UTILS ========================= */
        // pastikan minAge <= maxAge
        document.addEventListener('input', e => {
            if (e.target.id === 'minAgeInput') {
                const min = parseInt(e.target.value || 0);
                const maxEl = document.getElementById('maxAgeInput');
                if (!isNaN(min) && maxEl && parseInt(maxEl.value || 0) < min) maxEl.value = min;
            }
            if (e.target.id === 'maxAgeInput') {
                const max = parseInt(e.target.value || 0);
                const minEl = document.getElementById('minAgeInput');
                if (!isNaN(max) && minEl && parseInt(minEl.value || 0) > max) minEl.value = max;
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            filterPetugasByRW();
        });

    </script>


</body>

</html>