<?php
session_start();
if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="logo kemhan 1.png">
    <title>Profile</title>
    <link rel="stylesheet" href="profile.css">

<style>
    body {
        background: #f2f2f2;
        margin: 0;
        font-family: "Inter", sans-serif;
    }

    .btn-back-shopee {
    position: fixed;
    top: 50px;
    left: calc(50% - 450px); /* sejajar container */
    width: 44px;
    height: 44px;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #ffffff;
    border-radius: 50%;
    border: 1px solid #e5e5e5;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    z-index: 9999; /* pastikan di atas semua */
   }

    .btn-back-shopee::before {
        content: "";
        color: #7a0202;
        position: absolute;
        width: 58px;
        height: 58px;
        border-radius: 10%;
        background: rgba(0,0,0,0.05);
        z-index: -1;
    }

    .btn-back-shopee:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
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

    @media (max-width: 900px) { /* Gunakan 900px agar tombol terlihat bagus di layar laptop kecil */
        .btn-back-shopee {
            left: 20px;
            top: 20px;
        }
    }

    .profile-container {
        max-width: 650px;
        margin: 120px auto 40px;
        background: white;
        padding: 35px;
        border-radius: 16px;
        box-shadow: 0 6px 24px rgba(0,0,0,0.12);
        animation: fadeIn 0.3s ease;
    }

    .profile-header {
        text-align: center;
        margin-bottom: 25px;
    }

    .profile-header .avatar {
        width: 95px;
        height: 95px;
        border-radius: 50%;
        background: #a30202;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 34px;
        font-weight: 700;
        color: white;
        margin: 0 auto 18px;
        box-shadow: 0 4px 14px rgba(163,2,2,0.35);
    }

    .profile-header h2 {
        font-size: 26px;
        margin: 0;
        font-weight: 700;
        color: #a30202;
    }

    .profile-card {
        margin-top: 20px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .field {
        display: flex;
        flex-direction: column;
    }

    .field label {
        font-size: 14px;
        color: #555;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .field p {
        background: #e4e3e3ff;
        padding: 12px 16px;
        border: 2px solid #c5c5c5ff;
        box-shadow: #555;
        border-radius: 8px;
        font-size: 16px;
        color: #333;
    }

    .field p,
    .editable {
        box-sizing: border-box; /* KUNCI UKURAN */
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }

    /* Responsive */
    @media (max-width: 600px) {

        .profile-container {
            margin: 100px 20px 30px;
            padding: 25px;
        }

        .profile-header .avatar {
            width: 80px;
            height: 80px;
            font-size: 28px;
        }

        .profile-header h2 {
            font-size: 22px;
        }

        .field p {
            font-size: 15px;
        }
    }

    /* Smooth animation */
    @keyframes fadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    .editable {
    background: #fafafa;
    padding: 12px 16px;
    border: 1px solid #e3e3e3;
    border-radius: 8px;
    cursor: pointer;
}

.editable:hover {
    background: #f0f0f0;
}

.inline-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #ffffff;
    outline: none;
    border-radius: 8px;
    background: #fff;
    font-size: 16px;
    box-shadow: 0 0 0 2px #a30202;
    height: 12px;
    line-height: 16px;
}
.eye-icon-modern {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    padding: 12px 16px;
    background: white;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    cursor: pointer;
    transition: 0.15s;
    overflow: visible;
}

.eye-icon-modern:hover {
    transform: translateY(-50%) scale(1.05);
}
.password-wrapper {
    position: relative;
    overflow: visible;
}

.password-wrapper input {
    padding-right: 50px; /* ruang untuk icon mata */
}

.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #a30202;
    color: #fff;
    padding: 16px 22px;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 500;
    box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.35s ease;
    z-index: 9999;
}

.toast.show {
    opacity: 1;
    transform: translateX(0);
}
</style>
</head>

<body>

<!-- Hilangkan header dashboard -->
<a href="javascript:history.back()" class="btn-back-shopee">
    <svg class="arrow-shopee" viewBox="0 0 24 24">
        <path d="M15 6 L9 12 L15 18"></path>
    </svg>
</a>

<div class="profile-container">
    <div class="profile-header">
    <div class="avatar">
        <?= strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
    </div>
    <h2>Profil Pengguna</h2>
</div>

    <div class="profile-card">

    <div class="field">
        <label>Nama</label>
        <p id="namaDisplay" class="editable" data-field="nama">
    <?= htmlspecialchars($_SESSION['nama']); ?>
</p>
    </div>

    <div class="field">
        <label>Email</label>
        <p><?= htmlspecialchars($_SESSION['email']); ?></p>
    </div>

    <div class="field">
        <label>Satker</label>
        <p><?= htmlspecialchars($_SESSION['satker'] ?? '-'); ?></p>
    </div>

    <div class="field">
        <label>Password</label>
        <p id="passwordDisplay" class="editable" data-field="password">
    ********
</p>
    </div>

    <button id="btnSaveModern" 
    style="
        width: 100%;
        padding: 14px;
        background: #a30202;
        color: white;
        font-size: 16px;
        font-weight: 600;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        margin-top: 10px;
        box-shadow: 0 4px 13px rgba(163,2,2,0.35);
        transition: 0.2s;
    ">
   Simpan Perubahan
