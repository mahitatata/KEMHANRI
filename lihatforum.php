<?php
// lihatforum.php (FINAL - komentar disamain dengan komentar.php)
include "koneksi.php";
session_start();

// Pastikan ada ID forum
if (!isset($_GET['id'])) {
  header("Location: forum.php");
  exit;
}

$forum_id = intval($_GET['id']);

// Jika user belum login, munculkan fungsi JS (tetap lanjutkan render halaman soalnya)
if (!isset($_SESSION['email'])) {
    $isLoggedIn = false;
} else {
    $isLoggedIn = true;
}

// Ambil session user (role & nama)
// TARUH DI AWAL FILE sebelum function tampilkanKomentar:
$sessionNama = $_SESSION['nama'] ?? null;
$sessionRole = $_SESSION['role'] ?? 'user';

// Ambil data forum
$stmt = $conn->prepare("SELECT * FROM forum WHERE id = ?");
$stmt->bind_param("i", $forum_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
  echo "<h3>Topik tidak ditemukan</h3>";
  exit;
}

$forum = $res->fetch_assoc();
$forumPenulis = $forum['penulis_nama'] ?? '';

// ========== INSERT KOMENTAR / BALASAN ==========
// Support both normal POST (redirect) and AJAX POST (respond with success/error)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ===== KUNCI KOMENTAR: HARUS LOGIN =====
    if (!isset($_SESSION['email'])) {

        // jika AJAX
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            echo "not_logged_in";
            exit;
        }

        // jika POST biasa
        header("Location: login.php");
        exit;
    }

    // ===== USER PASTI LOGIN =====
    $nama = $_SESSION['nama'];
    $isi  = trim($_POST['isi'] ?? '');
    

    // Jika request via AJAX, kirim plain text, jangan redirect
    $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $balasan = intval($_POST['balasan'] ?? 0);

$stmt = $conn->prepare("
    INSERT INTO komentar_forum (forum_id, nama, isi_text, balasan, tanggal)
    VALUES (?, ?, ?, ?, NOW())
");
$stmt->bind_param("issi", $forum_id, $nama, $isi, $balasan);

$ok = $stmt->execute();
    if ($isAjax) {
        echo $ok ? "success" : "error";
        exit;
    } else {
        header("Location: lihatforum.php?id=".$forum_id);
        exit;
    }
}

// Ambil semua komentar (untuk fungsi recursive)
$ambil = $conn->prepare("SELECT * FROM komentar_forum WHERE forum_id = ? ORDER BY tanggal ASC");
$ambil->bind_param("i", $forum_id);
$ambil->execute();
$allKomentar = $ambil->get_result()->fetch_all(MYSQLI_ASSOC);


