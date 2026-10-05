<?php
// bot.php - Telegram Bot Webhook & Handler for SUPPER AI Mini App
require_once __DIR__ . '/config/db.php';

$pdo = getDb();
$settings = getSettings($pdo);

// Cấu hình Bot Token & WebApp URL
$botToken = $settings['telegram_bot_token'] ?? '8764330129:AAFGAUF01c2bvCXQu7cftO_BELfNg78G0So';
$webAppUrl = $settings['telegram_webapp_url'] ?? (isset($_SERVER['HTTP_HOST']) ? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']) : 'https://edencoin.onrender.com');

// Helper gửi request đến Telegram Bot API
function telegramApi(string $method, array $data = [], string $token = ''): array {
    global $botToken;
    $tk = !empty($token) ? $token : $botToken;
    $url = "https://api.telegram.org/bot{$tk}/{$method}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true) ?: [];
}

// -------------------------------------------------------------
// XỬ LÝ CÁC THAO TÁC CÀI ĐẶT NHANH (SETUP ACTIONS)
// -------------------------------------------------------------
$action = $_GET['action'] ?? '';

// 1. Cài đặt Menu Button (Nút Mini App nằm góc trái thanh gõ tin nhắn của Bot)
if ($action === 'set_menu_button') {
    $targetUrl = $_GET['url'] ?? $webAppUrl;
    $customToken = $_GET['token'] ?? $botToken;

    if ($customToken === 'YOUR_BOT_TOKEN_HERE') {
        echo json_encode(['error' => 'Vui lòng cung cấp bot token qua tham số ?token=YOUR_TOKEN'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $res = telegramApi('setChatMenuButton', [
        'menu_button' => [
            'type' => 'web_app',
            'text' => '🚀 Mở SUPPER AI App',
            'web_app' => [
                'url' => $targetUrl
            ]
        ]
    ], $customToken);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $res['ok'] ?? false,
        'message' => ($res['ok'] ?? false) ? 'Đã cài đặt Menu Button Mini App thành công!' : 'Lỗi cài đặt Menu Button',
        'telegram_response' => $res,
        'configured_url' => $targetUrl
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// 2. Cài đặt Webhook Telegram
if ($action === 'set_webhook') {
    $webhookUrl = $_GET['url'] ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/bot.php');
    $customToken = $_GET['token'] ?? $botToken;

    $res = telegramApi('setWebhook', [
        'url' => $webhookUrl
    ], $customToken);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $res['ok'] ?? false,
        'message' => ($res['ok'] ?? false) ? 'Đã kích hoạt Webhook thành công!' : 'Lỗi kích hoạt Webhook',
        'telegram_response' => $res,
        'webhook_url' => $webhookUrl
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// -------------------------------------------------------------
// NHẬN VÀ XỬ LÝ TIN NHẮN TỪ TELEGRAM WEBHOOK
// -------------------------------------------------------------
$rawUpdate = file_get_contents('php://input');
if (empty($rawUpdate)) {
    // Truy cập trực tiếp qua trình duyệt -> Hiển thị trang hướng dẫn kết nối
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
      <meta charset="UTF-8">
      <title>SUPPER AI - Telegram Bot Webhook</title>
      <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #07090e; color: #fff; padding: 30px; line-height: 1.6; }
        .card { max-width: 650px; margin: 0 auto; background: #111422; border: 1px solid rgba(0, 242, 254, 0.2); border-radius: 16px; padding: 26px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { color: #00f2fe; margin-top: 0; font-size: 1.5rem; text-shadow: 0 0 12px rgba(0,242,254,0.4); }
        code { background: #000; padding: 3px 8px; border-radius: 6px; color: #38bdf8; font-family: monospace; }
        .btn { display: inline-block; background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%); color: #000; text-decoration: none; padding: 12px 22px; border-radius: 10px; font-weight: bold; margin-top: 14px; }
      </style>
    </head>
    <body>
      <div class="card">
        <h1>🤖 SUPPER AI Telegram Bot Hub</h1>
        <p>File webhook đang hoạt động bình thường trên máy chủ của bạn.</p>
        <h3>🛠️ Thao tác nhanh:</h3>
        <ul>
          <li><strong>Cài đặt Menu Button:</strong> <code>bot.php?action=set_menu_button&token=BOT_TOKEN&url=DOMAIN_URL</code></li>
          <li><strong>Cài đặt Webhook:</strong> <code>bot.php?action=set_webhook&token=BOT_TOKEN&url=DOMAIN_URL/bot.php</code></li>
        </ul>
        <p><a href="index.php" class="btn">🚀 Mở SUPPER AI WebApp &rarr;</a></p>
      </div>
    </body>
    </html>
    <?php
    exit;
}

$update = json_decode($rawUpdate, true);
if (!$update || !isset($update['message'])) {
    exit;
}

$message = $update['message'];
$chatId = $message['chat']['id'] ?? null;
$text = trim($message['text'] ?? '');
$from = $message['from'] ?? [];
$userId = $from['id'] ?? null;
$firstName = htmlspecialchars($from['first_name'] ?? 'Miner', ENT_QUOTES, 'UTF-8');

if (!$chatId) exit;

// Xử lý lệnh /start hoặc /start <ref_uid>
if (strpos($text, '/start') === 0) {
    $parts = explode(' ', $text);
    $startParam = $parts[1] ?? '';

    // URL Mini App kèm startapp
    $appUrlWithParam = !empty($startParam) ? "{$webAppUrl}?startapp={$startParam}" : $webAppUrl;

    $welcomeMessage = "👋 Xin chào <b>{$firstName}</b>! Chào mừng bạn đến với <b>SUPPER AI Ecosystem</b> 🤖⚡\n\n";
    $welcomeMessage .= "⚡ <b>Nền tảng khai thác SUPPER AI tự động thế hệ mới</b>:\n";
    $welcomeMessage .= "• Nhận ngay <b>100 USDT</b> khởi nghiệp trải nghiệm miễn phí\n";
    $welcomeMessage .= "• Máy khai thác AI Quantum <b>6,000 SUPPER/ngày</b> sinh lời 24/7\n";
    $welcomeMessage .= "• Nâng cấp VIP Node: Cứ 10 gói 10 USDT tăng 1 Level Hashrate\n";
    $welcomeMessage .= "• Hoa hồng giới thiệu bạn bè nhận ngay <b>10% USDT</b> về ví tức thì!\n\n";
    $welcomeMessage .= "Bấm vào nút <b>🚀 Mở SUPPER AI App</b> phía dưới để bắt đầu ngay:";

    $keyboard = [
        'inline_keyboard' => [
            [
                [
                    'text' => '🚀 Mở SUPPER AI App',
                    'web_app' => [
                        'url' => $appUrlWithParam
                    ]
                ]
            ],
            [
                [
                    'text' => '👥 Mời bạn bè (+10% USDT)',
                    'url' => "https://t.me/share/url?url=" . urlencode("https://t.me/SUPPERAI5_BOT/app?startapp={$userId}") . "&text=" . urlencode("⚡ Tham gia khai thác SUPPER AI Token cùng tôi trên Telegram! Nhận ngay 100 USDT:")
                ]
            ]
        ]
    ];

    telegramApi('sendMessage', [
        'chat_id' => $chatId,
        'text' => $welcomeMessage,
        'parse_mode' => 'HTML',
        'reply_markup' => $keyboard
    ]);
    exit;
}

// Xử lý các lệnh khác
if ($text === '/help') {
    $helpText = "📖 <b>Hướng dẫn sử dụng SUPPER AI Mini App</b>:\n\n";
    $helpText .= "1. Bấm <b>🚀 Mở SUPPER AI App</b> để truy cập ứng dụng.\n";
    $helpText .= "2. Hệ thống tự động kích hoạt Nodes đào AI Quantum cho tài khoản của bạn.\n";
    $helpText .= "3. Thu hoạch SUPPER mỗi ngày hoặc nâng cấp thêm AI Quantum Rig.\n";
    $helpText .= "4. Hoán đổi SUPPER sang USDT và rút tiền về ví cá nhân (BEP20/TRC20) 24/7.";

    telegramApi('sendMessage', [
        'chat_id' => $chatId,
        'text' => $helpText,
        'parse_mode' => 'HTML'
    ]);
    exit;
}