</button>

<div id="toast" class="toast"></div>

</div>
<script>
let isSaving = false;
let activeInput = null;

function activateEditable(item) {

    item.onclick = function () {

        if (activeInput) return;

        let oldValue = this.innerText.trim();
        let field = this.dataset.field;

        let wrapper = null;
        let input = document.createElement("input");

        input.type = field === "password" ? "password" : "text";
        input.value = field === "password" ? "" : oldValue;
        input.className = "inline-input";
        input.dataset.field = field; // FIX WAJIB

        // PASSWORD MODE
        if (field === "password") {

            wrapper = document.createElement("div");
            wrapper.className = "password-wrapper";

            let eye = document.createElement("div");
            eye.className = "eye-icon-modern";
            eye.innerHTML = `<svg width="20" height="20"><path fill="#a30202"
                d="M12 5C7 5 2.73 8.11 1 12c1.73 3.89 6 7 
                11 7s9.27-3.11 11-7c-1.73-3.89-6-7-11-7zm0 
                12a5 5 0 110-10 5 5 0 010 10zm0-8a3 
                3 0 100 6 3 3 0 000-6z"/></svg>`;

            eye.onclick = (ev) => {
                ev.stopPropagation();
                input.type = input.type === "password" ? "text" : "password";
            };

            wrapper.appendChild(input);
            wrapper.appendChild(eye);
            this.replaceWith(wrapper);

        } else {
            this.replaceWith(input);
        }

        activeInput = input;
        input.focus();

        // ENTER → SAVE INLINE
        input.onkeydown = function (e) {
            if (e.key === "Enter") saveInline(field, input.value, wrapper || input);
        };

        // CLICK OUTSIDE CANCEL
        const clickOutside = function (e) {

    if (isSaving) return; // ⬅️ FIX UTAMA
    if (e.target.closest("#btnSaveModern")) return;
    if (wrapper && wrapper.contains(e.target)) return;
    if (input.contains(e.target)) return;

    document.removeEventListener("click", clickOutside);
    restore();
};

        setTimeout(() => {
            document.addEventListener("click", clickOutside);
        }, 50);

        function restore() {
            let p = document.createElement("p");
            p.className = "editable";
            p.dataset.field = field;
            p.innerText = field === "password" ? "********" : oldValue;

            (wrapper || input).replaceWith(p);
            activeInput = null;
            activateEditable(p);
        }
    };
}

// BIND AWAL
document.querySelectorAll(".editable").forEach(activateEditable);


// =============== SAVE INLINE ELEMENT ===============
function saveInline(field, newValue, wrapperOrInput) {

    let p = document.createElement("p");
    p.className = "editable";
    p.dataset.field = field;

    if (field === "nama") {
        p.id = "namaDisplay"; // ⬅️ FIX UTAMA
        p.innerText = newValue;
    } else {
        p.innerText = "********";
    }

    wrapperOrInput.replaceWith(p);

    activeInput = null;
    activateEditable(p);
}

// =============== SAVE TO SERVER ===================
document.getElementById("btnSaveModern").onclick = function () {

    isSaving = true; // ⬅️ KUNCI CLICK OUTSIDE

    let inputNama = document.querySelector(".inline-input[data-field='nama']");
    let namaBaru = inputNama
        ? inputNama.value.trim()
        : document.getElementById("namaDisplay").innerText.trim();

    let passInput = document.querySelector(".password-wrapper input");
    let passBaru = passInput ? passInput.value.trim() : "";

    if (passBaru !== "") {
    const v = validatePassword(passBaru);

    if (!v.length || !v.upper || !v.lower || !v.number || !v.symbol) {
        showToast(
            "Password minimal 8 karakter, huruf besar, kecil, angka, dan simbol"
        );
        isSaving = false;
        return;
    }
}

    fetch("update_profile.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            nama: namaBaru,
            password: passBaru
        })
    })
    .then(res => res.json())
    .then(data => {
        isSaving = false; // ⬅️ BUKA LAGI

        if (data.status === "ok") {
    showToast("Perubahan berhasil disimpan!");

    // Update nama
    const namaEl = document.querySelector(".editable[data-field='nama']");
    if (namaEl) namaEl.innerText = data.nama;

    // Update avatar huruf
    document.querySelector(".avatar").innerText =
        data.nama.charAt(0).toUpperCase();
        } else {
            showToast(data.message || "Gagal menyimpan perubahan");
        }
    })
    .catch(err => {
        isSaving = false;
        console.error(err);
        showToast("Terjadi kesalahan");
    });
};

// TOAST
function showToast(message) {
    const toast = document.getElementById("toast");
    toast.innerText = message;
    toast.classList.add("show");

    setTimeout(() => {
        toast.classList.remove("show");
    }, 3000);
}
function validatePassword(pw) {
    const rules = {
        length: pw.length >= 8,
        upper: /[A-Z]/.test(pw),
        lower: /[a-z]/.test(pw),
        number: /[0-9]/.test(pw),
        symbol: /[@$!%*#?&]/.test(pw)
    };

    return rules;
}
</script>

</body>
</html>
