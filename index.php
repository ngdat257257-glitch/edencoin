<?php
// index.php
require_once __DIR__ . '/config/db.php';
$pdo = getDb();
$settings = getSettings($pdo);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>SUPPER Mining - Telegram Mini App</title>
  <meta name="description" content="Nền tảng đào coin SUPPER thế hệ mới trên Telegram, mua gói đào SVIP hashrate cao, sinh lời tự động 24/7." />
  
  <!-- Telegram Web App SDK -->
  <script src="https://telegram.org/js/telegram-web-app.js"></script>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
  
  <!-- CSS Stylesheet -->
  <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
  
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23dfc5b2' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='12' cy='12' r='10'/><path d='M16 8h-6a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h6'/><line x1='8' y1='12' x2='14' y2='12'/></svg>">
</head>
<body>

<!-- Floating Simulator Toolbar (Tự động ẩn để vào giao diện Mobile / Telegram Mini App chuẩn) -->
<div id="iphoneToolbar" class="iphone-toolbar" style="display: none !important;">
  <span class="iphone-badge">📱 iPhone 17 Pro Max</span>
  <button id="btnToggleIphone" class="btn btn-sm btn-primary" onclick="toggleIphoneFrame()">
    Khung iPhone: BẬT
  </button>
  <button class="btn btn-sm btn-secondary scale-btn" data-scale="0.6" onclick="scaleIphone(0.6)">60%</button>
  <button class="btn btn-sm btn-secondary scale-btn" data-scale="0.7" onclick="scaleIphone(0.7)">70%</button>
  <button class="btn btn-sm btn-secondary scale-btn" data-scale="0.8" onclick="scaleIphone(0.8)">80%</button>
  <button class="btn btn-sm btn-secondary scale-btn" data-scale="1.0" onclick="scaleIphone(1.0)">100%</button>
</div>

