<?php
// api/admin.php
require_once __DIR__ . '/../config/db.php';

$pdo = getDb();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'overview';
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

// Guard all admin routes
$admin = requireAdmin($pdo);

// 1. Overview
if ($action === 'overview' && $method === 'GET') {
    $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $activeMiners = (int)$pdo->query("SELECT COUNT(*) FROM user_miners WHERE status = 'active'")->fetchColumn();
    $totalHashrate = (float)$pdo->query("SELECT COALESCE(SUM(hashrate), 0) FROM user_miners WHERE status = 'active'")->fetchColumn();
    $totalUsdt = (float)$pdo->query("SELECT COALESCE(SUM(usdt_balance), 0) FROM users")->fetchColumn();
    $totalCoins = (float)$pdo->query("SELECT COALESCE(SUM(coin_balance), 0) FROM users")->fetchColumn();

    $pendingDeposits = (int)$pdo->query("SELECT COUNT(*) FROM transactions WHERE type = 'deposit' AND status = 'pending'")->fetchColumn();
    $pendingWithdrawals = (int)$pdo->query("SELECT COUNT(*) FROM transactions WHERE type = 'withdraw' AND status = 'pending'")->fetchColumn();

    jsonResponse([
        'total_users' => $totalUsers,
        'active_miners' => $activeMiners,
        'total_hashrate' => $totalHashrate,
        'total_usdt' => round($totalUsdt, 2),
        'total_coins' => round($totalCoins, 4),
        'pending_deposits_count' => $pendingDeposits,
        'pending_withdrawals_count' => $pendingWithdrawals,
        'settings' => getSettings($pdo)
    ]);
}

// 2. All Transactions
if ($action === 'transactions' && $method === 'GET') {
    $txs = $pdo->query("SELECT * FROM transactions ORDER BY created_at DESC")->fetchAll();
    foreach ($txs as &$t) {
        $t['detail'] = json_decode($t['detail'] ?? '{}', true);
    }
    jsonResponse(['transactions' => $txs]);
}

// 3. Approve Transaction
if ($action === 'approve_tx' && $method === 'POST') {
    $id = $input['id'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
    $stmt->execute([$id]);
    $tx = $stmt->fetch();

    if (!$tx) {
        jsonResponse(['error' => 'Không tìm thấy giao dịch'], 404);
    }
    if ($tx['status'] !== 'pending') {
        jsonResponse(['error' => "Giao dịch đã ở trạng thái {$tx['status']}"], 400);
    }

    $pdo->beginTransaction();
    try {
        if ($tx['type'] === 'deposit') {
            // Credit USDT
            $uStmt = $pdo->prepare("UPDATE users SET usdt_balance = usdt_balance + ? WHERE id = ?");
            $uStmt->execute([$tx['amount'], $tx['user_id']]);
        }

        $updTx = $pdo->prepare("UPDATE transactions SET status = 'approved', notes = 'Đã được duyệt bởi Admin' WHERE id = ?");
        $updTx->execute([$id]);

        $pdo->commit();
        jsonResponse(['message' => "Đã duyệt thành công giao dịch #{$id}"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => 'Lỗi duyệt giao dịch: ' . $e->getMessage()], 500);
    }
}

// 4. Reject Transaction
if ($action === 'reject_tx' && $method === 'POST') {
    $id = $input['id'] ?? '';
    $reason = trim($input['reason'] ?? '') ?: 'Bị từ chối bởi Quản trị viên';

    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
    $stmt->execute([$id]);
    $tx = $stmt->fetch();

    if (!$tx) {
        jsonResponse(['error' => 'Không tìm thấy giao dịch'], 404);
    }
    if ($tx['status'] !== 'pending') {
        jsonResponse(['error' => "Giao dịch đã ở trạng thái {$tx['status']}"], 400);
    }

    $pdo->beginTransaction();
    try {
        if ($tx['type'] === 'withdraw') {
            // Refund USDT back to user
            $uStmt = $pdo->prepare("UPDATE users SET usdt_balance = usdt_balance + ? WHERE id = ?");
            $uStmt->execute([$tx['amount'], $tx['user_id']]);
        }

        $updTx = $pdo->prepare("UPDATE transactions SET status = 'rejected', notes = ? WHERE id = ?");
        $updTx->execute([$reason, $id]);

        $pdo->commit();
        jsonResponse(['message' => "Đã từ chối giao dịch #{$id}"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => 'Lỗi từ chối giao dịch: ' . $e->getMessage()], 500);
    }
}

// 5. Users List
if ($action === 'users' && $method === 'GET') {
    $users = $pdo->query("SELECT id, email, name, role, usdt_balance, coin_balance, created_at FROM users ORDER BY created_at DESC")->fetchAll();

    foreach ($users as &$u) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count, COALESCE(SUM(hashrate), 0) as hashrate FROM user_miners WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$u['id']]);
        $stats = $stmt->fetch();
        $u['active_miners_count'] = (int)($stats['count'] ?? 0);
        $u['hashrate'] = (float)($stats['hashrate'] ?? 0);
    }

    jsonResponse(['users' => $users]);
}

