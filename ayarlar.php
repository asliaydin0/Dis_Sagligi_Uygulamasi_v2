<?php
ob_start();
include 'baglanti.php';
session_start();

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$mesaj = "";
$mesaj_tur = "";

// 1. GÜNCELLEME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad_soyad = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $yeni_sifre = trim($_POST['password']);
    
    // E-posta kontrolü (Aynı mail başkasında var mı?)
    $mail_kontrol = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $mail_kontrol->bind_param("si", $email, $user_id);
    $mail_kontrol->execute();
    
    if ($mail_kontrol->get_result()->num_rows > 0) {
        $mesaj = "Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor.";
        $mesaj_tur = "danger";
    } else {
        // Şifre değiştirilecek mi?
        if (!empty($yeni_sifre)) {
            // Şifreli güncelleme (Password Hash)
            // Not: Eğer veritabanında şifreler düz metin tutuluyorsa password_hash kısmını kaldırıp direkt $yeni_sifre yaz.
            // Ancak güvenlik için password_hash önerilir.
            $hashed_password = password_hash($yeni_sifre, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET fullname = ?, email = ?, password = ? WHERE id = ?");
            $update->bind_param("sssi", $ad_soyad, $email, $hashed_password, $user_id);
        } else {
            // Sadece bilgi güncelleme
            $update = $conn->prepare("UPDATE users SET fullname = ?, email = ? WHERE id = ?");
            $update->bind_param("ssi", $ad_soyad, $email, $user_id);
        }

        if ($update->execute()) {
            $mesaj = "Bilgilerin başarıyla güncellendi!";
            $mesaj_tur = "success";
            // Session'daki ismi de güncelle
            $_SESSION['username'] = $ad_soyad; // Eğer login'de session'a isim atıyorsan
        } else {
            $mesaj = "Güncelleme sırasında bir hata oluştu.";
            $mesaj_tur = "danger";
        }
    }
}

