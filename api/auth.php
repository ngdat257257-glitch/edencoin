<?php
// api/auth.php
require_once __DIR__ . '/../config/db.php';

$pdo = getDb();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Support JSON input
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

if ($action === 'register' && $method === 'POST') {
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $name = trim($input['name'] ?? '');

    if (empty($email) || empty($password)) {
        jsonResponse(['error' => 'Email và mật khẩu không được để trống'], 400);
    }
    if (strlen($password) < 6) {
        jsonResponse(['error' => 'Mật khẩu phải có ít nhất 6 ký tự'], 400);
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([strtolower($email)]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'Email này đã được đăng ký trên hệ thống'], 400);
    }

    $refCode = trim($input['ref'] ?? $input['ref_code'] ?? '');
    $referrerId = null;
    if (!empty($refCode)) {
        $findRef = $pdo->prepare("SELECT id FROM users WHERE uid = ? OR id = ? LIMIT 1");
        $findRef->execute([$refCode, $refCode]);
        $referrerUser = $findRef->fetch();
        if ($referrerUser) {
            $referrerId = $referrerUser['id'];
        }
    }

    $userId = 'user_' . time() . '_' . substr(bin2hex(random_bytes(4)), 0, 6);
    $uid = (string)mt_rand(100000, 999999);
    $displayName = $name ?: explode('@', $email)[0];
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $initialUsdt = 100.0; // 100 USDT Welcome bonus

    $stmt = $pdo->prepare("INSERT INTO users (id, uid, email, name, password_hash, role, usdt_balance, coin_balance, created_at, referrer_id) VALUES (?, ?, ?, ?, ?, 'user', ?, 0, ?, ?)");
    $stmt->execute([$userId, $uid, strtolower($email), $displayName, $passwordHash, $initialUsdt, date('c'), $referrerId]);

    // Transaction bonus
    $txId = 'tx_bonus_' . time();
    $txStmt = $pdo->prepare("INSERT INTO transactions (id, user_id, type, amount, currency, detail, status, created_at) VALUES (?, ?, 'deposit', ?, 'USDT', ?, 'approved', ?)");
    $txStmt->execute([$txId, $userId, $initialUsdt, json_encode(['note' => 'Tặng thưởng đăng ký thành viên mới (+100 USDT)']), date('c')]);

    $_SESSION['user_id'] = $userId;

    $user = [
        'id' => $userId,
        'uid' => $uid,
        'email' => strtolower($email),
        'name' => $displayName,
        'role' => 'user',
        'usdt_balance' => $initialUsdt,
        'coin_balance' => 0.0,
        'created_at' => date('c'),
        'referrer_id' => $referrerId
    ];

    jsonResponse([
        'message' => 'Đăng ký tài khoản thành công! Bạn nhận được 100 USDT khởi nghiệp.',
        'token' => $userId,
        'user' => $user
    ], 201);
}

