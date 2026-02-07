<?php
session_start();
include 'baglanti.php';

if (!isset($_SESSION['user_id'])) { die("unauthorized"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $vakit = $_POST['vakit'];
    $islem = isset($_POST['islem']) ? $_POST['islem'] : 'kaydet';
    $tarih = date('Y-m-d');

    if ($islem === 'sil') {
        $stmt = $conn->prepare("DELETE FROM fircalama_takip WHERE user_id = ? AND tarih = ? AND vakit = ?");
        $stmt->bind_param("iss", $user_id, $tarih, $vakit);
        echo $stmt->execute() ? "deleted" : "error";
    } else {
        $check = $conn->prepare("SELECT id FROM fircalama_takip WHERE user_id = ? AND tarih = ? AND vakit = ?");
        $check->bind_param("iss", $user_id, $tarih, $vakit);
        $check->execute();
        if ($check->get_result()->num_rows == 0) {
            $ins = $conn->prepare("INSERT INTO fircalama_takip (user_id, tarih, vakit) VALUES (?, ?, ?)");
            $ins->bind_param("iss", $user_id, $tarih, $vakit);
            echo $ins->execute() ? "success" : "error";
        } else { echo "exists"; }
    }
}
?>