<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
 <link rel="stylesheet" href="header.css">

<header class="main-header">
  <div class="nav-left">
    <img src="logo kemhan 1.png" alt="banner" class="banner-img">
    <div class="text-left">
    <h2>HanZone</h2>
    <p class="tagline">Zona Pengetahuan Pertahanan</p>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  </div>
  </div>

  <div class="hamburger" id="hamburger">&#9776;</div>

<nav class="nav-right" id="navMenu">
  <div class="close-btn" id="closeBtn">&times;</div>

  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'pegawai'): ?>

      <a href="index.php"   class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Beranda</a>
      <a href="pegawai.php" class="<?= $currentPage === 'pegawai.php' ? 'active' : '' ?>">Artikel</a>
      <a href="forum.php"   class="<?= $currentPage === 'forum.php' ? 'active' : '' ?>">Forum</a>
      <a href="faq.php"     class="<?= $currentPage === 'faq.php' ? 'active' : '' ?>">FAQs</a>

  <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>

      <a href="index.php"   class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Beranda</a>
      <a href="artikel.php" class="<?= $currentPage === 'artikel.php' ? 'active' : '' ?>">Artikel</a>
      <a href="forum.php"   class="<?= $currentPage === 'forum.php' ? 'active' : '' ?>">Forum</a>
      <a href="faq.php"     class="<?= $currentPage === 'faq.php' ? 'active' : '' ?>">FAQs</a>

  <?php else: ?> <!-- USER BIASA / BELUM LOGIN -->

      <a href="index.php"   class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Beranda</a>
      <a href="artikel.php" class="<?= $currentPage === 'artikel.php' ? 'active' : '' ?>">Artikel</a>
      <a href="forum.php"   class="<?= $currentPage === 'forum.php' ? 'active' : '' ?>">Forum</a>
      <a href="faq.php"     class="<?= $currentPage === 'faq.php' ? 'active' : '' ?>">FAQs</a>

  <?php endif; ?>

  <!-- LOGIN / PROFILE -->
  <?php if (isset($_SESSION['nama'])): ?>
    <div class="dropdown">
      <button class="btn-login btn-user">
        <i class="fas fa-user-circle"></i>
        <span class="username"><?= htmlspecialchars($_SESSION['nama']); ?></span>
      </button>

      <div class="dropdown-content">
        <a href="profile.php">Profile</a>

        <?php if ($_SESSION['role'] === 'pegawai'): ?>
          <a href="dashboardpegawai.php">Dashboard Pegawai</a>
        <?php elseif ($_SESSION['role'] === 'admin'): ?>
          <a href="dashboard.php">Dashboard Admin</a>
        <?php endif; ?>

        <a href="logout.php">Keluar</a>
      </div>
    </div>
  <?php else: ?>
    <a class="btn-login" href="login.php">Masuk</a>
    <a class="btn-login" href="register.php">Daftar</a>
  <?php endif; ?>
</nav>

</header>
<script src="header.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
  // ===== Hamburger & Nav =====
  const hamburger = document.querySelector('.hamburger');
  const navMenu = document.querySelector('.nav-right');
  const closeBtn = document.querySelector('.close-btn');

  if (hamburger && navMenu) {
    hamburger.addEventListener('click', function(e) {
      e.stopPropagation();
      navMenu.classList.toggle('active');
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', function() {
        navMenu.classList.remove('active');
      });
    }

    document.addEventListener('click', function(e) {
      if (!navMenu.contains(e.target) && !hamburger.contains(e.target)) {
        navMenu.classList.remove('active');
      }
    });
  }

  // ===== Dropdown =====
  const dropdown = document.querySelector(".dropdown");
  if (dropdown) {
    const btn = dropdown.querySelector(".btn-login");
    const menu = dropdown.querySelector(".dropdown-content");

    if (btn && menu) {
      // Klik tombol → toggle dropdown
      btn.addEventListener("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropdown.classList.toggle("open");
      });

      // Klik di luar → tutup dropdown
      document.addEventListener("click", function(e) {
        if (!dropdown.contains(e.target)) {
          dropdown.classList.remove("open");
        }
      });

      // ESC untuk menutup
      document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") dropdown.classList.remove("open");
      });

      // Hover di desktop (optional)
      if (window.matchMedia("(hover: hover) and (pointer: fine)").matches) {
        dropdown.addEventListener("mouseenter", () => {
          // tetap bisa di klik
        });
        dropdown.addEventListener("mouseleave", () => {
          if (!dropdown.classList.contains("open")) {
            // nothing
          }
        });
      }
    }
  }

  // ===== FAQ =====
  const faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach(item => {
    item.addEventListener('click', function() {
      this.classList.toggle('active');
      const answer = this.querySelector('.answer');
      if (answer) {
        answer.style.display = (answer.style.display === "block") ? "none" : "block";
      }
    });
  });

  // ===== Form (optional) =====
  const form = document.querySelector("form");
  if (form) {
    form.addEventListener("submit", function(e) {
      e.preventDefault();
      // fungsi pencarian / submit disini
    });
  }
});
</script>