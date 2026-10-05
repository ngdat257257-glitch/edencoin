<?php
// config/db.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getDb(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dbDir = __DIR__ . '/../data';
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0777, true);
    }

    $dbPath = $dbDir . '/database.sqlite';
    $isNewDb = !file_exists($dbPath);

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    if ($isNewDb) {
        initializeDatabase($pdo);
    } else {
        migrateDatabase($pdo);
    }

    return $pdo;
}

function migrateDatabase(PDO $pdo): void {
    try {
        $cols = array_column($pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC), 'name');
        if (!in_array('telegram_id', $cols)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN telegram_id TEXT");
        }
        if (!in_array('telegram_username', $cols)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN telegram_username TEXT");
        }
        if (!in_array('telegram_photo_url', $cols)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN telegram_photo_url TEXT");
        }
    } catch (Exception $e) {
        // Ignored
    }
}

function initializeDatabase(PDO $pdo): void {
    // 1. Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id TEXT PRIMARY KEY,
        uid TEXT UNIQUE,
        email TEXT UNIQUE NOT NULL,
        name TEXT,
        password_hash TEXT NOT NULL,
        role TEXT DEFAULT 'user',
        usdt_balance REAL DEFAULT 0,
        coin_balance REAL DEFAULT 0,
        created_at TEXT,
        referrer_id TEXT,
        telegram_id TEXT,
        telegram_username TEXT,
        telegram_photo_url TEXT
    )");

    // Safe migration if table already existed without telegram columns
    try {
        $cols = array_column($pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC), 'name');
        if (!in_array('telegram_id', $cols)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN telegram_id TEXT");
        }
        if (!in_array('telegram_username', $cols)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN telegram_username TEXT");
        }
        if (!in_array('telegram_photo_url', $cols)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN telegram_photo_url TEXT");
        }
    } catch (Exception $e) {
        // Ignored
    }

    // 2. Miners catalog
    $pdo->exec("CREATE TABLE IF NOT EXISTS miners (
        id TEXT PRIMARY KEY,
        name TEXT NOT NULL,
        tier TEXT,
        hashrate REAL NOT NULL,
        unit TEXT DEFAULT 'TH/s',
        price_usdt REAL NOT NULL,
        daily_yield_coins REAL NOT NULL,
        power_consumption TEXT,
        is_active INTEGER DEFAULT 1
    )");

    // 3. User owned miners
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_miners (
        id TEXT PRIMARY KEY,
        user_id TEXT NOT NULL,
        miner_id TEXT NOT NULL,
        miner_name TEXT NOT NULL,
        tier TEXT,
        hashrate REAL NOT NULL,
        unit TEXT DEFAULT 'TH/s',
        daily_yield_coins REAL NOT NULL,
        purchased_at INTEGER NOT NULL,
        last_claim_at INTEGER NOT NULL,
        status TEXT DEFAULT 'active'
    )");

    // 4. Transactions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        id TEXT PRIMARY KEY,
        user_id TEXT NOT NULL,
        type TEXT NOT NULL,
        amount REAL NOT NULL,
        currency TEXT NOT NULL,
        detail TEXT,
        status TEXT DEFAULT 'pending',
        notes TEXT,
        created_at TEXT
    )");

    // 5. System Settings
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        setting_key TEXT PRIMARY KEY,
        setting_val TEXT
    )");

    seedInitialData($pdo);
}

