<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TREE</title>
  <link rel="icon" type="image/png" href="../Asset/Image/Logo.png">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="../CSS/LandingPage.css" />
  <link rel="stylesheet" href="../CSS/Main.css" />

  <!-- Font Awesome -->
  <script src="https://kit.fontawesome.com/8eb0a590d4.js" crossorigin="anonymous"></script>
</head>

<body>
  <div class="All">
    <!-- Navbar -->
    <nav class="Navbar">
      <div class="Container">
        <div class="LogoContainer">
          <img src="../Asset/Image/Logo.png" alt="Logo TREE" class="Logo" />
          <a href="#Beranda" class="LogoText">TREE</a>
        </div>

        <ul class="Nav-Links">
          <li><a href="#Beranda">Beranda</a></li>
          <li><a href="#Tentang">Tentang</a></li>
          <li><a href="#Layanan">Layanan</a></li>
          <li><a href="#Kontak">Kontak</a></li>
          <li><a href="Masuk.php">Masuk</a></li>
        </ul>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="Main">
      <!-- Beranda Section -->
      <section class="Beranda" id="Beranda">
        <div class="Container">
          <div class="ContainerText">
            <h1>TREE</h1>
            <h2>Teknologi Ronda Efektif dan Efisien</h2>
          </div>

          <div class="ContainerIlustrasi">
            <img style="width: 750px; " src="../Asset/Image/Illustration.png" alt="Ilustrasi Beranda" class="Ilustrasi" />
          </div>
        </div>
      </section>

      <!-- Tentang Section with Carousel -->
      <section class="Tentang" id="Tentang">
        <div class="Container">
          <h2>Tentang Kami</h2>
          <div class="carousel">
            <button class="carousel-btn prev"><i class="fa-solid fa-chevron-left"></i></button>

            <div class="carousel-track-container">
              <ul class="carousel-track">
                <li class="carousel-slide current-slide">
                  <img src="../Asset/Image/Tim.jpg" alt="Tim TREE">
                  <h3>Kolaborasi Warga</h3>
                  <p>Kami percaya keamanan lingkungan dimulai dari kerja sama antarwarga. TREE membantu menyatukan
                    komunitas dalam ronda digital.</p>
                </li>
                <li class="carousel-slide">
                  <img src="../Asset/Image/Vision.png" alt="TREE Vision" style="width: 300px;">
                  <h3>Visi Kami</h3>
                  <p>Menciptakan lingkungan yang aman, nyaman, dan adil melalui teknologi ronda berbasis digital yang
                    efektif.</p>
                </li>
                <li class="carousel-slide">
                  <img src="../Asset/Image/Mission.png" alt="TREE Mission" style="width: 300px;">
                  <h3>Misi Kami</h3>
                  <p>Meningkatkan partisipasi masyarakat dalam ronda dengan sistem penjadwalan dan notifikasi otomatis.
                  </p>
                </li>
              </ul>
            </div>

            <button class="carousel-btn next"><i class="fa-solid fa-chevron-right"></i></button>
          </div>
          <div class="carousel-nav">
            <button class="current-slide"></button>
            <button></button>
            <button></button>
          </div>
        </div>
      </section>


      <!-- Layanan Section -->
      <section class="Layanan" id="Layanan">
        <div class="Container">
          <h2>Layanan</h2>

          <div class="Feature-Box">
            <div class="Feature">
              <h3>Penjadwalan Efektif &amp; Efisien</h3>
              <img src="../Asset/Image/Calendar.png" alt="Penjadwalan" class="img1" />
              <hr>
              <p>Sistem pintar untuk jadwal ronda otomatis yang efisien dan terkoordinasi.</p>
            </div>

            <div class="Feature">
              <h3>Notifikasi Ronda</h3>
              <img src="../Asset/Image/Notification.png" alt="Notifikasi" class="img2" />
              <hr>
              <p>Notifikasi otomatis agar ronda selalu tepat waktu dan terkoordinasi.</p>
            </div>

            <div class="Feature">
              <h3>Rekapan Laporan Bulanan</h3>
              <img src="../Asset/Image/Report.png" alt="Rekapan Laporan" class="img3" />
              <hr>
              <p>Semua laporan ronda tersusun otomatis dan siap diakses untuk evaluasi kapan saja.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Kontak Section -->
      <section class="Kontak" id="Kontak">
        <div class="Container">
          <div class="ContainerAtas">
            <div class="ContainerShortDesc">
              <div class="ShortDesc">
                <img src="../Asset/Image/LogoText.png" alt="Icon TREE" class="LogoTextImg"/>
                <p>Tugas Ronda Efektif, Efisien, dan Adil bersama TREE</p>
              </div>

              <div class="Social">
                <ul class="Social-links">
                  <li>
                    <a href="https://www.instagram.com/terpal_b25/" target="_blank" aria-label="Instagram">
                      <i class="fa-brands fa-instagram"></i>
                    </a>
                  </li>
                </ul>
              </div>
            </div>

            <div class="ContainerNavigasi">
              <h3>Navigasi</h3>
              <ul class="Navi-Links">
                <li><a href="#Beranda">Beranda</a></li>
                <li><a href="#Tentang">Tentang Kami</a></li>
                <li><a href="#Layanan">Layanan</a></li>
                <li><a href="#Kontak">Kontak</a></li>
              </ul>
            </div>

            <div class="ContainerTautan">
              <h3>Tautan</h3>
              <ul class="Navi-Links">
                <li><a href="https://www.polibatam.ac.id/" target="_blank">Web Polibatam</a></li>
              </ul>
            </div>

            <div class="ContainerAlamat">
              <ul class="AlamatDesc">
                <li>Jl. Ahmad Yani, Batam Kota, Kota Batam, Kepulauan Riau, Indonesia</li>
                <li>Phone: +62-823-8759-3452</li>
                <li>Email: TreePohonHijau@gmail.com</li>
              </ul>
            </div>
          </div>

          <div class="ContainerBawah">
            <p>&copy; TREE 2025. All Rights Reserved.</p>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Carousel Script -->
  <script>
    const track = document.querySelector('.carousel-track');
    const slides = Array.from(track.children);
    const nextButton = document.querySelector('.next');
    const prevButton = document.querySelector('.prev');
    const dotsNav = document.querySelector('.carousel-nav');
    const dots = Array.from(dotsNav.children);

    let currentIndex = 0;

    function moveToSlide(index) {
      track.style.transform = `translateX(-${index * 100}%)`;
      slides.forEach(slide => slide.classList.remove('current-slide'));
      dots.forEach(dot => dot.classList.remove('current-slide'));
      slides[index].classList.add('current-slide');
      dots[index].classList.add('current-slide');
    }

    nextButton.addEventListener('click', () => {
      currentIndex = (currentIndex + 1) % slides.length;
      moveToSlide(currentIndex);
    });

    prevButton.addEventListener('click', () => {
      currentIndex = (currentIndex - 1 + slides.length) % slides.length;
      moveToSlide(currentIndex);
    });

    dotsNav.addEventListener('click', e => {
      const targetDot = e.target.closest('button');
      if (!targetDot) return;
      const index = dots.findIndex(dot => dot === targetDot);
      moveToSlide(index);
    });

  </script>
</body>

</html>