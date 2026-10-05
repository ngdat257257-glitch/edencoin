<?php
// admin.php - Trang Quản Trị Hệ Thống SUPPER AI (Standalone Admin Portal)
require_once __DIR__ . '/config/db.php';

$pdo = getDb();
$currentUser = getCurrentUser($pdo);
$isAdmin = $currentUser && ($currentUser['role'] === 'admin');

// Xử lý đăng nhập Admin trực tiếp từ form
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR name = ? OR uid = ?) LIMIT 1");
    $stmt->execute([strtolower($email), $email, $email]);
    $u = $stmt->fetch();

    if ($u && password_verify($password, $u['password_hash'])) {
        if ($u['role'] === 'admin') {
            $_SESSION['user_id'] = $u['id'];
            header('Location: admin.php');
            exit;
        } else {
            $loginError = 'Tài khoản này không có quyền Quản trị viên (Admin).';
        }
    } else {
        $loginError = 'Email hoặc mật khẩu không chính xác.';
    }
}

// Xử lý Đăng xuất
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['user_id']);
    header('Location: admin.php');
    exit;
}

// Lấy dữ liệu nếu đã đăng nhập Admin
$overview = [];
$pendingWithdrawals = [];
$pendingDeposits = [];
$recentTxs = [];
$settings = getSettings($pdo);

