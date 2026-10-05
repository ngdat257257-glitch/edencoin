<?php
// api/miners.php
require_once __DIR__ . '/../config/db.php';

$pdo = getDb();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'catalog';
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

// 1. Get Catalog of Miners
if ($action === 'catalog' && $method === 'GET') {
    $miners = $pdo->query("SELECT * FROM miners WHERE is_active = 1 ORDER BY price_usdt ASC")->fetchAll();
    jsonResponse(['miners' => $miners]);
}

// 2. Get User's Active Miners
if ($action === 'my' && $method === 'GET') {
    $user = requireAuth($pdo);
    $stmt = $pdo->prepare("SELECT * FROM user_miners WHERE user_id = ? AND status = 'active' ORDER BY purchased_at DESC");
    $stmt->execute([$user['id']]);
    $myMiners = $stmt->fetchAll();

    $totalHashrate = 0;
    $totalDailyYield = 0;
    foreach ($myMiners as $m) {
        $totalHashrate += (float)$m['hashrate'];
        $totalDailyYield += (float)$m['daily_yield_coins'];
    }

    $packagesCount = count($myMiners);
    // Khách mua 10 gói 10 USDT thì lên 1 cấp (Level 1 ban đầu)
    $userLevel = max(1, 1 + (int)floor($packagesCount / 10));

    jsonResponse([
        'miners' => $myMiners,
        'totalHashrate' => $totalHashrate,
        'totalDailyYield' => $totalDailyYield,
        'activeCount' => $packagesCount,
        'packagesCount' => $packagesCount,
        'level' => $userLevel
    ]);
}

// 3. Buy Miner with USDT
if ($action === 'buy' && $method === 'POST') {
    $user = requireAuth($pdo);
    $minerId = $input['minerId'] ?? '';
    $quantity = max(1, (int)($input['quantity'] ?? 1));

    if (empty($minerId)) {
        jsonResponse(['error' => 'Vui lòng chọn máy đào cần mua'], 400);
    }

    if ($quantity > 1000) {
        jsonResponse(['error' => 'Số lượng mỗi lần mua tối đa là 1,000 gói'], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM miners WHERE id = ? AND is_active = 1");
    $stmt->execute([$minerId]);
    $miner = $stmt->fetch();

    if (!$miner) {
        jsonResponse(['error' => 'Gói máy đào không tồn tại hoặc đã ngừng cung cấp'], 404);
    }

    $unitPrice = (float)$miner['price_usdt'];
    $totalPrice = round($unitPrice * $quantity, 4);
    $currentUsdt = (float)$user['usdt_balance'];

    if ($currentUsdt < $totalPrice) {
        jsonResponse([
            'error' => "Số dư USDT không đủ. Bạn có " . number_format($currentUsdt, 2) . " USDT, cần " . number_format($totalPrice, 2) . " USDT để mua {$quantity} gói. Vui lòng nạp thêm USDT."
        ], 400);
    }

    // Begin transaction
    $pdo->beginTransaction();
    try {
        $newBalance = round($currentUsdt - $totalPrice, 4);
        $updStmt = $pdo->prepare("UPDATE users SET usdt_balance = ? WHERE id = ?");
        $updStmt->execute([$newBalance, $user['id']]);

        $nowMs = (int)(microtime(true) * 1000);
        $addStmt = $pdo->prepare("INSERT INTO user_miners (id, user_id, miner_id, miner_name, tier, hashrate, unit, daily_yield_coins, purchased_at, last_claim_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");

        for ($i = 0; $i < $quantity; $i++) {
            $umId = 'um_' . time() . '_' . substr(bin2hex(random_bytes(3)), 0, 4) . '_' . ($i + 1);
            $addStmt->execute([
                $umId,
                $user['id'],
                $miner['id'],
                $miner['name'],
                $miner['tier'],
                $miner['hashrate'],
                $miner['unit'] ?? 'TH/s',
                $miner['daily_yield_coins'],
                $nowMs,
                $nowMs
            ]);
        }

        $totalHashrate = (float)$miner['hashrate'] * $quantity;
        $totalDailyCoins = (float)$miner['daily_yield_coins'] * $quantity;

        $txId = 'tx_buy_' . time() . '_' . substr(bin2hex(random_bytes(3)), 0, 4);
        $txStmt = $pdo->prepare("INSERT INTO transactions (id, user_id, type, amount, currency, detail, status, created_at) VALUES (?, ?, 'buy_miner', ?, 'USDT', ?, 'completed', ?)");
        $txStmt->execute([
            $txId,
            $user['id'],
            $totalPrice,
            json_encode([
                'miner_id' => $miner['id'],
                'miner_name' => $miner['name'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'hashrate' => "+{$totalHashrate} " . ($miner['unit'] ?? 'TH/s'),
                'daily_yield' => "+{$totalDailyCoins} " . ($settings['coin_symbol'] ?? 'SUPPER') . "/ngày"
            ]),
            date('c')
        ]);

        // 10% commission for referrer (tuyến trên)
        if (!empty($user['referrer_id'])) {
            $refStmt = $pdo->prepare("SELECT id, uid, usdt_balance FROM users WHERE id = ?");
            $refStmt->execute([$user['referrer_id']]);
            $referrer = $refStmt->fetch();
            if ($referrer) {
                $commissionAmount = round($totalPrice * 0.10, 4);
                if ($commissionAmount > 0) {
                    $newRefBalance = round((float)$referrer['usdt_balance'] + $commissionAmount, 4);
                    $updRef = $pdo->prepare("UPDATE users SET usdt_balance = ? WHERE id = ?");
                    $updRef->execute([$newRefBalance, $referrer['id']]);

                    $commTxId = 'tx_comm_' . time() . '_' . substr(bin2hex(random_bytes(3)), 0, 4);
                    $commTxStmt = $pdo->prepare("INSERT INTO transactions (id, user_id, type, amount, currency, detail, status, created_at) VALUES (?, ?, 'commission', ?, 'USDT', ?, 'approved', ?)");
                    $commTxStmt->execute([
                        $commTxId,
                        $referrer['id'],
                        $commissionAmount,
                        json_encode([
                            'note' => "Hoa hồng tuyến trên 10% từ F1 (UID: {$user['uid']}) mua {$quantity}x {$miner['name']}",
                            'f1_uid' => $user['uid'],
                            'f1_user_id' => $user['id'],
                            'miner_name' => $miner['name'],
                            'quantity' => $quantity,
                            'total_spent' => $totalPrice,
                            'commission_rate' => '10%'
                        ]),
                        date('c')
                    ]);
                }
            }
        }

        $pdo->commit();

        $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM user_miners WHERE user_id = ? AND status = 'active'");
        $cntStmt->execute([$user['id']]);
        $totalActivePackages = (int)$cntStmt->fetchColumn();
        $newLevel = max(1, 1 + (int)floor($totalActivePackages / 10));

        jsonResponse([
            'message' => "Chúc mừng! Bạn đã mua thành công {$quantity}x {$miner['name']} (Tổng: {$totalPrice} USDT). Cấp bậc hiện tại: Level {$newLevel}.",
            'usdt_balance' => $newBalance,
            'quantity' => $quantity,
            'totalHashrate' => $totalHashrate,
            'packages_count' => $totalActivePackages,
            'level' => $newLevel
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => 'Lỗi xử lý giao dịch mua máy đào: ' . $e->getMessage()], 500);
    }
}

jsonResponse(['error' => 'Yêu cầu không hợp lệ'], 400);