if ($action === 'login' && $method === 'POST') {
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        jsonResponse(['error' => 'Vui lòng nhập email và mật khẩu'], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR name = ? OR uid = ?)");
    $stmt->execute([strtolower($email), $email, $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        jsonResponse(['error' => 'Email hoặc mật khẩu không chính xác'], 400);
    }

    $_SESSION['user_id'] = $user['id'];
    unset($user['password_hash']);

    jsonResponse([
        'message' => 'Đăng nhập thành công',
        'token' => $user['id'],
        'user' => $user
    ]);
}

if ($action === 'telegram_auth' && $method === 'POST') {
    $initDataRaw = $input['initData'] ?? '';
    $tgUser = $input['user'] ?? null;
    $startParam = trim($input['start_param'] ?? '');

    // If initData is provided as a query string, parse it
    if (!empty($initDataRaw) && is_string($initDataRaw)) {
        parse_str($initDataRaw, $parsedData);
        if (isset($parsedData['user'])) {
            $parsedUser = json_decode($parsedData['user'], true);
            if ($parsedUser) {
                $tgUser = $parsedUser;
            }
        }
        if (empty($startParam) && isset($parsedData['start_param'])) {
            $startParam = trim($parsedData['start_param']);
        }
    }

    if (empty($tgUser) || empty($tgUser['id'])) {
        jsonResponse(['error' => 'Dữ liệu Telegram không hợp lệ'], 400);
    }

    $tgId = (string)$tgUser['id'];
    $firstName = trim($tgUser['first_name'] ?? '');
    $lastName = trim($tgUser['last_name'] ?? '');
    $username = trim($tgUser['username'] ?? '');
    $photoUrl = trim($tgUser['photo_url'] ?? '');
    $displayName = trim($firstName . ' ' . $lastName) ?: ($username ?: 'Telegram Miner');

    // Find if user already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE telegram_id = ? OR id = ? LIMIT 1");
    $stmt->execute([$tgId, 'tg_' . $tgId]);
    $user = $stmt->fetch();

    $now = date('c');

    if ($user) {
        // Update profile info if needed
        $upd = $pdo->prepare("UPDATE users SET telegram_username = ?, telegram_photo_url = ?, name = COALESCE(NULLIF(name, ''), ?) WHERE id = ?");
        $upd->execute([$username, $photoUrl, $displayName, $user['id']]);
        $user['name'] = !empty($user['name']) ? $user['name'] : $displayName;
        $user['telegram_username'] = $username;
        $user['telegram_photo_url'] = $photoUrl;
    } else {
        // Register new user from Telegram
        $userId = 'tg_' . $tgId;
        $uid = (strlen($tgId) <= 8) ? $tgId : (string)mt_rand(100000, 999999);
        $email = "tg_{$tgId}@telegram.org";
        $passwordHash = password_hash(bin2hex(random_bytes(10)), PASSWORD_BCRYPT);
        $initialUsdt = 100.0; // 100 USDT Welcome bonus

        // Check referral
        $referrerId = null;
        if (!empty($startParam)) {
            $findRef = $pdo->prepare("SELECT id FROM users WHERE uid = ? OR id = ? LIMIT 1");
            $findRef->execute([$startParam, $startParam]);
            $refUser = $findRef->fetch();
            if ($refUser && $refUser['id'] !== $userId) {
                $referrerId = $refUser['id'];
            }
        }

        $ins = $pdo->prepare("INSERT INTO users (id, uid, email, name, password_hash, role, usdt_balance, coin_balance, created_at, referrer_id, telegram_id, telegram_username, telegram_photo_url) VALUES (?, ?, ?, ?, ?, 'user', ?, 0, ?, ?, ?, ?, ?)");
        $ins->execute([$userId, $uid, $email, $displayName, $passwordHash, $initialUsdt, $now, $referrerId, $tgId, $username, $photoUrl]);

        // Welcome bonus transaction
        $txId = 'tx_bonus_' . time() . '_' . substr(bin2hex(random_bytes(3)), 0, 4);
        $txStmt = $pdo->prepare("INSERT INTO transactions (id, user_id, type, amount, currency, detail, status, created_at) VALUES (?, ?, 'deposit', ?, 'USDT', ?, 'approved', ?)");
        $txStmt->execute([$txId, $userId, $initialUsdt, json_encode(['note' => 'Tặng thưởng chào mừng Telegram Mini App (+100 USDT)']), $now]);

        // Add 1 starting SVIP package (Level 1)
        $nowMs = (int)(microtime(true) * 1000);
        $umId = 'um_tg_' . time() . '_' . substr(bin2hex(random_bytes(3)), 0, 4);
        $addMinerStmt = $pdo->prepare("INSERT INTO user_miners (id, user_id, miner_id, miner_name, tier, hashrate, unit, daily_yield_coins, purchased_at, last_claim_at, status) VALUES (?, ?, 'miner_svip', 'SVIP', 'SVIP', 10, 'TH/s', 6000, ?, ?, 'active')");
        $addMinerStmt->execute([$umId, $userId, $nowMs, $nowMs]);

        $user = [
            'id' => $userId,
            'uid' => $uid,
            'email' => $email,
            'name' => $displayName,
            'role' => 'user',
            'usdt_balance' => $initialUsdt,
            'coin_balance' => 0.0,
            'created_at' => $now,
            'referrer_id' => $referrerId,
            'telegram_id' => $tgId,
            'telegram_username' => $username,
            'telegram_photo_url' => $photoUrl
        ];
    }

    $_SESSION['user_id'] = $user['id'];
    unset($user['password_hash']);

    // Packages & Level
    $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM user_miners WHERE user_id = ? AND status = 'active'");
    $cntStmt->execute([$user['id']]);
    $packagesCount = (int)$cntStmt->fetchColumn();
    $user['packages_count'] = $packagesCount;
    $user['level'] = max(1, 1 + (int)floor($packagesCount / 10));

    jsonResponse([
        'message' => 'Đăng nhập Telegram thành công!',
        'token' => $user['id'],
        'user' => $user
    ]);
}

if ($action === 'me' && $method === 'GET') {
    $user = getCurrentUser($pdo);
    if (!$user) {
        jsonResponse(['error' => 'Chưa đăng nhập', 'user' => null], 401);
    }
    $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM user_miners WHERE user_id = ? AND status = 'active'");
    $cntStmt->execute([$user['id']]);
    $packagesCount = (int)$cntStmt->fetchColumn();
    $user['packages_count'] = $packagesCount;
    $user['level'] = max(1, 1 + (int)floor($packagesCount / 10));

    $settings = getSettings($pdo);
    jsonResponse([
        'user' => $user,
        'settings' => $settings
    ]);
}

if ($action === 'referral_stats' && $method === 'GET') {
    $user = getCurrentUser($pdo);
    if (!$user) {
        jsonResponse(['error' => 'Chưa đăng nhập'], 401);
    }

    // 1. Count F1 members
    $f1CountStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE referrer_id = ?");
    $f1CountStmt->execute([$user['id']]);
    $f1Count = (int)$f1CountStmt->fetchColumn();

    // 2. Total commission earned from F1 purchases (10%)
    $commStmt = $pdo->prepare("SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'commission'");
    $commStmt->execute([$user['id']]);
    $totalComm = (float)($commStmt->fetchColumn() ?: 0.0);

    // 3. F1 members list
    $f1ListStmt = $pdo->prepare("
        SELECT u.id, u.uid, u.name, u.email, u.created_at,
               (SELECT COUNT(*) FROM user_miners um WHERE um.user_id = u.id) as miners_count,
               (SELECT IFNULL(SUM(amount), 0) FROM transactions t WHERE t.user_id = u.id AND t.type = 'buy_miner') as total_spent
        FROM users u 
        WHERE u.referrer_id = ? 
        ORDER BY u.created_at DESC 
        LIMIT 20
    ");
    $f1ListStmt->execute([$user['id']]);
    $f1Users = $f1ListStmt->fetchAll();

    // 4. Commission history
    $commHistoryStmt = $pdo->prepare("
        SELECT id, amount, currency, detail, status, created_at 
        FROM transactions 
        WHERE user_id = ? AND type = 'commission' 
        ORDER BY created_at DESC 
        LIMIT 20
    ");
    $commHistoryStmt->execute([$user['id']]);
    $commHistory = $commHistoryStmt->fetchAll();

    jsonResponse([
        'ref_code' => $user['uid'],
        'commission_rate' => 10,
        'f1_count' => $f1Count,
        'total_commission' => round($totalComm, 4),
        'f1_users' => $f1Users,
        'commissions' => $commHistory
    ]);
}

if ($action === 'logout') {
    session_destroy();
    jsonResponse(['message' => 'Đăng xuất thành công']);
}

jsonResponse(['error' => 'Hành động không hợp lệ'], 400);
