<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hızlı Diş Analizi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <link rel="stylesheet" href="style.css" />
  <style>
    .container {
      flex: 1;
      max-width: 900px;
      margin: 5px auto;
      background: white;
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      border-top: 6px solid #06b6d4;
      
    }

    textarea {
      width: 100%;
      height: 150px;
      padding: 15px;
      font-size: 16px;
      border: 2px solid #cbd5e1;
      border-radius: 12px;
      background: #f8fafc;
      color: #1e293b;
      resize: none;
    }

    textarea:focus {
      outline: none;
      border-color: #4f46e5;
      box-shadow: 0 0 8px rgba(79, 70, 229, 0.3);
    }

    .btn {
      display: block;
      width: 100%;
      margin-top: 25px;
      background: #4f46e5;
      color: white;
      border: none;
      padding: 15px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn:hover {
      background: #4338ca;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
    }

    .ai-response {
      margin-top: 30px;
      background: #f1f5f9;
      padding: 20px;
      border-left: 6px solid #4f46e5;
      border-radius: 10px;
      color: #1e293b;
      line-height: 1.6;
    }

    .bottom-buttons {
      display: flex;
      justify-content: center;
      margin-top: 30px;
    }

    .bottom-buttons a {
      text-decoration: none;
      background: #16a34a;
      color: white;
      padding: 12px 25px;
      border-radius: 10px;
      transition: background 0.3s ease;
    }

    .bottom-buttons a:hover {
      background: #15803d;
    }

    .tooth {
      min-width: 60px;
      text-align: center;
      border-radius: 8px;
      transition: 0.3s;
    }

    .tooth.active {
      background-color: #4f46e5 !important;
      color: white !important;
      border-color: #4f46e5 !important;
    }

    .tooth-image {
      width: 100%;
      max-height: 350px;
      object-fit: contain;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    label {
      font-weight: 500;
    }

    select, textarea {
      margin-bottom: 20px;
    }

    .btn-primary {
      background-color: #0077b6;
      border: none;
    }

    .btn-primary:hover {
      background-color: #023e8a;
    }

    .ai-suggestion {
      background-color: #e0f7fa;
      padding: 15px;
      border-left: 4px solid #00acc1;
      margin-top: 20px;
      border-radius: 8px;
    }

    .mhrs-btn {
      display: block;
      margin-left: auto;
      margin-right: auto;
      margin-top: 20px;
      background: #16a34a;
      color: white;
      border: none;
      padding: 10px 20px;
      font-size: 14px;
      font-weight: 500;
      border-radius: 8px;
      text-decoration: none;
      transition: all 0.3s ease;
      width: fit-content;
    }

    .mhrs-btn:hover {
      background: #107133ff;
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
    }
    
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <span class="navbar-brand">🦷 Diş Sağlığı</span>
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a class="nav-link" href="anasayfa.php">Anasayfa</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="analiz.php">Diş Analizi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="fircalama.php">Fırçalama Takibi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="bahcem.php">Diş Haritam</a>
        </li>
      </ul>
    </div>
  </nav>
  <h1>Hızlı Diş Analizi</h1>
  <div class="container">
<img src="img/dis-numarali.png" alt="Numaralandırılmış Diş Görseli" class="tooth-image">

    <form method="POST">
      <div class="mb-3">
        <label for="toothNumber" class="form-label">Lütfen şikayetçi olduğunuz diş numarasını seçin:</label>
        <select class="form-select" name="toothNumber" id="toothNumber" required>
          <option value="" disabled selected>Seçiniz</option>
          <?php
          // Sağ Üst (Quadrant 1: 11-18)
          echo '<optgroup label="Sağ Üst Çeyrek">';
          for ($i = 1; $i <= 8; $i++) {
              $toothNumber = 10 + $i; // 11-18
              echo "<option value='$toothNumber'>Sağ Üst $i ($toothNumber)</option>";
          }
          echo '</optgroup>';

          // Sağ Alt (Quadrant 4: 41-48)
          echo '<optgroup label="Sağ Alt Çeyrek">';
          for ($i = 1; $i <= 8; $i++) {
              $toothNumber = 40 + $i; // 41-48
              echo "<option value='$toothNumber'>Sağ Alt $i ($toothNumber)</option>";
          }
          echo '</optgroup>';

          // Sol Üst (Quadrant 2: 21-28)
          echo '<optgroup label="Sol Üst Çeyrek">';
          for ($i = 1; $i <= 8; $i++) {
              $toothNumber = 20 + $i; // 21-28
              echo "<option value='$toothNumber'>Sol Üst $i ($toothNumber)</option>";
          }
          echo '</optgroup>';

          // Sol Alt (Quadrant 3: 31-38)
          echo '<optgroup label="Sol Alt Çeyrek">';
          for ($i = 1; $i <= 8; $i++) {
              $toothNumber = 30 + $i; // 31-38
              echo "<option value='$toothNumber'>Sol Alt $i ($toothNumber)</option>";
          }
          echo '</optgroup>';
          ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="complaint" class="form-label">

Şikayetinizi kısaca yazın:</label>
        <textarea class="form-control" name="complaint" id="complaint" rows="4" placeholder="Örneğin: Ağrı, hassasiyet, şişlik..." required></textarea>
      </div>

      <div class="mb-3">
        <label for="painLevel" class="form-label">Şikayet Seviyesi:</label>
        <select class="form-select" name="painLevel" id="painLevel" required>
          <option value="" disabled selected>Seçiniz</option>
          <option value="Hafif">Hafif</option>
          <option value="Orta">Orta</option>
          <option value="Şiddetli">Şiddetli</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">Analizi Göster</button>
      
    </form>

    <?php
    require_once 'gemini_api.php';

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $tooth = htmlspecialchars($_POST['toothNumber']);
        $complaint = htmlspecialchars($_POST['complaint']);
        $pain = htmlspecialchars($_POST['painLevel']);

        $aiResponse = analyzeToothWithGemini($tooth, $complaint, $pain);

        echo "<div class='ai-suggestion'>";
        echo "<strong>Yapay Zeka Önerisi:</strong><br>";
        echo "📍 Diş: <strong>$tooth</strong><br>";
        echo "📄 Şikayet: <em>$complaint</em><br>";
        echo "🔺 Ağrı Şiddeti: <strong>$pain</strong><br><br>";
        echo "🤖 $aiResponse";
        echo "</div>";
    }
    
    ?>
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
        <p>Diş Sağlığı platformu, yapay zeka destekli çözümlerle diş sağlığınızı korumanıza yardımcı olur. Güvenilir analizler ve önerilerle sağlığınıza değer katıyoruz.</p>
      </div>
    </div>
    <div class="footer-bottom">
      © 2025 Aslı AYDIN tarafından geliştirildi.
    </div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>