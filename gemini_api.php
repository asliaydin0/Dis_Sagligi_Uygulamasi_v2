<?php
// ────────────────────────────────────────────────────────────
// 1. .ENV DOSYASINI OKUMA FONKSİYONU
// (Bu kısım sayesinde kütüphane kurmadan .env dosyasını okuruz)
// ────────────────────────────────────────────────────────────
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Yorum satırlarını (#) atla
        if (strpos(trim($line), '#') === 0) continue;
        
        // Eşittir işaretine göre ayır
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            // Tırnak işaretlerini temizle
            $value = trim($value, '"\'');
            
            // Ortam değişkenlerine ekle
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// .env dosyasını yükle (Dosyanın bu php dosyasıyla aynı klasörde olduğunu varsayar)
loadEnv(__DIR__ . '/.env');

// ────────────────────────────────────────────────────────────
// 2. ANA FONKSİYON
// ────────────────────────────────────────────────────────────
function analyzeToothWithGemini($tooth, $complaint, $painLevel) {
    
    // API Anahtarını kodun içine yazmıyoruz, .env'den çekiyoruz
    $apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
    
    // Olası boşlukları temizle (Hata riskini azaltır)
    $apiKey = trim($apiKey);

    // Anahtar kontrolü
    if (empty($apiKey) || $apiKey === 'KEY_YOK') {
        return "Hata: API anahtarı .env dosyasında bulunamadı.";
    }

    // ────────────────────────────────────────────────────────────
    // GÜNCELLEME 1: PROMPT'A MHRS BÖLÜM SEÇİMİ EKLENDİ
    // ────────────────────────────────────────────────────────────
    $prompt = "Sen profesyonel bir diş hekimisin. Aşağıdaki hasta bilgilerine göre analiz yap:

Diş Numarası: $tooth
Şikayet: $complaint  
Ağrı Seviyesi: $painLevel

Lütfen şu başlıklar altında yanıt ver:
1. Olası Nedenler (Kısa 2 madde)
2. Evde Ne Yapılabilir? (Pratik öneriler)
3. Tedavi Yöntemi (Dolgu, kanal vb.)
4. MHRS RANDEVU REHBERİ: (Bu kısmı çok net yaz)
   - Hastanın hastaneden randevu alırken hangi polikliniği seçmesi gerektiğini belirt.
   - Seçenekler: 'Restoratif Diş Tedavisi' (Dolgu için), 'Endodonti' (Kanal tedavisi için), 'Ağız Diş ve Çene Cerrahisi' (Çekim/Gömülü diş), 'Periodontoloji' (Diş eti), 'Protetik Diş Tedavisi' (Kaplama/Protez).
   - Eğer emin değilsen veya ilk muayene gerekliyse 'Genel Diş Polikliniği (Muayene)' öner.
   - Bölüm adını mutlaka **kalın** yaz.

Türkçe, anlaşılır ve empatik bir dil kullan. Maximum 300 kelime.";

    $postData = [
        "contents" => [[
            "parts" => [["text" => $prompt]]
        ]],
        "generationConfig" => [
            "temperature" => 0.7,
            "maxOutputTokens" => 1000
        ]
    ];
    
    
    $model = "gemini-2.5-flash-lite"; 
    $url = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key=" . $apiKey;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return "Bağlantı Hatası: " . $curlError;
    }

    if ($httpCode !== 200) {
        $errorData = json_decode($response, true);
        $errorMessage = $errorData['error']['message'] ?? 'Bilinmeyen hata';
        return "API Hatası (HTTP $httpCode): $errorMessage";
    }

    // Debug (test modunda)
    if (isset($_GET['test']) && $_GET['test'] == '1') {
        echo "<details><summary>Debug: Tam API Yanıtı</summary><pre>" . htmlspecialchars($response) . "</pre></details><hr>";
    }

    $data = json_decode($response, true);

    // Ham metni al
    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        $text = $data['candidates'][0]['content']['parts'][0]['text'];
    } elseif (!empty($data['candidates'][0]['content']['parts'])) {
        $text = implode("\n", array_column($data['candidates'][0]['content']['parts'], 'text'));
    } else {
        return "Yapay zeka yanıtı alınamadı. (Boş içerik)";
    }

    // ────────────────────────────────
    // GÜZELLEŞTİRME 
    // ────────────────────────────────
    $text = trim($text);
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    // Kalın yazı (**text** veya __text__)
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/__(.*?)__/', '<strong>$1</strong>', $text);

    // Başlıkları güvenli şekilde işle
    $text = preg_replace_callback('/^(#{1,3})\s*(.+)$/m', function($m) {
        $hashes = strlen($m[1]);
        $title  = trim($m[2]);
        
        if ($hashes == 1) {
            return '<h1 style="color:#1e40af; margin:24px 0 12px; font-size:1.6em;">Diş ' . $title . '</h1>';
        } elseif ($hashes == 2) {
            return '<h2 style="color:#1e40af; margin:22px 0 10px; font-size:1.4em;">Diş ' . $title . '</h2>';
        } else { // ### 
            return '<h3 style="color:#1e40af; margin:18px 0 8px; font-size:1.2em;">' . $title . '</h3>';
        }
    }, $text);

    // Madde işaretlerini güzelleştir
    $text = preg_replace('/^(\s*)(?:\d+\.|\-|\•|\*)\s+/m', '$1• ', $text);

    // Satır sonları
    $text = nl2br($text);

    // ────────────────────────────────────────────────────────────
    // ÇIKTI KUTUSU VE MHRS BUTONU TASARIMI
    // ────────────────────────────────────────────────────────────
    $finalOutput = '
    <div style="font-family:system-ui,Arial,sans-serif; max-width:680px; margin:20px auto; background:#f0f9ff; padding:26px; border-radius:16px; border-left:6px solid #0ea5e9; box-shadow:0 6px 25px rgba(14,165,233,0.15); line-height:1.8;">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:18px; color:#1e293b;">
            <strong style="font-size:2.3em; color:#1e40af;">Diş Hekimi Asistanı</strong>
        </div>
        <div style="color:#2d3748; font-size:1.05em;">
            ' . $text . '
        </div>
        <div style="margin-top:24px; padding-top:14px; border-top:1px dashed #94a3b8; font-size:0.9em; color:#64748b;">
            Bu değerlendirme yapay zeka tarafından yapılmıştır. Kesin tanı ve tedavi için diş hekiminize başvurunuz.
        </div>
        
        <div style="margin-top:20px; background:#eff6ff; border: 2px dashed #3b82f6; border-radius:12px; padding:15px; text-align:center;">
            <div style="color:#1e40af; font-weight:bold; margin-bottom:5px;">
                <i class="fas fa-hospital-user me-2"></i>Randevu İpucu
            </div>
            <div style="font-size:0.9em; color:#475569; margin-bottom:10px;">
                Yukarıda belirtilen poliklinik için randevu alabilirsiniz.
            </div>
            <a href="https://www.mhrs.gov.tr" target="_blank" style="display:inline-block; background:#e11d48; color:white; text-decoration:none; padding:10px 25px; border-radius:50px; font-weight:bold; transition:all 0.2s;">
                MHRS İle Randevu Al
            </a>
        </div>
    </div>';

    return $finalOutput;
}

// TEST MODU
if (isset($_GET['test']) && $_GET['test'] == '1') {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Gemini Diş Testi</title></head><body style='background:#f8fafc; padding:20px;'>";
    echo "<h2 style='text-align:center; color:#1e40af;'>Gemini Diş Analizi Testi</h2>";
    echo analyzeToothWithGemini("11", "Diş ağrısı var, sıcak soğuk hassasiyeti", "Orta");
    echo "</body></html>";
    exit;
}
?>