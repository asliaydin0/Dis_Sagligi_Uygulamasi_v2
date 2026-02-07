<?php
ob_start();
include 'baglanti.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?mesaj=login_required");
    exit;
}

$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $username = $user['fullname'];
} else {
    session_unset();
    session_destroy();
    header("Location: login.php?mesaj=user_not_found");
    exit;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <title>Hoş Geldin <?php echo htmlspecialchars($username); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <link rel="stylesheet" href="style.css" />
  <style>
    .container {
      text-align: center;
      padding: 60px 20px;
      flex: 1;
    }

    .box-group {
      display: flex;
      justify-content: center;
      gap: 40px;
      flex-wrap: wrap;
    }

    .box {
      background: linear-gradient(135deg, #ffffff, #f0f9ff);
      border-radius: 20px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
      width: 320px;
      padding: 35px 25px;
      transition: all 0.3s ease;
      text-align: left;
      cursor: pointer;
      border-top: 6px solid #06b6d4;
      position: relative;
      overflow: hidden;
    }

    .box::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(6, 182, 212, 0.2), transparent);
      transition: 0.5s;
    }

    .box:hover::before {
      left: 100%;
    }

    .box:hover {
      transform: translateY(-10px) scale(1.03);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
      border-top-color: #a855f7;
    }

    .box h2 {
      color: #023e8a;
      font-size: 1.5rem;
      margin-bottom: 15px;
      font-weight: 600;
    }

    .box p {
      color: #444;
      font-size: 1rem;
      line-height: 1.5;
    }

    .box-icon {
      font-size: 2rem;
      margin-bottom: 10px;
      color: #06b6d4;
      transition: color 0.3s ease;
    }

    .box:hover .box-icon {
      color: #a855f7;
    }

    @media (max-width: 768px) {
      .box {
        width: 90%;
      }
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <span class="navbar-brand">🦷 Diş Sağlığı</span>
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a class="nav-link active" href="anasayfa.php">Anasayfa</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="analiz.php">Diş Analizi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="fircalama.php">Fırçalama Takibi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="bahcem.php">Diş Haritam</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="ayarlar.php"><i class="fas fa-cog"></i></a>
        </li>
      </ul>
    </div>
  </nav>
  <div class="container">
    <h1>Hoş geldin, <?php echo htmlspecialchars($username); ?> 👋</h1>
    <div class="box-group">
      <div class="box" onclick="window.location.href='analiz.php'">
        <div class="box-icon">🦷</div>
        <h2>Hızlı Diş Analizi</h2>
        <p>Diş sağlığına yönelik şikayetlerini gir, yapay zekâ analiz etsin ve sana özel öneriler sunsun.</p>
      </div>
      <div class="box" onclick="window.location.href='fircalama.php'">
        <div class="box-icon">🪥</div>
        <h2>Fırçalama Takibi</h2>
        <p>Günde kaç kez diş fırçaladığını takip et, hatırlatmalar al ve düzenli alışkanlık kazan.</p>
      </div>
      <div class="box" onclick="window.location.href='bahcem.php'">
        <div class="box-icon">😊</div>
        <h2>Diş Haritam</h2>
        <p>Diş sağlığını korumak için eğlenceli bir alan.</p>
      </div>
    </div>
  </div>
  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <h3>Bize Ulaşın</h3>
        <p><i class="fas fa-envelope"></i> asliaydn12204@gmail.com</p>
        <p><i class="fas fa-phone"></i> +90 555 123 45 67</p>
        <p><i class="fas fa-map-marker-alt"></i> Aydın Diş Sağlığı Merkezi, Tokat/Türkiye</p>
      </div>
      <div class="footer-section">
        <h3>Bizi Takip Edin</h3>
        <div class="social-icons">
          <a href="https://twitter.com/aslaydn0" target="_blank"><i class="fab fa-twitter"></i></a>
          <a href="https://instagram.com/asliaydn_w" target="_blank"><i class="fab fa-instagram"></i></a>
          <a href="https://www.linkedin.com/in/asliaydin0" target="_blank"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>
      <div class="footer-section">
        <h3>Hakkımızda</h3>
        <p>Diş Sağlığı platformu, yapay zeka destekli çözümlerle diş sağlığınızı korumanıza yardımcı olur.</p>
      </div>
    </div>
    <div class="footer-bottom">
      © 2025 Aslı AYDIN tarafından geliştirildi.
    </div>
  </footer>
</body>
</html>
<?php ob_end_flush(); ?>