<?php
session_start();
include 'koneksi.php';

 // Default fallback
$backUrl = 'dashboard.php';

// Kalau ada parameter 'from' di URL, pakai itu
if (isset($_GET['from'])) {
    if ($_GET['from'] === 'beranda') {
        $backUrl = 'index.php';
    } elseif ($_GET['from'] === 'artikel') {
        $backUrl = 'artikel.php';
    } elseif ($_GET['from'] === 'review') {
        $backUrl = 'review.php';
    }
}

// Ambil riwayat blacklist (penghapusan pengguna)
$riwayatUser = mysqli_query($conn, "SELECT * FROM blacklist ORDER BY id DESC");

// Ambil history artikel yang publish / acc / tolak
$riwayatArtikel = mysqli_query($conn, "
  SELECT a.id, a.judul, a.status, a.created_at, r.nama AS penulis
  FROM artikel a
  LEFT JOIN regsitrasi r ON a.pegawai_id = r.id
  WHERE a.status IN ('publish', 'rejected')
  ORDER BY a.created_at DESC
");
?>


<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Aksi</title>
<link rel="icon" type="image/png" href="logo kemhan 1.png">

<!-- Bootstrap & Font -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
  body {
    background-color: #f8f9fb;
    font-family: 'Inter', sans-serif;
    margin: 0;
    padding: 0;
    color: #333;
  }

  .header-bar {
    background: linear-gradient(90deg, #7a0000, #a30000);
    color: white;
    padding: 14px 36px;
    font-size: 22px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .container-box {
    margin: 25px auto;
    width: 92%;
    background: white;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    overflow: hidden;
    border: 1px solid #e3e6ea;
    padding: 25px 30px;
  }

  h2 {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 18px;
    color: #7a0000;
    text-align: center;
    padding-bottom: 20px;
  }

  table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 30px;
  background: transparent;
}

  .table-container {
    margin: 25px auto;
    width: 92%;
    background: white;
    border-radius: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    border: 1px solid #e3e6ea;
    overflow: hidden;
}

  thead {
    background-color: #f6f7fb;
    color: #444;
    font-weight: 600;
    border-bottom: 1px solid #e0e3e7;
  }

  .table-container table {
    width: 100%;
    border-collapse: collapse;
}

/* hilangkan border default */
.table-container th,
.table-container td {
    border: none !important;
}
  
  thead th {
  background: #d8dbea;
  color: #333;
  padding: 14px 16px;
  font-size: 14px;
  font-weight: 600;
  text-align: center;
  border-bottom: 2px solid #cfd3e3;
}

tbody td {
  padding: 18px 16px;
  font-size: 14.5px;
  color: #333;
  text-align: center;
  border-bottom: 1px solid #b6b6b6ff; /* HANYA GARIS BAWAH */
  vertical-align: middle;
}

tbody tr:hover {
    background: #f7f8fc;
}

tbody tr:last-child td {
  border-bottom: none !important;
}

thead th:first-child {
  border-top-left-radius: 12px;
}
thead th:last-child {
  border-top-right-radius: 12px;
}

  tr:hover {
    background-color: #fafafa;
  }

   .back-btn-wrapper {
    width: 92%;
    margin: 22px auto 0;
    padding-left: 4px;
}

    .btn-back-shopee {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 44px;
    height: 44px;
    background: #ffffff;
    border-radius: 50%;
    border: 1px solid #e5e5e5;
    text-decoration: none;
    cursor: pointer;
    transition: 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    position: relative;
}

.btn-back-shopee::before {
    content: "";
    position: absolute;
    width: 58px;     
    height: 58px;
    border-radius: 10%;
    background: rgba(0,0,0,0.05);  
    z-index: -1;    
}

.btn-back-shopee:hover {
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.4);
    transform: scale(1.05);
}

.arrow-shopee {
    width: 22px;
    stroke: #333;
    stroke-width: 3.2; 
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}

  .badge-success {
  background-color: #d1fae5;
  color: #065f46;
  padding: 6px 16px;
  border-radius: 999px;
  font-weight: 600;
  font-size: 13px;
}

.badge-danger {
  background-color: #fee2e2;
  color: #991b1b;
  padding: 6px 16px;
  border-radius: 999px;
  font-weight: 600;
  font-size: 13px;
}

.badge-warning {
  background-color: #fff3cd;
  color: #664d03;
  padding: 6px 16px;
  border-radius: 999px;
  font-weight: 600;
  font-size: 13px;
}

  .section-divider {
  border: none;
  border-top: 2px solid #b0b0b0; 
  margin: 35px 0;
  opacity: 0.7;
}

</style>
</head>
<body>

  <!-- HEADER -->
  <div class="header-bar">
    <i>📜</i> Riwayat Aksi
  </div>

    <div class="back-btn-wrapper">
    <a href="<?= $backUrl ?>" class="btn-back-shopee">
        <svg class="arrow-shopee" viewBox="0 0 24 24">
            <path d="M15 6l-6 6 6 6" />
        </svg>
    </a>
</div>

  <!-- ISI KONTEN -->
  <div class="container-box">

    <!-- RIWAYAT PENGHAPUSAN -->
    <h2>Riwayat Penghapusan Pengguna</h2>
    <table>
      <thead>
        <tr>
          <th style="width: 60px;">No</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Aksi</th>
          <th>Tanggal</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $no = 1;
        if (mysqli_num_rows($riwayatUser) > 0) {
          while ($row = mysqli_fetch_assoc($riwayatUser)) { ?>
            <tr>
              <td><?= $no++; ?></td>
              <td><?= htmlspecialchars($row['nama'] ?? '-'); ?></td>
              <td><?= htmlspecialchars($row['email'] ?? '-'); ?></td>
              <td><span class="badge-warning">Dihapus</span></td>
              <td><?= htmlspecialchars($row['tanggal']); ?></td>
            </tr>
          <?php }
        } else { ?>
          <tr><td colspan="5">Belum ada riwayat penghapusan pengguna.</td></tr>
        <?php } ?>
      </tbody>
    </table>

    <div class="section-divider"></div>

    <!-- RIWAYAT PERSETUJUAN ARTIKEL -->
    <h2>Riwayat Persetujuan Artikel</h2>
    <table>
      <thead>
        <tr>
          <th style="width: 60px;">No</th>
          <th>Judul</th>
          <th>Penulis</th>
          <th>Status</th>
          <th>Tanggal</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $no = 1;
        if (mysqli_num_rows($riwayatArtikel) > 0) {
          while ($art = mysqli_fetch_assoc($riwayatArtikel)) { ?>
            <tr>
              <td><?= $no++; ?></td>
              <td><?= htmlspecialchars($art['judul']); ?></td>
              <td><?= htmlspecialchars($art['penulis']); ?></td>
              <td>
               <?php if($art['status'] == 'publish' || $art['status'] == 'acc'){ ?>
  <span class="badge-success">Publish</span>
<?php } elseif($art['status'] == 'rejected'){ ?>
  <span class="badge-danger">Ditolak</span>
<?php } else { ?>
  <span class="badge-warning">Menunggu</span>
<?php } ?>
              </td>
              <td><?= htmlspecialchars($art['created_at']); ?></td>
            </tr>
          <?php }
        } else { ?>
          <tr><td colspan="5">Belum ada riwayat persetujuan artikel.</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

</body>
</html>