if ($isAdmin) {
    $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $activeMiners = (int)$pdo->query("SELECT COUNT(*) FROM user_miners WHERE status = 'active'")->fetchColumn();
    $totalUsdt = (float)$pdo->query("SELECT COALESCE(SUM(usdt_balance), 0) FROM users")->fetchColumn();
    $totalCoins = (float)$pdo->query("SELECT COALESCE(SUM(coin_balance), 0) FROM users")->fetchColumn();

    $pendingWithdrawals = $pdo->query("SELECT t.*, u.name as user_name, u.uid as user_uid FROM transactions t LEFT JOIN users u ON t.user_id = u.id WHERE t.type = 'withdraw' AND t.status = 'pending' ORDER BY t.created_at DESC")->fetchAll();
    foreach ($pendingWithdrawals as &$pw) {
        $pw['detail'] = json_decode($pw['detail'] ?? '{}', true);
    }

    $pendingDeposits = $pdo->query("SELECT t.*, u.name as user_name, u.uid as user_uid FROM transactions t LEFT JOIN users u ON t.user_id = u.id WHERE t.type = 'deposit' AND t.status = 'pending' ORDER BY t.created_at DESC")->fetchAll();
    foreach ($pendingDeposits as &$pd) {
        $pd['detail'] = json_decode($pd['detail'] ?? '{}', true);
    }

    $recentTxs = $pdo->query("SELECT t.*, u.name as user_name, u.uid as user_uid FROM transactions t LEFT JOIN users u ON t.user_id = u.id WHERE t.status != 'pending' ORDER BY t.created_at DESC LIMIT 20")->fetchAll();
    foreach ($recentTxs as &$rt) {
        $rt['detail'] = json_decode($rt['detail'] ?? '{}', true);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bảng Quản Trị - SUPPER AI Admin Hub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0b0c10;
      --card-bg: #14151f;
      --card-border: rgba(255, 255, 255, 0.08);
      --accent: #dfc5b2;
      --accent-hover: #c9ad9a;
      --text: #f3f4f6;
      --text-muted: #9ca3af;
      --green: #10b981;
      --red: #ef4444;
      --yellow: #f59e0b;
      --font: 'Plus Jakarta Sans', -apple-system, sans-serif;
      --font-mono: 'JetBrains Mono', monospace;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background-color: var(--bg);
      color: var(--text);
      font-family: var(--font);
      line-height: 1.5;
      padding-bottom: 60px;
    }
    .header {
      background: rgba(20, 21, 31, 0.8);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--card-border);
      position: sticky;
      top: 0;
      z-index: 100;
      padding: 16px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .header-logo {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 1.2rem;
      font-weight: 800;
      color: #fff;
    }
    .header-logo span { color: var(--accent); }
    .badge {
      display: inline-block;
      padding: 3px 8px;
      font-size: 0.72rem;
      font-weight: 700;
      border-radius: 6px;
      text-transform: uppercase;
    }
    .badge-admin { background: rgba(245, 158, 11, 0.2); color: var(--yellow); border: 1px solid rgba(245, 158, 11, 0.4); }
    .badge-pending { background: rgba(245, 158, 11, 0.2); color: var(--yellow); }
    .badge-approved { background: rgba(16, 185, 129, 0.2); color: var(--green); }
    .badge-rejected { background: rgba(239, 68, 68, 0.2); color: var(--red); }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 24px 16px;
    }
    .grid-4 {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 16px;
      margin-bottom: 28px;
    }
    .card {
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      border-radius: 16px;
      padding: 20px;
    }
    .stat-label { font-size: 0.82rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-value { font-size: 1.8rem; font-weight: 800; margin-top: 6px; }
    
    .section-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: #fff;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .section-badge {
      background: #ef4444;
      color: #fff;
      font-size: 0.75rem;
      padding: 2px 8px;
      border-radius: 999px;
      font-weight: 800;
    }
    
    .table-container {
      overflow-x: auto;
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      border-radius: 16px;
      margin-bottom: 28px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
      font-size: 0.88rem;
    }
    th {
      background: rgba(255, 255, 255, 0.03);
      padding: 14px 16px;
      font-size: 0.75rem;
      color: var(--text-muted);
      text-transform: uppercase;
      font-weight: 700;
      letter-spacing: 0.5px;
      border-bottom: 1px solid var(--card-border);
    }
    td {
      padding: 14px 16px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.04);
      vertical-align: middle;
    }
    tr:last-child td { border-bottom: none; }
    
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 8px 14px;
      border-radius: 8px;
      font-size: 0.82rem;
      font-weight: 700;
      cursor: pointer;
      border: none;
      text-decoration: none;
      transition: all 0.2s;
    }
    .btn-success { background: #10b981; color: #fff; }
    .btn-success:hover { background: #059669; }
    .btn-danger { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.4); }
    .btn-danger:hover { background: #ef4444; color: #fff; }
    .btn-copy { background: rgba(255, 255, 255, 0.08); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 4px 8px; font-size: 0.75rem; border-radius: 6px; }
    .btn-copy:hover { background: rgba(56, 189, 248, 0.2); }
    
    .wallet-pill {
      font-family: var(--font-mono);
      font-size: 0.8rem;
      background: #0d0e15;
      padding: 4px 8px;
      border-radius: 6px;
      color: #38bdf8;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      word-break: break-all;
    }
    
    .login-box {
      max-width: 420px;
      margin: 80px auto;
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      border-radius: 20px;
      padding: 32px 28px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.6);
    }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 0.84rem; color: var(--text-muted); margin-bottom: 6px; font-weight: 600; }
    .form-control {
      width: 100%;
      padding: 12px 14px;
      background: #0e0f17;
      border: 1px solid var(--card-border);
      border-radius: 10px;
      color: #fff;
      font-family: inherit;
      font-size: 0.95rem;
    }
    .form-control:focus { outline: none; border-color: var(--accent); }
    .btn-primary { width: 100%; background: var(--accent); color: #000; font-size: 0.95rem; padding: 12px; border-radius: 10px; font-weight: 700; margin-top: 8px; }
    .btn-primary:hover { background: var(--accent-hover); }
    .alert-error { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5; padding: 10px 14px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 16px; }
  </style>
</head>
<body>

<?php if (!$isAdmin): ?>
  <!-- Form Đăng Nhập Quản Trị Viên -->
  <div class="login-box">
    <div style="text-align: center; margin-bottom: 24px;">
      <div style="font-size: 2.2rem; margin-bottom: 8px;">🛡️</div>
      <h2 style="font-size: 1.4rem; font-weight: 800; color: #fff;">SUPPER AI Admin Portal</h2>
      <p style="font-size: 0.84rem; color: var(--text-muted); margin-top: 4px;">Đăng nhập để quản lý và duyệt nạp/rút tiền</p>
    </div>

    <?php if (!empty($loginError)): ?>
      <div class="alert-error"><?= htmlspecialchars($loginError) ?></div>
    <?php endif; ?>

    <form method="POST" action="admin.php">
      <input type="hidden" name="admin_login" value="1">
      <div class="form-group">
        <label>Tài khoản hoặc Email Quản Trị</label>
        <input type="text" name="email" class="form-control" placeholder="ngdat257257@gmail.com hoặc admin" value="ngdat257257@gmail.com" required>
      </div>
      <div class="form-group">
        <label>Mật Khẩu</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" value="dat112233" required>
      </div>
      <button type="submit" class="btn btn-primary">Đăng Nhập Quản Trị &rarr;</button>
    </form>
    
    <div style="margin-top: 20px; text-align: center;">
      <a href="index.php" style="color: var(--text-muted); font-size: 0.82rem; text-decoration: none;">&larr; Quay lại SUPPER AI WebApp</a>
    </div>
  </div>

<?php else: ?>
  <!-- Bảng Quản Trị Đầy Đủ -->
  <header class="header">
    <div class="header-logo">
      <span>🤖 SUPPER AI</span> | Admin Dashboard <span class="badge badge-admin">Quản Trị Viên</span>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
      <a href="index.php" target="_blank" class="btn" style="background: rgba(255,255,255,0.06); color: #fff;">📱 Mở Mini App</a>
      <a href="admin.php?action=logout" class="btn btn-danger">Đăng Xuất</a>
    </div>
  </header>

  <main class="container">
    <!-- Thống kê tổng quan -->
    <div class="grid-4">
      <div class="card">
        <div class="stat-label">Lệnh Rút Chờ Duyệt</div>
        <div class="stat-value" style="color: <?= count($pendingWithdrawals) > 0 ? '#ef4444' : '#10b981' ?>;">
          <?= count($pendingWithdrawals) ?>
        </div>
      </div>
      <div class="card">
        <div class="stat-label">Lệnh Nạp Chờ Duyệt</div>
        <div class="stat-value" style="color: <?= count($pendingDeposits) > 0 ? '#f59e0b' : '#10b981' ?>;">
          <?= count($pendingDeposits) ?>
        </div>
      </div>
      <div class="card">
        <div class="stat-label">Tổng Thành Viên</div>
        <div class="stat-value"><?= number_format($totalUsers) ?></div>
      </div>
      <div class="card">
        <div class="stat-label">Tổng Số Dư USDT Khách</div>
        <div class="stat-value" style="color: #10b981;"><?= number_format($totalUsdt, 2) ?> $</div>
      </div>
    </div>

    <!-- 1. Danh sách Lệnh Rút Chờ Duyệt -->
    <div class="section-title">
      <span>💸 Lệnh Rút Tiền Chờ Duyệt (Pending Withdrawals)</span>
      <?php if (count($pendingWithdrawals) > 0): ?>
        <span class="section-badge"><?= count($pendingWithdrawals) ?></span>
      <?php endif; ?>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Mã Lệnh</th>
            <th>Khách Hàng (UID)</th>
            <th>Số Rút</th>
            <th>Thực Nhận (Sau phí)</th>
            <th>Địa Chỉ Ví Nhận (BEP20 / TRC20)</th>
            <th>Thời Gian</th>
            <th style="text-align: right;">Thao Tác Duyệt</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pendingWithdrawals)): ?>
            <tr>
              <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                🎉 Hiện không có lệnh rút tiền nào đang chờ duyệt.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($pendingWithdrawals as $pw): 
              $detail = $pw['detail'];
              $destAddr = $detail['recipient_address'] ?? 'Chưa rõ';
              $netAmount = $detail['net_amount'] ?? $pw['amount'];
            ?>
              <tr id="row-<?= htmlspecialchars($pw['id']) ?>">
                <td><strong style="color: #fff;"><?= htmlspecialchars($pw['id']) ?></strong></td>
                <td>
                  <div style="font-weight: 700; color: #fff;"><?= htmlspecialchars($pw['user_name'] ?? 'Miner') ?></div>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">UID: <?= htmlspecialchars($pw['user_uid'] ?? '') ?></div>
                </td>
                <td><strong style="color: #ef4444; font-size: 1rem;">-<?= number_format((float)$pw['amount'], 2) ?> USDT</strong></td>
                <td><strong style="color: #10b981; font-size: 1rem;"><?= number_format((float)$netAmount, 2) ?> USDT</strong></td>
                <td>
                  <div class="wallet-pill">
                    <span id="addr-<?= htmlspecialchars($pw['id']) ?>"><?= htmlspecialchars($destAddr) ?></span>
                    <button type="button" class="btn-copy" onclick="copyText('<?= htmlspecialchars($destAddr) ?>')">Copy</button>
                  </div>
                </td>
                <td style="color: var(--text-muted); font-size: 0.8rem;"><?= date('H:i:s d/m/Y', strtotime($pw['created_at'])) ?></td>
                <td style="text-align: right;">
                  <button type="button" class="btn btn-success" onclick="approveTx('<?= htmlspecialchars($pw['id']) ?>')">
                    ✓ Đã Chuyển Tiền & Duyệt
                  </button>
                  <button type="button" class="btn btn-danger" style="margin-left: 6px;" onclick="rejectTx('<?= htmlspecialchars($pw['id']) ?>')">
                    ✕ Từ Chối
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- 2. Danh sách Lệnh Nạp Chờ Duyệt -->
    <div class="section-title">
      <span>📥 Lệnh Nạp Tiền Chờ Duyệt (Pending Deposits)</span>
      <?php if (count($pendingDeposits) > 0): ?>
        <span class="section-badge"><?= count($pendingDeposits) ?></span>
      <?php endif; ?>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Mã Lệnh</th>
            <th>Khách Hàng (UID)</th>
            <th>Số Tiền Nạp</th>
            <th>Mã Giao Dịch (TxHash)</th>
            <th>Thời Gian</th>
            <th style="text-align: right;">Thao Tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pendingDeposits)): ?>
            <tr>
              <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                🎉 Hiện không có lệnh nạp tiền nào đang chờ duyệt.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($pendingDeposits as $pd): 
              $detail = $pd['detail'];
              $txHash = $detail['tx_hash'] ?? '';
            ?>
              <tr id="row-<?= htmlspecialchars($pd['id']) ?>">
                <td><strong style="color: #fff;"><?= htmlspecialchars($pd['id']) ?></strong></td>
                <td>
                  <div style="font-weight: 700; color: #fff;"><?= htmlspecialchars($pd['user_name'] ?? 'Miner') ?></div>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">UID: <?= htmlspecialchars($pd['user_uid'] ?? '') ?></div>
                </td>
                <td><strong style="color: #10b981; font-size: 1rem;">+<?= number_format((float)$pd['amount'], 2) ?> USDT</strong></td>
                <td>
                  <div class="wallet-pill">
                    <span><?= htmlspecialchars($txHash) ?></span>
                    <button type="button" class="btn-copy" onclick="copyText('<?= htmlspecialchars($txHash) ?>')">Copy</button>
                  </div>
                </td>
                <td style="color: var(--text-muted); font-size: 0.8rem;"><?= date('H:i:s d/m/Y', strtotime($pd['created_at'])) ?></td>
                <td style="text-align: right;">
                  <button type="button" class="btn btn-success" onclick="approveTx('<?= htmlspecialchars($pd['id']) ?>')">
                    ✓ Duyệt Nạp Tiền
                  </button>
                  <button type="button" class="btn btn-danger" style="margin-left: 6px;" onclick="rejectTx('<?= htmlspecialchars($pd['id']) ?>')">
                    ✕ Từ Chối
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- 3. Lịch sử giao dịch gần đây -->
    <div class="section-title">
      <span>📜 Lịch Sử Giao Dịch Gần Đây (Đã Xử Lý)</span>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Mã Lệnh</th>
            <th>Khách Hàng (UID)</th>
            <th>Loại</th>
            <th>Số Lượng</th>
            <th>Trạng Thái</th>
            <th>Ghi Chú</th>
            <th>Thời Gian</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentTxs)): ?>
            <tr>
              <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 20px;">Chưa có lịch sử.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($recentTxs as $rt): ?>
              <tr>
                <td><?= htmlspecialchars($rt['id']) ?></td>
                <td>
                  <span style="font-weight: 600;"><?= htmlspecialchars($rt['user_name'] ?? 'Miner') ?></span>
                  <span style="color: var(--text-muted); font-size: 0.75rem;">(UID: <?= htmlspecialchars($rt['user_uid'] ?? '') ?>)</span>
                </td>
                <td>
                  <span class="badge" style="background: rgba(255,255,255,0.06);"><?= strtoupper($rt['type']) ?></span>
                </td>
                <td>
                  <strong style="color: <?= $rt['type'] === 'withdraw' ? '#ef4444' : '#10b981' ?>;">
                    <?= $rt['type'] === 'withdraw' ? '-' : '+' ?><?= number_format((float)$rt['amount'], 2) ?> <?= htmlspecialchars($rt['currency']) ?>
                  </strong>
                </td>
                <td>
                  <span class="badge badge-<?= htmlspecialchars($rt['status']) ?>">
                    <?= htmlspecialchars($rt['status']) ?>
                  </span>
                </td>
                <td style="color: var(--text-muted); font-size: 0.8rem;"><?= htmlspecialchars($rt['notes'] ?? '—') ?></td>
                <td style="color: var(--text-muted); font-size: 0.8rem;"><?= date('H:i:s d/m/Y', strtotime($rt['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <script>
    function copyText(txt) {
      navigator.clipboard.writeText(txt).then(() => {
        alert('Đã sao chép: ' + txt);
      }).catch(() => {
        prompt('Copy thủ công:', txt);
      });
    }

    async function approveTx(txId) {
      if (!confirm('Bạn có chắc chắn muốn DUYỆT giao dịch #' + txId + '?\n\n(Nếu là lệnh RÚT TIỀN, hãy chắc chắn bạn đã chuyển USDT đến ví của khách rồi nhé).')) {
        return;
      }
      try {
        const res = await fetch('api/admin.php?action=approve_tx', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: txId })
        });
        const data = await res.json();
        if (data.message) {
          alert('Thành công: ' + data.message);
          const row = document.getElementById('row-' + txId);
          if (row) row.remove();
          location.reload();
        } else {
          alert('Lỗi: ' + (data.error || 'Không thể duyệt'));
        }
      } catch (err) {
        alert('Lỗi kết nối: ' + err.message);
      }
    }

    async function rejectTx(txId) {
      const reason = prompt('Nhập lý do từ chối lệnh #' + txId + ' (nếu từ chối lệnh RÚT, tiền sẽ được hoàn trả lại cho số dư của khách):', 'Thông tin ví không hợp lệ');
      if (!reason) return;

      try {
        const res = await fetch('api/admin.php?action=reject_tx', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: txId, reason: reason })
        });
        const data = await res.json();
        if (data.message) {
          alert('Đã từ chối: ' + data.message);
          const row = document.getElementById('row-' + txId);
          if (row) row.remove();
          location.reload();
        } else {
          alert('Lỗi: ' + (data.error || 'Không thể từ chối'));
        }
      } catch (err) {
        alert('Lỗi kết nối: ' + err.message);
      }
    }
  </script>
<?php endif; ?>

</body>
</html>
