<?php
session_start();
include "koneksi.php";

$error = "";
$email_value = ""; // Untuk mempertahankan email di form jika error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $email_value = htmlspecialchars($email); // Simpan email

    $sql = "SELECT * FROM regsitrasi WHERE email='$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['ID']; // simpan ID user-nya
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];


            // Update status & last_active di tabel regsitrasi
            $updateStatus = $conn->prepare("UPDATE regsitrasi SET status='active', last_active = NOW() WHERE email = ?");
            $updateStatus->bind_param("s", $email);
            $updateStatus->execute();

            // Kalau role = pegawai → sinkronkan ke tabel pegawai
            if (strtolower($user['role']) === 'pegawai') {
                $email_sync = $user['email'];
                $nama_sync = $user['nama'];
                $satker_sync = isset($user['satker']) ? $user['satker'] : ''; // antisipasi kalau gak ada

                // Cek apakah email sudah ada di tabel pegawai
                $cekPegawai = $conn->prepare("SELECT id FROM pegawai WHERE Email = ?");
                $cekPegawai->bind_param("s", $email_sync);
                $cekPegawai->execute();
                $hasil = $cekPegawai->get_result();

                if ($hasil->num_rows == 0) {
                    // Kalau belum ada, tambahkan
                    $insert = $conn->prepare("INSERT INTO pegawai (Email, Nama, Satker, last_active) VALUES (?, ?, ?, NOW())");
                    $insert->bind_param("sss", $email_sync, $nama_sync, $satker_sync);
                    $insert->execute();
                } else {
                    // Kalau sudah ada, update last_active-nya aja
                    $update = $conn->prepare("UPDATE pegawai SET last_active = NOW() WHERE Email = ?");
                    $update->bind_param("s", $email_sync);
                    $update->execute();
                }
            }

            // redirect sesuai role
            switch (strtolower($user['role'])) {
                case 'admin':
                    header("Location: dashboard.php");
                    break;
                case 'pegawai':
                    header("Location: pegawai.php");
                    break;
                default:
                    header("Location: index.php");
                    break;
            }
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Akun HanZone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>

    <style>
        /* CSS DISINKRONKAN DENGAN REGISTER.PHP */

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
            /* Menggunakan nilai yang ringkas dari register.php */
            padding: 25px 30px; 
            border-radius: 20px;
            width: 320px; 
            max-width: 90%;
            color: white;
            position: relative;
            box-shadow: 0 10px 25px rgba(0,0,0,0.4); 
            animation: fadeIn 0.8s ease-out;
            max-height: 95vh;
            overflow-y: auto; 
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header-content {
            text-align: center;
            margin-bottom: 15px; 
        }

        .logo {
            width: 60px; 
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
            margin-top: -10px !important; 
            opacity: 0.8;
        }

        .welcome {
            text-align: center;
            font-size: 22px; 
            font-weight: bold;
            margin-bottom: 15px; 
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 10px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .form-group {
            width: 100%; /* Dibuat 100% agar input full sesuai container width */
            margin-bottom: 12px; 
            text-align: left;
            padding: 0;
            box-sizing: border-box;
        }
        
        label {
            display: block;
            margin-bottom: 4px; 
            font-size: 13px;
            font-weight: 600;
        }

        input {
            padding: 10px; /* Padding input ringkas */
            border-radius: 8px; 
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

        /* Khusus Password */
        .password-container { 
            position: relative; 
            width: 100%; 
        }
        .password-container input {
            padding-right: 40px; 
        }
        .toggle-password { 
            position: absolute; 
            right: 10px; 
            top: 50%; 
            transform: translateY(-50%); 
            cursor: pointer; 
            color: #8B0000; 
            font-size: 16px; 
            transition: color 0.2s;
        }

        .form-actions {
            display: flex; 
            justify-content: space-between; 
            gap: 10px; 
            width: 100%; /* Dibuat 100% agar tombol full sesuai container width */
            margin: 15px auto 0 auto; 
        }

        .btn-action {
            flex: 1; 
            padding: 10px 12px; 
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            border: none;
            font-size: 14px; 
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        .btn-action:first-child { /* Tombol Kembali */
            background: rgba(255, 255, 255, 0.1); 
            color: white;
            border: 1px solid white;
        }
        .btn-action:first-child:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .btn-action:last-child { /* Tombol Masuk/Daftar */
            background: #fff;
            color: #8B0000;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        .btn-action:last-child:hover {
            background: #f0f0f0;
        }

        .login-link {
            text-align: center;
            margin-top: 15px;
            font-size: 13px; 
        }

        .login-link a {
            color: #ffd700;
            font-weight: bold;
            text-decoration: none;
            transition: color 0.2s;
        }

        .login-link a:hover {
            text-decoration: underline;
            color: #fff;
        }
        
        .error-text { 
            color: #ffcccc; 
            font-size: 11px; /* Dikecilkan agar lebih ringkas */
            margin-top: 4px; 
        }

        /* Toast Notification */
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
        
        /* Shake effect */
        .shake { animation: shake 0.4s ease; }
        @keyframes shake { 0%,100%{transform:translateX(0);}25%{transform:translateX(-6px);}50%{transform:translateX(6px);}75%{transform:translateX(-6px);} }

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

        <h2 class="welcome">Selamat Datang</h2>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" name="email" placeholder="Email" value="<?= $email_value ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required placeholder="Masukkan kata sandi">
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>
                <div id="passwordError" class="error-text"></div> 
            </div>

            <div class="form-actions">
                <button type="button" class="btn-action" onclick="window.location.href='javascript:history.back()'">Kembali</button>
                <button type="submit" class="btn-action">Masuk</button>
            </div>

            <p class="login-link">Belum punya akun? <a href="register.php">Daftar</a></p>
        </form>
    </div>

    <script>
    function showToast(msg) {
        const toast = document.getElementById("toast");
        const toastMessage = document.getElementById("toast-message");
        toastMessage.textContent = msg;
        toast.classList.add("show");
        setTimeout(() => toast.classList.remove("show"), 5000);
    }

    document.addEventListener("DOMContentLoaded", function() {
        <?php if (!empty($error)): ?>
            // Munculkan toast
            showToast("<?= htmlspecialchars($error) ?>");

            // Shake input password
            const passwordInput = document.getElementById("password");
            passwordInput.classList.add("shake");
            setTimeout(() => passwordInput.classList.remove("shake"), 400);

            // Tambahkan pesan error kecil di bawah password
            document.getElementById("passwordError").textContent = "<?= htmlspecialchars($error) ?>";
        <?php endif; ?>
    });

    // Toggle lihat password
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("password");
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
    </script>
</body>
</html>