<?php
session_start();
include 'baglanti.php';

if (!isset($_SESSION['user_id'])) { die("unauthorized"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $sabah = $_POST['sabah'];
    $aksam = $_POST['aksam'];

    // Veritabanını güncelle
    $stmt = $conn->prepare("UPDATE users SET hatirlatma_sabah = ?, hatirlatma_aksam = ? WHERE id = ?");
    $stmt->bind_param("ssi", $sabah, $aksam, $user_id);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}
?>