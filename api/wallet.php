<?php
// api/wallet.php
require_once __DIR__ . '/../config/db.php';

$pdo = getDb();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'summary';
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

// 1. Wallet Summary
if ($action === 'summary' && $method === 'GET') {
    $user = requireAuth($pdo);
    $settings = getSettings($pdo);

    jsonResponse([
        'usdt_balance' => round((float)$user['usdt_balance'], 4),
        'coin_balance' => round((float)$user['coin_balance'], 6),
        'coin_price_usdt' => (float)($settings['coin_price_usdt'] ?? 0.001),
        'coin_symbol' => $settings['coin_symbol'] ?? 'SUPPER',
        'coin_name' => $settings['coin_name'] ?? 'SUPPER AI Token',
        'deposit_address' => $settings['usdt_deposit_address'] ?? '0x596b41afd2b5f6336a3171898752c2265ae86878',
        'network' => $settings['network'] ?? 'USDT (BEP20)',
        'min_deposit' => (float)($settings['min_deposit'] ?? 10),
        'min_withdraw' => (float)($settings['min_withdraw'] ?? 15),
        'withdraw_fee_percent' => (float)($settings['withdraw_fee_percent'] ?? 2.5)
    ]);
}

// 2a. Nạp qua MetaMask: xác minh giao dịch on-chain (BSC) rồi tự động cộng đúng số USDT đã chuyển
if ($action === 'deposit_onchain' && $method === 'POST') {
    $user = requireAuth($pdo);
    $settings = getSettings($pdo);

    $txHash = strtolower(trim($input['txHash'] ?? ''));
    if (!preg_match('/^0x[0-9a-f]{64}$/', $txHash)) {
        jsonResponse(['error' => 'Mã giao dịch (TxHash) không hợp lệ'], 400);
    }

    $depositAddr = strtolower($settings['usdt_deposit_address'] ?? '0xcb3Fc21Af451e1D51Cd58078F025Dad92595f5BA');
    $usdtContract = '0x55d398326f99059ff775485246999027b3197955'; // USDT BEP20
    $transferTopic = '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef';

    // Chống nạp trùng cùng một TxHash
    $dup = $pdo->prepare("SELECT id FROM transactions WHERE type = 'deposit' AND detail LIKE ?");
    $dup->execute(['%' . $txHash . '%']);
    if ($dup->fetch()) {
        jsonResponse(['error' => 'Giao dịch này đã được nạp trước đó'], 400);
    }

    $rpcs = ['https://bsc-dataseed.binance.org', 'https://bsc-dataseed1.defibit.io', 'https://bsc-rpc.publicnode.com'];
    $receipt = null;
    $payload = json_encode(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'eth_getTransactionReceipt', 'params' => [$txHash]]);
    foreach ($rpcs as $rpc) {
        $ch = curl_init($rpc);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 12,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        $res = $raw ? json_decode($raw, true) : null;
        if (is_array($res) && array_key_exists('result', $res)) {
            $receipt = $res['result'];
            break;
        }
    }

    if (!$receipt) {
        jsonResponse(['error' => 'Giao dịch chưa được xác nhận trên blockchain, vui lòng đợi ít phút rồi thử lại'], 400);
    }
    if (($receipt['status'] ?? '') !== '0x1') {
        jsonResponse(['error' => 'Giao dịch on-chain thất bại'], 400);
    }

    $amount = 0.0;
    foreach (($receipt['logs'] ?? []) as $log) {
        if (strtolower($log['address'] ?? '') !== $usdtContract) continue;
        $topics = $log['topics'] ?? [];
        if (count($topics) < 3 || strtolower($topics[0]) !== $transferTopic) continue;
        $to = '0x' . substr(strtolower($topics[2]), -40);
        if ($to !== $depositAddr) continue;
        $hex = ltrim(substr($log['data'] ?? '0x0', 2), '0');
        $amount += $hex === '' ? 0.0 : (float)hexdec($hex) / 1e18;
    }
    $amount = round($amount, 4);

    if ($amount <= 0) {
        jsonResponse(['error' => 'Không tìm thấy giao dịch USDT (BEP20) chuyển vào ví nạp của hệ thống'], 400);
    }

    $txId = 'dep_' . time() . '_' . substr(bin2hex(random_bytes(3)), 0, 4);
    $pdo->beginTransaction();
    try {
        $newBalance = round((float)$user['usdt_balance'] + $amount, 4);
        $pdo->prepare("UPDATE users SET usdt_balance = ? WHERE id = ?")->execute([$newBalance, $user['id']]);
        $pdo->prepare("INSERT INTO transactions (id, user_id, type, amount, currency, detail, status, notes, created_at) VALUES (?, ?, 'deposit', ?, 'USDT', ?, 'approved', 'Nạp MetaMask - xác minh on-chain', ?)")
            ->execute([
                $txId,
                $user['id'],
                $amount,
                json_encode(['network' => 'BEP20', 'tx_hash' => $txHash, 'deposit_address' => $depositAddr, 'method' => 'metamask']),
                date('c')
            ]);
        $pdo->commit();
        jsonResponse([
            'message' => "Nạp thành công +{$amount} USDT qua MetaMask!",
            'amount' => $amount,
            'usdt_balance' => $newBalance
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => 'Lỗi xử lý nạp tiền: ' . $e->getMessage()], 500);
    }
}

