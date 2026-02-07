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
$bugun = date('Y-m-d');
$suan_saat = (int)date('H');

// Bugünün kayıtlarını kontrol et
$sabah_check = $conn->query("SELECT id FROM fircalama_takip WHERE user_id=$user_id AND tarih='$bugun' AND vakit='sabah'");
$aksam_check = $conn->query("SELECT id FROM fircalama_takip WHERE user_id=$user_id AND tarih='$bugun' AND vakit='aksam'");

$sabah_fircalandi = ($sabah_check->num_rows > 0);
$aksam_fircalandi = ($aksam_check->num_rows > 0);

// Haftalık Başarı Hesaplama (Son 7 gün)
$yedi_gun_once = date('Y-m-d', strtotime('-7 days'));
$toplam_kayit_sorgu = $conn->query("SELECT COUNT(*) as toplam FROM fircalama_takip WHERE user_id=$user_id AND tarih >= '$yedi_gun_once'");
$toplam_kayit = $toplam_kayit_sorgu->fetch_assoc()['toplam'];
$basari_yuzdesi = round(($toplam_kayit / 14) * 100);

// ZENGİNLEŞTİRİLMİŞ ANALİZ VERİLERİ
$sabah_toplam = $conn->query("SELECT COUNT(*) as t FROM fircalama_takip WHERE user_id=$user_id AND vakit='sabah' AND tarih >= '$yedi_gun_once'")->fetch_assoc()['t'];
$aksam_toplam = $conn->query("SELECT COUNT(*) as t FROM fircalama_takip WHERE user_id=$user_id AND vakit='aksam' AND tarih >= '$yedi_gun_once'")->fetch_assoc()['t'];
$seri_sorgu = $conn->query("SELECT COUNT(DISTINCT tarih) as gun FROM fircalama_takip WHERE user_id=$user_id AND tarih >= '$yedi_gun_once'")->fetch_assoc()['gun'];

// AI KARAR MEKANİZMASI
$ai_tips = [
    "Gece fırçalaması, tükürük akışının azaldığı uyku sırasında diş minesini korumak için en kritik adımdır.",
    "Fırçanı 45 derecelik açıyla tutarak diş eti çizgisine masaj yapman, plak oluşumunu %30 daha fazla engeller.",
    "Dil temizliği, ağız kokusuna neden olan bakterilerin %80'ini yok eder. Unutma!",
    "Diş fırçanı her 3 ayda bir veya hastalık sonrası mutlaka değiştirmelisin."
];