// 2. MEVCUT BİLGİLERİ ÇEKME
$stmt = $conn->prepare("SELECT fullname, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <title>Ayarlar | Diş Sağlığı Asistanı</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <link rel="stylesheet" href="style.css" />
  <style>
    /* Diğer sayfalarla uyumlu genel stil */
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #f5f7fa, #c3e0ff, #e0c3fc);
      background-size: 200% 200%;
      animation: backgroundGradient 15s ease infinite;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    @keyframes backgroundGradient {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    
    .navbar {
      background: linear-gradient(45deg, #ff6b6b, #a855f7, #06b6d4);
      padding: 10px 30px;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
      border-bottom: 3px solid rgba(255, 255, 255, 0.2);
    }
    .navbar a { color: #fff; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; padding: 10px 15px; text-decoration: none; }
    .navbar-brand { font-weight: 700; font-size: 1.5rem; color: white; }
    .nav-tabs .nav-link.active { background: rgba(255, 255, 255, 0.9); color: #4f46e5; border-radius: 8px 8px 0 0; }

    .container { padding-top: 40px; flex: 1; }
    h1 { 
      font-size: 2.5rem; color: #023e8a; font-weight: 700; 
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); text-align: center; margin-bottom: 40px; margin-top: -40px;
    }

    /* Kart Tasarımı (Diğer sayfalardaki feature-section ile aynı) */
    .settings-card {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
      max-width: 800px;
      margin: 0 auto;
    }

    .profile-header {
      text-align: center;
      margin-bottom: 30px;
    }
    .profile-avatar {
      width: 100px; height: 100px;
      background: linear-gradient(45deg, #06b6d4, #a855f7);
      border-radius: 50%;
      display: inline-flex;
      align-items: center; justify-content: center;
      color: white; font-size: 3rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      margin-bottom: 15px;
    }
    
    .form-control {
      padding: 12px; border-radius: 10px; border: 1px solid #ddd; background: #f8f9fa;
    }
    .form-control:focus {
      box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.2); border-color: #06b6d4;
    }
    .btn-update {
      background: linear-gradient(45deg, #06b6d4, #a855f7);
      border: none; color: white; padding: 12px 30px;
      border-radius: 50px; font-weight: 600; font-size: 1.1rem;
      transition: transform 0.2s;
    }
    .btn-update:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(168, 85, 247, 0.4); color: white;}
    
    .btn-logout {
      background: #ff6b6b; border: none; color: white;
      padding: 10px 20px; border-radius: 10px; font-weight: 500;
      text-decoration: none; display: inline-block; margin-top: 20px;
    }
    .btn-logout:hover { background: #e63946; color: white; }
  </style>

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="theme-color" content="#06b6d4" />
  <link rel="manifest" href="manifest.json" />
  
  <link rel="apple-touch-icon" href="img/icon-192.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function() {
        navigator.serviceWorker.register('service-worker.js').then(function(registration) {
          console.log('PWA Servis Çalışanı başarıyla kaydedildi: ', registration.scope);
        }, function(err) {
          console.log('PWA Servis Çalışanı hatası: ', err);
        });
      });
    }
  </script>
  
</head>
<body>

  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <span class="navbar-brand">🦷 Diş Sağlığı</span>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="nav nav-tabs ms-auto">
          <li class="nav-item"><a class="nav-link" href="anasayfa.php">Anasayfa</a></li>
          <li class="nav-item"><a class="nav-link" href="analiz.php">Diş Analizi</a></li>
          <li class="nav-item"><a class="nav-link" href="fircalama.php">Fırçalama Takibi</a></li>
          <li class="nav-item"><a class="nav-link" href="bahcem.php">Diş Haritam</a></li>
          <li class="nav-item"><a class="nav-link active" href="ayarlar.php"><i class="fas fa-cog"></i></a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container">
    <h1>Hesap Ayarları</h1>

    <div class="settings-card">
      
      <?php if($mesaj): ?>
        <div class="alert alert-<?= $mesaj_tur ?> alert-dismissible fade show" role="alert">
          <?= $mesaj ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <div class="profile-header">
        <div class="profile-avatar">
          <i class="fas fa-user"></i>
        </div>
        <h3 class="fw-bold text-dark"><?= htmlspecialchars($user['fullname']) ?></h3>
        <p class="text-muted">Üyelik Tarihi: <?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
      </div>

      <form method="POST" action="">
        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label fw-bold text-secondary">Ad Soyad</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-muted"></i></span>
                <input type="text" name="fullname" class="form-control border-start-0" value="<?= htmlspecialchars($user['fullname']) ?>" required>
            </div>
          </div>
          
          <div class="col-md-6">
            <label class="form-label fw-bold text-secondary">E-posta Adresi</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                <input type="email" name="email" class="form-control border-start-0" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
          </div>

          <div class="col-12">
            <hr class="my-4 text-muted opacity-25">
            <h5 class="mb-3 text-primary"><i class="fas fa-lock me-2"></i>Şifre Değiştir</h5>
            <p class="small text-muted">Şifrenizi değiştirmek istemiyorsanız bu alanı boş bırakın.</p>
          </div>

          <div class="col-md-12">
            <label class="form-label fw-bold text-secondary">Yeni Şifre</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-key text-muted"></i></span>
                <input type="password" name="password" class="form-control border-start-0" placeholder="Yeni şifreniz (İsteğe bağlı)">
            </div>
          </div>

          <div class="col-12 text-center mt-4">
            <button type="submit" class="btn btn-update w-50 shadow">
              <i class="fas fa-save me-2"></i>Bilgileri Güncelle
            </button>
          </div>
        </div>
      </form>

      <div class="text-center mt-5">
        <a href="cikis.php" class="btn btn-logout shadow-sm">
          <i class="fas fa-sign-out-alt me-2"></i>Güvenli Çıkış Yap
        </a>
      </div>

    </div>
  </div>

  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <h3>Bize Ulaşın</h3>
        <p><i class="fas fa-envelope"></i> asliaydn12204@gmail.com</p>
        <p><i class="fas fa-map-marker-alt"></i> Aydın Diş Sağlığı Merkezi</p>
      </div>
      <div class="footer-section">
        <h3>Hakkımızda</h3>
        <p>Kişisel verilerinizi buradan güncelleyebilirsiniz.</p>
      </div>
    </div>
    <div class="footer-bottom">
      © 2025 Aslı AYDIN
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>