// 2. Submit Deposit (Tự động cộng tiền ngay lập tức)
if ($action === 'deposit' && $method === 'POST') {
    $user = requireAuth($pdo);
    $settings = getSettings($pdo);

    $amount = (float)($input['amount'] ?? 0);
    $minDeposit = (float)($settings['min_deposit'] ?? 10);

    if ($amount < $minDeposit) {
        jsonResponse(['error' => "Số tiền nạp tối thiểu là {$minDeposit} USDT"], 400);
    }

    $txHash = trim($input['txHash'] ?? '') ?: 'tx_' . substr(bin2hex(random_bytes(6)), 0, 12);
    $txId = 'dep_' . time() . '_' . substr(bin2hex(random_bytes(3)), 0, 4);

    $pdo->beginTransaction();
    try {
        // Tự động cộng tiền vào số dư USDT của khách ngay lập tức
        $newBalance = round((float)$user['usdt_balance'] + $amount, 4);
        $updUser = $pdo->prepare("UPDATE users SET usdt_balance = ? WHERE id = ?");
        $updUser->execute([$newBalance, $user['id']]);

        // Ghi nhận giao dịch đã duyệt thành công
        $stmt = $pdo->prepare("INSERT INTO transactions (id, user_id, type, amount, currency, detail, status, notes, created_at) VALUES (?, ?, 'deposit', ?, 'USDT', ?, 'approved', 'Tự động duyệt và cộng tiền', ?)");
        $stmt->execute([
            $txId,
            $user['id'],
            $amount,
            json_encode([
                'network' => $settings['network'] ?? 'USDT (BEP20)',
                'tx_hash' => $txHash,
                'deposit_address' => $settings['usdt_deposit_address'] ?? ''
            ]),
            date('c')
        ]);

        $pdo->commit();

        jsonResponse([
            'message' => "Nạp thành công +{$amount} USDT! Số dư của bạn đã được cộng tự động.",
            'usdt_balance' => $newBalance,
            'transaction' => [
                'id' => $txId,
                'amount' => $amount,
                'status' => 'approved'
            ]
        ], 200);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => 'Lỗi xử lý nạp tiền: ' . $e->getMessage()], 500);
    }
}

