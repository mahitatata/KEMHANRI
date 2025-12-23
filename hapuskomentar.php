<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user_id'])) {
    exit("not_logged_in");
}

$komentar_id = intval($_POST['id'] ?? 0);
if ($komentar_id <= 0) exit("invalid_id");

$sessionUserId = (int)$_SESSION['user_id'];
$sessionRole   = $_SESSION['role'] ?? 'user';

/* =========================
   1. CEK KOMENTAR FORUM
========================= */
$stmt = $conn->prepare("SELECT user_id FROM komentar_forum WHERE id = ?");
$stmt->bind_param("i", $komentar_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $ownerId = (int)$row['user_id'];

    if ($sessionRole !== 'admin' && $sessionUserId !== $ownerId) {
        exit("forbidden");
    }

    // hapus komentar + semua balasan
    $del = $conn->prepare("DELETE FROM komentar_forum WHERE id = ? OR balasan = ?");
    $del->bind_param("ii", $komentar_id, $komentar_id);

    exit($del->execute() ? "success" : "db_error");
}

/* =========================
   2. CEK KOMENTAR ARTIKEL
========================= */
$stmt = $conn->prepare("SELECT user_id FROM komentar WHERE id = ?");
$stmt->bind_param("i", $komentar_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $ownerId = (int)$row['user_id'];

    if ($sessionRole !== 'admin' && $sessionUserId !== $ownerId) {
        exit("forbidden");
    }

    $del = $conn->prepare("DELETE FROM komentar WHERE id = ? OR parent_id = ?");
    $del->bind_param("ii", $komentar_id, $komentar_id);

    exit($del->execute() ? "success" : "db_error");
}

exit("not_found");