// Recursive renderer (YouTube-like / komentar.php style)
function tampilkanKomentar($data, $parent = 0) {
    global $sessionNama, $sessionRole, $forumPenulis;

    foreach ($data as $row) {
        $parentField = intval($row['balasan']);
        if ($parentField === intval($parent)) {

            $id = intval($row['id']);
            $tanggal = date('d M Y, H:i', strtotime($row['tanggal']));

            // cek apakah komentar ini milik user yang login
            $isOwnComment = ($sessionNama && $sessionNama === $row['nama']);

            // cek apakah komentar ini dari penulis forum
            $isForumAuthor = ($forumPenulis && $row['nama'] === $forumPenulis);

            echo "<div class='komentar-item ".($parent ? 'nested' : '')."' data-id='{$id}'>";

            echo "<div class='meta-line'>";
            echo "<strong>".htmlspecialchars($row['nama'])."</strong>";

            // BADGE PENULIS
            if ($isForumAuthor) {
                echo "<span class='badge-penulis'>Penulis</span>";
            }

            echo "<span class='waktu'>{$tanggal}</span>";
            echo "</div>";

            if (!empty($row['balasan']) && $row['balasan'] != 0) {
                $parentName = '';
                foreach ($data as $p) {
                    if ($p['id'] == $row['balasan']) { 
                        $parentName = $p['nama']; 
                        break; 
                    }
                }
                if ($parentName !== "") {
                    echo "<div class='reply-to'>Membalas @".htmlspecialchars($parentName)."</div>";
                }
            }

            echo "<p>".nl2br(htmlspecialchars($row['isi_text']))."</p>";

            $hasChildren = false;
            foreach ($data as $ch) { 
                if (intval($ch['balasan']) === $id) { 
                    $hasChildren = true; 
                    break; 
                } 
            }

            echo "<div class='actions'>";

            if ($sessionNama) {
                echo "<button class='reply-btn' data-parent='{$id}'>Balas</button>";
            }

            if ($hasChildren) {
                echo "<button class='toggle-replies' data-id='{$id}'>Tampilkan Balasan</button>";
            }

            if ($sessionRole === 'admin' || $isOwnComment) {
                echo '<button class="delete-btn" data-id="'.$id.'" title="Hapus komentar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6l-1 14H6L5 6"></path>
                        <path d="M10 11v6"></path>
                        <path d="M14 11v6"></path>
                        <path d="M9 6V4h6v2"></path>
                    </svg>
                  </button>';
            }

            echo "</div>";

            echo "<div class='reply-container' data-parent='{$id}' style='display:none;'>";
            tampilkanKomentar($data, $id);
            echo "</div>";

            echo "</div>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
 <title>Forum</title>
  <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* --- GENERAL --- */
    body { font-family: Inter, Arial, sans-serif; background:#f5f6fa; margin:0; color:#333; }
    header { background:#8B0000; color:#fff; padding:16px 28px; font-weight:700; }
    .container { max-width:860px; margin:40px auto; background:#fff; border-radius:12px; padding:32px; box-shadow:0 6px 18px rgba(0,0,0,0.08); }

    h1 { color:#8B0000; margin-top:0; }
    .meta { color:#666; margin-bottom:18px; }
    .isi_text { line-height:1.7; margin-bottom:22px; }

    /* --- KOMENTAR AREA (style match komentar.php) --- */
    .komentar-list { margin-top:18px; }
    .komentar-section { margin-top:28px; }

    /* Comment item */
    .komentar-item { margin: 18px 0; padding: 0; }
    .komentar-item.nested { margin-left: 26px; border-left:1px solid #c74d4dff; padding-left:12px; }

    .komentar-item .meta-line { display:flex; align-items:center; gap:10px; margin-bottom:6px; }
    .komentar-item strong { color:#a30202; font-weight:700; display:block; }
    .waktu { font-size:12px; color:#777; }

    .reply-to { font-size:13px; color:#555; margin-bottom:6px; }

    .komentar-item p { margin:4px 0 8px 0; line-height:1.45; white-space:pre-wrap; }

    .actions { display:flex; gap:10px; align-items:center; margin-top:6px; }
    .reply-btn, .toggle-replies { background:none; border:none; color:#a30202; font-weight:700; cursor:pointer; font-size:13px; padding:0; }
    .reply-btn:hover, .toggle-replies:hover { text-decoration:underline; }

    .reply-container { margin-top:8px; }

    /* komentar main form (top) */
    .komentar-form { margin-top:18px; }
    .komentar-form textarea { width:100%; padding:12px; border-radius:8px; border:1px solid #901616ff; resize:vertical; min-height:90px; font-family:inherit; }
    .komentar-form .btns { margin-top:10px; display:flex; gap:8px; align-items:center; }
    .komentar-form button.primary { background:#a30202; color:#fff; border:none; padding:10px 16px; border-radius:8px; font-weight:700; cursor:pointer; }
    .komentar-form button.cancel { background:#ccc; color:#333; border:none; padding:10px 16px; border-radius:8px; cursor:pointer; }
    .komentar-form textarea:focus,
    .small-reply-form textarea:focus {
        outline: none;              /* HILANGKAN garis hitam */
        border-color: #a30202;      /* warna merah konsisten */
        box-shadow: 0 0 0 2px rgba(163, 2, 2, 0.2);
    }

    /* reply small form style (same as komentar.php) */
    .small-reply-form textarea { width:100%; padding:10px; border-radius:6px; border:1px solid #ddd; min-height:70px; resize:vertical; }
    .small-reply-form .btns { margin-top:8px; display:flex; gap:8px; }

    /* responsive */
    @media (max-width:600px) {
      .container { margin:18px; padding:20px; }
      .komentar-item.nested { margin-left:14px; padding-left:8px; }
    }
    /* === POPUP KONFIRMASI HAPUS (BARU | FIX) === */
.popup-login {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 9999;
  backdrop-filter: blur(3px);
}
.popup-login .popup-content {
  background: white;
  padding: 40px 30px;
  border-radius: 12px;
  width: 340px;
  text-align: center;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
  animation: fadeIn 0.25s ease;
}
.popup-login h2 {
  font-size: 24px;
  font-weight: 700;
  color: #111;
  margin-bottom: 12px;
}
.popup-login p {
  color: #434343;
  font-size: 15px;
  line-height: 1.5;
  margin-bottom: 25px;
}
.popup-buttons {
  display: flex;
  justify-content: center;
  gap: 12px;
}
.popup-btn-login {
  background: #a30202;
  color: white;
  text-decoration: none;
  padding: 10px 26px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 15px;
  transition: all 0.3s ease;
}
.popup-btn-login:hover {
  background: #8b0202;
  transform: scale(1.05);
}
.popup-btn-cancel {
  background: #cacaca;
  border: none;
  color: #1f1f1f;
  padding: 10px 26px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 700;
  font-size: 15px;
  transition: all 0.3s ease;
}
.popup-btn-cancel:hover {
  background: #bbbbbb;
  transform: scale(1.05);
}

 /* delete button komentar */
.actions {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-top: 6px;
}

.delete-btn {
    background: #f8d7da;
    border: 1px solid #f5c2c7;
    border-radius: 6px;
    cursor: pointer;
    padding: 4px;
    color: #a30202;
    transition: all 0.2s;
}

.delete-btn svg {
    vertical-align: middle;
}

.delete-btn:hover {
    background: #a30202;
    color: #fff;
    transform: scale(1.2);
}


.badge-penulis {
    background: #a30202;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 999px;
    margin-left: 6px;
    line-height: 1;
}

/* === KOMENTAR STYLE SAMA PERSIS SEPERTI komentar.php === */

.komentar-item {
    margin: 15px 0;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.komentar-item.nested {
    margin-left: 25px;
    border-left: 2px solid #a30202;
    padding-left: 15px;
}

.komentar-item strong {
    font-weight: 700;
    color: #a30202;
}

.komentar-item p {
    margin: 6px 0 10px 0;
    line-height: 1.5;
}

.actions {
    display: flex;
    gap: 12px;
    margin-top: 5px;
}

.reply-btn {
    background: none;
    border: none;
    color: #a30202;
    font-weight: 700;
    cursor: pointer;
}

.reply-btn:hover {
    text-decoration: underline;
}

.small-reply-form {
    margin-top: 10px;
}

.small-reply-form textarea {
    width: 100%;
    min-height: 70px;
    border: 1px solid #a30202;
    border-radius: 8px;
    padding: 8px;
}

.small-reply-form .btns {
    margin-top: 8px;
    display: flex;
    gap: 10px;
}

.small-reply-form button.primary {
    background: #a30202;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
}

.small-reply-form button.cancel {
    background: #ccc;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    cursor: pointer;
}

/* Tombol hapus PERSIS seperti komentar.php */
.delete-btn {
    background: #f8d7da;
    border: 1px solid #f5c2c7;
    border-radius: 8px;
    padding: 6px;
    cursor: pointer;
    color: #a30202;
    transition: 0.2s;
}

.delete-btn:hover {
    background: #a30202;
    color: #fff;
    transform: scale(1.15);
}

  </style>
</head>
<body>
<header>💬 Forum Diskusi</header>

<div class="container">
  <h1><?= htmlspecialchars($forum['judul']) ?></h1>
  <div class="meta">
    Oleh: <strong><?= htmlspecialchars($forum['penulis_nama'] ?? 'Penulis') ?></strong> |
    <?= date('d M Y, H:i', strtotime($forum['tanggal'])) ?>
  </div>

  <div class="isi_text"><?= nl2br(htmlspecialchars($forum['isi_text'])) ?></div>

  <section class="komentar-list" id="daftarKomentar">
    <h2>Komentar</h2>

    <!-- render komentar (recursive) -->
    <?php tampilkanKomentar($allKomentar); ?>
  </section>

  <section class="komentar-section">
    <h3>Tinggalkan Komentar</h3>

    <?php if ($isLoggedIn): ?>
      <form id="formKomentar" class="komentar-form" onsubmit="return false;">
        <p><strong><?= htmlspecialchars($_SESSION['nama'] ?? $_SESSION['email'] ?? 'Anonim') ?></strong></p>
        <textarea id="mainIsi" name="isi" placeholder="Tulis komentar..." required></textarea>
        <div class="btns">
          <button class="primary" id="kirimUtama">Kirim</button>
          <button class="cancel" id="resetParent" style="display:none;">Batal</button>
        </div>
        <!-- hidden parent marker (used when replying via main form if needed) -->
        <input type="hidden" id="parentMarker" name="balasan" value="0">
      </form>
    <?php else: ?>
      <button id="loginForComment" class="primary" style="background:#a30202;color:#fff;border:none;padding:10px 16px;border-radius:8px;cursor:pointer;">
        Login untuk berkomentar
      </button>
    <?php endif; ?>

  </section>
</div>

<!-- popup login (same simple modal) -->
<!-- POPUP LOGIN -->
<div class="popup-login" id="loginPopup">
  <div class="popup-content">
    <h2>Anda belum login</h2>
    <p>Silakan login terlebih dahulu untuk membuat/melihat topik.</p>
    <div class="popup-buttons">
      <button class="popup-btn-cancel" onclick="closeLoginPopup()">Batal</button>
      <a href="login.php" class="popup-btn-login">Login</a>
    </div>
  </div>
</div>

<!-- POPUP KONFIRMASI HAPUS -->
<div class="popup-login" id="deletePopup">
  <div class="popup-content">
    <h2>Konfirmasi Hapus</h2>
    <p>Apakah kamu yakin ingin menghapus komentar ini?</p>
    <div class="popup-buttons">
      <button class="popup-btn-cancel" id="cancelDeleteBtn">Batal</button>
      <button class="popup-btn-login" id="confirmDeleteBtn">Hapus</button>
    </div>
  </div>
</div>

<script>

const isLoggedIn = <?= isset($_SESSION['email']) ? 'true' : 'false' ?>;

/* helper popup */
function showLoginPopup(){ document.getElementById('loginPopup').style.display='flex'; }
function closeLoginPopup(){ document.getElementById('loginPopup').style.display='none'; }

/* submit main komentar */
document.getElementById('kirimUtama')?.addEventListener('click', async function(e){
  e.preventDefault();
  const isi = document.getElementById('mainIsi').value.trim();
  if (!isi) return;
  const balasan = document.getElementById('parentMarker').value || 0;

  const form = new URLSearchParams();
  form.append('isi', isi);
  form.append('balasan', balasan);

  // send AJAX with X-Requested-With header so PHP returns 'success' text
  const resp = await fetch(window.location.href, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: form.toString()
  });
  const text = await resp.text();
  if (text === 'success') {
    location.reload();
} 
else if (text === 'not_logged_in') {
    showLoginPopup();
} 
else {
    alert('Gagal mengirim komentar.');
}
});

// Tombol Hapus Komentar
  let deleteCommentId = null;

document.addEventListener("click", function(e) {
    const btn = e.target.closest(".delete-btn");
    if (btn) {
        deleteCommentId = btn.dataset.id;
        document.getElementById("deletePopup").style.display = "flex";
    }
});

// Tombol batal
document.getElementById("cancelDeleteBtn")?.addEventListener("click", () => {
    deleteCommentId = null;
    document.getElementById("deletePopup").style.display = "none";
});

// Tombol konfirmasi hapus
document.getElementById("confirmDeleteBtn")?.addEventListener("click", async () => {
    if (!deleteCommentId) return;

    const res = await fetch("hapuskomentar.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ id: deleteCommentId })
    });

    const r = await res.text();
    if (r === "success") {
        location.reload();
    } else {
        alert("Gagal menghapus komentar!");
    }
});

// === BALAS KOMENTAR (SAMA SEPERTI komentar.php) ===
document.addEventListener("click", function(e) {
    
    if (e.target.classList.contains("reply-btn")) {

    // === BLOK USER BELUM LOGIN ===
    if (!isLoggedIn) {
        showLoginPopup();
        return;
    }

    // lanjutkan logic balas


        // Hapus form balasan lama
        const old = document.getElementById("smallReplyForm");
        if (old) old.remove();

        const parentId = e.target.dataset.parent;
        const parentItem = e.target.closest(".komentar-item");

        // buat form baru
        const form = document.createElement("div");
        form.id = "smallReplyForm";
        form.className = "small-reply-form";

        form.innerHTML = `
            <textarea id="replyText" placeholder="Tulis balasan..."></textarea>
            <div class="btns">
                <button class="primary" id="sendReply">Kirim Balasan</button>
                <button class="cancel" id="cancelReply">Batal</button>
            </div>
        `;

        parentItem.appendChild(form);

        // batal
        document.getElementById("cancelReply").onclick = () => form.remove();

        // kirim komentar AJAX
        document.getElementById("sendReply").onclick = async () => {
            const isi = document.getElementById("replyText").value.trim();
            if (!isi) return;

            const data = new URLSearchParams();
            data.append("isi", isi);
            data.append("balasan", parentId);

            const res = await fetch(window.location.href, {
    method: "POST",
    headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-Requested-With": "XMLHttpRequest"
    },
    body: data.toString()
});

            const txt = await res.text();
            if (txt === "success") location.reload();
            else alert("Gagal mengirim balasan.");
        };
    }
});

// === TOGGLE BALASAN (Tampilkan / Sembunyikan) ===
document.addEventListener("click", function(e) {
    const btn = e.target.closest(".toggle-replies");
    if (!btn) return;

    const parentId = btn.dataset.id;
    const container = document.querySelector(`.reply-container[data-parent='${parentId}']`);

    if (!container) return;

    if (container.style.display === "none" || container.style.display === "") {
        container.style.display = "block";
        btn.textContent = "Sembunyikan Balasan";
    } else {
        container.style.display = "none";
        btn.textContent = "Tampilkan Balasan";
    }
});

/* login button */
document.getElementById('loginForComment')?.addEventListener('click', function(){
  showLoginPopup();
});

let deleteForumId = null;

// klik tombol hapus
document.addEventListener("click", function(e) {
    const btn = e.target.closest(".delete-btn");
    if (btn) {
        deleteForumId = btn.dataset.id;
        document.getElementById("deleteForumPopup").style.display = "flex";
    }
});

// batal
document.getElementById("cancelForumDelete")?.addEventListener("click", () => {
    deleteForumId = null;
    document.getElementById("deletePopup").style.display = "flex";
});

// konfirmasi
document.getElementById("confirmForumDelete")?.addEventListener("click", async () => {
    if (!deleteForumId) return;

    const res = await fetch("hapuskomentar.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ id: deleteForumId })
    });

    const r = await res.text();
    if (r === "success") {
        location.reload();
    } else {
        alert("Gagal menghapus komentar!");
    }
});
</script>
</body>
</html>