// 3. Submit Withdrawal
if ($action === 'withdraw' && $method === 'POST') {
    $user = requireAuth($pdo);
    $settings = getSettings($pdo);

    $amount = (float)($input['amount'] ?? 0);
    $address = trim($input['address'] ?? '');
    $minWithdraw = (float)($settings['min_withdraw'] ?? 15);
    $feePercent = (float)($settings['withdraw_fee_percent'] ?? 2.5);

    if ($amount < $minWithdraw) {
        jsonResponse(['error' => "Số tiền rút tối thiểu là {$minWithdraw} USDT"], 400);
    }

    if (empty($address) || strlen($address) < 10) {
        jsonResponse(['error' => 'Địa chỉ ví nhận USDT không hợp lệ'], 400);
    }

    $currentUsdt = (float)$user['usdt_balance'];
    if ($currentUsdt < $amount) {
        jsonResponse(['error' => "Số dư không đủ. Bạn có " . number_format($currentUsdt, 2) . " USDT"], 400);
    }

    $fee = round($amount * ($feePercent / 100), 4);
    $netAmount = max(0, round($amount - $fee, 4));

    $pdo->beginTransaction();
    try {
        $newBalance = round($currentUsdt - $amount, 4);
        $updUser = $pdo->prepare("UPDATE users SET usdt_balance = ? WHERE id = ?");
        $updUser->execute([$newBalance, $user['id']]);

        $txId = 'wd_' . time() . '_' . substr(bin2hex(random_bytes(3)), 0, 4);
        $stmt = $pdo->prepare("INSERT INTO transactions (id, user_id, type, amount, currency, detail, status, created_at) VALUES (?, ?, 'withdraw', ?, 'USDT', ?, 'pending', ?)");
        $stmt->execute([
            $txId,
            $user['id'],
            $amount,
            json_encode([
                'recipient_address' => $address,
                'network' => $settings['network'] ?? 'USDT (TRC20)',
                'fee_percent' => $feePercent,
                'fee_amount' => $fee,
                'net_amount' => $netAmount
            ]),
            date('c')
        ]);

        $pdo->commit();

        jsonResponse([
            'message' => "Lệnh rút {$amount} USDT (thực nhận: {$netAmount} USDT) đã được gửi và đang chờ xét duyệt!",
            'usdt_balance' => $newBalance
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => 'Lỗi xử lý rút tiền: ' . $e->getMessage()], 500);
    }
}

// 4. Swap Coin -> USDT
if ($action === 'swap' && $method === 'POST') {
    $user = requireAuth($pdo);
    $settings = getSettings($pdo);

    $coinAmount = (float)($input['coinAmount'] ?? 0);
    $currentCoin = (float)$user['coin_balance'];
    $currentUsdt = (float)$user['usdt_balance'];

    if ($coinAmount <= 0) {
        jsonResponse(['error' => 'Vui lòng nhập số lượng Coin hợp lệ cần đổi'], 400);
    }

    if ($coinAmount > $currentCoin) {
        jsonResponse(['error' => "Số dư Coin không đủ. Bạn đang có " . number_format($currentCoin, 4) . " " . ($settings['coin_symbol'] ?? 'SUPPER')], 400);
    }

    $rate = (float)($settings['coin_price_usdt'] ?? 0.001);
    $usdtReceived = round($coinAmount * $rate, 4);

    $newCoinBalance = round($currentCoin - $coinAmount, 6);
    $newUsdtBalance = round($currentUsdt + $usdtReceived, 4);

    $pdo->beginTransaction();
    try {
        $updUser = $pdo->prepare("UPDATE users SET coin_balance = ?, usdt_balance = ? WHERE id = ?");
        $updUser->execute([$newCoinBalance, $newUsdtBalance, $user['id']]);

        $txId = 'swap_' . time() . '_' . substr(bin2hex(random_bytes(3)), 0, 4);
        $stmt = $pdo->prepare("INSERT INTO transactions (id, user_id, type, amount, currency, detail, status, created_at) VALUES (?, ?, 'swap', ?, ?, ?, 'completed', ?)");
        $stmt->execute([
            $txId,
            $user['id'],
            $coinAmount,
            $settings['coin_symbol'] ?? 'SUPPER',
            json_encode([
                'rate_usdt' => $rate,
                'usdt_received' => $usdtReceived,
                'coin_swapped' => $coinAmount
            ]),
            date('c')
        ]);

        $pdo->commit();

        jsonResponse([
            'message' => "Đổi thành công {$coinAmount} " . ($settings['coin_symbol'] ?? 'SUPPER') . " lấy +{$usdtReceived} USDT!",
            'coin_balance' => $newCoinBalance,
            'usdt_balance' => $newUsdtBalance,
            'usdt_received' => $usdtReceived
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => 'Lỗi quy đổi coin: ' . $e->getMessage()], 500);
    }
}

// 5. Get User Transactions
if ($action === 'transactions' && $method === 'GET') {
    $user = requireAuth($pdo);
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $txs = $stmt->fetchAll();

    foreach ($txs as &$t) {
        $t['detail'] = json_decode($t['detail'] ?? '{}', true);
    }

    jsonResponse([
        'transactions' => $txs,
        'usdt_balance' => round((float)$user['usdt_balance'], 4)
    ]);
}

jsonResponse(['error' => 'Yêu cầu không hợp lệ'], 400);