// 6. Adjust User Balance
if ($action === 'adjust_balance' && $method === 'POST') {
    $userId = $input['userId'] ?? '';
    $usdtAmount = (float)($input['usdtAmount'] ?? 0);
    $coinAmount = (float)($input['coinAmount'] ?? 0);
    $reason = trim($input['reason'] ?? '') ?: 'Điều chỉnh bởi Admin';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $u = $stmt->fetch();

    if (!$u) {
        jsonResponse(['error' => 'Không tìm thấy người dùng'], 404);
    }

    $newUsdt = max(0, round((float)$u['usdt_balance'] + $usdtAmount, 4));
    $newCoin = max(0, round((float)$u['coin_balance'] + $coinAmount, 6));

    $pdo->beginTransaction();
    try {
        $upd = $pdo->prepare("UPDATE users SET usdt_balance = ?, coin_balance = ? WHERE id = ?");
        $upd->execute([$newUsdt, $newCoin, $userId]);

        $txId = 'tx_adj_' . time();
        $adjStmt = $pdo->prepare("INSERT INTO transactions (id, user_id, type, amount, currency, detail, status, created_at) VALUES (?, ?, 'admin_adjust', ?, ?, ?, 'completed', ?)");
        $adjStmt->execute([
            $txId,
            $userId,
            abs($usdtAmount ?: $coinAmount),
            $usdtAmount ? 'USDT' : 'MNX',
            json_encode(['reason' => $reason, 'usdt_change' => $usdtAmount, 'coin_change' => $coinAmount]),
            date('c')
        ]);

        $pdo->commit();
        jsonResponse(['message' => 'Cập nhật số dư người dùng thành công!']);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => 'Lỗi điều chỉnh số dư: ' . $e->getMessage()], 500);
    }
}

// 7. Update Settings & Coin Rate
if ($action === 'update_settings' && $method === 'POST') {
    $settings = $input['settings'] ?? $input;

    $stmt = $pdo->prepare("INSERT OR REPLACE INTO system_settings (setting_key, setting_val) VALUES (?, ?)");
    foreach ($settings as $k => $v) {
        $stmt->execute([$k, (string)$v]);
    }

    jsonResponse([
        'message' => 'Cập nhật cấu hình hệ thống & tỷ giá Coin thành công!',
        'settings' => getSettings($pdo)
    ]);
}

// 8. Manage Miners
if ($action === 'miners' && $method === 'GET') {
    $miners = $pdo->query("SELECT * FROM miners ORDER BY price_usdt ASC")->fetchAll();
    jsonResponse(['miners' => $miners]);
}

if ($action === 'add_miner' && $method === 'POST') {
    $name = trim($input['name'] ?? '');
    $tier = trim($input['tier'] ?? '') ?: 'Enterprise Rig';
    $hashrate = (float)($input['hashrate'] ?? 0);
    $price = (float)($input['price_usdt'] ?? 0);
    $daily = (float)($input['daily_yield_coins'] ?? 0);
    $power = trim($input['power_consumption'] ?? '') ?: '350W';

    if (empty($name) || $hashrate <= 0 || $price <= 0) {
        jsonResponse(['error' => 'Vui lòng nhập tên, hashrate và giá máy hợp lệ'], 400);
    }

    $id = 'miner_' . time();
    $stmt = $pdo->prepare("INSERT INTO miners (id, name, tier, hashrate, unit, price_usdt, daily_yield_coins, power_consumption, is_active) VALUES (?, ?, ?, ?, 'TH/s', ?, ?, ?, 1)");
    $stmt->execute([$id, $name, $tier, $hashrate, $price, $daily, $power]);

    jsonResponse(['message' => 'Thêm gói máy đào ảo mới thành công!'], 201);
}

jsonResponse(['error' => 'Yêu cầu không hợp lệ'], 400);
