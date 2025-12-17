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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>

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
        z-index: -1;
    }

    .btn-back-shopee:hover {
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.49);
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
        margin: 0;
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
    box-sizing: border-box;
    margin: 0;
    display: block;
    color: #333;
}
.password-wrapper {
    position: relative;
    width: 100%;
}

.password-wrapper input {
    width: 100%;
    padding: 12px 16px;
    padding-right: 42px;
    border-radius: 8px;
    border: 1px solid #e3e3e3;
    font-size: 16px;
}

.toggle-password {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #a30202;
    font-size: 16px;
}

.password-display {
    background: #e4e3e3ff;
    padding: 12px 16px;
    border: 2px solid #c5c5c5ff;
    border-radius: 8px;
    cursor: pointer;
}

.password-display:hover {
    background: #f0f0f0;
}

.hidden {
    display: none;
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

    <!-- MODE DIAM (TITIK-TITIK) -->
    <p id="passwordDisplay" class="password-display">
        ********
    </p>

    <!-- MODE EDIT -->
    <div class="password-wrapper hidden" id="passwordEdit">
        <input
            type="password"
            id="passwordInput"
            class="inline-input"
            placeholder="Masukkan password baru"
            autocomplete="new-password"
        >
        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
    </div>
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

/* =======================
   INLINE EDIT (NAMA SAJA)
   ======================= */
function activateEditable(item) {
    item.onclick = function () {
        if (activeInput) return;

        let oldValue = this.innerText.trim();
        let field = this.dataset.field;
        if (field !== "nama") return;

        let input = document.createElement("input");
        input.type = "text";
        input.value = oldValue;
        input.className = "inline-input";
        input.dataset.field = field;

        this.replaceWith(input);
        activeInput = input;
        input.focus();

        input.onkeydown = function (e) {
            if (e.key === "Enter") restore(input.value);
        };

        function clickOutside(e) {
            if (isSaving) return;
            if (input.contains(e.target)) return;

            document.removeEventListener("click", clickOutside);
            restore(input.value);
        }

        setTimeout(() => {
            document.addEventListener("click", clickOutside);
        }, 50);

        function restore(val) {
            let p = document.createElement("p");
            p.className = "editable";
            p.dataset.field = "nama";
            p.id = "namaDisplay";
            p.innerText = val || oldValue;

            input.replaceWith(p);
            activeInput = null;
            activateEditable(p);
        }
    };
}

document
    .querySelectorAll(".editable[data-field='nama']")
    .forEach(activateEditable);

/* =======================
   PASSWORD ELEMENTS
   ======================= */
const passwordInput   = document.getElementById("passwordInput");
const togglePassword  = document.getElementById("togglePassword");
const passwordDisplay = document.getElementById("passwordDisplay");
const passwordEdit    = document.getElementById("passwordEdit");

/* =======================
   TOGGLE PASSWORD (EYE)
   ======================= */
if (togglePassword && passwordInput) {
    togglePassword.addEventListener("click", (e) => {
        e.stopPropagation();

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            togglePassword.classList.remove("fa-eye");
            togglePassword.classList.add("fa-eye-slash");
        } else {
            passwordInput.type = "password";
            togglePassword.classList.remove("fa-eye-slash");
            togglePassword.classList.add("fa-eye");
        }
    });
}

/* =======================
   RESET PASSWORD VIEW
   ======================= */
function resetPasswordView() {
    passwordInput.value = "";
    passwordInput.type = "password";

    togglePassword.classList.remove("fa-eye-slash");
    togglePassword.classList.add("fa-eye");

    passwordEdit.classList.add("hidden");
    passwordDisplay.classList.remove("hidden");
}

/* =======================
   PASSWORD STATE CONTROL
   ======================= */
passwordDisplay.addEventListener("click", (e) => {
    e.stopPropagation();
    passwordDisplay.classList.add("hidden");
    passwordEdit.classList.remove("hidden");
    passwordInput.focus();
});

document.addEventListener("click", (e) => {
    if (
        passwordEdit.classList.contains("hidden") ||
        passwordEdit.contains(e.target) ||
        passwordDisplay.contains(e.target)
    ) return;

    resetPasswordView();
});

/* =======================
   SAVE KE SERVER
   ======================= */
document.getElementById("btnSaveModern").onclick = function () {
    isSaving = true;

    let namaBaru =
        document.querySelector(".inline-input[data-field='nama']")?.value.trim()
        || document.getElementById("namaDisplay").innerText.trim();

    let passBaru = passwordInput.value.trim();

    if (passBaru !== "") {
        const v = validatePassword(passBaru);
        if (!v.length || !v.upper || !v.lower || !v.number || !v.symbol) {
            showToast("Password minimal 8 karakter, huruf besar, kecil, angka, dan simbol");
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
        isSaving = false;

        if (data.status === "ok") {
            showToast("Perubahan berhasil disimpan!");

            document.getElementById("namaDisplay").innerText = data.nama;
            document.querySelector(".avatar").innerText =
                data.nama.charAt(0).toUpperCase();

            resetPasswordView();
        } else {
            showToast(data.message || "Gagal menyimpan perubahan");
        }
    })
    .catch(() => {
        isSaving = false;
        showToast("Terjadi kesalahan");
    });
};

/* =======================
   UTIL
   ======================= */
function showToast(message) {
    const toast = document.getElementById("toast");
    toast.innerText = message;
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 3000);
}

function validatePassword(pw) {
    return {
        length: pw.length >= 8,
        upper: /[A-Z]/.test(pw),
        lower: /[a-z]/.test(pw),
        number: /[0-9]/.test(pw),
        symbol: /[@$!%*#?&]/.test(pw)
    };
}
</script>

</body>
</html>
