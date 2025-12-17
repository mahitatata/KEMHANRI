<?php
// Pastikan tidak ada spasi, baris kosong, atau teks apapun sebelum tag <?php
include "koneksi.php"; // koneksi ke database

$error_message = "";
$nama = $email = $satker = ''; // Inisialisasi variabel untuk mempertahankan nilai di form

if (isset($_POST['register'])) {
    // Ambil data dari form
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $satker = $_POST['satker'];
    $password_raw = $_POST['password'];

    // 🔒 Validasi Password (Minimal 8 karakter dan ada simbol khusus)
    if (strlen($password_raw) < 8) {
        $error_message = "Kata sandi harus minimal 8 karakter.";
    } elseif (!preg_match('/[!@#$%^&*()_+=\[\]{}|;:",.<>?~-]/', $password_raw)) {
        $error_message = "Kata sandi harus menggunakan simbol khusus.";
    } else {
        $password = password_hash($password_raw, PASSWORD_DEFAULT);

        // 🔒 Cek apakah email sudah diblacklist
        $email_escaped = mysqli_real_escape_string($conn, $email);
        $checkBlacklist = mysqli_query($conn, "SELECT * FROM blacklist WHERE email = '$email_escaped'");

        if (mysqli_num_rows($checkBlacklist) > 0) {
            $error_message = "Email ini telah diblokir.";
        }

        // 🔍 Cek apakah email sudah terdaftar
        if (empty($error_message)) {
            $checkEmail = mysqli_query($conn, "SELECT email FROM regsitrasi WHERE email = '$email_escaped'");
            if (mysqli_num_rows($checkEmail) > 0) {
                $error_message = "Email sudah terdaftar!";
            }
        }
        
        if (empty($error_message)) {
            // 🧩 Tentukan role otomatis
            if ($email === "admin1@kemhan.go.id") {
                $role = "admin";
            } elseif (preg_match('/@kemhan\.go\.id$/', $email)) {
                $role = "pegawai";
            } else {
                $role = "guest";
            }

            // 📝 Insert ke regsitrasi
            $sql = "INSERT INTO regsitrasi (nama, email, satker, password, role)
                    VALUES ('$nama', '$email', '$satker', '$password', '$role')";
            if ($conn->query($sql) === TRUE) {
                session_start();
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;
                $_SESSION['nama'] = $nama;

                // Redirect setelah registrasi sukses
                if ($role === "admin") {
                    header("Location: dashboard.php");
                } elseif ($role === "pegawai") {
                    header("Location: pegawai.php");
                } else {
                    header("Location: index.php");
                }
                exit;

            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun HanZone</title>
    <link 
        rel="stylesheet" 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
    />
    <style>
        /* CSS Gabungan: Memastikan Posisi Tengah & Ukuran Ringkas */

        html, body {
            height: 100%; 
            margin: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ecececff;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center; 
        }

        .container {
            background: #8B0000; 
            /* PERUBAHAN UTAMA: Mengurangi padding atas/bawah (30px) */
            padding: 25px 30px; 
            border-radius: 20px;
            width: 320px; 
            max-width: 90%;
            color: white;
            position: relative;
            box-shadow: 0 10px 25px rgba(0,0,0,0.4); 
            animation: fadeIn 0.8s ease-out;
            max-height: 95vh; /* Batas tinggi, agar ada jarak di atas/bawah */
            overflow-y: auto; 
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header-content {
            text-align: center;
            /* Mengurangi margin bawah */
            margin-bottom: 15px; 
        }

        .logo {
            width: 60px; /* Sedikit lebih kecil */
            display: block;
            margin: 0 auto;
        }
        
        .hanzone {
            font-size: 24px;
            font-weight: 800;
            margin-top: 5px; 
            letter-spacing: 1px;
        }
        
        .subtitle {
            font-size: 13px; 
            margin-top: -10px;
            opacity: 0.8;
        }

        .welcome {
            text-align: center;
            font-size: 22px; /* Dikecilkan */
            font-weight: bold;
            margin-bottom: 15px; /* Margin bawah dikurangi */
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 10px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .form-group {
            width: 100%; 
            /* Mengurangi margin bawah */
            margin-bottom: 12px; 
            text-align: left;
            padding: 0;
            box-sizing: border-box;
        }
        
        label {
            display: block;
            margin-bottom: 4px; /* Margin label dikurangi */
            font-size: 13px;
            font-weight: 600;
        }

        input[type="text"], 
        input[type="email"], 
        input[type="password"],
        input[list] {
            padding: 10px; /* Padding input dikurangi */
            border-radius: 8px; /* Sudut lebih kecil */
            border: none;
            outline: none;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
            background-color: #fff;
            color: #333;
            transition: box-shadow 0.3s;
        }

        input:focus {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.4);
        }

        .password-container {
            position: relative;
            width: 100%;
        }

        .password-container input {
            padding-right: 40px; /* Ruang untuk ikon dikurangi */
        }

        .toggle-password {
            position: absolute;
            right: 10px; 
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #8B0000;
            font-size: 16px; /* Ikon dikecilkan */
            transition: color 0.2s;
        }

        .password-hint {
            display: block;
            margin-top: 5px;
            font-size: 10px; /* Font hint lebih kecil */
            color: rgba(255, 255, 255, 0.7);
        }

        .form-actions {
            display: flex; 
            justify-content: space-between; 
            gap: 10px; /* Jarak tombol dikurangi */
            width: 100%; 
            /* Mengurangi margin atas */
            margin: 15px auto 0 auto; 
        }

        .btn-action {
            flex: 1; 
            padding: 10px 12px; /* Padding tombol dikurangi */
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            border: none;
            font-size: 14px; /* Font tombol dikurangi */
        }

        .btn-action.daftar {
            background: #fff;
            color: #8B0000;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }

        .btn-action.daftar:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
        }

        .btn-action.kembali {
            background: rgba(255, 255, 255, 0.1); 
            color: white;
            border: 1px solid white;
        }

        .btn-action.kembali:hover {
            transform: translateY(-2px);
           background: rgba(255, 255, 255, 0.2);
        }

        .login-link {
            text-align: center;
            margin-top: 15px;
            font-size: 13px; /* Font link dikurangi */
        }

        .login-link a {
            color: #ffdd88 !important;
            text-decoration: none;
            font-weight: bold;
        }

        .login-link a:hover {
            color: #ffe9a8 !important;
        }


        /* Toast Notification (tidak berubah, hanya untuk kelengkapan) */
        .toast {
            position: fixed;
            top: 30px;
            right: 30px;
            background: #ff4d4d;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            font-size: 16px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
            opacity: 0;
            transform: translateY(-50px);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            z-index: 9999;
            display: flex;
            align-items: center;
        }
        .toast.show { 
            opacity: 1; 
            transform: translateY(0); 
        }
        .toast .icon {
            margin-right: 10px;
            font-size: 20px;
        }
    </style>
</head>
<body>

    <div id="toast" class="toast">
        <i class="icon fas fa-exclamation-circle"></i> 
        <span id="toast-message"></span>
    </div>

    <div class="container">

        <div class="header-content">
            <img src="logo kemhan 1.png" alt="Logo" class="logo">
            <h2 class="hanzone">HanZone</h2>
            <p class="subtitle">Zona Pengetahuan Pertahanan</p>
        </div>

        <h2 class="welcome">Daftar Akun Baru</h2>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" placeholder="Nama" required value="<?= htmlspecialchars($nama) ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>">
            </div>
            
            <div class="form-group">
                <label for="satker">Satker</label>
                <input 
                    list="satker-list" 
                    id="satker" 
                    name="satker" 
                    placeholder="Ketik atau Pilih Satker" 
                    required
                    value="<?= htmlspecialchars($satker) ?>"
                >
                
                <datalist id="satker-list">
                    <option value="Pihak Luar">
                    <option value="Setjen">
                    <option value="Itjen">
                    <option value="Ditjen Strahan">
                    <option value="Ditjen Pothan">
                    <option value="Ditjen Renhan">
                    <option value="Baranahan">
                    <option value="Balitbang">
                    <option value="Badiklat">
                    <option value="Bainstrahan">
                    <option value="Staf Ahli Bidang Politik">
                    <option value="Staf Ahli Bidang Sosial">
                    <option value="Staf Ahli Bidang Ekonomi">
                    <option value="Staf Ahli Bidang Keamanan">
                    <option value="Puslaik">
                    <option value="Pusdatin">
                </datalist>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-container">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        minlength="8" 
                        required 
                        placeholder="Masukkan kata sandi"
                    >
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>
                <small class="password-hint">Kata sandi harus minimal **8 karakter** dan menggunakan **simbol khusus**.</small>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-action kembali" onclick="window.location.href='javascript:history.back()'">Kembali</button>
                <button type="submit" class="btn-action daftar" name="register">Daftar</button>
            </div>

            <p class="login-link">
                Sudah punya akun? <a href="login.php">Masuk</a>
            </p>

        </form>
    </div>
    
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const togglePassword = document.getElementById("togglePassword");
            const passwordInput = document.getElementById("password");
            const toast = document.getElementById("toast");
            const toastMessage = document.getElementById("toast-message");

            // Fungsi Toggle Password
            togglePassword.addEventListener("click", () => {
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
            
            // Fungsi Tampilkan Toast
            function showToast(msg) {
                toastMessage.textContent = msg;
                toast.classList.add("show");
                setTimeout(() => toast.classList.remove("show"), 5000);
            }

            // Tampilkan error dari PHP (jika ada)
            <?php if (!empty($error_message)): ?>
                showToast("<?= htmlspecialchars($error_message) ?>");
            <?php endif; ?>
        });
    </script>
</body>
</html>