<!-- Main Device Wrapper -->
<div id="deviceWrapper" class="iphone-mode-active">
  <div id="iphoneScaleContainer" class="iphone-scale-container">
    <!-- iPhone 17 Pro Max Titanium Chassis -->
    <div id="iphoneChassis" class="iphone-chassis">
    <!-- Physical Side Buttons -->
    <div class="iphone-btn-action" title="Action Button"></div>
    <div class="iphone-btn-vol-up" title="Volume Up"></div>
    <div class="iphone-btn-vol-down" title="Volume Down"></div>
    <div class="iphone-btn-power" title="Power"></div>

    <!-- Screen Viewport -->
    <div id="iphoneScreen" class="iphone-screen">
      <!-- 1. Top Fixed Bar: Status Bar & Dynamic Island -->
      <div id="iphoneTopBar" class="iphone-top-bar">
        <!-- iOS Status Bar -->
        <div id="iosStatusBar" class="ios-status-bar">
          <span class="status-time" id="iosTime">00:03</span>
          <div class="status-icons">
            <!-- Signal -->
            <svg width="15" height="11" viewBox="0 0 16 12" fill="currentColor"><rect x="0" y="9" width="2.5" height="3" rx="0.5"/><rect x="4" y="6" width="2.5" height="6" rx="0.5"/><rect x="8" y="3" width="2.5" height="9" rx="0.5"/><rect x="12" y="0" width="2.5" height="12" rx="0.5"/></svg>
            <!-- 5G -->
            <span style="font-size: 0.68rem; font-weight: 800; letter-spacing: -0.05em;">5G</span>
            <!-- Battery -->
            <svg width="22" height="11" viewBox="0 0 22 11" fill="none" stroke="currentColor" stroke-width="1"><rect x="0.5" y="0.5" width="18" height="10" rx="2.5"/><rect x="2" y="2" width="15" height="7" rx="1.5" fill="currentColor"/><path d="M20 3.5v4" stroke-linecap="round"/></svg>
          </div>
        </div>

        <!-- Dynamic Island -->
        <div id="dynamicIsland" class="dynamic-island" title="Dynamic Island (iPhone 17 Pro Max)">
          <div class="island-camera"></div>
          <div class="island-sensor"></div>
        </div>
      </div> <!-- /iphoneTopBar -->

      <!-- 2. Scrollable Middle Area -->
      <div id="iphoneContentScroll" class="iphone-content-scroll">
        <!-- Top Navigation Bar -->
        <!-- Top Navigation Bar (Chuẩn 100% theo ảnh: UID: 120850 + Level 10 pill + Icon quả địa cầu) -->
        <header class="navbar" style="background: #000000 !important; border-bottom: 1px solid rgba(255,255,255,0.06); padding: 14px 18px !important;">
          <div class="navbar-inner" style="display: flex; align-items: center; justify-content: space-between; width: 100%; max-width: 100%; box-sizing: border-box;">
            <!-- Left: UID Text & Level 10 pill badge -->
            <div class="nav-left" style="display: flex; align-items: center; gap: 10px; cursor: pointer;" onclick="switchTab('store')">
              <span class="nav-uid-text" style="font-size: 1.05rem; font-weight: 500; color: #ead9cf !important; letter-spacing: 0.01em; user-select: none;">
                UID: <span class="user-uid-display">120850</span>
              </span>
              <span class="nav-level-badge" style="background: #dfc5b2 !important; color: #1e1b18 !important; font-size: 0.88rem; font-weight: 600; padding: 3px 14px; border-radius: 9999px; line-height: 1.35; letter-spacing: 0.01em; user-select: none;">
                Level 1
              </span>
            </div>

            <!-- Desktop Links (Center) -->
            <ul class="nav-links">
              <li class="nav-item active" data-tab="dashboard" data-i18n="nav_dashboard">Dashboard</li>
              <li class="nav-item" data-tab="store" data-i18n="nav_store">Máy Đào Ảo</li>
              <li class="nav-item" data-tab="wallet" data-i18n="nav_wallet">Ví & Swap</li>
              <li class="nav-item" data-tab="about" data-i18n="nav_about">Giới Thiệu</li>
              <li class="nav-item admin-only" data-tab="admin" data-i18n="nav_admin" style="display:none; color: #fbbf24;">Admin Quản Trị</li>
            </ul>

            <!-- Right: Minimalist World Globe Icon for Language Selector -->
            <div class="nav-actions" style="display: flex; align-items: center;">
              <div class="lang-selector-dropdown" id="langSelector" style="position: relative;">
                <button type="button" class="lang-globe-btn" id="langBtn" onclick="toggleLangDropdown(event)" aria-label="Select Language" title="Chọn ngôn ngữ" style="background: transparent; border: none; padding: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; outline: none; transition: transform 0.2s;">
                  <!-- Icon quả địa cầu trắng chuẩn theo ảnh -->
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="#ead9cf" style="display: block;">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                  </svg>
                </button>
                <div class="lang-menu" id="langMenu" style="display: none;"></div>
              </div>
            </div>
          </div>
        </header>

  <!-- Toast Container -->
  <div id="toastContainer" style="position: fixed; top: 70px; right: 20px; z-index: 999; max-width: 360px; width: calc(100% - 40px); display: flex; flex-direction: column; gap: 8px; pointer-events: none;"></div>

  <!-- Main Pages -->
  <main class="main-content">
    
    <!-- ==================== TAB 1: DASHBOARD ==================== -->
    <section id="page-dashboard" class="tab-page active">
      <!-- Mining Realtime & Claim Coin Card with 3D Golden Lighting Around Original Coin -->
      <div class="glass-card" id="dashMiningCard" style="padding: 22px 18px; border-radius: 20px; border: 1px solid rgba(245, 158, 11, 0.45); background: #000; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7), 0 0 35px rgba(245, 158, 11, 0.2); text-align: center; margin-top: 6px;">
        
        <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 14px;">
          <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); padding: 5px 14px; border-radius: 20px;">
            <span class="pulse-dot" style="background: #fbbf24; box-shadow: 0 0 10px #fbbf24;"></span>
            <span data-i18n="dash_badge" style="font-size: 0.74rem; font-weight: 700; color: #fbbf24; letter-spacing: 0.05em; text-transform: uppercase;">⚡ KHAI THÁC COIN REALTIME</span>
          </div>
        </div>

        <!-- 3D Golden Lighting Effects Around Untouched Original Coin -->
        <div class="coin-aura-stage">
          <!-- 1. Ambient Golden Flare (Ánh sáng vàng tỏa mềm mại phía sau) -->
          <div class="coin-aura-glow"></div>

          <!-- 2. Rotating Corona Rays (Tia sáng hào quang xoay tròn xung quanh) -->
          <div class="coin-corona-rays"></div>

          <!-- 3. 3D Golden Orbit Ring 1 (Vòng quỹ đạo vàng nghiêng 3D quay quanh coin) -->
          <div class="coin-orbit-ring-1"></div>

          <!-- 4. 3D Golden Orbit Ring 2 (Vòng quỹ đạo vàng nghiêng chéo đối xứng 3D) -->
          <div class="coin-orbit-ring-2"></div>

          <!-- 5. Golden Sparkles (Các điểm lấp lánh ánh kim xung quanh coin) -->
          <div class="coin-sparkle coin-sparkle-1"></div>
          <div class="coin-sparkle coin-sparkle-2"></div>
          <div class="coin-sparkle coin-sparkle-3"></div>
          <div class="coin-sparkle coin-sparkle-4"></div>

          <!-- 6. Golden Coin Image - KHUNG ẢNH TRÒN XUNG QUANH ĐỒNG COIN -->
          <div class="coin-pure-container" id="coinPureContainer">
            <img id="mainCoinImg" src="assets/images/golden_coin_round.png?v=5" alt="SUPPER COIN" class="coin-pure-img">
          </div>

          <!-- 7. Dynamic 3D Drop Shadow Beneath Coin -->
          <div class="coin-ground-shadow"></div>
        </div>

        <div style="margin-bottom: 6px;">
          <span data-i18n="dash_unclaimed" style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 4px;">Sản Lượng Tích Luỹ Chưa Nhận</span>
          <div style="display: flex; align-items: baseline; justify-content: center; gap: 8px;">
            <span id="liveCoinCounter" class="counter-digits" style="font-size: 2.6rem; font-weight: 900; color: #fbbf24; font-family: var(--font-mono); text-shadow: 0 0 24px rgba(245, 158, 11, 0.65);">0.000000</span>
            <span class="coin-symbol" style="font-size: 1.15rem; font-weight: 700; color: #fef08a;">SUPPER</span>
          </div>
        </div>

          <!-- Khối đào coin chuẩn theo ảnh: SUPPER / H + JOIN 100× + Đếm ngược + Nút Mining -->
          <div class="mine-panel">
            <div class="mine-rate-row">
              <span id="liveSpeedPerHour" class="mine-rate">250.00 SUPPER / H</span>
              <div class="mine-boost">
                <span class="mine-boost-x">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 2.5c3.6-.6 6.9.3 8 1.4 1.1 1.1 2 4.4 1.4 8-.5 3-2.3 5.7-5 7.6l.3 2.6-3.4 1.4-1.6-2.9-4.2-4.2-2.9-1.6 1.4-3.4 2.6.3c1.9-2.7 4.6-4.5 7.4-5.2zM16 6.5a1.5 1.5 0 100 3 1.5 1.5 0 000-3zM5.2 15.6l3.2 3.2c-1 1.8-3.1 2.9-6.4 3.2.3-3.3 1.4-5.4 3.2-6.4z"/></svg>100<small>×</small>
                </span>
                <button type="button" class="mine-join-btn" id="btnMineJoin" onclick="switchTab('store')" style="color: #000000 !important;">JOIN</button>
              </div>
            </div>

            <div class="mine-box">
              <button type="button" class="mine-info-btn" id="btnMineInfo" onclick="showMiningInfo()" aria-label="Thông tin đào">i</button>
              <div class="mine-timer">
                <span class="mine-timer-line"></span>
                <span id="claimCountdownText">00H 00M 00S</span>
                <span class="mine-timer-line"></span>
              </div>
              <div id="mineTimerLabel" class="mine-timer-label">Time until next start</div>

              <button id="btnClaimReward" type="button" class="mine-action-btn" onclick="claimReward()">
                <span id="btnClaimText">Mining</span>
              </button>
            </div>

          </div>

        </div>
    </section>

    <!-- ==================== TAB 2: STORE (MÁY ĐÀO) ==================== -->
    <section id="page-store" class="tab-page" style="display: none; width: 100%; max-width: 520px; margin: 0 auto; padding: 10px 10px 30px; box-sizing: border-box; overflow-x: hidden;">

      <!-- User Profile Banner (chuẩn ảnh 1) -->
      <div class="store-user-banner" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <!-- Avatar tròn vòng nguyệt quế + vương miện -->
          <div style="width: 58px; height: 58px; position: relative; flex-shrink: 0; border-radius: 50%; overflow: hidden; background: #0c0d12;">
            <img src="assets/images/user_avatar_wreath.png" alt="Avatar" style="width: 100%; height: 100%; object-fit: contain;">
          </div>
          <!-- Tên, UID và Người giới thiệu -->
          <div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 1.15rem; font-weight: 700; color: #fff;">
              <span style="color: #eab308; font-size: 0.95rem;">💎</span>
              <span id="storeProfileName" class="user-name-display">evansTi</span>
            </div>
            <div style="font-size: 0.85rem; color: #8a8793; margin-top: 3px; font-family: var(--font-mono);">
              UID: <span class="user-uid-display">120850</span>
            </div>
            <div style="font-size: 0.82rem; color: #8a8793; margin-top: 2px;">
              Invited by (<span id="storeInvitedBy">Dreddinh</span>)
            </div>
          </div>
        </div>

        <!-- 2 Nút cài đặt & thông báo bên phải -->
        <div style="display: flex; align-items: center; gap: 8px;">
          <button type="button" onclick="handleUserAccountAction()" class="store-action-btn" title="Tài khoản & Cài đặt">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ead9cf" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="3"></circle>
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
            </svg>
          </button>
          <button type="button" onclick="openHistoryModal(event)" class="store-action-btn" title="Lịch sử giao dịch">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ead9cf" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Tiêu đề: Thiết bị hiện tại -->
      <div style="font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 12px; letter-spacing: -0.01em;">
        Thiết bị hiện tại
      </div>

      <!-- 3 Ô thống kê: cấp bậc, Thời gian làm việc, Tốc độ khai thác -->
      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 24px;">
        <!-- Ô 1: Cấp bậc (khách mua 10 gói 10 USDT thì lên 1 level) -->
        <div class="store-stat-card">
          <div class="store-stat-icon-wrap">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="#dfc5b2">
              <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/>
            </svg>
          </div>
          <div class="store-stat-title">cấp bậc</div>
          <div class="store-stat-value user-level-display">Level 1</div>
        </div>

        <!-- Ô 2: Thời gian làm việc -->
        <div class="store-stat-card">
          <div class="store-stat-icon-wrap">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="#dfc5b2">
              <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 14.5h-2V11h2v5.5zm0-7.5h-2V7h2v2z"/>
            </svg>
          </div>
          <div class="store-stat-title">Thời gian làm việc</div>
          <div class="store-stat-value">24H</div>
        </div>

        <!-- Ô 3: Tốc độ khai thác -->
        <div class="store-stat-card">
          <div class="store-stat-icon-wrap">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="#dfc5b2">
              <path d="M14.7 6.3l3.5 3.5-1.4 1.4-3.5-3.5 1.4-1.4zm-4.9 9.9l1.4-1.4 7.1 7.1-1.4 1.4-7.1-7.1zm-7.1 2.1l1.4-1.4 3.5 3.5-1.4 1.4-3.5-3.5zM17.5 3.5l1.4-1.4 3.5 3.5-1.4 1.4-3.5-3.5z"/>
            </svg>
          </div>
          <div class="store-stat-title">Tốc độ khai thác</div>
          <div class="store-stat-value" id="storeSpeedValue">250.000000<br><span style="font-size: 0.65rem; color: #8a8793; font-weight: 500;">SUPPER/H</span></div>
        </div>
      </div>

      <!-- 2 Tab Chuyển: SVIP & Nâng cấp -->
      <div style="display: flex; gap: 8px; margin-bottom: 0;">
        <button type="button" id="tabBtnSVIP" class="store-tab-btn active" onclick="switchStoreSubTab('svip')">
          <span>SVIP</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="8" y1="8" x2="16" y2="8"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="8" y1="16" x2="16" y2="16"/></svg>
        </button>
        <button type="button" id="tabBtnUpgrade" class="store-tab-btn" onclick="switchStoreSubTab('upgrade')">
          <span>Nâng cấp</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="8" y1="8" x2="16" y2="8"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="8" y1="16" x2="16" y2="16"/></svg>
        </button>
      </div>

      <!-- Khung Nội Dung Gói Đào SVIP (Chuẩn 100% Ảnh 1) -->
      <div id="storeContentSVIP" class="store-card-container">
        <!-- Khối thông tin gói -->
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 22px;">
          <!-- Ảnh chip máy đào SVIP -->
          <div style="width: 86px; height: 86px; border-radius: 14px; overflow: hidden; background: #000; flex-shrink: 0; border: 1px solid rgba(255,255,255,0.08);">
            <img src="assets/images/svip_chip_clean.png" alt="SVIP Chip" style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <!-- Thông số gói SVIP -->
          <div style="flex: 1; min-width: 0;">
            <div style="display: flex; align-items: center; gap: 6px; font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 8px;">
              <span style="color: #eab308; font-size: 0.95rem;">💎</span>
              <span>SVIP</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem; margin-bottom: 5px;">
              <span style="color: #8a8793;">Daily Output</span>
              <span style="color: #fff; font-weight: 600; font-family: var(--font-mono);">6000 SUPPER</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem;">
              <span style="color: #8a8793;">Price</span>
              <span style="color: #fff; font-weight: 600; font-family: var(--font-mono);">10 USDT</span>
            </div>
          </div>
        </div>

        <!-- Nút Mua sắm (hồng phấn/be sang trọng #ead1bf) -->
        <button type="button" class="btn-store-buy" onclick="openSvipBuyModal('miner_svip')" style="color: #000000 !important;">
          Mua sắm
        </button>
      </div>

      <!-- Khung Nội Dung Gói Đào Nâng Cấp -->
      <div id="storeContentUpgrade" class="store-card-container" style="display: none;">
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 22px;">
          <div style="width: 86px; height: 86px; border-radius: 14px; overflow: hidden; background: #000; flex-shrink: 0; border: 1px solid rgba(255,255,255,0.08);">
            <img src="assets/images/svip_chip_clean.png" alt="SVIP Pro" style="width: 100%; height: 100%; object-fit: cover; filter: hue-rotate(45deg);">
          </div>
          <div style="flex: 1; min-width: 0;">
            <div style="display: flex; align-items: center; gap: 6px; font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 8px;">
              <span style="color: #38bdf8; font-size: 0.95rem;">💎</span>
              <span>SVIP Pro (Nâng Cấp)</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem; margin-bottom: 5px;">
              <span style="color: #8a8793;">Daily Output</span>
              <span style="color: #fff; font-weight: 600; font-family: var(--font-mono);">30000 SUPPER</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem;">
              <span style="color: #8a8793;">Price</span>
              <span style="color: #fff; font-weight: 600; font-family: var(--font-mono);">50 USDT</span>
            </div>
          </div>
        </div>

        <button type="button" class="btn-store-buy" onclick="openSvipBuyModal('miner_upgrade')" style="color: #000000 !important;">
          Mua sắm
        </button>
      </div>

      <!-- Danh sách gói phụ (nếu có thêm gói) -->
      <div id="storeMinersGrid" style="display: none;"></div>

    </section>

    <!-- ==================== TAB 3: WALLET & SWAP (VÍ TIỀN & MUA LẠI CHUẨN ẢNH) ==================== -->
    <section id="page-wallet" class="tab-page" style="display: none; width: 100%; max-width: 540px; margin: 0 auto; padding-bottom: 30px; box-sizing: border-box; overflow-x: hidden;">
      
      <!-- 1. MÀN HÌNH CHÍNH: VÍ TIỀN (CHUẨN 100% THEO ẢNH NGƯỜI DÙNG CUNG CẤP) -->
      <div id="userWalletMainView" style="display: flex; flex-direction: column; width: 100%;">
        
        <!-- Tiêu đề: Ví tiền -->
        <h1 class="wallet-title" style="font-size: 1.85rem; font-weight: 700; text-align: center; margin: 12px 0 22px; color: #ead9cf !important; letter-spacing: -0.01em;">
          Ví tiền
        </h1>

        <!-- Khối Số Dư Lớn: 1555.582095 SUPPER -->
        <div style="text-align: center; margin-bottom: 32px;">
          <div class="wallet-main-balance" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 1.85rem; font-weight: 700; color: #ead9cf !important; letter-spacing: -0.01em;">
            <span class="user-coin-balance" style="font-weight: 700; color: #ead9cf !important;">1555.582095</span>
            <span class="coin-symbol" style="font-weight: 700; color: #ead9cf !important;">SUPPER</span>
          </div>
          <!-- Quy đổi tương đương sang USDT -->
          <div class="wallet-sub-equiv" style="margin-top: 6px; font-size: 0.86rem; color: #7c7a82 !important; text-align: center;">
            ≈<span id="userPageEquivUsdt" style="color: #7c7a82 !important;">0.155558</span> USDT
          </div>
        </div>

        <!-- Ba Nút Tròn Thao Tác: Hóa đơn, Mua lại, lời hứa -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 30px; text-align: center;">
          
          <!-- Nút 1: Hóa đơn -->
          <div onclick="openInvoiceView(event)" style="cursor: pointer; display: flex; flex-direction: column; align-items: center;">
            <div class="action-btn-circle" style="width: 58px; height: 58px; border-radius: 50%; border: 1.5px solid rgba(234, 217, 207, 0.32); background: rgba(255, 255, 255, 0.02); display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ead9cf" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="4" y="3" width="16" height="18" rx="2"/>
                <line x1="8" y1="8" x2="16" y2="8"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
                <line x1="8" y1="16" x2="12" y2="16"/>
              </svg>
            </div>
            <span class="action-btn-label" style="font-size: 0.85rem; font-weight: 500; color: #ead9cf !important; margin-top: 8px;">Hóa đơn</span>
          </div>

          <!-- Nút 3: Mua lại (Mở màn hình Mua lại chuẩn ảnh) -->
          <div onclick="openBuybackView()" style="cursor: pointer; display: flex; flex-direction: column; align-items: center;">
            <div class="action-btn-circle" style="width: 58px; height: 58px; border-radius: 50%; border: 1.5px solid rgba(234, 217, 207, 0.32); background: rgba(255, 255, 255, 0.02); display: flex; align-items: center; justify-content: center; position: relative; transition: all 0.2s;">
              <span class="badge-new" style="position: absolute; top: -6px; right: -4px; border: 1px solid rgba(211, 184, 166, 0.7); background: #000; color: #d3b8a6 !important; border-radius: 10px; font-size: 0.62rem; font-weight: 700; padding: 1px 6px; line-height: 1.2;">new</span>
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ead9cf" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                <path d="M16 3H8a2 2 0 0 0-2 2v2h12V5a2 2 0 0 0-2-2z"/>
                <circle cx="15.5" cy="13.5" r="1.5" fill="#ead9cf"/>
              </svg>
            </div>
            <span class="action-btn-label" style="font-size: 0.85rem; font-weight: 500; color: #ead9cf !important; margin-top: 8px;">Mua lại</span>
          </div>

          <!-- Nút 4: lời hứa -->
          <div onclick="openTermsModal(event)" style="cursor: pointer; display: flex; flex-direction: column; align-items: center;">
            <div class="action-btn-circle" style="width: 58px; height: 58px; border-radius: 50%; border: 1.5px solid rgba(234, 217, 207, 0.32); background: rgba(255, 255, 255, 0.02); display: flex; align-items: center; justify-content: center; position: relative; transition: all 0.2s;">
              <span class="badge-new" style="position: absolute; top: -6px; right: -4px; border: 1px solid rgba(211, 184, 166, 0.7); background: #000; color: #d3b8a6 !important; border-radius: 10px; font-size: 0.62rem; font-weight: 700; padding: 1px 6px; line-height: 1.2;">new</span>
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ead9cf" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                <polyline points="2 17 12 22 22 17"/>
                <polyline points="2 12 12 17 22 12"/>
              </svg>
            </div>
            <span class="action-btn-label" style="font-size: 0.85rem; font-weight: 500; color: #ead9cf !important; margin-top: 8px;">lời hứa</span>
          </div>

        </div>

        <!-- Đường Kẻ Phân Cách & Mục "Số dư" -->
        <div class="wallet-divider" style="border-top: 1px solid rgba(255,255,255,0.08); margin-top: 10px;"></div>
        <div class="wallet-section-title" style="text-align: center; padding: 14px 0; font-size: 1.05rem; font-weight: 600; color: #ead9cf !important; letter-spacing: 0.01em;">
          Số dư
        </div>
        <div class="wallet-divider" style="border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 10px;"></div>

        <!-- Danh Sách Tài Sản (SUPPER & USDT) -->
        <div style="display: flex; flex-direction: column;">
          
          <!-- Hàng 1: SUPPER -->
          <div style="display: flex; align-items: center; justify-content: space-between; padding: 18px 4px; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <div style="display: flex; align-items: center; gap: 10px;">
              <span style="font-size: 1.2rem;">🤖</span>
              <span class="asset-name coin-symbol" style="font-size: 1.15rem; font-weight: 700; color: #ead9cf !important; letter-spacing: 0.02em;">SUPPER</span>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
              <span class="asset-amount user-coin-balance" style="font-size: 1.15rem; font-weight: 600; color: #ead9cf !important; font-family: var(--font-mono);">1555.582095</span>
              <svg class="asset-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5b595e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"/>
              </svg>
            </div>
          </div>

          <!-- Hàng 2: USDT -->
          <div onclick="openBuybackView()" style="display: flex; align-items: center; justify-content: space-between; padding: 18px 4px; border-bottom: 1px solid rgba(255,255,255,0.05); cursor: pointer;">
            <div style="display: flex; align-items: center; gap: 14px;">
              <img src="assets/usdt.png" alt="USDT" style="width: 34px; height: 34px; border-radius: 50%; object-fit: contain; flex-shrink: 0; box-shadow: 0 0 8px rgba(38,161,123,0.5);">
              <span class="asset-name" style="font-size: 1.15rem; font-weight: 700; color: #ead9cf !important; letter-spacing: 0.02em;">USDT</span>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
              <span class="asset-amount user-usdt-balance" style="font-size: 1.15rem; font-weight: 600; color: #ead9cf !important; font-family: var(--font-mono);">22539.62</span>
              <svg class="asset-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5b595e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"/>
              </svg>
            </div>
          </div>

        </div>

      </div> <!-- /userWalletMainView -->

      <!-- 2. MÀN HÌNH MUA LẠI (CHUẨN 100% THEO ẢNH NGƯỜI DÙNG CUNG CẤP) -->
      <div id="userBuybackView" style="display: none; flex-direction: column; width: 100%;">
        
        <!-- Thanh điều hướng trên cùng: Nút quay lại & Tiêu đề "Mua lại" -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
          <button type="button" onclick="closeBuybackView()" style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ead9cf" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
              <line x1="19" y1="12" x2="5" y2="12"/>
              <polyline points="12 19 5 12 12 5"/>
            </svg>
          </button>
          <h2 style="font-size: 1.25rem; font-weight: 700; color: #ead9cf !important; margin: 0; text-align: center; flex: 1;">
            Mua lại
          </h2>
          <div style="width: 44px;"></div>
        </div>

        <!-- Khung thẻ chính bo góc viền cam/hồng phấn nhẹ -->
        <div style="background: #000; border: 1.5px solid rgba(255, 200, 180, 0.22); border-radius: 18px; padding: 20px 16px; box-sizing: border-box;">
          
          <!-- Đầu thẻ: Nút Lịch sử giao dịch -->
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <span style="font-size: 0.95rem; font-weight: 700; color: #ead9cf !important;">Tài Sản SUPPER</span>
            </div>
            <button type="button" onclick="openInvoiceView(event)" style="border: 1px solid rgba(255,255,255,0.25); background: transparent; border-radius: 20px; padding: 4px 14px; font-size: 0.8rem; font-weight: 500; color: #ead9cf !important; cursor: pointer; transition: all 0.2s;">
              Lịch sử giao dịch
            </button>
          </div>

          <!-- Khối Sự cân bằng (Số dư USDT khả dụng) -->
          <div style="background: #000; border: 1px solid rgba(255,255,255,0.12); border-radius: 14px; padding: 14px 16px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
              <div style="font-size: 0.84rem; color: #9c9aa2 !important; margin-bottom: 4px;">Sự cân bằng</div>
              <div style="font-size: 1.6rem; font-weight: 800; color: #ead9cf !important; font-family: var(--font-mono); display: flex; align-items: baseline; gap: 8px;">
                <span class="user-usdt-balance" id="buybackBalanceDisplay">22539.62</span>
                <span style="font-size: 1.0rem; font-weight: 700; color: #ead9cf !important;">USDT</span>
              </div>
            </div>
            <button type="button" onclick="openDepositModal()" class="btn" style="background: #dfc5b2; color: #000000 !important; font-weight: 700; font-size: 0.82rem; padding: 8px 14px; border-radius: 10px; border: none; cursor: pointer;">
              + Nạp USDT
            </button>
          </div>

          <!-- Ô 1: Địa chỉ ví -->
          <div style="border: 1px solid rgba(255,255,255,0.16); border-radius: 10px; padding: 10px 14px; margin-bottom: 12px; background: #000; cursor: pointer;" onclick="openWalletAddFlow()">
            <div style="font-size: 0.8rem; color: #7c7a82 !important; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
              <span>Địa chỉ ví:</span>
              <span style="font-size: 0.72rem; color: #dfc5b2; font-weight: 600;">+ Thêm / Quản lý ví</span>
            </div>
            <input type="text" id="buybackAddressInput" readonly value="0xd90e17f8a8a6c0b749028b23828e7c188652ebbc" style="background: transparent; border: none; outline: none; width: 100%; color: #ead9cf !important; font-family: var(--font-mono); font-size: 0.84rem; word-break: break-all; padding: 0; cursor: pointer;">
          </div>

          <!-- Ô 2: Số tiền rút -->
          <div style="border: 1px solid rgba(255,255,255,0.16); border-radius: 10px; padding: 12px 14px; margin-bottom: 12px; background: #000; display: flex; align-items: center; justify-content: space-between;">
            <input type="number" id="buybackAmountInput" step="any" placeholder="Số tiền rút" style="background: transparent; border: none; outline: none; flex: 1; color: #ead9cf !important; font-size: 0.95rem; padding: 0;">
            <span style="font-weight: 700; color: #ead9cf !important; font-size: 0.92rem; margin-left: 8px;">USDT</span>
          </div>

          <!-- Ô 3: Tiền tệ ghi có -->
          <div style="border: 1px solid rgba(255,255,255,0.16); border-radius: 10px; padding: 12px 14px; margin-bottom: 14px; background: #000;">
            <div style="font-size: 0.8rem; color: #7c7a82 !important; margin-bottom: 8px;">Tiền tệ ghi có:</div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
              <div style="width: 20px; height: 20px; border-radius: 50%; background: #ceb09b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#121116" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
              </div>
              <span style="font-weight: 700; color: #ead9cf !important; font-size: 0.95rem;">USDT</span>
            </div>
            <div style="font-size: 0.78rem; color: #7c7a82 !important;">Phí xử lý:1USDT</div>
          </div>

          <!-- Dòng 2FA thông báo & Ô nhập mã xác minh 6 chữ số -->
          <div style="display: flex; align-items: flex-start; gap: 6px; margin-bottom: 8px; font-size: 0.78rem; color: #7c7a82 !important; line-height: 1.4;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7c7a82" stroke-width="2" style="flex-shrink: 0; margin-top: 1px;">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>Vui lòng nhập mã xác minh 6 chữ số được tạo ra bởi ứng dụng xác thực.:</span>
          </div>
          <div style="border: 1px solid rgba(255,255,255,0.16); border-radius: 10px; padding: 12px 14px; margin-bottom: 22px; background: #000;">
            <input type="text" id="buyback2faInput" placeholder="Mã xác minh 6 chữ số" maxlength="6" style="background: transparent; border: none; outline: none; width: 100%; color: #ead9cf !important; font-size: 0.92rem; padding: 0;">
          </div>

          <!-- Nút gửi Mua lại màu cam kem ấm đúng chuẩn ảnh -->
          <button type="button" id="btnSubmitBuyback" onclick="submitBuyback()" style="width: 100%; height: 50px; background: #ceb09b !important; color: #18171c !important; border: none; border-radius: 12px; font-size: 1.05rem; font-weight: 700; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; text-shadow: none !important;">
            Mua lại
          </button>
        </div> <!-- /buyback card -->
      </div> <!-- /userBuybackView -->

      <!-- 3. MÀN HÌNH HOÁ ĐƠN / LỊCH SỬ GIAO DỊCH (CHUẨN 100% THEO ẢNH NGƯỜI DÙNG CUNG CẤP) -->
      <div id="userInvoiceView" class="invoice-view-container" style="display: none;">
        <!-- Thanh điều hướng trên cùng: Nút quay lại + Tiêu đề "Số dư USDT" + Nút lọc -->
        <div class="invoice-header">
          <button type="button" class="invoice-back-btn" onclick="closeInvoiceView()" aria-label="Quay lại">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="19" y1="12" x2="5" y2="12"></line>
              <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
          </button>
          <div class="invoice-header-title">Số dư USDT</div>
          <div class="invoice-filter-wrapper">
            <button type="button" class="invoice-filter-btn" id="invoiceFilterTriggerBtn" onclick="toggleInvoiceFilter(event)" aria-label="Bộ lọc">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 6h16M7 12h10M10 18h4"/>
              </svg>
            </button>
            <div id="invoiceFilterMenu" class="invoice-filter-dropdown" style="display: none;">
              <button type="button" class="filter-opt active" data-filter="all" onclick="selectInvoiceFilter('all')">Tất cả</button>
              <button type="button" class="filter-opt" data-filter="deposit" onclick="selectInvoiceFilter('deposit')">Nạp tiền</button>
              <button type="button" class="filter-opt" data-filter="commission" onclick="selectInvoiceFilter('commission')">Phần thưởng hoa hồng</button>
              <button type="button" class="filter-opt" data-filter="withdraw" onclick="selectInvoiceFilter('withdraw')">Rút tiền</button>
              <button type="button" class="filter-opt" data-filter="buy_miner" onclick="selectInvoiceFilter('buy_miner')">Mua gói máy đào</button>
              <button type="button" class="filter-opt" data-filter="swap" onclick="selectInvoiceFilter('swap')">Hoán đổi sang USDT</button>
            </div>
          </div>
        </div>

        <!-- Khối Số dư to ở giữa + Phụ đề Lịch sử giao dịch chuẩn theo ảnh -->
        <div class="invoice-balance-section">
          <div class="invoice-balance-number" id="invoiceUsdtBalance">30.62</div>
          <div class="invoice-balance-subtitle">Lịch sử giao dịch</div>
        </div>

        <!-- Đường kẻ ngang mảnh -->
        <div class="invoice-divider"></div>

        <!-- Danh sách giao dịch động chuẩn ảnh -->
        <div id="invoiceTransactionList" class="invoice-tx-list">
          <!-- Rendered via JS -->
        </div>
      </div> <!-- /userInvoiceView -->

    </section>

    <!-- ==================== TAB 4: ABOUT & REFERRAL (GIỚI THIỆU & HOA HỒNG TUYẾN TRÊN) ==================== -->
        <section id="page-about" class="tab-page" style="display: none; width: 100%; max-width: 520px; margin: 0 auto; padding-bottom: 30px; box-sizing: border-box; overflow-x: hidden;">
      <!-- 1. MÀN HÌNH CHÍNH TRANG GIỚI THIỆU -->
      <div id="aboutMainView" class="about-page-container">

        <!-- Grid 8 Nút Tròn (4 cột x 2 hàng chuẩn ảnh 1) -->
        <div class="about-grid-actions">
          <!-- Nút 1: Nhóm -->
          <div class="about-action-col" onclick="openTeamModal()">
            <div class="about-circle-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
            </div>
            <span class="about-action-name">Nhóm</span>
          </div>

          <!-- Nút 2: Nhiệm vụ -->
          <div class="about-action-col" onclick="openTasksModal()">
            <div class="about-circle-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="16" rx="2"/>
                <line x1="7" y1="8" x2="17" y2="8"/>
                <line x1="7" y1="12" x2="17" y2="12"/>
                <line x1="7" y1="16" x2="13" y2="16"/>
              </svg>
            </div>
            <span class="about-action-name">Nhiệm vụ</span>
          </div>

          <!-- Nút 3: Hướng dẫn -->
          <div class="about-action-col" onclick="openGuideModal()">
            <div class="about-circle-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
              </svg>
            </div>
            <span class="about-action-name">Hướng dẫn</span>
          </div>

          <!-- Nút 4: Lịch trình phát triển -->
          <div class="about-action-col" onclick="openRoadmapModal()">
            <div class="about-circle-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
              </svg>
            </div>
            <span class="about-action-name">Lịch trình phát<br>triển</span>
          </div>

          <!-- Nút 5: giấy trắng -->
          <div class="about-action-col" onclick="openWhitepaperModal()">
            <div class="about-circle-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2c-4 4-6 7.5-6 11a6 6 0 0 0 12 0c0-3.5-2-7-6-11z"/>
              </svg>
            </div>
            <span class="about-action-name">giấy trắng</span>
          </div>

          <!-- Nút 6: SUPPER&USDT (MỞ ẢNH 2 CHUẨN 100%) -->
          <div class="about-action-col" onclick="openSupperUsdtSwapView()">
            <div class="about-circle-btn" style="border-color: #dfc5b2;">
              <svg viewBox="0 0 24 24" fill="none" stroke="#dfc5b2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="23 4 23 10 17 10"/>
                <polyline points="1 20 1 14 7 14"/>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
              </svg>
            </div>
            <span class="about-action-name" style="color: #dfc5b2; font-weight: 700;">SUPPER&USDT</span>
          </div>

          <!-- Nút 7: P2P -->
          <div class="about-action-col" onclick="openP2PModal()">
            <div class="about-circle-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="8.5" cy="7" r="4"/>
                <line x1="18" y1="8" x2="23" y2="8"/>
                <polyline points="21 6 23 8 21 10"/>
                <line x1="22" y1="14" x2="17" y2="14"/>
                <polyline points="19 16 17 14 19 12"/>
              </svg>
            </div>
            <span class="about-action-name">P2P</span>
          </div>

          <!-- Nút 8: Nạp USDT (Tạm thay đổi theo yêu cầu) -->
          <div class="about-action-col" onclick="openDepositModal()">
            <div class="about-circle-btn" style="border-color: #dfc5b2;">
              <svg viewBox="0 0 24 24" fill="none" stroke="#dfc5b2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="3"/>
                <path d="M12 8v8M8 12h8"/>
              </svg>
            </div>
            <span class="about-action-name" style="color: #dfc5b2; font-weight: 700;">Nạp USDT</span>
          </div>
        </div>

        <!-- 3. BẢNG XẾP HẠNG ĐỘI (CHUẨN ẢNH 1) -->
        <div class="about-leaderboard-section">
          <h2 class="about-lb-title">Bảng xếp hạng đội</h2>
          <div class="about-lb-subrow">
            <span class="about-lb-subtitle">Phần thưởng được phát hành một lần mỗi tuần!</span>
            <button type="button" class="about-lb-detail-btn" onclick="openLeaderboardDetailModal()" style="color: #000000 !important;">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="#000000" style="color: #000000 !important;"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
              <span style="color: #000000 !important; font-weight: 700;">Detail</span>
            </button>
          </div>

          <!-- Podium Top 3 (NO.2 - NO.1 - NO.3) -->
          <div class="podium-stage">
            <!-- Rank 2: Koye (NO.2) -->
            <div class="podium-item rank-2">
              <div class="podium-badge badge-silver">NO.2</div>
              <div class="podium-aura aura-silver"></div>
              <div class="podium-avatar-wrap">
                <div class="podium-avatar-circle">
                  <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
                <svg class="podium-laurel" viewBox="0 0 100 36" style="color: #818cf8;">
                  <path d="M10 24C16 28 28 32 50 32C72 32 84 28 90 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                  <path d="M18 20C14 16 15 11 20 12C22 15 22 19 18 20Z" fill="currentColor"/>
                  <path d="M28 24C24 20 26 15 31 16C33 19 32 23 28 24Z" fill="currentColor"/>
                  <path d="M39 27C36 23 39 18 44 20C45 23 43 27 39 27Z" fill="currentColor"/>
                  <path d="M82 20C86 16 85 11 80 12C78 15 78 19 82 20Z" fill="currentColor"/>
                  <path d="M72 24C76 20 74 15 69 16C67 19 68 23 72 24Z" fill="currentColor"/>
                  <path d="M61 27C64 23 61 18 56 20C55 23 57 27 61 27Z" fill="currentColor"/>
                </svg>
              </div>
              <div class="podium-username">Koye</div>
              <div class="podium-team-count">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                <span>722</span>
              </div>
            </div>

            <!-- Rank 1: luann4ezz (NO.1) -->
            <div class="podium-item rank-1">
              <div class="podium-badge badge-gold">NO.1</div>
              <div class="podium-aura aura-gold"></div>
              <div class="podium-avatar-wrap">
                <div class="podium-avatar-circle">
                  <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
                <svg class="podium-laurel" viewBox="0 0 100 36" style="color: #f59e0b;">
                  <path d="M10 24C16 28 28 32 50 32C72 32 84 28 90 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                  <path d="M18 20C14 16 15 11 20 12C22 15 22 19 18 20Z" fill="currentColor"/>
                  <path d="M28 24C24 20 26 15 31 16C33 19 32 23 28 24Z" fill="currentColor"/>
                  <path d="M39 27C36 23 39 18 44 20C45 23 43 27 39 27Z" fill="currentColor"/>
                  <path d="M82 20C86 16 85 11 80 12C78 15 78 19 82 20Z" fill="currentColor"/>
                  <path d="M72 24C76 20 74 15 69 16C67 19 68 23 72 24Z" fill="currentColor"/>
                  <path d="M61 27C64 23 61 18 56 20C55 23 57 27 61 27Z" fill="currentColor"/>
                </svg>
              </div>
              <div class="podium-username">luann4ezz</div>
              <div class="podium-team-count">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                <span>725</span>
              </div>
            </div>

            <!-- Rank 3: Arsalanmax (NO.3) -->
            <div class="podium-item rank-3">
              <div class="podium-badge badge-bronze">NO.3</div>
              <div class="podium-aura aura-bronze"></div>
              <div class="podium-avatar-wrap">
                <div class="podium-avatar-circle">
                  <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
                <svg class="podium-laurel" viewBox="0 0 100 36" style="color: #fb923c;">
                  <path d="M10 24C16 28 28 32 50 32C72 32 84 28 90 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                  <path d="M18 20C14 16 15 11 20 12C22 15 22 19 18 20Z" fill="currentColor"/>
                  <path d="M28 24C24 20 26 15 31 16C33 19 32 23 28 24Z" fill="currentColor"/>
                  <path d="M39 27C36 23 39 18 44 20C45 23 43 27 39 27Z" fill="currentColor"/>
                  <path d="M82 20C86 16 85 11 80 12C78 15 78 19 82 20Z" fill="currentColor"/>
                  <path d="M72 24C76 20 74 15 69 16C67 19 68 23 72 24Z" fill="currentColor"/>
                  <path d="M61 27C64 23 61 18 56 20C55 23 57 27 61 27Z" fill="currentColor"/>
                </svg>
              </div>
              <div class="podium-username">Arsalanmax</div>
              <div class="podium-team-count">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                <span>705</span>
              </div>
            </div>
          </div>

          <!-- Danh Sách Xếp Hạng Bên Dưới (Rows) -->
          <div class="about-rank-list">
            <!-- Row 1 -->
            <div class="about-rank-row">
              <div class="about-rank-left">
                <div class="about-rank-num num-gold">1</div>
                <div class="about-rank-avatar">
                  <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
                <span class="about-rank-user">luann4ezz</span>
              </div>
              <div class="about-rank-right">
                <span class="about-rank-team-tag">👥 Team-A</span>
                <span class="about-rank-count">725</span>
              </div>
            </div>

            <!-- Row 2 -->
            <div class="about-rank-row">
              <div class="about-rank-left">
                <div class="about-rank-num num-silver">2</div>
                <div class="about-rank-avatar">
                  <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
                <span class="about-rank-user">Koye</span>
              </div>
              <div class="about-rank-right">
                <span class="about-rank-team-tag">👥 Team-A</span>
                <span class="about-rank-count">722</span>
              </div>
            </div>

            <!-- Row 3 -->
            <div class="about-rank-row">
              <div class="about-rank-left">
                <div class="about-rank-num num-bronze">3</div>
                <div class="about-rank-avatar">
                  <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
                <span class="about-rank-user">Arsalanmax</span>
              </div>
              <div class="about-rank-right">
                <span class="about-rank-team-tag">👥 Team-A</span>
                <span class="about-rank-count">705</span>
              </div>
            </div>

            <!-- Row 4 -->
            <div class="about-rank-row">
              <div class="about-rank-left">
                <div class="about-rank-num num-default">4</div>
                <div class="about-rank-avatar">
                  <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
                <span class="about-rank-user">CryptoKing_VN</span>
              </div>
              <div class="about-rank-right">
                <span class="about-rank-team-tag">👥 Team-B</span>
                <span class="about-rank-count">612</span>
              </div>
            </div>

            <!-- Row 5 -->
            <div class="about-rank-row">
              <div class="about-rank-left">
                <div class="about-rank-num num-default">5</div>
                <div class="about-rank-avatar">
                  <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
                <span class="about-rank-user">ThoDao_VIP</span>
              </div>
              <div class="about-rank-right">
                <span class="about-rank-team-tag">👥 Team-C</span>
                <span class="about-rank-count">489</span>
              </div>
            </div>
          </div>
        </div>
      </div> <!-- /aboutMainView -->

      <!-- 2. MÀN HÌNH SWAP SUPPER ❯ USDT (CHUẨN ẢNH 2) -->
      <div id="aboutSwapView" class="supper-swap-container" style="display: none;">
        <!-- Header: Nút back ← + SUPPER ❯ USDT -->
        <div class="supper-swap-header">
          <button type="button" class="supper-swap-back-btn" onclick="closeSupperUsdtSwapView()" aria-label="Quay lại">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="19" y1="12" x2="5" y2="12"></line>
              <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
          </button>
          <div class="supper-swap-header-title">
            <span>SUPPER</span>
            <div class="supper-swap-arrow-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="9 6 15 12 9 18"></polyline>
              </svg>
            </div>
            <span>USDT</span>
          </div>
          <div style="width: 44px;"></div>
        </div>

        <!-- Thẻ 1: You Pay (SUPPER) -->
        <div class="supper-swap-card">
          <div class="supper-swap-row-top">
            <span class="supper-swap-avail-bal" id="supperSwapAvailableBal">1555.582095</span>
            <span class="supper-swap-cur-name">SUPPER</span>
          </div>
          <div class="supper-swap-row-sub">
            <span class="supper-swap-label">You Pay</span>
            <span class="supper-swap-rate" id="supperSwapRateDisplay">SUPPER ≈ 0.00 USDT</span>
          </div>
          <div class="supper-swap-input-row">
            <input type="number" id="supperSwapInput" class="supper-swap-input" placeholder="0" step="any" oninput="calculateSupperToUsdt()">
            <button type="button" class="supper-all-btn" onclick="setSupperSwapAll()">ALL</button>
          </div>
        </div>

        <!-- Nút chuyển đổi mũi tên trỏ xuống ở giữa 2 thẻ -->
        <div class="supper-swap-center-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </div>

        <!-- Thẻ 2: You Get (USDT) -->
        <div class="supper-swap-card" style="margin-top: -10px;">
          <div class="supper-swap-row-sub" style="margin-bottom: 8px;">
            <span class="supper-swap-label">You Get</span>
          </div>
          <div class="supper-swap-row-top" style="margin-bottom: 0;">
            <span class="supper-swap-receive-val" id="supperSwapReceiveUsdt">0.000000</span>
            <span class="supper-swap-cur-name">USDT</span>
          </div>
        </div>

        <!-- Nút Xác nhận Swap (Màu kem cam ấm chuẩn ảnh) -->
        <button type="button" id="btnConfirmSupperSwap" onclick="executeSupperSwap()" class="supper-confirm-swap-btn">
          xác nhận
        </button>
      </div> <!-- /aboutSwapView -->
    </section>


    <!-- ==================== TAB 6: ADMIN ==================== -->
    <section id="page-admin" class="tab-page" style="display: none;">
      <div style="margin-bottom: 20px;">
        <h1 style="font-size: 1.8rem; font-weight: 800; color: #fbbf24;">Bảng Quản Trị Hệ Thống (Admin)</h1>
        <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 3px;">
          Duyệt nạp/rút, quản lý thành viên, điều chỉnh tỷ giá Coin/USDT và danh mục máy đào.
        </p>
      </div>

      <!-- Overview stats -->
      <div class="dashboard-grid" style="margin-bottom: 20px;">
        <div class="glass-card stat-card col-3">
          <div>
            <span class="stat-label">Tổng Người Dùng</span>
            <div class="stat-val" id="admTotalUsers">0</div>
          </div>
        </div>
        <div class="glass-card stat-card col-3">
          <div>
            <span class="stat-label">Lệnh Chờ Duyệt</span>
            <div class="stat-val" style="color: #fbbf24;" id="admPendingCount">0</div>
          </div>
        </div>
        <div class="glass-card stat-card col-3">
          <div>
            <span class="stat-label">Tổng Máy Hoạt Động</span>
            <div class="stat-val" style="color: #fbbf24;" id="admActiveMiners">0</div>
          </div>
        </div>
        <div class="glass-card stat-card col-3">
          <div>
            <span class="stat-label">Tỷ Giá Coin Hiện Tại</span>
            <div class="stat-val" style="color: #fbbf24;" id="admCoinPrice">$0.20</div>
          </div>
        </div>
      </div>

      <!-- Admin Pending Transactions -->
      <div class="glass-card" style="margin-bottom: 20px; padding: 0;">
        <div style="padding: 14px 18px; border-bottom: 1px solid var(--border-subtle);">
          <h3 style="font-size: 1.1rem; font-weight: 700;">Hàng Đợi Phê Duyệt Nạp / Rút</h3>
        </div>
        <div class="table-wrapper">
          <table class="custom-table">
            <thead>
              <tr>
                <th>Mã Lệnh</th>
                <th>User ID</th>
                <th>Loại</th>
                <th>Số Lượng</th>
                <th>Ví / TxHash</th>
                <th>Thời Gian</th>
                <th>Trạng Thái</th>
                <th>Thao Tác</th>
              </tr>
            </thead>
            <tbody id="admTxTableBody">
              <!-- Rendered via JS -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Admin Settings & Rates -->
      <div class="glass-card" style="margin-bottom: 20px;">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 14px;">Cập Nhật Cấu Hình Tỷ Giá & Tham Số Ví</h3>
        <form id="adminSettingsForm" style="max-width: 600px;">
          <div class="form-group">
            <label class="form-label">Tỷ giá Coin sang USDT ($)</label>
            <input type="number" step="0.001" id="admSetCoinPrice" class="form-input" required>
          </div>
          <div class="form-group">
            <label class="form-label">Địa chỉ ví nhận tiền Nạp USDT của Hệ thống</label>
            <input type="text" id="admSetDepositAddress" class="form-input" required>
          </div>
          <div class="form-group">
            <label class="form-label">Mạng lưới ví nạp</label>
            <input type="text" id="admSetNetwork" class="form-input" required>
          </div>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="form-group">
              <label class="form-label">Mức nạp tối thiểu (USDT)</label>
              <input type="number" step="any" id="admSetMinDep" class="form-input" required>
            </div>
            <div class="form-group">
              <label class="form-label">Mức rút tối thiểu (USDT)</label>
              <input type="number" step="any" id="admSetMinWd" class="form-input" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Phí rút tiền (%)</label>
            <input type="number" step="0.1" id="admSetWdFee" class="form-input" required>
          </div>
          <button type="submit" class="btn btn-primary" style="margin-top: 8px;">Lưu Cấu Hình</button>
        </form>
      </div>

      <!-- Admin Users List -->
      <div class="glass-card" style="margin-bottom: 20px; padding: 0;">
        <div style="padding: 14px 18px; border-bottom: 1px solid var(--border-subtle);">
          <h3 style="font-size: 1.1rem; font-weight: 700;">Danh Sách Người Dùng & Số Dư</h3>
        </div>
        <div class="table-wrapper">
          <table class="custom-table">
            <thead>
              <tr>
                <th>Tên & Email</th>
                <th>Vai Trò</th>
                <th>Số Dư USDT</th>
                <th>Số Dư Coin</th>
                <th>Số Máy</th>
                <th>Hashrate</th>
                <th>Thao Tác</th>
              </tr>
            </thead>
            <tbody id="admUsersTableBody">
              <!-- Rendered via JS -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Admin Miners Catalog -->
      <div class="glass-card" style="padding: 0;">
        <div style="padding: 14px 18px; border-bottom: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center;">
          <h3 style="font-size: 1.1rem; font-weight: 700;">Danh Mục Gói Máy Đào</h3>
          <button class="btn btn-primary btn-sm" onclick="openModal('addMinerModal')">+ Thêm Gói Mới</button>
        </div>
        <div class="table-wrapper">
          <table class="custom-table">
            <thead>
              <tr>
                <th>Tên Máy</th>
                <th>Phân Khúc</th>
                <th>Hashrate</th>
                <th>Giá USDT</th>
                <th>Sản Lượng/Ngày</th>
                <th>Điện Năng</th>
                <th>Trạng Thái</th>
              </tr>
            </thead>
            <tbody id="admMinersTableBody">
              <!-- Rendered via JS -->
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- ==================== TAB 6: TERMS & DISCLAIMER ==================== -->
    <section id="page-terms" class="tab-page" style="display: none; max-width: 860px; margin: 0 auto;">
      <div style="text-align: center; margin-bottom: 24px;">
        <h1 style="font-size: 1.9rem; font-weight: 800;">Điều Khoản Sử Dụng & Miễn Trừ Trách Nhiệm</h1>
        <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
          Quy định dịch vụ và khuyến cáo an toàn khi tham gia MINEX Cloud Mining.
        </p>
      </div>

      <div class="alert alert-warning" style="padding: 16px; margin-bottom: 20px;">
        <div>
          <strong style="display: block; margin-bottom: 3px;">CẢNH BÁO RỦI RO ĐẦU TƯ TÀI SẢN SỐ</strong>
          Thị trường tiền mã hóa có tính biến động cao. Giá trị đồng coin có thể biến động lớn theo thị trường. Người dùng tự chịu trách nhiệm đối với các quyết định tài chính của mình và không nên đầu tư số tiền vượt quá khả năng chấp nhận rủi ro.
        </div>
      </div>

      <div class="glass-card" style="margin-bottom: 20px;">
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 12px;">1. Điều Khoản Dịch Vụ</h2>
        <div style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.7; display: flex; flex-direction: column; gap: 10px;">
          <p><strong style="color:#fff;">1.1. Tài khoản:</strong> Người dùng có trách nhiệm tự bảo mật tài khoản đăng nhập của mình.</p>
          <p><strong style="color:#fff;">1.2. Máy đào ảo:</strong> Khi mua máy đào bằng USDT, hệ thống sẽ cấp quyền sức mạnh băm hashrate. Lợi nhuận đồng Coin dự án (<span class="coin-symbol">SUPPER</span>) được tính toán theo thuật toán thời gian thực và người dùng có thể nhận về ví bất cứ lúc nào.</p>
          <p><strong style="color:#fff;">1.3. Nạp & Rút:</strong> Tiền nạp và rút phải tuân thủ mạng lưới hỗ trợ (TRC20). Các lệnh rút được kiểm duyệt phòng chống gian lận.</p>
          <p><strong style="color:#fff;">1.4. Quy đổi Hoán đổi (Swap):</strong> Cung cấp tính năng hoán đổi Coin dự án sang USDT theo tỷ giá thị trường niêm yết.</p>
        </div>
      </div>

      <div class="glass-card">
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 12px; color: #fb7185;">2. Tuyên Bố Miễn Trừ Trách Nhiệm</h2>
        <div style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.7; display: flex; flex-direction: column; gap: 10px;">
          <p><strong style="color:#fff;">2.1. Bản chất dịch vụ:</strong> Hệ thống hoạt động dựa trên mô hình khai thác ảo đám mây mô phỏng sức mạnh tính toán.</p>
          <p><strong style="color:#fff;">2.2. Không phải tư vấn tài chính:</strong> Toàn bộ số liệu hiển thị mang tính tham khảo kỹ thuật, không cấu thành lời khuyên đầu tư tài chính.</p>
          <p><strong style="color:#fff;">2.3. Trách nhiệm người dùng:</strong> Ban quản trị không chịu trách nhiệm trong trường hợp người dùng nhập sai địa chỉ ví nhận USDT hoặc các sự cố bất khả kháng về mạng lưới blockchain.</p>
        </div>
      </div>
    </section>

  </main>

</div> <!-- /iphoneContentScroll -->

      <!-- 3. Bottom Fixed Bar: Bottom Navigation + iOS Home Indicator -->
      <div id="iphoneBottomBar" class="iphone-bottom-bar">
        <!-- Mobile Bottom Navigation Bar -->
        <nav class="bottom-nav">
          <button class="bottom-nav-btn active" data-tab="dashboard">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            <span data-i18n="nav_dashboard">Dashboard</span>
          </button>
          <button class="bottom-nav-btn" data-tab="store">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <span data-i18n="nav_store_short">Máy Đào</span>
          </button>
          <button class="bottom-nav-btn" data-tab="wallet">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M7 15h0M2 10h20"/></svg>
            <span data-i18n="nav_wallet">Ví & Swap</span>
          </button>
          <button class="bottom-nav-btn" data-tab="about">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span data-i18n="nav_about">Giới Thiệu</span>
          </button>
          <button class="bottom-nav-btn admin-only" data-tab="admin" style="display: none; color: #fbbf24;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span data-i18n="nav_admin_short">Admin</span>
          </button>
        </nav>
        <!-- iOS Home Indicator -->
        <div id="iosHomeIndicator" class="ios-home-indicator"></div>
      </div> <!-- /iphoneBottomBar -->

    </div> <!-- /iphoneScreen -->
  </div> <!-- /iphoneChassis -->
  </div> <!-- /iphoneScaleContainer -->
</div> <!-- /deviceWrapper -->

<!-- ==================== MODALS ==================== -->

<!-- Modal 0: Mua Máy Đào Chuẩn 100% Theo Ảnh 2 -->
<div id="buyMinerModal" class="modal-overlay" style="z-index: 10005;">
  <div class="modal-content svip-modal-box" style="position: relative; max-width: 400px; width: 92%; padding: 26px 20px 22px; border-radius: 20px; background: #111219; border: 1px solid rgba(255, 255, 255, 0.14); box-shadow: 0 20px 50px rgba(0,0,0,0.92); box-sizing: border-box;">
    
    <!-- Nút Đóng (X) ở góc trên bên phải -->
    <button type="button" class="svip-modal-close" onclick="closeModal('buyMinerModal')" aria-label="Close">
      &times;
    </button>

    <!-- Ảnh Chip máy đào ở giữa với huy hiệu kim cương vàng -->
    <div style="display: flex; justify-content: center; margin-top: 6px; margin-bottom: 14px;">
      <div style="position: relative; width: 105px; height: 105px; border-radius: 16px; overflow: hidden; background: #000; border: 1.5px solid rgba(255,255,255,0.12); box-shadow: 0 8px 24px rgba(0,0,0,0.6);">
        <img id="modalMinerImg" src="assets/images/svip_chip_clean.png" alt="Chip SVIP" style="width: 100%; height: 100%; object-fit: cover;">
        <!-- Huy hiệu kim cương vàng góc trên bên trái của chip -->
        <div style="position: absolute; top: -1px; left: -1px; background: linear-gradient(135deg, #f59e0b, #d97706); width: 26px; height: 26px; border-radius: 0 0 14px 0; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; box-shadow: 0 2px 6px rgba(0,0,0,0.4);">
          💎
        </div>
      </div>
    </div>

    <!-- Tên máy đào: SVIP -->
    <h3 id="modalMinerTitle" style="font-size: 1.35rem; font-weight: 700; color: #fff; text-align: center; margin: 0 0 6px;">
      SVIP
    </h3>

    <!-- Mô tả tiếng Anh chuẩn theo ảnh: Speed up the production... -->
    <p style="font-size: 0.82rem; color: #8a8793; text-align: center; margin: 0 0 18px; line-height: 1.45; padding: 0 8px;">
      Speed up the production of mining machines and improve work efficiency.
    </p>

    <!-- Hàng 1: Daily Output -->
    <div class="svip-modal-row">
      <span class="svip-modal-label">Daily Output</span>
      <span id="modalMinerDailyOutput" class="svip-modal-val">6000 SUPPER</span>
    </div>

    <!-- Hàng 2: Price -->
    <div class="svip-modal-row">
      <span class="svip-modal-label">Price</span>
      <span id="modalMinerPrice" class="svip-modal-val">10 USDT</span>
    </div>

    <!-- Hàng 3: Multiples (Chọn số lượng) -->
    <div class="svip-modal-row" style="border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 20px;">
      <span class="svip-modal-label">Multiples</span>
      <div style="display: flex; align-items: center; gap: 8px;">
        <button type="button" class="svip-qty-btn" onclick="stepSvipQuantity(-1)">−</button>
        <input type="number" id="buyModalQty" value="1" min="1" max="1000" oninput="onSvipQuantityChange()" class="svip-qty-input">
        <button type="button" class="svip-qty-btn" onclick="stepSvipQuantity(1)">+</button>
      </div>
    </div>

    <!-- Nút Mua sắm (hồng phấn/be sang trọng #ead1bf) -->
    <button type="button" id="btnConfirmSvipBuy" class="btn-store-buy" onclick="executeBuyMiner()" style="margin-bottom: 14px; color: #000000 !important;">
      Mua sắm
    </button>

    <!-- Chú thích chân trang: Mining results are harvested every 24 hours -->
    <div style="font-size: 0.78rem; color: #8a8793; text-align: center;">
      Mining results are harvested every 24 hours.
    </div>

  </div>
</div>

<!-- Modal 1: Auth Modal -->
<div id="authModal" class="modal-overlay">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="authModalTitle" style="font-size: 1.25rem; font-weight: 700;">Đăng Nhập Tài Khoản</h3>
      <button class="modal-close" onclick="closeModal('authModal')">&times;</button>
    </div>

    <!-- Quick Demo Login Box -->
    <div style="background: rgba(245,158,11,0.08); border: 1px dashed rgba(245,158,11,0.35); border-radius: 12px; padding: 12px 14px; margin: 14px 0 16px;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <span style="font-size: 0.78rem; font-weight: 700; color: #fbbf24; text-transform: uppercase;">⚡ 1-Click Trải Nghiệm Demo</span>
        <span style="font-size: 0.72rem; color: #38bdf8; font-family: var(--font-mono); font-weight: 700;">UID: 120850</span>
      </div>
      <div style="display: flex; gap: 8px;">
        <button type="button" class="btn btn-primary btn-sm" onclick="quickLogin('user'); closeModal('authModal');" style="flex: 1; font-weight: 700; font-size: 0.82rem; padding: 8px;">👤 Demo (UID: 120850)</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="quickLogin('admin'); closeModal('authModal');" style="flex: 1; font-weight: 700; font-size: 0.82rem; padding: 8px; border-color: rgba(245,158,11,0.35); color: #fbbf24;">🛡️ Admin (UID: 10001)</button>
      </div>
    </div>

    <!-- Login Form -->
    <form id="loginForm">
      <div class="form-group">
        <label class="form-label">Địa chỉ Email</label>
        <input type="email" id="loginEmail" class="form-input" placeholder="miner@example.com" required>
      </div>
      <div class="form-group">
        <label class="form-label">Mật khẩu</label>
        <input type="password" id="loginPassword" class="form-input" placeholder="••••••••" required>
      </div>

      <!-- Dấu tích đồng ý Điều khoản trong Đăng nhập -->
      <div style="display: flex; align-items: flex-start; gap: 8px; margin: 12px 0 16px; font-size: 0.82rem; color: var(--text-muted);">
        <input type="checkbox" id="loginAgreeTerms" style="margin-top: 2px; accent-color: var(--primary); width: 16px; height: 16px; cursor: pointer;" checked required>
        <label for="loginAgreeTerms" style="cursor: pointer; line-height: 1.4;">
          Tôi đồng ý với <a href="#" onclick="openTermsModal(event)" style="color: var(--primary); text-decoration: underline;">Điều khoản dịch vụ & Tuyên bố miễn trừ trách nhiệm</a>.
        </label>
      </div>

      <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
        Đăng Nhập
      </button>
      <div style="text-align: center; margin-top: 14px; font-size: 0.85rem; color: var(--text-muted);">
        Chưa có tài khoản? <a href="#" id="linkToRegister" style="font-weight: 600;">Đăng ký nhận 100 USDT</a>
      </div>
    </form>

    <!-- Register Form -->
    <form id="registerForm" style="display: none;">
      <div class="form-group">
        <label class="form-label">Họ và tên thợ đào</label>
        <input type="text" id="regName" class="form-input" placeholder="Nguyễn Văn A">
      </div>
      <div class="form-group">
        <label class="form-label">Địa chỉ Email</label>
        <input type="email" id="regEmail" class="form-input" placeholder="miner@example.com" required>
      </div>
      <div class="form-group">
        <label class="form-label">Mật khẩu</label>
        <input type="password" id="regPassword" class="form-input" placeholder="Tối thiểu 6 ký tự" required>
      </div>
      <div class="form-group">
        <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
          <span>Mã Người Giới Thiệu (Tuyến Trên)</span>
          <span style="font-size: 0.72rem; color: #fbbf24; font-weight: 600;">(Không bắt buộc)</span>
        </label>
        <input type="text" id="regRefCode" class="form-input" placeholder="Nhập UID tuyến trên (ví dụ: 120850)" style="font-family: var(--font-mono); letter-spacing: 0.05em;">
      </div>

      <!-- Dấu tích đồng ý Điều khoản trong Đăng ký -->
      <div style="display: flex; align-items: flex-start; gap: 8px; margin: 12px 0 16px; font-size: 0.82rem; color: var(--text-muted);">
        <input type="checkbox" id="regAgreeTerms" style="margin-top: 2px; accent-color: var(--primary); width: 16px; height: 16px; cursor: pointer;" checked required>
        <label for="regAgreeTerms" style="cursor: pointer; line-height: 1.4;">
          Tôi đã đọc và đồng ý với <a href="#" onclick="openTermsModal(event)" style="color: var(--primary); text-decoration: underline;">Điều khoản sử dụng & Tuyên bố miễn trừ rủi ro</a>.
        </label>
      </div>

      <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
        Đăng Ký & Nhận 100 USDT
      </button>
      <div style="text-align: center; margin-top: 14px; font-size: 0.85rem; color: var(--text-muted);">
        Đã có tài khoản? <a href="#" id="linkToLogin" style="font-weight: 600;">Đăng nhập tại đây</a>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Terms of Service & Disclaimer Popup -->
<div id="termsModal" class="modal-overlay" style="z-index: 10000;">
  <div class="modal-content" style="max-width: 620px;">
    <div class="modal-header">
      <h3 style="font-size: 1.25rem; font-weight: 700;">Điều Khoản Sử Dụng & Miễn Trừ Trách Nhiệm</h3>
      <button class="modal-close" onclick="closeModal('termsModal')">&times;</button>
    </div>
    
    <div class="alert alert-warning" style="margin-bottom: 16px; font-size: 0.82rem; padding: 10px 12px;">
      <strong>⚠️ CẢNH BÁO RỦI RO:</strong> Thị trường tiền kỹ thuật số và dịch vụ đào ảo đám mây có tính biến động cao. Người dùng tự chịu trách nhiệm đối với quyết định tài chính của mình.
    </div>

    <div style="max-height: 55vh; overflow-y: auto; padding-right: 6px; font-size: 0.86rem; color: var(--text-muted); line-height: 1.65; display: flex; flex-direction: column; gap: 12px;">
      <div>
        <strong style="color: #fff; font-size: 0.92rem;">1. Điều Khoản Dịch Vụ Nền Tảng</strong>
        <p style="margin-top: 4px;">1.1. Người dùng có nghĩa vụ tự bảo mật thông tin tài khoản và mật khẩu.</p>
        <p>1.2. Mua máy đào bằng USDT cấp quyền hashrate mô phỏng. Sản lượng coin (SUPPER) được tích lũy theo thời gian thực và người dùng có thể nhận thưởng (claim) về ví.</p>
        <p>1.3. Nạp USDT chỉ chấp nhận mạng lưới quy định (TRC20). Mọi yêu cầu rút USDT đều được xét duyệt phòng chống gian lận.</p>
        <p>1.4. Tính năng hoán đổi Swap hỗ trợ quy đổi Coin sang USDT ngay lập tức theo tỷ giá niêm yết.</p>
      </div>
      <div>
        <strong style="color: #fb7185; font-size: 0.92rem;">2. Tuyên Bố Miễn Trừ Trách Nhiệm</strong>
        <p style="margin-top: 4px;">2.1. Nền tảng hoạt động theo mô hình mô phỏng công suất đào ảo đám mây.</p>
        <p>2.2. Toàn bộ thông tin số liệu mang tính tham khảo kỹ thuật, không cấu thành lời khuyên đầu tư tài chính.</p>
        <p>2.3. Ban quản trị không chịu trách nhiệm trong trường hợp người dùng nhập sai địa chỉ ví nhận tiền khi rút.</p>
      </div>
    </div>

    <button class="btn btn-primary" style="width: 100%; margin-top: 18px;" onclick="closeModal('termsModal')" data-i18n="terms_modal_agree">Tôi Đã Hiểu Và Đồng Ý</button>
  </div>
</div>

<!-- Modal: Lịch Sử Giao Dịch & Hoá Đơn Chuẩn 100% Theo Ảnh -->
<div id="historyModal" class="modal-overlay" style="z-index: 10000;">
  <div class="modal-content invoice-modal-content" style="position: relative;">
    <!-- Thanh điều hướng trên cùng: Nút quay lại + Tiêu đề "Số dư USDT" + Nút lọc -->
    <div class="invoice-header">
      <button type="button" class="invoice-back-btn" onclick="closeModal('historyModal')" aria-label="Đóng">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"></line>
          <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
      </button>
      <div class="invoice-header-title">Số dư USDT</div>
      <div class="invoice-filter-wrapper">
        <button type="button" class="invoice-filter-btn" id="modalInvoiceFilterTriggerBtn" onclick="toggleModalInvoiceFilter(event)" aria-label="Bộ lọc">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 6h16M7 12h10M10 18h4"/>
          </svg>
        </button>
        <div id="modalInvoiceFilterMenu" class="invoice-filter-dropdown" style="display: none;">
          <button type="button" class="filter-opt active" data-filter="all" onclick="selectModalInvoiceFilter('all')">Tất cả</button>
          <button type="button" class="filter-opt" data-filter="deposit" onclick="selectModalInvoiceFilter('deposit')">Nạp tiền</button>
          <button type="button" class="filter-opt" data-filter="commission" onclick="selectModalInvoiceFilter('commission')">Phần thưởng hoa hồng</button>
          <button type="button" class="filter-opt" data-filter="withdraw" onclick="selectModalInvoiceFilter('withdraw')">Rút tiền</button>
          <button type="button" class="filter-opt" data-filter="buy_miner" onclick="selectModalInvoiceFilter('buy_miner')">Mua gói máy đào</button>
          <button type="button" class="filter-opt" data-filter="swap" onclick="selectModalInvoiceFilter('swap')">Hoán đổi sang USDT</button>
        </div>
      </div>
    </div>

    <!-- Khối Số dư to ở giữa + Phụ đề Lịch sử giao dịch chuẩn theo ảnh -->
    <div class="invoice-balance-section">
      <div class="invoice-balance-number" id="modalInvoiceUsdtBalance">30.62</div>
      <div class="invoice-balance-subtitle">Lịch sử giao dịch</div>
    </div>

    <!-- Đường kẻ ngang mảnh -->
    <div class="invoice-divider"></div>

    <!-- Danh sách giao dịch động chuẩn ảnh -->
    <div id="modalInvoiceTransactionList" class="invoice-tx-list">
      <!-- Rendered via JS -->
    </div>
  </div>
</div>

<!-- Modal 2: Adjust User Balance (Admin) -->
<div id="adjustBalanceModal" class="modal-overlay">
  <div class="modal-content">
    <div class="modal-header">
      <h3 style="font-size: 1.2rem; font-weight: 700;">Điều Chỉnh Số Dư</h3>
      <button class="modal-close" onclick="closeModal('adjustBalanceModal')">&times;</button>
    </div>
    <p style="font-size: 0.84rem; color: var(--text-muted); margin-bottom: 14px;">
      Tài khoản: <strong id="adjUserEmail" style="color: #fff;"></strong>
    </p>
    <form id="adjustBalanceForm">
      <input type="hidden" id="adjUserId">
      <div class="form-group">
        <label class="form-label">Thay đổi số dư USDT (+ để cộng, - để trừ)</label>
        <input type="number" step="any" id="adjUsdtAmount" class="form-input" placeholder="VD: 50 hoặc -20">
      </div>
      <div class="form-group">
        <label class="form-label">Thay đổi số dư Coin (+ để cộng, - để trừ)</label>
        <input type="number" step="any" id="adjCoinAmount" class="form-input" placeholder="VD: 100 hoặc -50">
      </div>
      <div class="form-group">
        <label class="form-label">Lý do điều chỉnh</label>
        <input type="text" id="adjReason" class="form-input" placeholder="Thưởng sự kiện, bù mạng..." required>
      </div>
      <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
        Xác Nhận Cập Nhật
      </button>
    </form>
  </div>
</div>

<!-- Modal 3: Add Miner (Admin) -->
<div id="addMinerModal" class="modal-overlay">
  <div class="modal-content">
    <div class="modal-header">
      <h3 style="font-size: 1.2rem; font-weight: 700;">Thêm Gói Máy Đào Mới</h3>
      <button class="modal-close" onclick="closeModal('addMinerModal')">&times;</button>
    </div>
    <form id="addMinerForm">
      <div class="form-group">
        <label class="form-label">Tên máy đào</label>
        <input type="text" id="newMinerName" class="form-input" placeholder="VD: Avalon Hydro Rig" required>
      </div>
      <div class="form-group">
        <label class="form-label">Phân khúc (Tier)</label>
        <input type="text" id="newMinerTier" class="form-input" value="Enterprise Rig">
      </div>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
        <div class="form-group">
          <label class="form-label">Hashrate (TH/s)</label>
          <input type="number" step="any" id="newMinerHashrate" class="form-input" value="600" required>
        </div>
        <div class="form-group">
          <label class="form-label">Giá bán (USDT)</label>
          <input type="number" step="any" id="newMinerPrice" class="form-input" value="250" required>
        </div>
      </div>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
        <div class="form-group">
          <label class="form-label">Sản lượng (Coin/ngày)</label>
          <input type="number" step="any" id="newMinerDaily" class="form-input" value="85" required>
        </div>
        <div class="form-group">
          <label class="form-label">Điện năng</label>
          <input type="text" id="newMinerPower" class="form-input" value="500W">
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
        Thêm Vào Cửa Hàng
      </button>
    </form>
  </div>
</div>

<!-- Modal: Nhóm & Đội ngũ -->
<div id="teamModal" class="modal-overlay" style="z-index: 10005;">
  <div class="modal-content" style="max-width: 440px; padding: 24px 20px; border-radius: 20px; background: #111219; border: 1px solid rgba(255, 255, 255, 0.14); box-shadow: 0 20px 50px rgba(0,0,0,0.92);">
    <div class="modal-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
      <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
        <span>👥</span> Đội Ngũ & Giới Thiệu
      </h3>
      <button class="modal-close" onclick="closeModal('teamModal')" style="background: none; border: none; color: #8e8c94; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>
    <div style="background: rgba(223, 197, 178, 0.08); border: 1px solid rgba(223, 197, 178, 0.25); border-radius: 14px; padding: 14px; margin-bottom: 18px;">
      <div style="font-size: 0.82rem; color: #dfc5b2; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Chương trình hoa hồng tuyến trên</div>
      <div style="font-size: 0.9rem; color: #ffffff; line-height: 1.4;">Nhận ngay <strong style="color: #34d399;">10% hoa hồng trực tiếp USDT</strong> cho mỗi gói máy đào thành viên F1 kích hoạt!</div>
    </div>
    <div style="margin-bottom: 16px;">
      <label style="display: block; font-size: 0.8rem; color: #8e8c94; margin-bottom: 6px;">Mã Giới Thiệu Của Bạn</label>
      <div style="display: flex; gap: 8px;">
        <input type="text" id="teamModalUidInput" readonly class="form-input" style="font-weight: 800; font-family: var(--font-mono); letter-spacing: 0.05em; background: #1a1c26; color: #fbbf24;" value="120850">
        <button type="button" class="btn btn-secondary" onclick="copyReferralCode()" style="padding: 0 16px;">Sao Chép</button>
      </div>
    </div>
    <div style="margin-bottom: 20px;">
      <label style="display: block; font-size: 0.8rem; color: #8e8c94; margin-bottom: 6px;">Liên Kết Giới Thiệu (Telegram & Web)</label>
      <div style="display: flex; gap: 8px;">
        <input type="text" id="teamModalLinkInput" readonly class="form-input" style="font-size: 0.8rem; background: #1a1c26; color: #38bdf8;" value="https://t.me/SUPPERAI5_BOT/app?startapp=120850">
        <button type="button" class="btn btn-secondary" onclick="copyReferralLink()" style="padding: 0 14px;">Copy</button>
      </div>
    </div>
    <button type="button" class="btn btn-primary" onclick="shareReferralLink()" style="width: 100%; height: 46px; font-weight: 700; background: #dfc5b2; color: #1e1b18; border: none; border-radius: 12px; font-size: 0.95rem;">
      ✈️ Chia Sẻ Cho Bạn Bè Ngay
    </button>
  </div>
</div>

<!-- Modal: Nhiệm Vụ Hàng Ngày -->
<div id="tasksModal" class="modal-overlay" style="z-index: 10005;">
  <div class="modal-content" style="max-width: 440px; padding: 24px 20px; border-radius: 20px; background: #111219; border: 1px solid rgba(255, 255, 255, 0.14); box-shadow: 0 20px 50px rgba(0,0,0,0.92);">
    <div class="modal-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
      <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
        <span>📋</span> Nhiệm Vụ Hàng Ngày
      </h3>
      <button class="modal-close" onclick="closeModal('tasksModal')" style="background: none; border: none; color: #8e8c94; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>
    <div style="display: flex; flex-direction: column; gap: 10px; max-height: 380px; overflow-y: auto;">
      <div style="background: #171822; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <div style="font-size: 0.9rem; font-weight: 600; color: #fff;">Điểm danh hàng ngày</div>
          <div style="font-size: 0.78rem; color: #dfc5b2; font-weight: 600;">+50 SUPPER</div>
        </div>
        <button type="button" class="btn btn-sm btn-primary" onclick="claimTaskReward('daily', 50)" style="background: #dfc5b2; color: #1e1b18; border: none; font-weight: 700;">Nhận</button>
      </div>
      <div style="background: #171822; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <div style="font-size: 0.9rem; font-weight: 600; color: #fff;">Gia nhập @SUPPERAI5_BOT Telegram</div>
          <div style="font-size: 0.78rem; color: #dfc5b2; font-weight: 600;">+200 SUPPER</div>
        </div>
        <button type="button" class="btn btn-sm btn-secondary" onclick="window.open('https://t.me/SUPPERAI5_BOT', '_blank')" style="font-weight: 600;">Tham gia</button>
      </div>
      <div style="background: #171822; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <div style="font-size: 0.9rem; font-weight: 600; color: #fff;">Mời 1 thợ đào tham gia đội</div>
          <div style="font-size: 0.78rem; color: #dfc5b2; font-weight: 600;">+500 SUPPER</div>
        </div>
        <button type="button" class="btn btn-sm btn-secondary" onclick="closeModal('tasksModal'); openTeamModal();" style="font-weight: 600;">Mời</button>
      </div>
      <div style="background: #171822; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <div style="font-size: 0.9rem; font-weight: 600; color: #fff;">Sở hữu gói đào SVIP 6000 SUPPER</div>
          <div style="font-size: 0.78rem; color: #dfc5b2; font-weight: 600;">+1,000 SUPPER</div>
        </div>
        <button type="button" class="btn btn-sm btn-primary" onclick="closeModal('tasksModal'); openSvipBuyModal('miner_svip');" style="background: #dfc5b2; color: #1e1b18; border: none; font-weight: 700;">Nâng cấp</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Hướng Dẫn Khai Thác -->
<div id="guideModal" class="modal-overlay" style="z-index: 10005;">
  <div class="modal-content" style="max-width: 440px; padding: 24px 20px; border-radius: 20px; background: #111219; border: 1px solid rgba(255, 255, 255, 0.14); box-shadow: 0 20px 50px rgba(0,0,0,0.92);">
    <div class="modal-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
      <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
        <span>📖</span> Hướng Dẫn Khai Thác
      </h3>
      <button class="modal-close" onclick="closeModal('guideModal')" style="background: none; border: none; color: #8e8c94; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>
    <div style="display: flex; flex-direction: column; gap: 14px; font-size: 0.88rem; color: #d1cfd8; line-height: 1.5;">
      <div style="display: flex; gap: 12px; align-items: flex-start;">
        <span style="width: 26px; height: 26px; border-radius: 50%; background: #dfc5b2; color: #1e1b18; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.85rem;">1</span>
        <div><strong style="color: #fff;">Kích hoạt máy đào:</strong> Nhận ngay gói SVIP khởi nghiệp với tốc độ sản lượng đào SUPPER mỗi giây liên tục.</div>
      </div>
      <div style="display: flex; gap: 12px; align-items: flex-start;">
        <span style="width: 26px; height: 26px; border-radius: 50%; background: #dfc5b2; color: #1e1b18; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.85rem;">2</span>
        <div><strong style="color: #fff;">Bấm CLAIM mỗi 24 giờ:</strong> Sau 24h đầy phiên đào, bấm "CLAIM" để thu thập SUPPER về ví và tiếp tục phiên đào kế tiếp.</div>
      </div>
      <div style="display: flex; gap: 12px; align-items: flex-start;">
        <span style="width: 26px; height: 26px; border-radius: 50%; background: #dfc5b2; color: #1e1b18; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.85rem;">3</span>
        <div><strong style="color: #fff;">Quy đổi SUPPER sang USDT:</strong> Nhấp vào nút "SUPPER&USDT" ở trang Giới Thiệu để đổi ngay coin đào được ra USDT.</div>
      </div>
      <div style="display: flex; gap: 12px; align-items: flex-start;">
        <span style="width: 26px; height: 26px; border-radius: 50%; background: #dfc5b2; color: #1e1b18; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.85rem;">4</span>
        <div><strong style="color: #fff;">Rút tiền siêu tốc:</strong> Rút USDT về địa chỉ ví TRC20/BEP20 với thời gian xử lý nhanh chóng và minh bạch.</div>
      </div>
    </div>
    <button type="button" class="btn btn-primary" onclick="closeModal('guideModal')" style="width: 100%; margin-top: 20px; background: #dfc5b2; color: #1e1b18; border: none; font-weight: 700; border-radius: 12px; height: 44px;">
      Đã Hiểu
    </button>
  </div>
</div>

<!-- Modal: Lịch Trình Phát Triển (Roadmap) -->
<div id="roadmapModal" class="modal-overlay" style="z-index: 10005;">
  <div class="modal-content" style="max-width: 440px; padding: 24px 20px; border-radius: 20px; background: #111219; border: 1px solid rgba(255, 255, 255, 0.14); box-shadow: 0 20px 50px rgba(0,0,0,0.92);">
    <div class="modal-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
      <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
        <span>⏱️</span> Lịch Trình Phát Triển
      </h3>
      <button class="modal-close" onclick="closeModal('roadmapModal')" style="background: none; border: none; color: #8e8c94; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>
    <div style="display: flex; flex-direction: column; gap: 12px;">
      <div style="border-left: 2px solid #dfc5b2; padding-left: 12px;">
        <span style="font-size: 0.76rem; color: #dfc5b2; font-weight: 700;">GIAI ĐOẠN 1 • Q1 2026 (HIỆN TẠI)</span>
        <div style="font-size: 0.88rem; color: #fff; font-weight: 600; margin-top: 2px;">Khởi chạy Telegram Mini App & Mạng đào SUPPER</div>
        <div style="font-size: 0.78rem; color: #8e8c94;">Mở rộng cộng đồng thợ đào toàn cầu, tích hợp ví thanh toán nạp/rút USDT.</div>
      </div>
      <div style="border-left: 2px solid rgba(255,255,255,0.2); padding-left: 12px;">
        <span style="font-size: 0.76rem; color: #a5b4fc; font-weight: 700;">GIAI ĐOẠN 2 • Q2 2026</span>
        <div style="font-size: 0.88rem; color: #fff; font-weight: 600; margin-top: 2px;">Chợ P2P Nông Dân & Staking Vaults</div>
        <div style="font-size: 0.78rem; color: #8e8c94;">Khai trương sàn P2P tự do thương mại và pool gửi tích luỹ lãi kép hàng ngày.</div>
      </div>
      <div style="border-left: 2px solid rgba(255,255,255,0.2); padding-left: 12px;">
        <span style="font-size: 0.76rem; color: #fb923c; font-weight: 700;">GIAI ĐOẠN 3 • Q3 2026</span>
        <div style="font-size: 0.88rem; color: #fff; font-weight: 600; margin-top: 2px;">Niêm Yết CEX Hàng Đầu Thế Giới</div>
        <div style="font-size: 0.78rem; color: #8e8c94;">Listing cặp giao dịch SUPPER/USDT trên các sàn giao dịch hàng đầu.</div>
      </div>
      <div style="border-left: 2px solid rgba(255,255,255,0.2); padding-left: 12px;">
        <span style="font-size: 0.76rem; color: #34d399; font-weight: 700;">GIAI ĐOẠN 4 • Q4 2026</span>
        <div style="font-size: 0.88rem; color: #fff; font-weight: 600; margin-top: 2px;">Mainnet SUPPER AI Chain & GameFi Metaverse</div>
        <div style="font-size: 0.78rem; color: #8e8c94;">Triển khai chuỗi khối độc lập với phí gas siêu thấp và hệ sinh thái ứng dụng.</div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Giấy Trắng (Whitepaper) -->
<div id="whitepaperModal" class="modal-overlay" style="z-index: 10005;">
  <div class="modal-content" style="max-width: 440px; padding: 24px 20px; border-radius: 20px; background: #111219; border: 1px solid rgba(255, 255, 255, 0.14); box-shadow: 0 20px 50px rgba(0,0,0,0.92);">
    <div class="modal-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
      <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
        <span>💧</span> Sách Trắng SUPPER (Whitepaper)
      </h3>
      <button class="modal-close" onclick="closeModal('whitepaperModal')" style="background: none; border: none; color: #8e8c94; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>
    <div style="font-size: 0.86rem; color: #d1cfd8; line-height: 1.5; display: flex; flex-direction: column; gap: 12px;">
      <p style="margin: 0;"><strong style="color: #fff;">SUPPER AI</strong> là đồng tiền kỹ thuật số thế hệ mới được thiết kế dựa trên thuật toán đồng thuận Proof of Activity (PoA) nhằm tối ưu hoá hiệu suất năng lượng và tính phi tập trung.</p>
      <div style="background: #171822; padding: 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.06);">
        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
          <span style="color: #8e8c94;">Tổng Cung Tối Đa:</span>
          <strong style="color: #fbbf24;">1,000,000,000 SUPPER</strong>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
          <span style="color: #8e8c94;">Phân bổ Khai Thác:</span>
          <strong style="color: #34d399;">60% (Cộng đồng)</strong>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
          <span style="color: #8e8c94;">Quỹ Dự Trữ Thanh Khoản:</span>
          <strong style="color: #38bdf8;">25% (USDT Bảo Chứng)</strong>
        </div>
        <div style="display: flex; justify-content: space-between;">
          <span style="color: #8e8c94;">Hệ Sinh Thái & Đội Ngũ:</span>
          <strong style="color: #dfc5b2;">15% (Khoá 24 tháng)</strong>
        </div>
      </div>
      <p style="margin: 0; font-size: 0.8rem; color: #8e8c94;">Mỗi giao dịch swap đều được đối ứng 1:1 qua kho bạc ký quỹ thanh khoản để bảo đảm giá trị bền vững cho người nắm giữ.</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="closeModal('whitepaperModal')" style="width: 100%; margin-top: 18px; background: #dfc5b2; color: #1e1b18; border: none; font-weight: 700; border-radius: 12px; height: 44px;">
      Đóng
    </button>
  </div>
</div>

<!-- Modal: Giao Dịch P2P -->
<div id="p2pModal" class="modal-overlay" style="z-index: 10005;">
  <div class="modal-content" style="max-width: 440px; padding: 24px 20px; border-radius: 20px; background: #111219; border: 1px solid rgba(255, 255, 255, 0.14); box-shadow: 0 20px 50px rgba(0,0,0,0.92);">
    <div class="modal-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
      <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
        <span>🔄</span> Chợ Giao Dịch P2P
      </h3>
      <button class="modal-close" onclick="closeModal('p2pModal')" style="background: none; border: none; color: #8e8c94; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>
    <div style="background: rgba(34, 197, 94, 0.08); border: 1px solid rgba(34, 197, 94, 0.25); border-radius: 12px; padding: 12px; margin-bottom: 16px;">
      <div style="font-size: 0.85rem; color: #34d399; font-weight: 700;">🛡️ Ký Quỹ Escrow An Toàn 100%</div>
      <div style="font-size: 0.78rem; color: #d1cfd8; margin-top: 2px;">Tiền và Coin được giữ an toàn trên hợp đồng thông minh cho tới khi hai bên xác nhận thanh toán.</div>
    </div>
    <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
      <div style="background: #171822; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <div style="font-weight: 700; color: #fff; font-size: 0.9rem;">Thương Nhân: SUPPER AIVipTrader</div>
          <div style="font-size: 0.76rem; color: #8e8c94;">Tỷ lệ: 10,000 SUPPER = 10 USDT • Tỷ lệ hoàn thành: 99.8%</div>
        </div>
        <button type="button" class="btn btn-sm btn-primary" onclick="showToast('Tính năng P2P tự động mở khi liên kết Telegram cá nhân!', 'info')" style="background: #dfc5b2; color: #1e1b18; border: none; font-weight: 700;">Bán</button>
      </div>
      <div style="background: #171822; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <div style="font-weight: 700; color: #fff; font-size: 0.9rem;">Thương Nhân: CryptoSaigon</div>
          <div style="font-size: 0.76rem; color: #8e8c94;">Tỷ lệ: 10,000 SUPPER = 10 USDT • Tỷ lệ hoàn thành: 100%</div>
        </div>
        <button type="button" class="btn btn-sm btn-primary" onclick="showToast('Tính năng P2P tự động mở khi liên kết Telegram cá nhân!', 'info')" style="background: #dfc5b2; color: #1e1b18; border: none; font-weight: 700;">Bán</button>
      </div>
    </div>
    <button type="button" class="btn btn-secondary" onclick="closeModal('p2pModal'); openSupperUsdtSwapView();" style="width: 100%; border-radius: 12px; height: 44px; font-weight: 700;">
      👉 Hoặc Đổi Trực Tiếp SUPPER&USDT Tại Sàn
    </button>
  </div>
</div>

<!-- Modal 1: Select Network (Chuẩn Ảnh 1) -->
<div id="selectNetworkModal" class="modal-overlay" style="z-index: 10020;">
  <div class="modal-content" style="max-width: 360px; width: 90%; padding: 22px 20px; border-radius: 20px; background: #161822; border: 1px solid rgba(255, 255, 255, 0.12); box-shadow: 0 20px 50px rgba(0,0,0,0.95); position: relative;">
    <div style="display: flex; align-items: center; justify-content: center; position: relative; margin-bottom: 22px;">
      <h3 style="font-size: 1.15rem; font-weight: 700; color: #ead9cf; margin: 0; text-align: center;">
        Select network
      </h3>
      <button class="modal-close" onclick="closeModal('selectNetworkModal')" style="position: absolute; right: 0; background: none; border: none; color: #94a3b8; font-size: 1.4rem; cursor: pointer; padding: 0; line-height: 1;">&times;</button>
    </div>

    <!-- Network Item: Binance Smart Chain (BEP20) -->
    <div class="network-select-item" onclick="selectWalletNetwork('BEP20')" style="display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-radius: 14px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); cursor: pointer; transition: all 0.2s;">
      <div style="display: flex; align-items: center; gap: 14px;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; color: #fff;">
          <!-- Node / Network Icon matching image 1 -->
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="9" y="3" width="6" height="5" rx="1.5"/>
            <rect x="3" y="16" width="6" height="5" rx="1.5"/>
            <rect x="15" y="16" width="6" height="5" rx="1.5"/>
            <path d="M12 8v4M6 16v-2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2"/>
          </svg>
        </div>
        <div>
          <div style="font-size: 0.95rem; font-weight: 600; color: #fff;">Binance Smart Chain</div>
          <div style="font-size: 0.85rem; color: #cbd5e1; margin-top: 2px;">(BEP20)</div>
        </div>
      </div>
      <!-- Checkmark circle -->
      <div style="width: 24px; height: 24px; border-radius: 50%; border: 1.5px solid #fff; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.8rem; font-weight: 700;">
        ✓
      </div>
    </div>
  </div>
</div>

<!-- Modal 2: Wallet ADD (Chuẩn Ảnh 2) -->
<div id="walletAddModal" class="modal-overlay" style="z-index: 10010;">
  <div class="modal-content" style="max-width: 440px; width: 92%; padding: 22px 20px; border-radius: 20px; background: #0c0e14; border: 1px solid rgba(255, 255, 255, 0.12); box-shadow: 0 20px 50px rgba(0,0,0,0.95); position: relative;">
    <!-- Header: KHÔNG CÓ MŨI TÊN THEO YÊU CẦU -->
    <div style="display: flex; align-items: center; justify-content: center; position: relative; margin-bottom: 22px;">
      <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin: 0; text-align: center;">
        Wallet ADD
      </h3>
      <button class="modal-close" onclick="closeModal('walletAddModal')" style="position: absolute; right: 0; background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; padding: 0; line-height: 1;">&times;</button>
    </div>

    <!-- 1. Enter wallet address -->
    <div style="margin-bottom: 16px;">
      <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 8px;">Enter wallet address:</label>
      <textarea id="newWalletAddressInput" rows="3" placeholder="Enter wallet address" style="width: 100%; box-sizing: border-box; background: #131722; border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; color: #fff; font-family: var(--font-mono); font-size: 0.88rem; padding: 12px; resize: none; outline: none;"></textarea>
    </div>

    <!-- 2. Verification code -->
    <div style="margin-bottom: 20px;">
      <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 8px;">Verification code:</label>
      <div style="display: flex; gap: 10px; align-items: center;">
        <input type="text" id="walletCaptchaInput" maxlength="6" placeholder="" style="flex: 1; height: 46px; background: #131722; border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; color: #fff; font-family: var(--font-mono); font-size: 1.1rem; font-weight: 700; padding: 0 14px; outline: none;">
        <!-- Captcha badge displaying colorful digits as in image 2 -->
        <div id="walletCaptchaBadge" onclick="generateWalletCaptcha()" title="Bấm để đổi mã mới" style="height: 46px; padding: 0 16px; background: #ebfbee; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 4px; font-family: 'Courier New', Courier, monospace; font-size: 1.45rem; font-weight: 900; letter-spacing: 2px; cursor: pointer; user-select: none; border: 1px solid #bbf7d0;">
          <span style="color: #2563eb;">5</span>
          <span style="color: #16a34a;">8</span>
          <span style="color: #1e293b;">4</span>
          <span style="color: #15803d;">6</span>
        </div>
      </div>
    </div>

    <!-- 3. Add Button -->
    <button type="button" class="btn" onclick="submitAddNewWalletAddress()" style="width: 100%; height: 48px; background: #dfc5b2; color: #000000 !important; font-size: 1.05rem; font-weight: 700; border: none; border-radius: 10px; cursor: pointer; transition: opacity 0.2s;">
      Add
    </button>

    <!-- 4. Address Table Card (Chuẩn hình 2) -->
    <div style="margin-top: 24px; background: #10131b; border: 1px solid rgba(255,255,255,0.1); border-radius: 14px; padding: 14px 16px;">
      <div style="display: flex; justify-content: space-between; align-items: center; color: #94a3b8; font-size: 0.85rem; font-weight: 600; padding-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.08);">
        <span>Address</span>
        <span>Operate</span>
      </div>
      <div id="savedWalletAddressList" style="margin-top: 10px; display: flex; flex-direction: column; gap: 10px; max-height: 220px; overflow-y: auto;">
        <!-- Rendered via JS -->
      </div>
    </div>
  </div>
</div>

<!-- Modal: Chọn Ngôn Ngữ / Language Selector Modal (10 Quốc Gia) -->
<div id="langModal" class="modal-overlay" style="z-index: 10035;">
  <div class="modal-content" style="max-width: 400px; width: 92%; padding: 0; border-radius: 22px; background: #0c0e14; border: 1px solid rgba(223, 197, 178, 0.4); box-shadow: 0 25px 60px rgba(0,0,0,0.95); overflow: hidden; position: relative;">
    <!-- Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; padding: 18px 20px 14px; border-bottom: 1px solid rgba(255,255,255,0.08); background: #11141e;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 1.45rem; line-height: 1;">🌐</span>
        <div>
          <h3 style="font-size: 1.08rem; font-weight: 700; color: #fff; margin: 0; line-height: 1.2;" data-i18n="lang_modal_title">Chọn Ngôn Ngữ</h3>
          <span style="font-size: 0.74rem; color: #8e8c94;" data-i18n="lang_modal_sub">Hỗ trợ 10 ngôn ngữ quốc tế</span>
        </div>
      </div>
      <button type="button" class="modal-close" onclick="closeModal('langModal')" style="background: rgba(255,255,255,0.06); border: none; color: #dfc5b2; font-size: 1.3rem; cursor: pointer; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; line-height: 1; transition: all 0.2s;">&times;</button>
    </div>

    <!-- Danh sách 10 ngôn ngữ lựa chọn (Scrollable) -->
    <div id="langModalList" style="padding: 14px 16px; max-height: 60vh; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
      <!-- Populated dynamically by i18n.js -->
    </div>
  </div>
</div>

<!-- Modal: Nạp USDT (Tự động cộng số dư) -->
<div id="depositModal" class="modal-overlay" style="z-index: 10025;">
  <div class="modal-content" style="max-width: 440px; width: 92%; padding: 22px 20px; border-radius: 20px; background: #0c0e14; border: 1px solid rgba(255, 255, 255, 0.14); box-shadow: 0 20px 50px rgba(0,0,0,0.95); position: relative;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
      <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
        <span>📥</span> Nạp USDT
      </h3>
      <button class="modal-close" onclick="closeModal('depositModal')" style="background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; padding: 0; line-height: 1;">&times;</button>
    </div>

    <!-- Mạng lưới -->
    <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px 14px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center;">
      <span style="font-size: 0.84rem; color: #8e8c94;">Mạng lưới nạp:</span>
      <span style="font-size: 0.88rem; font-weight: 700; color: #fbbf24;">Binance Smart Chain (BEP20)</span>
    </div>

    <!-- Địa chỉ ví nạp chính thức -->
    <div style="margin-bottom: 16px;">
      <label style="display: block; font-size: 0.82rem; color: #8e8c94; margin-bottom: 6px;">Địa chỉ ví nạp USDT chính thức của bạn:</label>
      <div style="display: flex; gap: 8px;">
        <input type="text" id="quickDepositAddrInput" readonly class="form-input" style="font-size: 0.8rem; font-family: var(--font-mono); background: #131722; color: #38bdf8; border: 1px solid rgba(255,255,255,0.12);" value="0xcb3Fc21Af451e1D51Cd58078F025Dad92595f5BA">
        <button type="button" class="btn btn-secondary" onclick="navigator.clipboard.writeText('0xcb3Fc21Af451e1D51Cd58078F025Dad92595f5BA'); showToast('Đã sao chép địa chỉ ví nạp!', 'success');" style="padding: 0 14px; font-weight: 600;">Copy</button>
      </div>
    </div>

    <!-- Số lượng USDT muốn nạp -->
    <div style="margin-bottom: 14px;">
      <label style="display: block; font-size: 0.82rem; color: #8e8c94; margin-bottom: 6px;">Số lượng USDT muốn nạp:</label>
      <div style="display: flex; gap: 6px; margin-bottom: 8px;">
        <button type="button" class="btn btn-secondary btn-sm" onclick="setQuickDepositAmount(10)" style="flex: 1; padding: 6px 0; font-size: 0.82rem; font-weight: 600;">10</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="setQuickDepositAmount(30)" style="flex: 1; padding: 6px 0; font-size: 0.82rem; font-weight: 600;">30</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="setQuickDepositAmount(50)" style="flex: 1; padding: 6px 0; font-size: 0.82rem; font-weight: 600;">50</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="setQuickDepositAmount(100)" style="flex: 1; padding: 6px 0; font-size: 0.82rem; font-weight: 600;">100</button>
      </div>
      <input type="number" id="quickDepositAmountInput" min="1" step="any" placeholder="Nhập số USDT (VD: 50)" value="50" class="form-input" style="background: #131722; border: 1px solid rgba(255,255,255,0.12); color: #fff; font-size: 1rem; font-weight: 700; height: 44px;">
    </div>

    <!-- Mã TxHash (Tùy chọn) -->
    <div style="margin-bottom: 18px;">
      <label style="display: block; font-size: 0.82rem; color: #8e8c94; margin-bottom: 6px;">Mã giao dịch TxHash (Tùy chọn):</label>
      <input type="text" id="quickDepositTxHashInput" placeholder="Dán mã TxHash nếu có (hoặc để trống)" class="form-input" style="background: #131722; border: 1px solid rgba(255,255,255,0.12); color: #fff; font-size: 0.85rem; height: 42px;">
    </div>

    <!-- Nút Xác Nhận Nạp Tiền -->
    <button type="button" class="btn" id="btnSubmitQuickDeposit" onclick="submitQuickDeposit()" style="width: 100%; height: 48px; background: #dfc5b2; color: #000000 !important; font-size: 1.05rem; font-weight: 700; border: none; border-radius: 10px; cursor: pointer; transition: opacity 0.2s;">
      Xác Nhận Nạp Tiền
    </button>

    <div style="margin-top: 14px; text-align: center; font-size: 0.78rem; color: #10b981; line-height: 1.4;">
      ⚡ Hệ thống tự động duyệt và cộng ngay số dư USDT vào tài khoản của bạn!
    </div>
  </div>
</div>

<!-- Modal: Địa Chỉ Ví & Hợp Đồng -->
<div id="contractModal" class="modal-overlay" style="z-index: 10005;">
  <div class="modal-content" style="max-width: 440px; padding: 24px 20px; border-radius: 20px; background: #111219; border: 1px solid rgba(255, 255, 255, 0.14); box-shadow: 0 20px 50px rgba(0,0,0,0.92);">
    <div class="modal-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
      <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
        <span>👛</span> Địa Chỉ Ví & Hợp Đồng
      </h3>
      <button class="modal-close" onclick="closeModal('contractModal')" style="background: none; border: none; color: #8e8c94; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>
    <div style="margin-bottom: 14px;">
      <label style="display: block; font-size: 0.8rem; color: #8e8c94; margin-bottom: 6px;">Địa Chỉ Nạp USDT Chính Thức (BEP20 / TRC20)</label>
      <div style="display: flex; gap: 8px;">
        <input type="text" id="contractDepositAddr" readonly class="form-input" style="font-size: 0.82rem; font-family: var(--font-mono); background: #1a1c26; color: #38bdf8;" value="0xcb3Fc21Af451e1D51Cd58078F025Dad92595f5BA">
        <button type="button" class="btn btn-secondary" onclick="navigator.clipboard.writeText('0xcb3Fc21Af451e1D51Cd58078F025Dad92595f5BA'); showToast('Đã sao chép địa chỉ ví!', 'success');" style="padding: 0 14px;">Copy</button>
      </div>
    </div>
    <div style="margin-bottom: 18px;">
      <label style="display: block; font-size: 0.8rem; color: #8e8c94; margin-bottom: 6px;">Smart Contract SUPPER Token</label>
      <div style="display: flex; gap: 8px;">
        <input type="text" readonly class="form-input" style="font-size: 0.82rem; font-family: var(--font-mono); background: #1a1c26; color: #dfc5b2;" value="0x71c853...supper2026">
        <button type="button" class="btn btn-secondary" onclick="navigator.clipboard.writeText('0x71c853fa98932402948293849234supper2026'); showToast('Đã sao chép Smart Contract!', 'success');" style="padding: 0 14px;">Copy</button>
      </div>
    </div>
    <div style="background: #171822; padding: 12px; border-radius: 12px; font-size: 0.82rem; color: #8e8c94; line-height: 1.4;">
      ℹ️ Vui lòng chỉ chuyển đúng loại mạng lưới đã chọn. Tiền nạp sẽ được cộng tự động vào số dư USDT của tài khoản.
    </div>
  </div>
</div>

<!-- Modal: Chi Tiết Thưởng Bảng Xếp Hạng Đội -->
<div id="leaderboardDetailModal" class="modal-overlay" style="z-index: 10005;">
  <div class="modal-content" style="max-width: 440px; padding: 24px 20px; border-radius: 20px; background: #111219; border: 1px solid rgba(255, 255, 255, 0.14); box-shadow: 0 20px 50px rgba(0,0,0,0.92);">
    <div class="modal-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
      <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
        <span>🏆</span> Chi Tiết Thưởng Đội Tuần
      </h3>
      <button class="modal-close" onclick="closeModal('leaderboardDetailModal')" style="background: none; border: none; color: #8e8c94; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>
    <div style="background: rgba(223, 197, 178, 0.1); border: 1px solid rgba(223, 197, 178, 0.3); border-radius: 12px; padding: 12px; margin-bottom: 16px;">
      <div style="font-size: 0.82rem; color: #dfc5b2; font-weight: 700;">📅 CHU KỲ TRẢ THƯỞNG: MỖI TUẦN 1 LẦN</div>
      <div style="font-size: 0.78rem; color: #ffffff; margin-top: 3px;">Phần thưởng được phát hành tự động vào ví thợ đào lúc 00:00 Chủ Nhật hàng tuần!</div>
    </div>
    <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px;">
      <div style="background: #171822; border-left: 4px solid #ffb800; border-radius: 8px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: 700; color: #ffb800;">🥇 TOP 1 (Vô Địch)</span>
        <strong style="color: #fff; font-family: var(--font-mono);">5,000 USDT + 50k SUPPER</strong>
      </div>
      <div style="background: #171822; border-left: 4px solid #a5b4fc; border-radius: 8px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: 700; color: #a5b4fc;">🥈 TOP 2 (Á Quân)</span>
        <strong style="color: #fff; font-family: var(--font-mono);">2,500 USDT + 25k SUPPER</strong>
      </div>
      <div style="background: #171822; border-left: 4px solid #fb923c; border-radius: 8px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: 700; color: #fb923c;">🥉 TOP 3 (Quý Quân)</span>
        <strong style="color: #fff; font-family: var(--font-mono);">1,000 USDT + 10k SUPPER</strong>
      </div>
      <div style="background: #171822; border-left: 4px solid #6b7280; border-radius: 8px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: 600; color: #d1cfd8;">🎖️ TOP 4 - TOP 10</span>
        <strong style="color: #fff; font-family: var(--font-mono);">200 USDT mỗi đội</strong>
      </div>
    </div>
    <button type="button" class="btn btn-primary" onclick="closeModal('leaderboardDetailModal')" style="width: 100%; background: #dfc5b2; color: #1e1b18; border: none; font-weight: 700; border-radius: 12px; height: 44px;">
      Đóng
    </button>
  </div>
</div>

<script src="assets/js/i18n.js?v=<?= time() ?>"></script>
<script src="assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