function seedInitialData(PDO $pdo): void {
    $now = date('c');

    // Default Settings
    $settings = [
        'coin_name' => 'SUPPER AI Token',
        'coin_symbol' => 'SUPPER',
        'coin_price_usdt' => '0.001',
        'usdt_deposit_address' => '0xcb3Fc21Af451e1D51Cd58078F025Dad92595f5BA',
        'network' => 'USDT (BEP20)',
        'min_deposit' => '10',
        'min_withdraw' => '15',
        'withdraw_fee_percent' => '2.5',
        'telegram_bot_username' => 'SUPPERAI5_BOT',
        'telegram_bot_token' => '8764330129:AAFGAUF01c2bvCXQu7cftO_BELfNg78G0So',
        'telegram_webapp_url' => 'https://edencoin.onrender.com'
    ];

    $stmt = $pdo->prepare("INSERT OR REPLACE INTO system_settings (setting_key, setting_val) VALUES (?, ?)");
    foreach ($settings as $k => $v) {
        $stmt->execute([$k, $v]);
    }

    // Default Users
    $adminHash = password_hash('dat112233', PASSWORD_BCRYPT);
    $userHash = password_hash('user123', PASSWORD_BCRYPT);

    $userStmt = $pdo->prepare("INSERT OR IGNORE INTO users (id, uid, email, name, password_hash, role, usdt_balance, coin_balance, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Admin
    $userStmt->execute(['user_admin', '10001', 'ngdat257257@gmail.com', 'admin', $adminHash, 'admin', 10000.0, 5000.0, $now]);
    // Demo User
    $userStmt->execute(['user_demo', '120850', 'user@mining.io', 'VIP Miner Demo', $userHash, 'user', 500.0, 120.0, $now]);

    // Miners Catalog (30 USDT, 100 USDT)
    $miners = [
        ['miner_30',   'SUPPER AI Node V1 (30 USDT)',   'Gói Khởi Động',      30,  'TH/s',    30,     1764.705882,   '90W',  1],
        ['miner_100',  'SUPPER AI Quantum Rig V2 (100 USDT)',   'Gói Cơ Bản',        100,  'TH/s',   100,    5882.352941,  '300W',  1]
    ];

    $minerStmt = $pdo->prepare("INSERT OR IGNORE INTO miners (id, name, tier, hashrate, unit, price_usdt, daily_yield_coins, power_consumption, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($miners as $m) {
        $minerStmt->execute($m);
    }

    // Initial Miner for user_demo
    $fourHoursAgo = (time() - 4 * 3600) * 1000;
    $umStmt = $pdo->prepare("INSERT OR IGNORE INTO user_miners (id, user_id, miner_id, miner_name, tier, hashrate, unit, daily_yield_coins, purchased_at, last_claim_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $umStmt->execute(['um_demo_1', 'user_demo', 'miner_100', 'SUPPER AI Quantum Rig V2 (100 USDT)', 'Gói Cơ Bản', 100, 'TH/s', 5882.352941, $fourHoursAgo, $fourHoursAgo, 'active']);

    // Initial Transactions
    $txStmt = $pdo->prepare("INSERT OR IGNORE INTO transactions (id, user_id, type, amount, currency, detail, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $txStmt->execute([
        'tx_init_dep', 
        'user_demo', 
        'deposit', 
        550.0, 
        'USDT', 
        json_encode(['network' => 'USDT (TRC20)', 'tx_hash' => '0x8f3c7b2e9d4a1c5b8e7f6a3d2c1b0e9f8a7b6c5d']), 
        'approved', 
        date('c', time() - 4.5 * 3600)
    ]);
    $txStmt->execute([
        'tx_init_buy', 
        'user_demo', 
        'buy_miner', 
        50.0, 
        'USDT', 
        json_encode(['miner_name' => 'Antminer S19k Pro', 'hashrate' => '120 TH/s']), 
        'completed', 
        date('c', time() - 4 * 3600)
    ]);
}

function getSettings(PDO $pdo): array {
    $rows = $pdo->query("SELECT setting_key, setting_val FROM system_settings")->fetchAll();
    $settings = [];
    foreach ($rows as $row) {
        $key = $row['setting_key'];
        $val = $row['setting_val'];
        if (in_array($key, ['coin_price_usdt', 'min_deposit', 'min_withdraw', 'withdraw_fee_percent'])) {
            $settings[$key] = (float)$val;
        } else {
            $settings[$key] = $val;
        }
    }
    return $settings;
}

function jsonResponse(mixed $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function getBearerToken(): ?string {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (empty($auth) && function_exists('getallheaders')) {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }

    if (!empty($auth) && preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
        return $matches[1];
    }

    // Also check GET/POST parameter or Cookie or session
    if (!empty($_GET['token'])) {
        return $_GET['token'];
    }
    if (!empty($_COOKIE['minex_token'])) {
        return $_COOKIE['minex_token'];
    }
    if (!empty($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    return null;
}

function getCurrentUser(PDO $pdo): ?array {
    $token = getBearerToken();
    if (!$token) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT id, uid, email, name, role, usdt_balance, coin_balance, created_at, telegram_id, telegram_username, telegram_photo_url, referrer_id FROM users WHERE id = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function requireAuth(PDO $pdo): array {
    $user = getCurrentUser($pdo);
    if (!$user) {
        jsonResponse(['error' => 'Vui lòng đăng nhập để tiếp tục'], 401);
    }
    return $user;
}

function requireAdmin(PDO $pdo): array {
    $user = requireAuth($pdo);
    if ($user['role'] !== 'admin') {
        jsonResponse(['error' => 'Quyền truy cập bị từ chối: Chỉ dành cho Quản trị viên'], 403);
    }
    return $user;
}
