<?php
session_start();
include "koneksi.php";

header("Content-Type: application/json");

if (!isset($_SESSION['nama'])) {
    echo json_encode(["status" => "error"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$nama = trim($data['nama'] ?? '');
$password = trim($data['password'] ?? '');

if ($nama === '') {
    echo json_encode(["status" => "error"]);
    exit;
}

$nama_baru = $conn->real_escape_string($nama);
$nama_lama = $conn->real_escape_string($_SESSION['nama']);

if ($password !== '') {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE regsitrasi SET nama='$nama_baru', password='$password_hash' WHERE nama='$nama_lama'";
} else {
    $sql = "UPDATE regsitrasi SET nama='$nama_baru' WHERE nama='$nama_lama'";
}

if (!$conn->query($sql)) {
    echo json_encode(["status" => "error"]);
    exit;
}

if ($password !== '') {

    if (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[@$!%*#?&]/', $password)
    ) {
        echo json_encode([
            "status" => "error",
            "message" => "Password tidak memenuhi standar keamanan"
        ]);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
}

// UPDATE SESSION
$_SESSION['nama'] = $nama_baru;

// ⬇️ PENTING: HANYA INI OUTPUTNYA
echo json_encode([
    "status" => "ok",
    "nama" => $nama_baru
]);
exit;
