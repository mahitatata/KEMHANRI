<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['email'];
$pesan = "";

/* ======================
   AMBIL USER
====================== */
$stmtUser = $conn->prepare("SELECT ID, nama FROM regsitrasi WHERE email = ? LIMIT 1");
$stmtUser->bind_param("s", $email);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();

if ($resultUser->num_rows === 0) {
    die("User tidak ditemukan.");
}

$user = $resultUser->fetch_assoc();
$user_id = (int)$user['ID'];
$stmtUser->close();

/* ======================
   PROSES SUBMIT
====================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $judul = trim($_POST['judul'] ?? '');
    $isi   = trim($_POST['isi'] ?? '');

    if ($judul === '' || $isi === '') {
        $pesan = "Judul dan isi wajib diisi.";
    }

    /* ======================
       UPLOAD GAMBAR
    ====================== */
    $gambar_name = null;

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {

        $imgExt = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowImg = ['jpg','jpeg','png','webp'];

        if (!in_array($imgExt, $allowImg)) {
            $pesan = "Format gambar tidak valid.";
        } else {
            if (!is_dir("uploads")) {
                mkdir("uploads", 0777, true);
            }

            $gambar_name = time() . "_" . basename($_FILES['gambar']['name']);
            move_uploaded_file($_FILES['gambar']['tmp_name'], "uploads/" . $gambar_name);
        }
    }

    /* ======================
       UPLOAD PDF (OPSIONAL)
    ====================== */
    $pdf_name = null;

    if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] === 0) {

        $pdfExt = strtolower(pathinfo($_FILES['file_pdf']['name'], PATHINFO_EXTENSION));

        if ($pdfExt !== 'pdf') {
            $pesan = "File PDF tidak valid.";
        } else {
            if (!is_dir("uploads/pdf")) {
                mkdir("uploads/pdf", 0777, true);
            }

            $pdf_name = "forum_" . rand(10000,99999) . ".pdf";
            move_uploaded_file($_FILES['file_pdf']['tmp_name'], "uploads/pdf/" . $pdf_name);
        }
    }

    /* ======================
       SIMPAN DATABASE
    ====================== */
    if (!$pesan) {

        $stmt = $conn->prepare("
            INSERT INTO forum
            (judul, isi_text, penulis_email, user_id, gambar, file_pdf, tanggal)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->bind_param(
            "sssiss",
            $judul,
            $isi,
            $email,
            $user_id,
            $gambar_name,
            $pdf_name
        );

        if ($stmt->execute()) {
            header("Location: forum.php");
            exit;
        } else {
            $pesan = "Gagal menyimpan forum.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buat Forum Baru</title>
  <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <style>
*{box-sizing:border-box;font-family:Poppins,Arial,sans-serif}
body{background:#f4f5f7;margin:0}
.container{
  max-width:900px;margin:40px auto;background:#fff;
  padding:30px;border-radius:16px;
  box-shadow:0 5px 25px rgba(0,0,0,.08)
}
h1{color:#a30202;border-bottom:2px solid #a30202;display:inline-block}
.form-group{margin-bottom:20px}
label{font-weight:600}
input,textarea{
  width:100%;padding:12px;border-radius:10px;
  border:2px solid #ccc;font-size:14px
}
textarea{min-height:180px}
input:focus,textarea:focus{
  border-color:#a30202;outline:none;
  box-shadow:0 0 0 3px rgba(163,2,2,.15)
}
.message{color:#b70000;text-align:center;font-weight:600}

/* UPLOAD */
.upload-box{
  border:2px dashed #ccc;
  border-radius:14px;
  padding:22px;
  text-align:center;
  background:#fafafa;
  transition:.25s;
  cursor:pointer;
  margin-bottom:18px;
}
.upload-label{color:#555}
.upload-box:hover{
  border-color:#a30202;
  background:#fff5f5;
}
.upload-box:hover .upload-label{color:#a30202}
.file-name{font-size:13px;font-weight:600;margin-top:8px}

.form-actions{text-align:right}
.btn{
  padding:12px 24px;
  background:#a30202;
  color:#fff;
  border:none;
  border-radius:10px;
  font-weight:600;
  cursor:pointer
}
</style>
</head>

<body>
<div class="container">
<h1>Buat Forum Baru</h1>

<?php if ($pesan): ?><p class="message"><?=htmlspecialchars($pesan)?></p><?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<div class="form-group">
<label>Judul Forum</label>
<input type="text" name="judul" required>
</div>

<div class="form-group">
<label>Isi Forum</label>
<textarea name="isi" required></textarea>
</div>

<div class="upload-box" data-input="file_pdf">
<label class="upload-label">Upload PDF<br><small>Maks 20MB</small></label>
<input type="file" id="file_pdf" name="file_pdf" accept="application/pdf" hidden>
<p class="file-name">Belum ada file</p>
</div>

<div class="upload-box" data-input="gambar">
<label class="upload-label">Upload Gambar<br><small>JPG / PNG / WEBP</small></label>
<input type="file" id="gambar" name="gambar" accept="image/*" hidden>
<p class="file-name">Belum ada file</p>
</div>

<div class="form-actions">
<button class="btn">Publikasikan</button>
</div>

</form>
</div>

<script>
document.querySelectorAll('.upload-box').forEach(box=>{
  const input=document.getElementById(box.dataset.input);
  const name=box.querySelector('.file-name');

  box.onclick=()=>input.click();
  box.ondragover=e=>{e.preventDefault();box.style.borderColor='#a30202'};
  box.ondragleave=()=>box.style.borderColor='#ccc';
  box.ondrop=e=>{
    e.preventDefault();
    input.files=e.dataTransfer.files;
    name.textContent=input.files[0]?.name||'Belum ada file';
    box.style.borderColor='#ccc';
  }
  input.onchange=()=>name.textContent=input.files[0]?.name||'Belum ada file';
});
</script>
</body>
</html>