if($basari_yuzdesi >= 90) {
    $ai_icon = "fa-crown text-warning";
    $ai_title = "Zirvedesin, Aslı!";
    $ai_bg = "rgba(255, 193, 7, 0.08)";
    $ai_text = "Muazzam bir disiplin! Son 7 günde neredeyse hiç fire vermedin. Dişlerin şu an bir kale kadar korunaklı.";
} else if ($aksam_toplam < $sabah_toplam && $aksam_toplam < 3) {
    $ai_icon = "fa-moon text-danger";
    $ai_title = "Gece Nöbeti Eksik";
    $ai_bg = "rgba(220, 53, 69, 0.06)";
    $ai_text = "Akşam fırçalamalarını sabahçılara göre daha çok ihmal ediyorsun. Gece bakterileri çok hızlı ürer, bu akşam bir istisna yapalım mı?";
} else if ($seri_sorgu >= 3) {
    $ai_icon = "fa-fire text-danger";
    $ai_title = "$seri_sorgu Günlük Seri!";
    $ai_bg = "rgba(253, 126, 20, 0.06)";
    $ai_text = "Harika gidiyorsun! Tam $seri_sorgu gündür dişlerine vakit ayırıyorsun. Bu seriyi bozmamak için bugünkü kayıtlarını tamamla.";
} else {
    $ai_icon = "fa-lightbulb text-info";
    $ai_title = "Biliyor muydun?";
    $ai_bg = "rgba(23, 162, 184, 0.05)";
    $ai_text = $ai_tips[array_rand($ai_tips)];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <title>Fırçalama Takibi | Diş Sağlığı Asistanı</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
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
      position: relative;
    }
    .navbar a { color: #fff; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; padding: 10px 15px; text-decoration: none; }
    .navbar-brand { font-weight: 700; font-size: 1.5rem; color: white; }
    .nav-tabs .nav-link.active { background: rgba(255, 255, 255, 0.9); color: #4f46e5; border-radius: 8px 8px 0 0; }
    .container { padding: 80px 20px; flex: 1; }
    h1 { 
      font-size: 2.8rem; color: #023e8a; font-weight: 700; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
      animation: fadeIn 1s ease-in-out; text-align: center; margin-top: -30px; margin-bottom: 40px;
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    .nav-shortcuts { display: flex; justify-content: center; gap: 30px; margin-bottom: 50px; }
    .nav-shortcuts button { border: none; background-color: #06b6d4; color: white; padding: 12px 20px; border-radius: 10px; font-weight: 500; cursor: pointer; transition: all 0.3s ease; }
    .nav-shortcuts button:hover { background-color: #a855f7; }

    .feature-section { background: rgba(255,255,255,0.95); padding: 45px 40px; border-radius: 20px; margin-bottom: 45px; box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08); border: 1px solid rgba(0,0,0,0.03); }
    .feature-section h2 { color: #023e8a; font-size: 1.8rem; margin-bottom: 25px; font-weight: 600; }
    
    .progress { height: 18px; border-radius: 10px; background-color: #f0f0f0; margin-bottom: 25px; overflow: hidden; }
    
    .ai-dashboard-card { border-radius: 15px; padding: 25px; border: 1px dashed rgba(0,0,0,0.1); position: relative; overflow: hidden; }
    .ai-badge { position: absolute; top: 15px; right: 15px; font-size: 0.8rem; background: white; padding: 5px 12px; border-radius: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); font-weight: 600; color: #666; }
    
    footer { background: linear-gradient(to bottom, transparent, #023d8aa6 70%); padding: 150px 20px 40px 20px; color: white; font-size: 0.9rem; }
    .footer-content { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px; max-width: 1200px; margin: 0 auto; }
    .footer-section h3 { font-size: 1.2rem; margin-bottom: 15px; }
    .footer-section a, .footer-section p { color: white; font-size: 0.9rem; text-decoration: none; }
    .footer-bottom { text-align: center; padding-top: 20px; }
    .social-icons a { color: white; margin-right: 15px; font-size: 1.2rem; }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <span class="navbar-brand">🦷 Diş Sağlığı</span>
      <ul class="nav nav-tabs">
        <li class="nav-item"><a class="nav-link" href="anasayfa.php">Anasayfa</a></li>
        <li class="nav-item"><a class="nav-link" href="analiz.php">Diş Analizi</a></li>
        <li class="nav-item"><a class="nav-link active" href="fircalama.php">Fırçalama Takibi</a></li>
        <li class="nav-item"><a class="nav-link" href="bahcem.php">Diş Haritam</a></li>
      </ul>
    </div>
  </nav>

  <div class="container">
    <h1>Fırçalama Takibi</h1>
    <div class="nav-shortcuts">
      <button onclick="document.getElementById('gecmis').scrollIntoView({ behavior: 'smooth' })">Fırçalama Geçmişi</button>
      <button onclick="document.getElementById('oneri').scrollIntoView({ behavior: 'smooth' })">AI Destekli Öneri</button>
      <button onclick="document.getElementById('hatirlatma').scrollIntoView({ behavior: 'smooth' })">Hatırlatıcı Ayarla</button>
    </div>

    <div class="feature-section" id="gecmis">
      <h2>Fırçalama Geçmişi</h2>
      <div class="progress">
        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $basari_yuzdesi ?>%;"></div>
      </div>
      <div class="d-flex justify-content-between mb-4">
          <span class="small text-muted fw-bold">Haftalık Başarı Puanın</span>
          <span class="badge bg-success shadow-sm p-2 px-3">%<?= $basari_yuzdesi ?> Başarı</span>
      </div>
      
      <p class="mb-4">Günlük fırçalama verilerinizi kaydederek haftalık başarı yüzdesi hesaplanır.</p>
      
      <div class="d-flex align-items-center gap-4">
          <div class="d-inline-flex align-items-center gap-2">
              <button id="btn-sabah" class="btn <?= $sabah_fircalandi ? 'btn-secondary' : 'btn-success' ?> btn-lg" onclick="islemYap('sabah', 'kaydet')" <?= $sabah_fircalandi ? 'disabled' : '' ?>>
                <i class="fas fa-sun me-2"></i> <?= $sabah_fircalandi ? 'Sabah Kaydedildi' : 'Sabah Fırçaladım' ?>
              </button>
              <?php if($sabah_fircalandi): ?>
                <button class="btn btn-outline-danger btn-lg border-0" onclick="islemYap('sabah', 'sil')"><i class="fas fa-trash-can"></i></button>
              <?php endif; ?>
          </div>
          <div class="d-inline-flex align-items-center gap-2">
              <button id="btn-aksam" class="btn <?= $aksam_fircalandi ? 'btn-secondary' : 'btn-success' ?> btn-lg" onclick="islemYap('aksam', 'kaydet')" <?= $aksam_fircalandi ? 'disabled' : '' ?>>
                <i class="fas fa-moon me-2"></i> <?= $aksam_fircalandi ? 'Akşam Kaydedildi' : 'Akşam Fırçaladım' ?>
              </button>
              <?php if($aksam_fircalandi): ?>
                <button class="btn btn-outline-danger btn-lg border-0" onclick="islemYap('aksam', 'sil')"><i class="fas fa-trash-can"></i></button>
              <?php endif; ?>
          </div>
      </div>
    </div>

    <div class="feature-section" id="oneri">
      <h2>AI Destekli Öneri</h2>
      <div class="ai-dashboard-card shadow-sm" style="background: <?= $ai_bg ?>;">
          <div class="ai-badge"><i class="fas fa-microchip me-1"></i> Akıllı Analiz</div>
          <div class="row align-items-center">
              <div class="col-auto">
                  <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px;">
                      <i class="fas <?= $ai_icon ?> fa-2x"></i>
                  </div>
              </div>
              <div class="col">
                  <h4 class="mb-1 fw-bold"><?= $ai_title ?></h4>
                  <div class="row g-3 mt-3">
                      <div class="col-6 col-md-4">
                          <div class="p-3 text-center rounded-4 shadow-sm border" style="background: rgba(255, 193, 7, 0.1); border-color: rgba(255, 193, 7, 0.2) !important;">
                              <div class="small fw-bold text-uppercase text-muted opacity-75 mb-1">Güneş (Sabah)</div>
                              <div class="h4 fw-bold mb-0 text-dark">
                                  <i class="fas fa-sun text-warning me-2"></i><?= $sabah_toplam ?> / 7
                              </div>
                          </div>
                      </div>

                      <div class="col-6 col-md-4">
                          <div class="p-3 text-center rounded-4 shadow-sm border" style="background: rgba(13, 110, 253, 0.1); border-color: rgba(13, 110, 253, 0.2) !important;">
                              <div class="small fw-bold text-uppercase text-muted opacity-75 mb-1">Ay (Akşam)</div>
                              <div class="h4 fw-bold mb-0 text-dark">
                                  <i class="fas fa-moon text-primary me-2"></i><?= $aksam_toplam ?> / 7
                              </div>
                          </div>
                      </div>

                      <div class="col-12 col-md-4">
                          <div class="p-3 text-center rounded-4 shadow-sm border" style="background: rgba(220, 53, 69, 0.1); border-color: rgba(220, 53, 69, 0.2) !important;">
                              <div class="small fw-bold text-uppercase text-muted opacity-75 mb-1">Ateş (Seri)</div>
                              <div class="h4 fw-bold mb-0 text-dark">
                                  <i class="fas fa-fire text-danger me-2"></i><?= $seri_sorgu ?> Gün
                              </div>
                          </div>
                      </div>
                  </div>


                  <div class="p-3 bg-white rounded-3 border-start border-4 border-info shadow-sm mt-4">
                      <p class="mb-0 text-dark fw-medium" style="font-size: 1.1rem;">"<?= $ai_text ?>"</p>
                  </div>
              </div>
          </div>
      </div>
    </div>

    <div class="feature-section" id="hatirlatma">
      <h2>Hatırlatıcı Ayarla</h2>
      <div class="row g-4 align-items-end">
          <div class="col-md-3">
              <label class="form-label fw-bold text-secondary small text-uppercase"><i class="fas fa-sun text-warning me-2"></i>Sabah Saati</label>
              <input type="time" id="sabah" class="form-control form-control-lg border-0 bg-light" value="08:00">
          </div>
          <div class="col-md-3">
              <label class="form-label fw-bold text-secondary small text-uppercase"><i class="fas fa-moon text-primary me-2"></i>Akşam Saati</label>
              <input type="time" id="aksam" class="form-control form-control-lg border-0 bg-light" value="22:30">
          </div>
          <div class="col-md-4">
              <button class="btn btn-primary btn-lg w-100 shadow fw-bold" onclick="saatleriKaydet()"><i class="fas fa-bell me-2"></i>Saatleri Kaydet</button>
          </div>
      </div>
      <div id="status-msg" class="mt-4 d-none"><div class="alert alert-success border-0 shadow-sm py-3"><strong>Başarılı!</strong> Hatırlatıcı saatlerin güncellendi.</div></div>
    </div>
  </div>

  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <h3>Bize Ulaşın</h3>
        <p><i class="fas fa-envelope"></i> asliaydn12204@gmail.com</p>
        <p><i class="fas fa-phone"></i> +90 555 123 45 67</p>
        <p><i class="fas fa-map-marker-alt"></i> Tokat/Türkiye</p>
      </div>
      <div class="footer-section text-center"><div class="social-icons"><a href="#"><i class="fab fa-twitter"></i></a><a href="#"><i class="fab fa-instagram"></i></a><a href="#"><i class="fab fa-linkedin-in"></i></a></div></div>
      <div class="footer-section text-end"><p>© 2025 Aslı AYDIN tarafından geliştirildi.</p></div>
    </div>
  </footer>

  <script>
    function islemYap(vakit, tip) {
        if (tip === 'sil' && !confirm("Bu kaydı silmek istediğinize emin misiniz?")) return;
        const formData = new FormData();
        formData.append('vakit', vakit); formData.append('islem', tip);
        fetch('fircalama_kaydet.php', { method: 'POST', body: formData })
        .then(response => response.text()).then(data => {
            if(data.trim() === 'success' || data.trim() === 'deleted') location.reload(); else alert('Hata oluştu!');
        });
    }
    function saatleriKaydet() {
        const msg = document.getElementById('status-msg');
        msg.classList.remove('d-none'); setTimeout(() => msg.classList.add('d-none'), 3000);
        if ("Notification" in window) Notification.requestPermission();
    }
  </script>
</body>
</html>