<?php
session_start();
require 'vendor/autoload.php';
include "koneksi.php";

use Dompdf\Dompdf;

/* ================= VALIDASI ================= */
if (!isset($_GET['id'])) {
    die("Artikel tidak ditemukan");
}

$id   = intval($_GET['id']);
$role = $_SESSION['role'] ?? 'user';

/* ================= AMBIL ARTIKEL ================= */
$stmt = $conn->prepare("SELECT * FROM artikel WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$artikel = $stmt->get_result()->fetch_assoc();

if (!$artikel) {
    die("Artikel tidak ditemukan");
}

/* ================= CEK AKSES INTERNAL ================= */
if ($artikel['tipe'] === 'internal' && !in_array($role, ['pegawai','admin'])) {
    die("Akses ditolak");
}

/* ================= DOMPDF ================= */
$dompdf = new Dompdf();
$dompdf->set_option('isRemoteEnabled', true);

/* ================= HTML PDF ================= */
$html = "
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
h1 { color:#7a0202; }
.meta { color:#555; font-size:11px; margin-bottom:15px; }
img { max-width:100%; margin:15px 0; }
hr { border:none; border-top:1px solid #ccc; margin:20px 0; }
</style>

<h1>{$artikel['judul']}</h1>
<div class='meta'>
Tanggal: ".date("d M Y H:i", strtotime($artikel['created_at']))."
</div>
<hr>
";

/* ================= GAMBAR ================= */
if (!empty($artikel['gambar'])) {
    $path = "uploads/" . $artikel['gambar'];
    if (file_exists($path)) {
        $imgData = base64_encode(file_get_contents($path));
        $html .= "<img src='data:image/jpeg;base64,$imgData'>";
    }
}

/* ================= ISI ================= */
$html .= "<div>" . nl2br(htmlspecialchars($artikel['isi_artikel'])) . "</div>";

/* ================= GENERATE ================= */
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/* ================= DOWNLOAD ================= */
$dompdf->stream("Artikel-{$artikel['judul']}.pdf", ["Attachment" => true]);
