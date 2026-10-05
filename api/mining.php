<?php
// api/mining.php
require_once __DIR__ . '/../config/db.php';

$pdo = getDb();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'status';

// 1. Get Mining Live Status
if ($action === 'status' && $method === 'GET') {
    $user = requireAuth($pdo);
    $settings = getSettings($pdo);

    $stmt = $pdo->prepare("SELECT * FROM user_miners WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$user['id']]);
    $userMiners = $stmt->fetchAll();

    $nowMs = (int)(microtime(true) * 1000);

    // Nếu người dùng chưa có máy đào nào, tự động cấp 1 máy SVIP (6000 SUPPER/ngày)
    if (empty($userMiners)) {
        $umId = 'um_auto_' . time() . '_' . substr(bin2hex(random_bytes(3)), 0, 4);
        $twoHoursAgoMs = $nowMs - (2 * 3600 * 1000); // Đã đào sẵn 2 giờ (~500 SUPPER)
        $addMinerStmt = $pdo->prepare("INSERT INTO user_miners (id, user_id, miner_id, miner_name, tier, hashrate, unit, daily_yield_coins, purchased_at, last_claim_at, status) VALUES (?, ?, 'miner_svip', 'SVIP', 'SVIP', 10, 'TH/s', 6000, ?, ?, 'active')");
        $addMinerStmt->execute([$umId, $user['id'], $twoHoursAgoMs, $twoHoursAgoMs]);

        $stmt->execute([$user['id']]);
        $userMiners = $stmt->fetchAll();
    }

    $totalUnclaimed = 0.0;
    $totalHashrate = 0.0;
    $totalDailyYield = 0.0;

    $claimIntervalMs = 24 * 3600 * 1000; // 24 hours
    $minLastClaim = null;
    $activeMiners = [];

    foreach ($userMiners as $um) {
        $lastClaim = (int)($um['last_claim_at'] ?: $um['purchased_at']);
        if ($minLastClaim === null || $lastClaim < $minLastClaim) {
            $minLastClaim = $lastClaim;
        }
        $elapsedMs = min(max(0, $nowMs - $lastClaim), 24 * 3600 * 1000); // Dừng đào sau 24h, phải Claim mới chạy tiếp
        $dailyYield = (float)$um['daily_yield_coins'];
        $yieldPerMs = $dailyYield / (24 * 3600 * 1000);
        $accumulated = $elapsedMs * $yieldPerMs;

        $totalUnclaimed += $accumulated;
        $totalHashrate += (float)$um['hashrate'];
        $totalDailyYield += $dailyYield;

        $activeMiners[] = [
            'id' => $um['id'],
            'miner_id' => $um['miner_id'],
            'name' => $um['miner_name'],
            'tier' => $um['tier'],
            'hashrate' => (float)$um['hashrate'],
            'unit' => $um['unit'] ?? 'TH/s',
            'daily_yield' => $dailyYield,
            'accumulated' => round($accumulated, 6),
            'last_claim_at' => $lastClaim,
            'purchased_at' => (int)$um['purchased_at']
        ];
    }

    $canClaim = false;
    $nextClaimInMs = 0;
    if ($minLastClaim !== null && count($userMiners) > 0) {
        $elapsedSinceClaim = max(0, $nowMs - $minLastClaim);
        if ($elapsedSinceClaim >= $claimIntervalMs) {
            $canClaim = true;
            $nextClaimInMs = 0;
        } else {
            $canClaim = false;
            $nextClaimInMs = $claimIntervalMs - $elapsedSinceClaim;
        }
    }

    jsonResponse([
        'server_time' => $nowMs,
        'coin_symbol' => $settings['coin_symbol'] ?? 'SUPPER',
        'coin_name' => $settings['coin_name'] ?? 'SUPPER AI Token',
        'coin_price_usdt' => (float)($settings['coin_price_usdt'] ?? 0.001),
        'coin_balance' => (float)$user['coin_balance'],
        'usdt_balance' => (float)$user['usdt_balance'],
        'total_hashrate' => $totalHashrate,
        'total_daily_yield' => $totalDailyYield,
        'unclaimed_reward' => round($totalUnclaimed, 6),
        'active_miners_count' => count($activeMiners),
        'active_miners' => $activeMiners,
        'can_claim' => $canClaim,
        'next_claim_in_ms' => $nextClaimInMs,
        'claim_interval_ms' => $claimIntervalMs
    ]);
}

// 2. Claim Accumulated Rewards (24h / 1 lần)
if ($action === 'claim' && $method === 'POST') {
    $user = requireAuth($pdo);
    $settings = getSettings($pdo);

    $stmt = $pdo->prepare("SELECT * FROM user_miners WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$user['id']]);
    $userMiners = $stmt->fetchAll();

    if (empty($userMiners)) {
        jsonResponse(['error' => 'Bạn chưa sở hữu máy đào nào. Hãy mua máy đào để bắt đầu nhận thưởng!'], 400);
    }

    $nowMs = (int)(microtime(true) * 1000);
    $claimIntervalMs = 24 * 3600 * 1000; // 24 hours

    $minLastClaim = null;
    $totalClaim = 0.0;

    foreach ($userMiners as $um) {
        $lastClaim = (int)($um['last_claim_at'] ?: $um['purchased_at']);
        if ($minLastClaim === null || $lastClaim < $minLastClaim) {
            $minLastClaim = $lastClaim;
        }
        $elapsedMs = min(max(0, $nowMs - $lastClaim), 24 * 3600 * 1000); // Dừng đào sau 24h, phải Claim mới chạy tiếp
        $dailyYield = (float)$um['daily_yield_coins'];
        $yieldPerMs = $dailyYield / (24 * 3600 * 1000);
        $reward = $elapsedMs * $yieldPerMs;
        $totalClaim += $reward;
    }

    $elapsedSinceClaim = max(0, $nowMs - (int)$minLastClaim);
    if ($elapsedSinceClaim < $claimIntervalMs) {
        $remainingMs = $claimIntervalMs - $elapsedSinceClaim;
        $hours = floor($remainingMs / (3600 * 1000));
        $minutes = floor(($remainingMs % (3600 * 1000)) / (60 * 1000));
        $seconds = floor(($remainingMs % (60 * 1000)) / 1000);
        jsonResponse([
            'error' => sprintf("Hệ thống chỉ cho phép Claim 1 lần mỗi 24 giờ. Vui lòng quay lại sau %02d giờ %02d phút %02d giây.", $hours, $minutes, $seconds),
            'can_claim' => false,
            'next_claim_in_ms' => $remainingMs
        ], 400);
    }

    if ($totalClaim < 0.000001) {
        jsonResponse(['error' => 'Số lượng Coin tích lũy quá nhỏ để nhận. Vui lòng đợi máy đào sản xuất thêm.'], 400);
    }

    $formattedClaim = round($totalClaim, 6);
    $newCoinBalance = round((float)$user['coin_balance'] + $formattedClaim, 6);

    $pdo->beginTransaction();
    try {
        // Update user coin balance
        $updUser = $pdo->prepare("UPDATE users SET coin_balance = ? WHERE id = ?");
        $updUser->execute([$newCoinBalance, $user['id']]);

        // Reset last_claim_at
        $updMiners = $pdo->prepare("UPDATE user_miners SET last_claim_at = ? WHERE user_id = ? AND status = 'active'");
        $updMiners->execute([$nowMs, $user['id']]);

        // Log transaction
        $txId = 'tx_claim_' . time() . '_' . substr(bin2hex(random_bytes(3)), 0, 4);
        $txStmt = $pdo->prepare("INSERT INTO transactions (id, user_id, type, amount, currency, detail, status, created_at) VALUES (?, ?, 'claim', ?, ?, ?, 'completed', ?)");
        $txStmt->execute([
            $txId,
            $user['id'],
            $formattedClaim,
            $settings['coin_symbol'] ?? 'SUPPER',
            json_encode([
                'note' => "Nhận sản lượng từ " . count($userMiners) . " máy đào",
                'miners_count' => count($userMiners)
            ]),
            date('c')
        ]);

        $pdo->commit();

        jsonResponse([
            'message' => "Nhận thành công +{$formattedClaim} " . ($settings['coin_symbol'] ?? 'SUPPER') . " vào ví!",
            'claimed_amount' => $formattedClaim,
            'coin_balance' => $newCoinBalance,
            'server_time' => $nowMs
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => 'Lỗi khi nhận thưởng đào coin: ' . $e->getMessage()], 500);
    }
}

jsonResponse(['error' => 'Yêu cầu không hợp lệ'], 400);
