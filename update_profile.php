<?php
session_start();
header("Content-Type: application/json");
include "koneksi.php";

if (!isset($_SESSION['id'])) {
    echo json_encode(["status" => "error", "msg" => "Session ID tidak ditemukan"]);
    exit;
}

$id = $_SESSION['id'];

$data = json_decode(file_get_contents("php://input"), true);

$nama = $data['nama'] ?? null;
$password = $data['password'] ?? null;

// UPDATE NAMA
if (!empty($nama)) {

    $stmt = $conn->prepare("UPDATE regsitrasi SET nama=? WHERE ID=?");
    $stmt->bind_param("si", $nama, $id);
    $stmt->execute();

    $_SESSION['nama'] = $nama;
}

// UPDATE PASSWORD
if (!empty($password)) {

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE regsitrasi SET password=? WHERE ID=?");
    $stmt->bind_param("si", $hashed, $id);
    $stmt->execute();
}

echo json_encode(["status" => "ok"]);
