// assets/js/app.js

const State = {
  user: null,
  settings: {
    coin_name: 'SUPPER AI',
    coin_symbol: 'SUPPER',
    coin_price_usdt: 0.001,
    usdt_deposit_address: '0xcb3Fc21Af451e1D51Cd58078F025Dad92595f5BA',
    network: 'USDT (BEP20)',
    min_deposit: 10,
    min_withdraw: 15,
    withdraw_fee_percent: 2.5
  },
  activeTab: 'dashboard',
  miningData: null,
  liveReward: 0,
  minersCatalog: [],
  transactions: [],
  adminData: null
};

// API Helper
async function apiCall(endpoint, method = 'GET', data = null) {
  const options = {
    method,
    headers: {
      'Content-Type': 'application/json'
    }
  };

  const token = localStorage.getItem('minex_token');
  if (token) {
    options.headers['Authorization'] = `Bearer ${token}`;
  }

  if (data && (method === 'POST' || method === 'PUT')) {
    options.body = JSON.stringify(data);
  }

  try {
    const res = await fetch(endpoint, options);
    const result = await res.json();
    if (!res.ok) {
      throw new Error(result.error || 'Có lỗi xảy ra');
    }
    return result;
  } catch (err) {
    console.error(`API Error on ${endpoint}:`, err);
    throw err;
  }
}

// Toast Alert Helper
function showToast(message, type = 'success') {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `alert alert-${type}`;
  toast.style.animation = 'fadeIn 0.2s ease';
  toast.innerHTML = `
    <span>${type === 'success' ? '✅' : '⚠️'}</span>
    <span style="flex: 1">${message}</span>
  `;

  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 250);
  }, 4500);
}

// Navigation & Tab Switching
function switchTab(tabId) {
  // If user clicks or requests 'user', redirect seamlessly to the store / miner page
  if (tabId === 'user') {
    switchTab('store');
    return;
  }

  // If user clicks or requests 'history', redirect seamlessly to the store page and open history modal
  if (tabId === 'history') {
    switchTab('store');
    setTimeout(() => {
      openHistoryModal();
    }, 120);
    return;
  }

  State.activeTab = tabId;

  // Toggle tab body classes to control navbar visibility on mobile / iphone frame
  if (tabId === 'wallet') {
    document.body.classList.add('tab-wallet-active');
  } else {
    document.body.classList.remove('tab-wallet-active');
  }

  // Update Top Nav Items
  document.querySelectorAll('.nav-item').forEach(el => {
    if (el.dataset.tab === tabId) {
      el.classList.add('active');
    } else {
      el.classList.remove('active');
    }
  });

  // Update Bottom Nav Items
  document.querySelectorAll('.bottom-nav-btn').forEach(el => {
    if (el.dataset.tab === tabId) {
      el.classList.add('active');
    } else {
      el.classList.remove('active');
    }
  });

  // Close Mobile Drawer
  closeDrawer();

  // Show/Hide Page Sections
  document.querySelectorAll('.tab-page').forEach(page => {
    if (page.id === `page-${tabId}`) {
      page.style.display = 'block';
      page.classList.add('active');
    } else {
      page.style.display = 'none';
      page.classList.remove('active');
    }
  });

  // Reset scroll to top when switching tabs (unless transitioning to history anchor)
  if (tabId !== 'history') {
    const scrollContainer = document.getElementById('iphoneContentScroll');
    if (scrollContainer) scrollContainer.scrollTop = 0;
    window.scrollTo(0, 0);
  }

  // Load Data for Active Tab
  if (tabId === 'dashboard') loadDashboard();
  if (tabId === 'store') loadStore();
  if (tabId === 'wallet') loadWallet();
  if (tabId === 'admin') loadAdmin();
  if (tabId === 'about') {
    closeSupperUsdtSwapView();
    loadReferralData();
  }
}

function openInvoiceView(e) {
  if (e) {
    e.preventDefault();
    e.stopPropagation();
  }
  const mainView = document.getElementById('userWalletMainView');
  const buybackView = document.getElementById('userBuybackView');
  const invoiceView = document.getElementById('userInvoiceView');
  if (mainView && invoiceView) {
    mainView.style.display = 'none';
    if (buybackView) buybackView.style.display = 'none';
    invoiceView.style.display = 'flex';
  }
  loadInvoiceHistory('all');
}
window.openInvoiceView = openInvoiceView;

function closeInvoiceView(e) {
  if (e) {
    e.preventDefault();
    e.stopPropagation();
  }
  const mainView = document.getElementById('userWalletMainView');
  const buybackView = document.getElementById('userBuybackView');
  const invoiceView = document.getElementById('userInvoiceView');
  if (mainView && invoiceView) {
    invoiceView.style.display = 'none';
    if (buybackView) buybackView.style.display = 'none';
    mainView.style.display = 'flex';
  }
}
window.closeInvoiceView = closeInvoiceView;

function openHistoryModal(e) {
  if (e) {
    e.preventDefault();
    e.stopPropagation();
  }
  if (State.activeTab === 'wallet') {
    openInvoiceView();
    return;
  }
  loadInvoiceHistory('all');
  openModal('historyModal');
}
window.openHistoryModal = openHistoryModal;

function scrollToUserHistory() {
  openHistoryModal();
}
window.scrollToUserHistory = scrollToUserHistory;

function openBuybackView() {
  const mainView = document.getElementById('userWalletMainView');
  const buybackView = document.getElementById('userBuybackView');
  if (mainView && buybackView) {
    mainView.style.display = 'none';
    buybackView.style.display = 'flex';
  }
  const balDisplay = document.getElementById('buybackBalanceDisplay');
  if (balDisplay && State.user) {
    balDisplay.innerText = Number(State.user.usdt_balance || 0).toFixed(2);
  }
  const amountInput = document.getElementById('buybackAmountInput');
  if (amountInput) amountInput.value = '';
  const codeInput = document.getElementById('buyback2faInput');
  if (codeInput) codeInput.value = '';
}
window.openBuybackView = openBuybackView;

function closeBuybackView() {
  const mainView = document.getElementById('userWalletMainView');
  const buybackView = document.getElementById('userBuybackView');
  if (mainView && buybackView) {
    buybackView.style.display = 'none';
    mainView.style.display = 'flex';
  }
}
window.closeBuybackView = closeBuybackView;

function toggleWalletSwapPanel(subtab = 'swap') {
  const panel = document.getElementById('walletExtraPanel');
  if (!panel) return;
  if (panel.style.display === 'none' || !panel.style.display) {
    panel.style.display = 'block';
    if (subtab) switchWalletTab(subtab);
    panel.scrollIntoView({ behavior: 'smooth' });
  } else {
    if (subtab) {
      switchWalletTab(subtab);
    } else {
      panel.style.display = 'none';
    }
  }
}
window.toggleWalletSwapPanel = toggleWalletSwapPanel;

function copyMyUid() {
  const uid = (State.user && State.user.uid) ? State.user.uid : '120850';
  navigator.clipboard.writeText(uid);
  showToast('Đã sao chép mã UID: ' + uid, 'info');
}
window.copyMyUid = copyMyUid;

async function submitBuyback() {
  if (!State.user) {
    showToast('Vui lòng đăng nhập để thực hiện', 'warning');
    return;
  }
  const elAmount = document.getElementById('buybackAmountInput');
  const elAddress = document.getElementById('buybackAddressInput');
  const amount = parseFloat(elAmount ? elAmount.value : 0);
  const address = elAddress ? elAddress.value.trim() : '0xd90e17f8a8a6c0b749028b23828e7c188652ebbc';

  if (!amount || amount <= 0) {
    showToast('Vui lòng nhập số tiền rút hợp lệ', 'error');
    if (elAmount) elAmount.focus();
    return;
  }

  const currentUsdt = Number(State.user.usdt_balance || 0);
  if (amount > currentUsdt) {
    showToast(`Số dư không đủ. Bạn có ${currentUsdt.toFixed(2)} USDT`, 'error');
    return;
  }

  const btn = document.getElementById('btnSubmitBuyback');
  if (btn) {
    btn.disabled = true;
    btn.innerText = 'Đang xử lý...';
  }

  try {
    const res = await apiCall('api/wallet.php?action=withdraw', 'POST', {
      amount: amount,
      address: address
    });

    if (res.success) {
      showToast(`Yêu cầu mua lại / rút tiền ${amount} USDT đã được gửi thành công!`, 'success');
      State.user.usdt_balance = Math.max(0, currentUsdt - amount);
      updateUserInterface();
      closeBuybackView();
    } else {
      showToast(res.error || 'Có lỗi xảy ra', 'error');
    }
  } catch (err) {
    showToast(err.message || 'Lỗi khi gửi yêu cầu mua lại', 'error');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerText = 'Mua lại';
    }
  }
}
window.submitBuyback = submitBuyback;

function openDrawer() {
  const el = document.getElementById('mobileDrawer');
  if (el) el.classList.add('active');
}

function closeDrawer() {
  const el = document.getElementById('mobileDrawer');
  if (el) el.classList.remove('active');
}

// Haptic Feedback for Telegram
function triggerHaptic(type = 'light') {
  try {
    if (window.Telegram && window.Telegram.WebApp && window.Telegram.WebApp.HapticFeedback) {
      if (type === 'success' || type === 'error' || type === 'warning') {
        window.Telegram.WebApp.HapticFeedback.notificationOccurred(type);
      } else {
        window.Telegram.WebApp.HapticFeedback.impactOccurred(type);
      }
    }
  } catch (e) {
    // Ignored
  }
}
window.triggerHaptic = triggerHaptic;

// Modals
function openModal(modalId) {
  const el = document.getElementById(modalId);
  if (el) {
    el.style.display = 'flex';
    void el.offsetWidth;
    el.classList.add('active');
  }
  triggerHaptic('light');

  if (window.Telegram && window.Telegram.WebApp && window.Telegram.WebApp.BackButton) {
    try {
      window.Telegram.WebApp.BackButton.show();
    } catch (e) {}
  }
}

function closeModal(modalId) {
  const el = document.getElementById(modalId);
  if (el) {
    el.classList.remove('active');
    setTimeout(() => {
      if (el && !el.classList.contains('active')) {
        el.style.display = 'none';
      }
    }, 220);
  }
  triggerHaptic('light');

  const remaining = document.querySelectorAll('.modal-overlay.active');
  if (remaining.length === 0 && window.Telegram && window.Telegram.WebApp && window.Telegram.WebApp.BackButton) {
    try {
      window.Telegram.WebApp.BackButton.hide();
    } catch (e) {}
  }
}

function openTermsModal(e) {
  if (e) {
    e.preventDefault();
    e.stopPropagation();
  }
  openModal('termsModal');
}
window.openTermsModal = openTermsModal;

// Telegram WebApp Initialization
async function initTelegramWebApp() {
  const tg = window.Telegram && window.Telegram.WebApp;
  if (!tg || !tg.initData) return false;
  if (localStorage.getItem('minex_logged_out') === '1') return false;

  try {
    tg.ready();
    tg.expand();

    if (tg.setHeaderColor) tg.setHeaderColor('#0c0d12');
    if (tg.setBackgroundColor) tg.setBackgroundColor('#000000');
    if (tg.enableClosingConfirmation) tg.enableClosingConfirmation();

    // BackButton event listener
    if (tg.BackButton) {
      tg.BackButton.onClick(() => {
        const activeModal = document.querySelector('.modal-overlay.active');
        if (activeModal) {
          activeModal.classList.remove('active');
          const remaining = document.querySelectorAll('.modal-overlay.active');
          if (remaining.length === 0) tg.BackButton.hide();
          return;
        }

        const buybackView = document.getElementById('userWalletBuybackView');
        if (buybackView && buybackView.style.display !== 'none') {
          if (typeof closeBuybackView === 'function') closeBuybackView();
          return;
        }

        const swapPanel = document.getElementById('walletSwapPanel');
        if (swapPanel && swapPanel.style.display !== 'none') {
          swapPanel.style.display = 'none';
          return;
        }

        if (State.activeTab !== 'dashboard') {
          switchTab('dashboard');
          tg.BackButton.hide();
        }
      });
    }

    // Auto authenticate if Telegram user data exists
    if (tg.initDataUnsafe && tg.initDataUnsafe.user) {
      const tgUser = tg.initDataUnsafe.user;
      const urlParams = new URLSearchParams(window.location.search);
      const startParam = tg.initDataUnsafe.start_param || urlParams.get('startapp') || urlParams.get('ref') || '';

      const res = await apiCall('api/auth.php?action=telegram_auth', 'POST', {
        initData: tg.initData,
        user: tgUser,
        start_param: startParam
      });

      if (res && res.user) {
        State.user = res.user;
        localStorage.setItem('minex_token', res.token); localStorage.removeItem('minex_logged_out');
        updateUserInterface();
        return true;
      }
    }
  } catch (err) {
    console.warn('Telegram WebApp init notice:', err);
  }
  return false;
}

// -------------------------------------------------------------
// USER AVATAR MANAGEMENT (Đổi ảnh đại diện)
// -------------------------------------------------------------
function triggerAvatarUpload() {
  const fileInput = document.getElementById('userAvatarFileInput');
  if (fileInput) {
    fileInput.click();
  }
}
window.triggerAvatarUpload = triggerAvatarUpload;

function handleAvatarUpload(event) {
  const file = event.target.files && event.target.files[0];
  if (!file) return;

  if (!file.type.startsWith('image/')) {
    showToast('Vui lòng chọn tệp hình ảnh hợp lệ!', 'warning');
    return;
  }

  if (file.size > 5 * 1024 * 1024) {
    showToast('Kích thước ảnh quá lớn. Vui lòng chọn ảnh dưới 5MB!', 'warning');
    return;
  }

  const reader = new FileReader();
  reader.onload = function(e) {
    const dataUrl = e.target.result;
    try {
      localStorage.setItem('supper_custom_avatar', dataUrl);
    } catch (err) {
      console.warn('Lỗi lưu ảnh vào localStorage:', err);
    }
    updateUserAvatarDisplay(dataUrl);
    showToast('Cập nhật ảnh đại diện thành công!', 'success');
  };
  reader.readAsDataURL(file);
}
window.handleAvatarUpload = handleAvatarUpload;

function updateUserAvatarDisplay(customSrc = null) {
  const avatarSrc = customSrc 
    || localStorage.getItem('supper_custom_avatar') 
    || (State.user && State.user.telegram_photo_url) 
    || 'assets/images/user_avatar_wreath.png';

  document.querySelectorAll('#storeUserAvatarImg, .user-avatar-img, .profile-avatar-img').forEach(img => {
    img.src = avatarSrc;
  });
}
window.updateUserAvatarDisplay = updateUserAvatarDisplay;

// Global App Initialization
document.addEventListener('DOMContentLoaded', async () => {
  setupEventListeners();
  initIphoneSimulator();
  initReferralTracking();
  if (window.initI18n) window.initI18n();

  // Khôi phục số coin khai thác và avatar từ cache ngay lập tức để không bị về 0 khi reload
  const cachedReward = parseFloat(localStorage.getItem('supper_live_reward'));
  if (!isNaN(cachedReward) && cachedReward > 0) {
    State.liveReward = cachedReward;
    const counterEl = document.getElementById('liveCoinCounter');
    if (counterEl) counterEl.innerText = cachedReward.toFixed(6);
  }
  updateUserAvatarDisplay();

  const isTgAuthed = await initTelegramWebApp();
  if (!isTgAuthed) {
    await checkSession();
  }

  switchTab('dashboard');

  // Start live ticker
  setInterval(liveTicker, 100);
});

function setupEventListeners() {
  // Navigation clicks
  document.querySelectorAll('[data-tab]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      switchTab(btn.dataset.tab);
    });
  });

  // Auth toggle
  const toRegister = document.getElementById('linkToRegister');
  const toLogin = document.getElementById('linkToLogin');
  if (toRegister) {
    toRegister.addEventListener('click', (e) => {
      e.preventDefault();
      document.getElementById('loginForm').style.display = 'none';
      document.getElementById('registerForm').style.display = 'block';
      document.getElementById('authModalTitle').innerText = 'Tạo Tài Khoản Thợ Đào';
    });
  }
  if (toLogin) {
    toLogin.addEventListener('click', (e) => {
      e.preventDefault();
      document.getElementById('registerForm').style.display = 'none';
      document.getElementById('loginForm').style.display = 'block';
      document.getElementById('authModalTitle').innerText = 'Đăng Nhập Tài Khoản';
    });
  }

  // Quick Demo Logins
  const btnUserDemo = document.getElementById('btnUserDemo');
  const btnAdminDemo = document.getElementById('btnAdminDemo');
  if (btnUserDemo) {
    btnUserDemo.addEventListener('click', () => quickLogin('user'));
  }
  if (btnAdminDemo) {
    btnAdminDemo.addEventListener('click', () => quickLogin('admin'));
  }

  // Login Submit
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', handleLogin);
  }

  // Register Submit
  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', handleRegister);
  }

  // User Page Form Submissions
  const userPageLoginForm = document.getElementById('userPageLoginForm');
  if (userPageLoginForm) {
    userPageLoginForm.addEventListener('submit', handleUserPageLogin);
  }
  const userPageRegisterForm = document.getElementById('userPageRegisterForm');
  if (userPageRegisterForm) {
    userPageRegisterForm.addEventListener('submit', handleUserPageRegister);
  }

  // Deposit Form
  const depositForm = document.getElementById('depositForm');
  if (depositForm) {
    depositForm.addEventListener('submit', handleDeposit);
  }

  // Withdraw Form
  const withdrawForm = document.getElementById('withdrawForm');
  if (withdrawForm) {
    withdrawForm.addEventListener('submit', handleWithdraw);
  }

  // Swap Form
  const swapForm = document.getElementById('swapForm');
  if (swapForm) {
    swapForm.addEventListener('submit', handleSwap);
  }

  // Admin Settings Form
  const adminSettingsForm = document.getElementById('adminSettingsForm');
  if (adminSettingsForm) {
    adminSettingsForm.addEventListener('submit', handleSaveAdminSettings);
  }

  // Admin Add Miner Form
  const addMinerForm = document.getElementById('addMinerForm');
  if (addMinerForm) {
    addMinerForm.addEventListener('submit', handleAddMiner);
  }

  // Admin Adjust Balance Form
  const adjustBalanceForm = document.getElementById('adjustBalanceForm');
  if (adjustBalanceForm) {
    adjustBalanceForm.addEventListener('submit', handleSaveAdjustBalance);
  }

  // Close modals when clicking overlay
  document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.classList.remove('active');
      }
    });
  });
}

// -------------------------------------------------------------
// AUTH FUNCTIONS
// -------------------------------------------------------------
async function checkSession() {
  // Người dùng đã chủ động đăng xuất: không tự đăng nhập lại
  if (localStorage.getItem('minex_logged_out') === '1') {
    State.user = null;
    updateUserInterface();
    return;
  }
  try {
    let token = localStorage.getItem('minex_token');
    if (!token) {
      // Auto login as demo miner (UID: 120850) by default so user page and full features are immediately active
      const loginRes = await apiCall('api/auth.php?action=login', 'POST', { email: 'user@mining.io', password: 'user123' });
      localStorage.setItem('minex_token', loginRes.token);
      State.user = loginRes.user;
    } else {
      const res = await apiCall('api/auth.php?action=me');
      State.user = res.user;
      if (res.settings) State.settings = res.settings;
    }
    updateUserInterface();
    loadHistory();
  } catch (err) {
    try {
      const loginRes = await apiCall('api/auth.php?action=login', 'POST', { email: 'user@mining.io', password: 'user123' });
      localStorage.setItem('minex_token', loginRes.token);
      State.user = loginRes.user;
      updateUserInterface();
      loadHistory();
    } catch (e) {
      State.user = null;
      updateUserInterface();
    }
  }
}

function updateUserInterface() {
  const loggedOutNav = document.getElementById('loggedOutNav');
  const loggedInNav = document.getElementById('loggedInNav');
  const navLogoutBtn = document.getElementById('navLogoutBtn');
  const adminNavItems = document.querySelectorAll('.admin-only');

  const userPageLoggedOut = document.getElementById('userPageLoggedOut');
  const userPageLoggedIn = document.getElementById('userPageLoggedIn');

  if (State.user) {
    if (loggedOutNav) loggedOutNav.style.display = 'none';
    if (loggedInNav) loggedInNav.style.display = 'flex';
    if (navLogoutBtn) navLogoutBtn.style.display = 'inline-flex';

    if (userPageLoggedOut) userPageLoggedOut.style.display = 'none';
    if (userPageLoggedIn) userPageLoggedIn.style.display = 'flex';

    // Populate user profile info on User page
    const elName = document.getElementById('userProfileName');
    const elEmail = document.getElementById('userProfileEmail');
    const elBadge = document.getElementById('userProfileRoleBadge');
    const elInitial = document.getElementById('userProfileInitial');
    const elNavName = document.querySelector('.user-name-display');

    const uid = State.user.uid || (State.user.id === 'user_demo' ? '120850' : (State.user.id === 'user_admin' ? '10001' : (State.user.id ? State.user.id.replace(/\D/g, '').slice(-6) : '120850')));
    document.querySelectorAll('.user-uid-display').forEach(el => {
      el.innerText = uid;
    });

    const displayName = State.user.name || State.user.telegram_username || (State.user.id === 'user_demo' ? 'evansTi' : 'Thợ Đào');
    if (elName) elName.innerText = displayName;
    document.querySelectorAll('.user-name-display').forEach(el => {
      el.innerText = displayName;
    });

    const storeProfileName = document.getElementById('storeProfileName');
    if (storeProfileName) {
      storeProfileName.innerText = displayName;
    }

    if (elEmail) elEmail.innerText = State.user.email || '';
    if (elInitial) {
      const initial = displayName.charAt(0).toUpperCase();
      elInitial.innerText = initial;
    }

    // Khách mua 10 gói 10 USDT thì lên 1 cấp (bắt đầu từ Level 1)
    const packagesCount = (State.user && State.user.packages_count !== undefined) 
      ? State.user.packages_count 
      : ((State.miningData && State.miningData.active_miners_count !== undefined) ? State.miningData.active_miners_count : 1);
    const userLevel = (State.user && State.user.level) ? State.user.level : Math.max(1, 1 + Math.floor(packagesCount / 10));

    if (elBadge) {
      elBadge.innerText = `Level ${userLevel}`;
      elBadge.style.color = '#1e1b18';
      elBadge.style.background = '#dfc5b2';
      elBadge.style.borderColor = 'transparent';
      elBadge.style.borderRadius = '9999px';
      elBadge.style.padding = '3px 12px';
      elBadge.style.fontWeight = '600';
    }
    document.querySelectorAll('.nav-level-badge, .user-level-display').forEach(el => {
      el.innerText = `Level ${userLevel}`;
    });

    const storeInvitedBy = document.getElementById('storeInvitedBy');
    if (storeInvitedBy) {
      storeInvitedBy.innerText = State.user.referrer_name || State.user.referrer_id || 'Hệ thống';
    }

    if (typeof updateUserAvatarDisplay === 'function') {
      updateUserAvatarDisplay();
    }

    // Update balances
    document.querySelectorAll('.user-usdt-balance').forEach(el => {
      el.innerText = Number(State.user.usdt_balance || 0).toFixed(2);
    });
    document.querySelectorAll('.user-coin-balance').forEach(el => {
      const val = Number(State.user.coin_balance || 0);
      el.innerText = val.toFixed(6);
    });

    // BXH EDEN: Hiển thị số coin người dùng khai thác được ở Top 1
    const userCoinBal = Number(State.user.coin_balance || 0);
    const displayMinedCoin = userCoinBal > 12580 
      ? userCoinBal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
      : '12,580.00';
    document.querySelectorAll('.user-mined-coin-display').forEach(el => {
      el.innerText = displayMinedCoin;
    });
    document.querySelectorAll('.coin-symbol').forEach(el => {
      el.innerText = State.settings.coin_symbol || 'SUPPER';
    });
    const elEquiv = document.getElementById('userPageEquivUsdt');
    if (elEquiv) {
      const price = (State.settings && parseFloat(State.settings.coin_price_usdt)) ? parseFloat(State.settings.coin_price_usdt) : 0.0001;
      elEquiv.innerText = (Number(State.user.coin_balance || 0) * price).toFixed(6);
    }
    const elProfileEquiv = document.getElementById('userProfileEquivUsdt');
    if (elProfileEquiv) {
      const price = (State.settings && parseFloat(State.settings.coin_price_usdt)) ? parseFloat(State.settings.coin_price_usdt) : 0.0001;
      elProfileEquiv.innerText = (Number(State.user.coin_balance || 0) * price).toFixed(4);
    }

    // Admin toggle
    adminNavItems.forEach(el => {
      el.style.display = State.user.role === 'admin' ? 'flex' : 'none';
    });
  } else {
    if (loggedOutNav) loggedOutNav.style.display = 'flex';
    if (loggedInNav) loggedInNav.style.display = 'none';
    if (navLogoutBtn) navLogoutBtn.style.display = 'none';

    if (userPageLoggedOut) userPageLoggedOut.style.display = 'block';
    if (userPageLoggedIn) userPageLoggedIn.style.display = 'none';

    const elNavName = document.querySelector('.user-name-display');
    if (elNavName) elNavName.innerText = 'User';

    adminNavItems.forEach(el => { el.style.display = 'none'; });
  }
}

function setUserAuthMode(mode) {
  const loginForm = document.getElementById('userPageLoginForm');
  const regForm = document.getElementById('userPageRegisterForm');
  const btnLogin = document.getElementById('userBtnTabLogin');
  const btnReg = document.getElementById('userBtnTabRegister');
  const title = document.getElementById('userPageTitle');
  const subtitle = document.getElementById('userPageSubtitle');

  const currentLangCode = localStorage.getItem('minex_lang') || 'vi';
  const dict = (window.I18N_DICTIONARY && window.I18N_DICTIONARY[currentLangCode]) ? window.I18N_DICTIONARY[currentLangCode] : {};

  if (mode === 'register') {
    if (loginForm) loginForm.style.display = 'none';
    if (regForm) regForm.style.display = 'block';
    if (btnLogin) {
      btnLogin.style.background = 'transparent';
      btnLogin.style.color = 'var(--text-muted)';
    }
    if (btnReg) {
      btnReg.style.background = 'var(--primary)';
      btnReg.style.color = '#fff';
    }
    if (title) title.innerText = dict.btn_register ? `${dict.btn_register} MINEX` : 'Đăng Ký MINEX';
    if (subtitle) subtitle.innerText = dict.user_subtitle || 'Tạo tài khoản thợ đào và nhận 100 USDT trải nghiệm';
  } else {
    if (loginForm) loginForm.style.display = 'block';
    if (regForm) regForm.style.display = 'none';
    if (btnLogin) {
      btnLogin.style.background = 'var(--primary)';
      btnLogin.style.color = '#fff';
    }
    if (btnReg) {
      btnReg.style.background = 'transparent';
      btnReg.style.color = 'var(--text-muted)';
    }
    if (title) title.innerText = dict.user_title || 'Tài Khoản MINEX';
    if (subtitle) subtitle.innerText = dict.user_subtitle || 'Đăng nhập hoặc đăng ký tài khoản thợ đào';
  }
}
window.setUserAuthMode = setUserAuthMode;

async function quickLogin(role) {
  const email = role === 'admin' ? 'ngdat257257@gmail.com' : 'user@mining.io';
  const password = role === 'admin' ? 'dat112233' : 'user123';
  try {
    const res = await apiCall('api/auth.php?action=login', 'POST', { email, password });
    localStorage.setItem('minex_token', res.token); localStorage.removeItem('minex_logged_out');
    State.user = res.user;
    closeModal('authModal');
    updateUserInterface();
    loadHistory();
    showToast(`Đăng nhập thành công với vai trò ${role.toUpperCase()}!`, 'success');
    if (State.activeTab !== 'user') {
      switchTab('dashboard');
    }
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function handleLogin(e) {
  e.preventDefault();
  const agree = document.getElementById('loginAgreeTerms');
  if (agree && !agree.checked) {
    showToast('Vui lòng tích chọn đồng ý với Điều khoản dịch vụ & Tuyên bố miễn trừ!', 'warning');
    return;
  }

  const email = document.getElementById('loginEmail').value;
  const password = document.getElementById('loginPassword').value;

  try {
    const res = await apiCall('api/auth.php?action=login', 'POST', { email, password });
    localStorage.setItem('minex_token', res.token); localStorage.removeItem('minex_logged_out');
    State.user = res.user;
    closeModal('authModal');
    updateUserInterface();
    showToast('Đăng nhập thành công!', 'success');
    if (State.activeTab !== 'user') {
      switchTab('dashboard');
    }
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function handleRegister(e) {
  e.preventDefault();
  const agree = document.getElementById('regAgreeTerms');
  if (agree && !agree.checked) {
    showToast('Vui lòng tích chọn đồng ý với Điều khoản sử dụng & Tuyên bố miễn trừ rủi ro!', 'warning');
    return;
  }

  const name = document.getElementById('regName').value;
  const email = document.getElementById('regEmail').value;
  const password = document.getElementById('regPassword').value;
  const ref = (document.getElementById('regRefCode')?.value || localStorage.getItem('minex_ref') || '').trim();

  try {
    const res = await apiCall('api/auth.php?action=register', 'POST', { name, email, password, ref });
    localStorage.setItem('minex_token', res.token); localStorage.removeItem('minex_logged_out');
    State.user = res.user;
    closeModal('authModal');
    updateUserInterface();
    showToast(res.message, 'success');
    if (State.activeTab !== 'user') {
      switchTab('dashboard');
    }
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function handleUserPageLogin(e) {
  e.preventDefault();
  const agree = document.getElementById('userPageLoginAgreeTerms');
  if (agree && !agree.checked) {
    showToast('Vui lòng tích chọn đồng ý với Điều khoản dịch vụ & Tuyên bố miễn trừ!', 'warning');
    return;
  }

  const email = document.getElementById('userPageLoginEmail').value;
  const password = document.getElementById('userPageLoginPassword').value;

  try {
    const res = await apiCall('api/auth.php?action=login', 'POST', { email, password });
    localStorage.setItem('minex_token', res.token); localStorage.removeItem('minex_logged_out');
    State.user = res.user;
    updateUserInterface();
    showToast('Đăng nhập thành công!', 'success');
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function handleUserPageRegister(e) {
  e.preventDefault();
  const agree = document.getElementById('userPageRegAgreeTerms');
  if (agree && !agree.checked) {
    showToast('Vui lòng tích chọn đồng ý với Điều khoản sử dụng & Tuyên bố miễn trừ rủi ro!', 'warning');
    return;
  }

  const name = document.getElementById('userPageRegName').value;
  const email = document.getElementById('userPageRegEmail').value;
  const password = document.getElementById('userPageRegPassword').value;
  const ref = (document.getElementById('userPageRegRefCode')?.value || localStorage.getItem('minex_ref') || '').trim();

  try {
    const res = await apiCall('api/auth.php?action=register', 'POST', { name, email, password, ref });
    localStorage.setItem('minex_token', res.token); localStorage.removeItem('minex_logged_out');
    State.user = res.user;
    updateUserInterface();
    showToast(res.message, 'success');
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function handleLogout() {
  localStorage.removeItem('minex_token');
  localStorage.setItem('minex_logged_out', '1');
  try { await apiCall('api/auth.php?action=logout'); } catch (e) {}
  State.user = null;
  updateUserInterface();
  showToast('Đã đăng xuất tài khoản', 'success');
  switchTab('dashboard');
  openModal('authModal');
}
window.handleLogout = handleLogout;

// -------------------------------------------------------------
// DASHBOARD & MINING ENGINE
// -------------------------------------------------------------
async function loadDashboard() {
  if (!State.user) return;
  try {
    const data = await apiCall('api/mining.php?action=status');
    State.miningData = data;

    const serverUnclaimed = parseFloat(data.unclaimed_reward) || 0;
    if (serverUnclaimed > 0) {
      State.liveReward = serverUnclaimed;
      try { localStorage.setItem('supper_live_reward', State.liveReward.toFixed(6)); } catch(e){}
    } else {
      const cached = parseFloat(localStorage.getItem('supper_live_reward'));
      if (!isNaN(cached) && cached > 0) {
        State.liveReward = cached;
      } else {
        State.liveReward = 0;
      }
    }

    // Cập nhật số coin khai thác được lên BXH EDEN và các thẻ xếp hạng
    const minedCoins = (data.coin_balance !== undefined) ? Number(data.coin_balance) : (State.user ? Number(State.user.coin_balance || 0) : 0);
    const formattedMined = minedCoins > 12580 ? minedCoins.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '12,580.00';
    document.querySelectorAll('.user-mined-coin-display').forEach(el => {
      el.innerText = formattedMined;
    });

    // Update stats (safely if elements exist)
    const elHashrate = document.getElementById('dashTotalHashrate');
    if (elHashrate) elHashrate.innerText = data.total_hashrate.toLocaleString();
    const elMinersCount = document.getElementById('dashMinersCount');
    if (elMinersCount) elMinersCount.innerText = `${data.active_miners_count} máy đang vận hành`;
    const elDailyYield = document.getElementById('dashDailyYield');
    if (elDailyYield) elDailyYield.innerText = data.total_daily_yield.toFixed(2);
    const estUsdt = (data.total_daily_yield * data.coin_price_usdt).toFixed(2);
    const elDailyUsdt = document.getElementById('dashDailyUsdt');
    if (elDailyUsdt) elDailyUsdt.innerText = `≈ $${estUsdt} USDT / ngày`;
    const elCoinPrice = document.getElementById('dashCoinPrice');
    if (elCoinPrice) elCoinPrice.innerText = `$${parseFloat(data.coin_price_usdt)}`;

    // Tốc độ đào theo giờ (SUPPER / H)
    const elSpeedHour = document.getElementById('liveSpeedPerHour');
    if (elSpeedHour) {
      const perHour = data.total_daily_yield / 24;
      elSpeedHour.innerText = `${perHour.toFixed(2)} ${data.coin_symbol || 'SUPPER'} / H`;
    }

    // Rig Status & Fans
    const hasMiners = data.active_miners_count > 0;
    const rigStatusBadge = document.getElementById('rigStatusBadge');
    if (rigStatusBadge) {
      rigStatusBadge.innerHTML = hasMiners
        ? '<span class="pulse-dot"></span><span>HỆ THỐNG ĐANG ĐÀO COIN (ACTIVE)</span>'
        : '<span class="pulse-dot" style="background:#64748b"></span><span>CHƯA KÍCH HOẠT MÁY ĐÀO</span>';
    }

    document.querySelectorAll('.fan').forEach(fan => {
      if (hasMiners) {
        fan.classList.remove('idle');
      } else {
        fan.classList.add('idle');
      }
    });

    // 24h claim countdown setup
    if (data.next_claim_in_ms > 0) {
      State.nextClaimTargetTime = Date.now() + data.next_claim_in_ms;
    } else {
      State.nextClaimTargetTime = 0;
    }
    updateClaimButtonState();

    renderActiveMinersTable(data.active_miners);
  } catch (err) {
    console.error('Failed to load dashboard:', err);
  }
}

// Trạng thái đào: đang đào (đếm ngược 24h) -> đủ 24h thì DỪNG, phải Claim mới chạy tiếp
function isMiningCycleComplete() {
  if (!State.miningData || State.miningData.active_miners_count === 0) return false;
  return State.miningData.can_claim || (State.nextClaimTargetTime > 0 && Date.now() >= State.nextClaimTargetTime);
}

function updateClaimButtonState() {
  const btn = document.getElementById('btnClaimReward');
  const textEl = document.getElementById('btnClaimText');
  const countdownEl = document.getElementById('claimCountdownText');
  const labelEl = document.getElementById('mineTimerLabel');

  if (!btn || !State.miningData) return;

  btn.classList.remove('ready', 'idle');

  // Chưa có máy đào
  if (!State.user || State.miningData.active_miners_count === 0) {
    btn.disabled = false;
    btn.classList.add('idle');
    if (textEl) textEl.innerText = 'Start';
    if (countdownEl) countdownEl.innerText = '00H 00M 00S';
    if (labelEl) labelEl.innerText = 'Buy a miner to start';
    return;
  }

  if (isMiningCycleComplete()) {
    // Đã đủ 24h: máy dừng đào, chờ khách Claim (chữ Claim màu đen)
    State.miningData.can_claim = true;
    btn.disabled = false;
    btn.classList.add('ready');
    if (textEl) {
      textEl.innerText = 'Claim';
      textEl.style.color = '#000000';
      textEl.style.fontWeight = '800';
    }
    if (countdownEl) countdownEl.innerText = '00H 00M 00S';
    if (labelEl) labelEl.innerText = 'Mining stopped • Claim to restart';
  } else {
    const remainingMs = Math.max(0, (State.nextClaimTargetTime || 0) - Date.now());
    const totalSec = Math.floor(remainingMs / 1000);
    const hrs = String(Math.floor(totalSec / 3600)).padStart(2, '0');
    const mins = String(Math.floor((totalSec % 3600) / 60)).padStart(2, '0');
    const secs = String(totalSec % 60).padStart(2, '0');

    btn.disabled = true;
    if (textEl) {
      textEl.innerText = 'Mining';
      textEl.style.color = '';
      textEl.style.fontWeight = '';
    }
    if (countdownEl) countdownEl.innerText = `${hrs}H ${mins}M ${secs}S`;
    if (labelEl) labelEl.innerText = 'Time until next start';
  }
}

function showMiningInfo() {
  showToast('⛏️ Mỗi phiên đào kéo dài 24 giờ. Hết 24h máy sẽ dừng, bấm Claim để nhận coin và bắt đầu phiên mới.', 'info');
}
window.showMiningInfo = showMiningInfo;

function liveTicker() {
  if (!State.miningData || State.miningData.total_daily_yield <= 0) return;
  // Đủ 24h thì dừng tích luỹ, chờ Claim
  if (!isMiningCycleComplete()) {
    const yieldPerSecond = State.miningData.total_daily_yield / 86400;
    State.liveReward = Math.min(State.liveReward + yieldPerSecond * 0.1, State.miningData.total_daily_yield);
  }

  // Cập nhật lưu vào localStorage để không bao giờ bị về 0 khi người dùng reload trang
  if (State.liveReward > 0) {
    try { localStorage.setItem('supper_live_reward', State.liveReward.toFixed(6)); } catch(e){}
  }

  const counterEl = document.getElementById('liveCoinCounter');
  if (counterEl) {
    counterEl.innerText = State.liveReward.toFixed(6);
  }

  const rewardUsdtEl = document.getElementById('liveRewardUsdt');
  if (rewardUsdtEl) {
    const coinPrice = (State.settings && State.settings.coin_price_usdt) ? parseFloat(State.settings.coin_price_usdt) : 0.001;
    rewardUsdtEl.innerHTML = `<span style="display: inline-flex; align-items: center; gap: 5px; background: #000; padding: 3px 10px 3px 6px; border-radius: 14px; border: 1px solid rgba(38,161,123,0.3); box-shadow: 0 2px 8px rgba(0,0,0,0.6);"><img src="assets/usdt.png" alt="USDT" style="width: 14px; height: 14px; border-radius: 50%;"> ≈ $${(State.liveReward * coinPrice).toFixed(4)} <span style="color:#26a17b; font-weight:700;">USDT</span></span>`;
  }

  updateClaimButtonState();
}

async function claimReward() {
  if (!State.user) {
    showToast('Vui lòng đăng nhập để nhận thưởng!', 'warning');
    switchTab('user');
    return;
  }
  if (State.miningData && State.miningData.active_miners_count === 0) {
    switchTab('store');
    return;
  }
  if (State.miningData && !isMiningCycleComplete()) {
    showToast('Máy đang đào. Vui lòng chờ hết 24 giờ để Claim!', 'warning');
    return;
  }
  if (State.liveReward < 0.00001) {
    showToast('Chưa có coin để nhận thưởng hoặc đang tích luỹ...', 'info');
    return;
  }
  const btn = document.getElementById('btnClaimReward');
  if (btn) btn.disabled = true;

  try {
    const res = await apiCall('api/mining.php?action=claim', 'POST');
    showCenterSuccessClaim(res.message || 'Thu hoạch coin thành công!', res.claimed_amount);
    showToast(res.message, 'success');
    State.liveReward = 0;
    try { localStorage.removeItem('supper_live_reward'); } catch(e){}
    await checkSession();
    await loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  } finally {
    if (btn) btn.disabled = false;
  }
}

// Thông báo thành công hiển thị chính giữa màn hình khi Claim
function showCenterSuccessClaim(message, amount) {
  const old = document.getElementById('centerClaimModal');
  if (old) old.remove();

  const overlay = document.createElement('div');
  overlay.id = 'centerClaimModal';
  overlay.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.78);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: fadeIn 0.2s ease;
  `;

  overlay.innerHTML = `
    <div style="
      background: linear-gradient(145deg, #13151f, #090a10);
      border: 1.5px solid rgba(245, 158, 11, 0.6);
      box-shadow: 0 20px 60px rgba(0,0,0,0.95), 0 0 35px rgba(245, 158, 11, 0.35);
      border-radius: 24px;
      padding: 30px 22px;
      max-width: 360px;
      width: 100%;
      text-align: center;
      color: #fff;
      transform: scale(1);
    ">
      <div style="width: 72px; height: 72px; margin: 0 auto 16px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); border: 2px solid #10b981; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; box-shadow: 0 0 25px rgba(16, 185, 129, 0.45);">
        ✅
      </div>
      <h3 style="font-size: 1.35rem; font-weight: 800; color: #fbbf24; margin-bottom: 8px; letter-spacing: -0.01em;">
        THU HOẠCH THÀNH CÔNG!
      </h3>
      <p style="font-size: 0.95rem; color: #ead9cf; margin-bottom: 20px; line-height: 1.5;">
        ${message}
      </p>
      <button type="button" onclick="document.getElementById('centerClaimModal').remove()" style="
        width: 100%;
        height: 48px;
        background: linear-gradient(135deg, #dfc5b2, #fbbf24);
        color: #000000 !important;
        font-weight: 800;
        font-size: 1rem;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
      ">
        Xác Nhận & Đóng
      </button>
    </div>
  `;

  overlay.onclick = (e) => {
    if (e.target === overlay) overlay.remove();
  };

  document.body.appendChild(overlay);

  setTimeout(() => {
    if (overlay && overlay.parentNode) {
      overlay.style.opacity = '0';
      overlay.style.transition = 'opacity 0.25s';
      setTimeout(() => overlay.remove(), 250);
    }
  }, 4500);
}
window.claimReward = claimReward;

function renderActiveMinersTable(miners = []) {
  const container = document.getElementById('activeMinersList');
  if (!container) return;

  if (miners.length === 0) {
    container.innerHTML = `
      <tr>
        <td colspan="5" style="text-align:center; padding: 25px 10px; color: var(--text-muted);">
          <div style="font-size: 1.6rem; margin-bottom: 6px;">⛏️</div>
          <p style="margin: 0; font-size: 0.86rem;">Bạn chưa sở hữu gói máy đào nào.</p>
          <button class="btn btn-primary btn-sm" style="margin-top: 10px;" onclick="switchTab('store')">
            Xem Các Gói Máy Đào
          </button>
        </td>
      </tr>
    `;
    return;
  }

  const coinPrice = (State.settings && State.settings.coin_price_usdt) ? parseFloat(State.settings.coin_price_usdt) : 0.001;
  const coinSymbol = (State.settings && State.settings.coin_symbol) || 'SUPPER';

  container.innerHTML = miners.map(m => {
    const perSec = (m.daily_yield / 86400).toFixed(6);
    const dailyUsdt = (m.daily_yield * coinPrice).toFixed(2);
    return `
    <tr>
      <td>
        <strong style="color: #fff; font-size: 0.88rem;">${m.name}</strong>
        <div style="font-size: 0.72rem; color: var(--text-muted);">${m.tier || 'Máy Đào'}</div>
      </td>
      <td style="font-family: var(--font-mono); color: #22d3ee; font-weight: 700;">
        ${m.hashrate} ${m.unit || 'TH/s'}
      </td>
      <td style="font-family: var(--font-mono); color: #38bdf8; font-weight: 700;">
        +${perSec}/s
      </td>
      <td style="font-family: var(--font-mono); color: #fbbf24; font-weight: 700;">
        +${Number(m.daily_yield).toLocaleString()} ${coinSymbol}
        <div style="font-size: 0.72rem; color: #fbbf24;">≈ $${dailyUsdt} USDT/ngày</div>
      </td>
      <td>
        <span class="badge badge-completed">Đang Đào</span>
      </td>
    </tr>
  `;
  }).join('');
}

// -------------------------------------------------------------
// MINERS STORE
// -------------------------------------------------------------
// STORE & SVIP MINING PACKAGES
// -------------------------------------------------------------
function switchStoreSubTab(tab) {
  const btnSvip = document.getElementById('tabBtnSVIP');
  const btnUp = document.getElementById('tabBtnUpgrade');
  const cntSvip = document.getElementById('storeContentSVIP');
  const cntUp = document.getElementById('storeContentUpgrade');

  if (tab === 'svip') {
    if (btnSvip) btnSvip.classList.add('active');
    if (btnUp) btnUp.classList.remove('active');
    if (cntSvip) cntSvip.style.display = 'block';
    if (cntUp) cntUp.style.display = 'none';
  } else {
    if (btnUp) btnUp.classList.add('active');
    if (btnSvip) btnSvip.classList.remove('active');
    if (cntUp) cntUp.style.display = 'block';
    if (cntSvip) cntSvip.style.display = 'none';
  }
}
window.switchStoreSubTab = switchStoreSubTab;

async function loadStore() {
  try {
    const res = await apiCall('api/miners.php?action=catalog');
    State.minersCatalog = res.miners || [];

    // Fetch user miners & hourly speed
    try {
      const myMinersRes = await apiCall('api/miners.php?action=my');
      if (myMinersRes && myMinersRes.miners) {
        const totalDailyYield = myMinersRes.miners.reduce((acc, m) => acc + (parseFloat(m.daily_yield_coins) || 0), 0);
        const hourlyYield = (totalDailyYield / 24).toFixed(6);
        const elSpeed = document.getElementById('storeSpeedValue');
        if (elSpeed) {
          elSpeed.innerHTML = `${hourlyYield}<br><span style="font-size: 0.65rem; color: #8a8793; font-weight: 500;">SUPPER/H</span>`;
        }

        const userLevel = myMinersRes.level || Math.max(1, 1 + Math.floor((myMinersRes.packages_count || myMinersRes.miners.length) / 10));
        document.querySelectorAll('.user-level-display, .nav-level-badge').forEach(el => {
          el.innerText = `Level ${userLevel}`;
        });
      }
    } catch (err) {
      console.warn('Could not fetch active miners for speed:', err);
    }

    renderStoreMiners();
  } catch (err) {
    console.error('Failed to load store:', err);
  }
}

function renderStoreMiners() {
  const container = document.getElementById('storeMinersGrid');
  if (!container) return;

  const currentLangCode = localStorage.getItem('minex_lang') || 'vi';
  const dict = (window.I18N_DICTIONARY && window.I18N_DICTIONARY[currentLangCode]) ? window.I18N_DICTIONARY[currentLangCode] : (window.I18N_DICTIONARY ? window.I18N_DICTIONARY.vi : {});
  const coinPrice = (State.settings && State.settings.coin_price_usdt) ? parseFloat(State.settings.coin_price_usdt) : 0.001;
  const coinSymbol = (State.settings && State.settings.coin_symbol) || 'SUPPER';
  const userUsdt = State.user ? (State.user.usdt_balance || 0) : 0;

  container.innerHTML = State.minersCatalog.map(m => {
    const dailyCoins = m.daily_yield_coins || 0;
    const perSec = (dailyCoins / 86400).toFixed(6);
    const dailyUsdt = (dailyCoins * coinPrice).toFixed(2);

    return `
      <div class="glass-card miner-card col-3" style="border-radius: 18px; padding: 20px 18px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <div>
              <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em; display: block; margin-bottom: 4px;">${dict.store_price || 'GIÁ GÓI'}</span>
              <div style="display: inline-flex; align-items: center; gap: 8px; background: #000; padding: 5px 12px 5px 6px; border-radius: 28px; border: 1px solid rgba(38,161,123,0.4); box-shadow: 0 4px 15px rgba(0,0,0,0.8), 0 0 10px rgba(38,161,123,0.25);">
                <img src="assets/usdt.png" alt="USDT" style="width: 26px; height: 26px; border-radius: 50%; box-shadow: 0 0 8px rgba(38,161,123,0.5); object-fit: contain; flex-shrink: 0;">
                <span style="font-size: 1.45rem; font-weight: 900; color: #fff; font-family: var(--font-mono); line-height: 1;">
                  $${Number(m.price_usdt).toLocaleString()} <span style="font-size: 0.82rem; color: #26a17b; font-weight: 800;">USDT</span>
                </span>
              </div>
            </div>
            <span class="badge" style="background: rgba(0,0,0,0.7); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); font-size: 0.72rem; padding: 4px 10px; border-radius: 20px; font-weight: 700; box-shadow: 0 2px 8px rgba(0,0,0,0.5);">● 24/7</span>
          </div>

          <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 14px; color: #f1f5f9;">${m.name}</h3>

          <div style="background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 12px 14px; margin-bottom: 16px; display: flex; flex-direction: column; gap: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: 0.82rem; color: var(--text-muted);">${dict.store_speed || 'Tốc độ đào'}:</span>
              <span style="font-family: var(--font-mono); font-size: 0.95rem; font-weight: 700; color: #38bdf8;">+${perSec} ${coinSymbol}/s</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: 0.82rem; color: var(--text-muted);">${dict.store_daily || 'Thu nhập'}:</span>
              <span style="font-family: var(--font-mono); font-size: 0.95rem; font-weight: 800; color: #fbbf24;">+${Number(dailyCoins).toLocaleString()} ${coinSymbol} <span style="color:#fbbf24; font-size:0.8rem; font-weight:600;">(≈ $${dailyUsdt})</span></span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: 0.82rem; color: var(--text-muted);">${dict.store_power || 'Công suất'}:</span>
              <span style="font-family: var(--font-mono); font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">${m.hashrate} ${m.unit || 'TH/s'}</span>
            </div>
          </div>
        </div>

        <div>
          <button class="btn btn-primary" style="width: 100%; font-weight: 700; border-radius: 12px; padding: 12px; display: flex; align-items: center; justify-content: center; gap: 8px;" onclick="openSvipBuyModal('${m.id}')">
            <span>🛒 ${dict.store_btn_buy || 'Kích Hoạt Gói'}</span>
            <span style="display: inline-flex; align-items: center; gap: 5px; background: rgba(0,0,0,0.5); padding: 3px 9px 3px 6px; border-radius: 16px; font-family: var(--font-mono); font-size: 0.86rem; border: 1px solid rgba(255,255,255,0.1);">
              <img src="assets/usdt.png" alt="USDT" style="width: 16px; height: 16px; border-radius: 50%;">
              $${Number(m.price_usdt).toLocaleString()} USDT
            </span>
          </button>
        </div>
      </div>
    `;
  }).join('');
}
window.renderStoreMiners = renderStoreMiners;

let selectedMinerForBuy = null;

function openSvipBuyModal(minerId) {
  if (!State.user) {
    switchTab('user');
    showToast('Vui lòng đăng nhập để mua máy đào!', 'info');
    return;
  }

  let miner = null;
  if (State.minersCatalog && State.minersCatalog.length > 0) {
    miner = State.minersCatalog.find(m => m.id === minerId);
  }

  // Fallback defaults if catalog hasn't loaded yet
  if (!miner) {
    if (minerId === 'miner_upgrade') {
      miner = {
        id: 'miner_upgrade',
        name: 'SVIP Pro (Nâng Cấp)',
        price_usdt: 50,
        daily_yield_coins: 30000,
        hashrate: '250',
        unit: 'TH/s'
      };
    } else {
      miner = {
        id: 'miner_svip',
        name: 'SVIP',
        price_usdt: 10,
        daily_yield_coins: 6000,
        hashrate: '50',
        unit: 'TH/s'
      };
    }
  }

  selectedMinerForBuy = miner;

  const elTitle = document.getElementById('modalMinerTitle');
  const elDaily = document.getElementById('modalMinerDailyOutput');
  const elPrice = document.getElementById('modalMinerPrice');
  const qtyInput = document.getElementById('buyModalQty');

  if (elTitle) elTitle.innerText = miner.name.includes('SVIP Pro') ? 'SVIP Pro' : 'SVIP';
  if (elDaily) elDaily.innerText = `${Number(miner.daily_yield_coins).toLocaleString()} SUPPER`;
  if (elPrice) elPrice.innerText = `${Number(miner.price_usdt).toLocaleString()} USDT`;
  if (qtyInput) qtyInput.value = 1;

  openModal('buyMinerModal');
}
window.openSvipBuyModal = openSvipBuyModal;
window.openBuyMinerModal = openSvipBuyModal;

function stepSvipQuantity(delta) {
  const input = document.getElementById('buyModalQty');
  if (!input) return;
  let val = parseInt(input.value) || 1;
  val = Math.max(1, Math.min(1000, val + delta));
  input.value = val;
  updateSvipModalPrice();
}
window.stepSvipQuantity = stepSvipQuantity;

function onSvipQuantityChange() {
  const input = document.getElementById('buyModalQty');
  if (!input) return;
  let val = parseInt(input.value) || 1;
  if (val < 1) val = 1;
  if (val > 1000) val = 1000;
  input.value = val;
  updateSvipModalPrice();
}
window.onSvipQuantityChange = onSvipQuantityChange;

function updateSvipModalPrice() {
  if (!selectedMinerForBuy) return;
  const input = document.getElementById('buyModalQty');
  const qty = Math.max(1, parseInt(input ? input.value : 1) || 1);
  const elDaily = document.getElementById('modalMinerDailyOutput');
  const elPrice = document.getElementById('modalMinerPrice');

  const unitDaily = parseFloat(selectedMinerForBuy.daily_yield_coins) || 6000;
  const unitPrice = parseFloat(selectedMinerForBuy.price_usdt) || 10;

  if (elDaily) elDaily.innerText = `${Number(unitDaily * qty).toLocaleString()} SUPPER`;
  if (elPrice) elPrice.innerText = `${Number(unitPrice * qty).toLocaleString()} USDT`;
}

async function executeBuyMiner() {
  if (!selectedMinerForBuy) {
    selectedMinerForBuy = { id: 'miner_svip', name: 'SVIP', price_usdt: 10, daily_yield_coins: 6000 };
  }
  if (!State.user) {
    switchTab('user');
    showToast('Vui lòng đăng nhập!', 'info');
    return;
  }

  const input = document.getElementById('buyModalQty');
  const quantity = Math.max(1, parseInt(input ? input.value : 1) || 1);
  const totalPrice = (parseFloat(selectedMinerForBuy.price_usdt) || 10) * quantity;
  const userUsdt = State.user ? (parseFloat(State.user.usdt_balance) || 0) : 0;

  if (userUsdt < totalPrice) {
    showToast(`Số dư USDT không đủ (${userUsdt.toFixed(2)} / ${totalPrice} USDT). Vui lòng nạp thêm!`, 'error');
    return;
  }

  const btnConfirm = document.getElementById('btnConfirmSvipBuy');
  if (btnConfirm) {
    btnConfirm.disabled = true;
    btnConfirm.innerText = 'Đang xử lý...';
  }

  try {
    const res = await apiCall('api/miners.php?action=buy', 'POST', {
      minerId: selectedMinerForBuy.id,
      quantity: quantity
    });

    showToast(res.message || 'Mua gói đào thành công!', 'success');
    closeModal('buyMinerModal');

    if (res.level) {
      document.querySelectorAll('.user-level-display, .nav-level-badge').forEach(el => {
        el.innerText = `Level ${res.level}`;
      });
    }

    await checkSession();
    await loadStore();
    await loadMiningData();
    if (typeof loadHistory === 'function') loadHistory();
  } catch (err) {
    showToast(err.message || 'Lỗi khi mua gói đào', 'error');
  } finally {
    if (btnConfirm) {
      btnConfirm.disabled = false;
      btnConfirm.innerText = 'Mua sắm';
    }
  }
}
window.executeBuyMiner = executeBuyMiner;
window.buyMiner = openBuyMinerModal;

// -------------------------------------------------------------
// WALLET & SWAP
// -------------------------------------------------------------
async function loadWallet() {
  if (!State.user) return;
  try {
    const data = await apiCall('api/wallet.php?action=summary');
    document.getElementById('walletDepositAddress').innerText = data.deposit_address;
    document.getElementById('walletDepositNetwork').innerText = data.network;
    document.getElementById('walletMinDeposit').innerText = `$${data.min_deposit} USDT`;
    document.getElementById('walletMinWithdraw').innerText = `$${data.min_withdraw} USDT`;
    document.getElementById('walletWithdrawFee').innerText = `${data.withdraw_fee_percent}%`;
    document.getElementById('walletSwapRate').innerText = `1 ${data.coin_symbol} = $${data.coin_price_usdt} USDT`;

    updateSwapPreview();
  } catch (err) {
    console.error('Failed to load wallet summary:', err);
  }
}

function switchWalletTab(tab) {
  document.querySelectorAll('.wallet-subtab').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.wallet-nav-btn').forEach(el => el.classList.remove('active'));

  const activeSubtab = document.getElementById(`walletTab-${tab}`);
  if (activeSubtab) activeSubtab.style.display = 'block';

  const activeBtn = document.getElementById(`walletNavBtn-${tab}`);
  if (activeBtn) activeBtn.classList.add('active');
}

function copyDepositAddress() {
  const addr = document.getElementById('walletDepositAddress').innerText;
  navigator.clipboard.writeText(addr);
  showToast('Đã sao chép địa chỉ ví USDT vào clipboard!', 'info');
}

async function handleDeposit(e) {
  e.preventDefault();
  const amount = parseFloat(document.getElementById('depAmount').value);
  const txHash = document.getElementById('depTxHash').value;

  try {
    const res = await apiCall('api/wallet.php?action=deposit', 'POST', { amount, txHash });
    showToast(res.message, 'success');
    document.getElementById('depTxHash').value = '';
    await checkSession();
    switchTab('history');
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function handleWithdraw(e) {
  e.preventDefault();
  const amount = parseFloat(document.getElementById('wdAmount').value);
  const address = document.getElementById('wdAddress').value;

  try {
    const res = await apiCall('api/wallet.php?action=withdraw', 'POST', { amount, address });
    showToast(res.message, 'success');
    document.getElementById('wdAddress').value = '';
    await checkSession();
    switchTab('history');
  } catch (err) {
    showToast(err.message, 'error');
  }
}

function updateSwapPreview() {
  const input = document.getElementById('swapCoinAmount');
  if (!input) return;
  const coins = parseFloat(input.value) || 0;
  const rate = State.settings.coin_price_usdt || 0.001;
  const usdt = (coins * rate).toFixed(4);

  const previewEl = document.getElementById('swapUsdtPreview');
  if (previewEl) previewEl.innerText = `+${usdt} USDT`;
}

function setSwapPercent(ratio) {
  if (!State.user) return;
  const coins = (State.user.coin_balance || 0) * ratio;
  const input = document.getElementById('swapCoinAmount');
  if (input) {
    input.value = coins.toFixed(4);
    updateSwapPreview();
  }
}

async function handleSwap(e) {
  e.preventDefault();
  const coinAmount = parseFloat(document.getElementById('swapCoinAmount').value);

  try {
    const res = await apiCall('api/wallet.php?action=swap', 'POST', { coinAmount });
    showToast(res.message, 'success');
    await checkSession();
    await loadWallet();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// -------------------------------------------------------------
// TRANSACTIONS HISTORY & HOÁ ĐƠN (CHUẨN 100% THEO ẢNH)
// -------------------------------------------------------------
let currentInvoiceFilter = 'all';

function formatTxDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  const pad = n => String(n).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
}

function getTxDisplayName(tx) {
  switch (tx.type) {
    case 'deposit':
      return 'Nạp tiền';
    case 'commission':
      return 'phần thưởng hoa hồng';
    case 'withdraw':
      return 'Rút tiền';
    case 'buy_miner':
      return 'Mua gói máy đào';
    case 'swap':
      return 'Hoán đổi sang USDT';
    case 'claim':
      return 'Nhận sản lượng khai thác';
    default:
      return tx.type || 'Giao dịch';
  }
}

function renderInvoiceList(transactions, containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;

  if (!transactions || transactions.length === 0) {
    container.innerHTML = `
      <div style="text-align: center; padding: 48px 16px; color: #8e8c94;">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 12px; opacity: 0.5;">
          <rect x="4" y="3" width="16" height="18" rx="2"/>
          <line x1="8" y1="8" x2="16" y2="8"/>
          <line x1="8" y1="12" x2="16" y2="12"/>
          <line x1="8" y1="16" x2="12" y2="16"/>
        </svg>
        <div style="font-size: 0.95rem; font-weight: 500;">Chưa có giao dịch nào</div>
      </div>
    `;
    return;
  }

  container.innerHTML = transactions.map(tx => {
    const isCredit = tx.type === 'deposit' || tx.type === 'commission' || tx.type === 'claim' || (tx.type === 'swap' && Number(tx.detail?.usdt_received || 0) > 0);
    const sign = isCredit ? '+' : '-';
    let rawAmount = Number(tx.amount || 0);
    if (tx.type === 'swap' && tx.detail?.usdt_received) {
      rawAmount = Number(tx.detail.usdt_received);
    }
    const amountStr = `${sign}${rawAmount.toFixed(2)}`;
    const currency = (tx.currency === 'MNX' ? 'SUPPER' : (tx.currency || 'USDT'));
    const isUsdt = currency === 'USDT';
    const dateFormatted = formatTxDate(tx.created_at);
    const displayName = getTxDisplayName(tx);

    const iconHtml = isUsdt
      ? `<img src="assets/usdt.png" alt="USDT" class="invoice-usdt-icon">`
      : `<div class="invoice-usdt-icon" style="background: rgba(245,158,11,0.2); border: 1.5px solid rgba(245,158,11,0.5); display: flex; align-items: center; justify-content: center;">
           <span style="color:#fbbf24; font-weight:800; font-size:1.15rem;">E</span>
         </div>`;

    return `
      <div class="invoice-tx-item">
        <div class="invoice-tx-left">
          ${iconHtml}
          <div class="invoice-tx-meta">
            <div class="invoice-tx-name">${displayName}</div>
            <div class="invoice-tx-date">${dateFormatted}</div>
          </div>
        </div>
        <div class="invoice-tx-right">
          <div class="invoice-tx-amount">${amountStr}</div>
          <div class="invoice-tx-unit">
            <span>${currency}</span>
            <svg class="invoice-check-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

async function loadInvoiceHistory(filterType = currentInvoiceFilter) {
  currentInvoiceFilter = filterType;
  if (!State.user) return;
  try {
    const res = await apiCall('api/wallet.php?action=transactions');
    State.transactions = res.transactions || [];

    // Live USDT balance from API or State
    const rawBal = Number(res.usdt_balance ?? State.user.usdt_balance ?? 0);
    const formattedBal = rawBal.toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

    const elBal1 = document.getElementById('invoiceUsdtBalance');
    if (elBal1) elBal1.innerText = formattedBal;
    const elBal2 = document.getElementById('modalInvoiceUsdtBalance');
    if (elBal2) elBal2.innerText = formattedBal;

    // Filter transactions
    const filtered = State.transactions.filter(t => {
      if (filterType === 'all') return true;
      return t.type === filterType;
    });

    renderInvoiceList(filtered, 'invoiceTransactionList');
    renderInvoiceList(filtered, 'modalInvoiceTransactionList');
  } catch (err) {
    console.error('Failed to load invoice history:', err);
  }
}
window.loadInvoiceHistory = loadInvoiceHistory;

function loadHistory() {
  return loadInvoiceHistory(currentInvoiceFilter || 'all');
}
window.loadHistory = loadHistory;

function toggleInvoiceFilter(e) {
  if (e) e.stopPropagation();
  const menu = document.getElementById('invoiceFilterMenu');
  if (!menu) return;
  menu.style.display = (menu.style.display === 'none' || !menu.style.display) ? 'flex' : 'none';
}
window.toggleInvoiceFilter = toggleInvoiceFilter;

function selectInvoiceFilter(type) {
  const menu = document.getElementById('invoiceFilterMenu');
  if (menu) menu.style.display = 'none';

  document.querySelectorAll('#invoiceFilterMenu .filter-opt').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.filter === type);
  });
  loadInvoiceHistory(type);
}
window.selectInvoiceFilter = selectInvoiceFilter;

function toggleModalInvoiceFilter(e) {
  if (e) e.stopPropagation();
  const menu = document.getElementById('modalInvoiceFilterMenu');
  if (!menu) return;
  menu.style.display = (menu.style.display === 'none' || !menu.style.display) ? 'flex' : 'none';
}
window.toggleModalInvoiceFilter = toggleModalInvoiceFilter;

function selectModalInvoiceFilter(type) {
  const menu = document.getElementById('modalInvoiceFilterMenu');
  if (menu) menu.style.display = 'none';

  document.querySelectorAll('#modalInvoiceFilterMenu .filter-opt').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.filter === type);
  });
  loadInvoiceHistory(type);
}
window.selectModalInvoiceFilter = selectModalInvoiceFilter;

// Close filter menus on outside click
document.addEventListener('click', (e) => {
  const menu1 = document.getElementById('invoiceFilterMenu');
  const trigger1 = document.getElementById('invoiceFilterTriggerBtn');
  if (menu1 && menu1.style.display === 'flex' && !menu1.contains(e.target) && (!trigger1 || !trigger1.contains(e.target))) {
    menu1.style.display = 'none';
  }
  const menu2 = document.getElementById('modalInvoiceFilterMenu');
  const trigger2 = document.getElementById('modalInvoiceFilterTriggerBtn');
  if (menu2 && menu2.style.display === 'flex' && !menu2.contains(e.target) && (!trigger2 || !trigger2.contains(e.target))) {
    menu2.style.display = 'none';
  }
});

// -------------------------------------------------------------
// ADMIN MANAGEMENT
// -------------------------------------------------------------
async function loadAdmin() {
  if (!State.user || State.user.role !== 'admin') return;

  try {
    // 1. Overview
    const overview = await apiCall('api/admin.php?action=overview');
    document.getElementById('admTotalUsers').innerText = overview.total_users;
    document.getElementById('admPendingCount').innerText = overview.pending_deposits_count + overview.pending_withdrawals_count;
    document.getElementById('admActiveMiners').innerText = overview.active_miners;
    document.getElementById('admCoinPrice').innerText = `$${overview.settings.coin_price_usdt}`;

    // Fill settings form
    document.getElementById('admSetCoinPrice').value = overview.settings.coin_price_usdt;
    document.getElementById('admSetDepositAddress').value = overview.settings.usdt_deposit_address;
    document.getElementById('admSetNetwork').value = overview.settings.network || 'USDT (TRC20)';
    document.getElementById('admSetMinDep').value = overview.settings.min_deposit;
    document.getElementById('admSetMinWd').value = overview.settings.min_withdraw;
    document.getElementById('admSetWdFee').value = overview.settings.withdraw_fee_percent;

    // 2. Pending Transactions
    const txsRes = await apiCall('api/admin.php?action=transactions');
    renderAdminTransactions(txsRes.transactions || []);

    // 3. Users list
    const usersRes = await apiCall('api/admin.php?action=users');
    renderAdminUsers(usersRes.users || []);

    // 4. Miners catalog
    const minersRes = await apiCall('api/admin.php?action=miners');
    renderAdminMiners(minersRes.miners || []);
  } catch (err) {
    console.error('Failed to load admin:', err);
  }
}

function renderAdminTransactions(txs) {
  const container = document.getElementById('admTxTableBody');
  if (!container) return;

  container.innerHTML = txs.map(tx => `
    <tr>
      <td style="font-family: var(--font-mono); font-size: 0.78rem;">#${tx.id.slice(0, 10)}</td>
      <td style="font-size: 0.8rem;">${tx.user_id}</td>
      <td><strong>${tx.type}</strong></td>
      <td style="font-family: var(--font-mono); font-weight: 700;">${tx.amount} ${tx.currency}</td>
      <td style="font-size: 0.8rem; color: var(--text-muted);">
        ${tx.detail?.recipient_address ? `Ví: ${tx.detail.recipient_address}` : ''}
        ${tx.detail?.tx_hash ? `Hash: ${tx.detail.tx_hash}` : ''}
        ${tx.notes ? `<div style="color:#fbbf24;">Note: ${tx.notes}</div>` : ''}
      </td>
      <td style="font-size: 0.78rem; color: var(--text-dim);">${new Date(tx.created_at).toLocaleString('vi-VN')}</td>
      <td><span class="badge badge-${tx.status}">${tx.status}</span></td>
      <td>
        ${tx.status === 'pending' ? `
          <button class="btn btn-success btn-sm" onclick="approveTx('${tx.id}')">Duyệt</button>
          <button class="btn btn-danger btn-sm" onclick="rejectTx('${tx.id}')">Từ Chối</button>
        ` : '<span style="color:var(--text-dim); font-size: 0.78rem;">Đã duyệt</span>'}
      </td>
    </tr>
  `).join('');
}

async function approveTx(id) {
  try {
    const res = await apiCall('api/admin.php?action=approve_tx', 'POST', { id });
    showToast(res.message, 'success');
    await loadAdmin();
    await checkSession();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function rejectTx(id) {
  const reason = prompt('Lý do từ chối:', 'Thông tin không hợp lệ');
  if (reason === null) return;

  try {
    const res = await apiCall('api/admin.php?action=reject_tx', 'POST', { id, reason });
    showToast(res.message, 'success');
    await loadAdmin();
    await checkSession();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

function renderAdminUsers(users) {
  const container = document.getElementById('admUsersTableBody');
  if (!container) return;

  container.innerHTML = users.map(u => `
    <tr>
      <td>
        <strong style="color: #fff;">${u.name || 'Thợ đào'}</strong>
        <div style="font-size: 0.78rem; color: var(--text-dim);">${u.email}</div>
      </td>
      <td><span class="badge ${u.role === 'admin' ? 'badge-pending' : 'badge-completed'}">${u.role}</span></td>
      <td style="font-family: var(--font-mono); color: #fbbf24; font-weight: 700;">${Number(u.usdt_balance || 0).toFixed(2)} USDT</td>
      <td style="font-family: var(--font-mono); color: #22d3ee; font-weight: 700;">${Number(u.coin_balance || 0).toFixed(4)} SUPPER</td>
      <td>${u.active_miners_count} máy</td>
      <td style="font-family: var(--font-mono); color: #a78bfa;">${u.hashrate || 0} TH/s</td>
      <td>
        <button class="btn btn-secondary btn-sm" onclick="openAdjustBalanceModal('${u.id}', '${u.email}')">
          Chỉnh Số Dư
        </button>
      </td>
    </tr>
  `).join('');
}

function openAdjustBalanceModal(userId, email) {
  document.getElementById('adjUserId').value = userId;
  document.getElementById('adjUserEmail').innerText = email;
  openModal('adjustBalanceModal');
}

async function handleSaveAdjustBalance(e) {
  e.preventDefault();
  const userId = document.getElementById('adjUserId').value;
  const usdtAmount = parseFloat(document.getElementById('adjUsdtAmount').value) || 0;
  const coinAmount = parseFloat(document.getElementById('adjCoinAmount').value) || 0;
  const reason = document.getElementById('adjReason').value;

  try {
    const res = await apiCall('api/admin.php?action=adjust_balance', 'POST', {
      userId, usdtAmount, coinAmount, reason
    });
    showToast(res.message, 'success');
    closeModal('adjustBalanceModal');
    await loadAdmin();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function handleSaveAdminSettings(e) {
  e.preventDefault();
  const settings = {
    coin_price_usdt: parseFloat(document.getElementById('admSetCoinPrice').value),
    usdt_deposit_address: document.getElementById('admSetDepositAddress').value.trim(),
    network: document.getElementById('admSetNetwork').value.trim(),
    min_deposit: parseFloat(document.getElementById('admSetMinDep').value),
    min_withdraw: parseFloat(document.getElementById('admSetMinWd').value),
    withdraw_fee_percent: parseFloat(document.getElementById('admSetWdFee').value)
  };

  try {
    const res = await apiCall('api/admin.php?action=update_settings', 'POST', { settings });
    showToast(res.message, 'success');
    State.settings = res.settings;
    updateUserInterface();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

function renderAdminMiners(miners) {
  const container = document.getElementById('admMinersTableBody');
  if (!container) return;

  container.innerHTML = miners.map(m => `
    <tr>
      <td><strong>${m.name}</strong></td>
      <td>${m.tier}</td>
      <td style="font-family: var(--font-mono); color: #22d3ee;">${m.hashrate} ${m.unit || 'TH/s'}</td>
      <td style="font-family: var(--font-mono); color: #fbbf24; font-weight: 700;">$${m.price_usdt} USDT</td>
      <td style="font-family: var(--font-mono); color: #fbbf24;">+${m.daily_yield_coins} SUPPER</td>
      <td style="color: var(--text-dim);">${m.power_consumption}</td>
      <td><span class="badge badge-completed">Đang Bán</span></td>
    </tr>
  `).join('');
}

async function handleAddMiner(e) {
  e.preventDefault();
  const name = document.getElementById('newMinerName').value;
  const tier = document.getElementById('newMinerTier').value;
  const hashrate = parseFloat(document.getElementById('newMinerHashrate').value);
  const price_usdt = parseFloat(document.getElementById('newMinerPrice').value);
  const daily_yield_coins = parseFloat(document.getElementById('newMinerDaily').value);
  const power_consumption = document.getElementById('newMinerPower').value;

  try {
    const res = await apiCall('api/admin.php?action=add_miner', 'POST', {
      name, tier, hashrate, price_usdt, daily_yield_coins, power_consumption
    });
    showToast(res.message, 'success');
    closeModal('addMinerModal');
    await loadAdmin();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// -------------------------------------------------------------
// IPHONE 17 PRO MAX SIMULATOR CONTROLS
// -------------------------------------------------------------
let isIphoneMode = false; // Luôn hiển thị giao diện Mobile & Telegram Mini App chuẩn 100%

function initIphoneSimulator() {
  applyIphoneFrameState();
}

// Lắng nghe thay đổi kích thước màn hình
window.addEventListener('resize', () => {
  applyIphoneFrameState();
});

function applyIphoneFrameState() {
  const container = document.getElementById('iphoneScaleContainer');
  const chassis = document.getElementById('iphoneChassis');
  const wrapper = document.getElementById('deviceWrapper');
  const topBar = document.getElementById('iphoneTopBar');
  const bottomBar = document.getElementById('iphoneBottomBar');

  if (wrapper) {
    wrapper.style.padding = '0';
    wrapper.style.margin = '0 auto';
    wrapper.style.maxWidth = '440px';
    wrapper.style.width = '100%';
  }
  if (container) {
    container.style.width = '100%';
    container.style.maxWidth = '440px';
    container.style.height = 'auto';
    container.style.transform = 'none';
  }
  if (chassis) {
    chassis.style.width = '100%';
    chassis.style.maxWidth = '440px';
    chassis.style.transform = 'none';
    chassis.style.zoom = '1';
    chassis.style.border = 'none';
    chassis.style.boxShadow = 'none';
  }
  if (topBar) topBar.style.display = 'none';
  if (bottomBar) bottomBar.style.display = 'flex';
}

function scaleIphone(factor) {
  // Mobile mode keeps clean 100% responsive scale
  applyIphoneFrameState();
}

// -------------------------------------------------------------
// 3D GOLDEN COIN IMAGE HANDLER
// -------------------------------------------------------------
function handleCustomCoinImage(input) {
  if (!input || !input.files || !input.files[0]) return;
  const file = input.files[0];
  const img = document.getElementById('mainCoinImg');
  if (!img) return;

  const reader = new FileReader();
  reader.onload = function(e) {
    img.src = e.target.result;
    showToast(`Đã áp dụng ảnh đồng coin "${file.name}"!`, 'success');
  };
  reader.readAsDataURL(file);
}

// -------------------------------------------------------------
// REFERRAL & 10% F1 COMMISSION PROGRAM HANDLERS
// -------------------------------------------------------------
function initReferralTracking() {
  try {
    const urlParams = new URLSearchParams(window.location.search);
    const refCode = urlParams.get('ref') || urlParams.get('r');
    if (refCode) {
      localStorage.setItem('minex_ref', refCode.trim());
    }
    const savedRef = localStorage.getItem('minex_ref');
    if (savedRef) {
      const regRef = document.getElementById('regRefCode');
      if (regRef && !regRef.value) regRef.value = savedRef;
      const userRegRef = document.getElementById('userPageRegRefCode');
      if (userRegRef && !userRegRef.value) userRegRef.value = savedRef;
    }
  } catch (e) {
    console.error('Ref parse error', e);
  }
}

async function loadReferralData() {
  const uid = (State.user && State.user.uid) ? State.user.uid : '120850';
  const origin = window.location.origin && window.location.origin !== 'null' ? window.location.origin : 'http://localhost:8000';
  const pathname = window.location.pathname || '/';
  const refLink = `${origin}${pathname}?ref=${uid}`;

  const elUid = document.getElementById('refMyUidDisplay');
  if (elUid) elUid.innerText = uid;

  const elLinkInput = document.getElementById('refMyLinkInput');
  if (elLinkInput) elLinkInput.value = refLink;

  if (!State.user) return;

  try {
    const res = await apiCall('api/auth.php?action=referral_stats');
    if (res) {
      const elF1 = document.getElementById('refStatsF1Count');
      if (elF1) elF1.innerText = res.f1_count || 0;

      const elComm = document.getElementById('refStatsTotalComm');
      if (elComm) elComm.innerText = `$${Number(res.total_commission || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

      let totalF1Spent = 0;
      if (res.f1_users && res.f1_users.length > 0) {
        totalF1Spent = res.f1_users.reduce((acc, u) => acc + (parseFloat(u.total_spent) || 0), 0);
      }
      const elSpent = document.getElementById('refStatsF1Spent');
      if (elSpent) elSpent.innerText = `$${Number(totalF1Spent).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

      // Render F1 List
      const elF1List = document.getElementById('refF1ListContainer');
      if (elF1List) {
        if (!res.f1_users || res.f1_users.length === 0) {
          elF1List.innerHTML = `<div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 0.85rem;" data-i18n="ref_no_f1">Chưa có thành viên F1 nào. Hãy chia sẻ link giới thiệu ngay để bắt đầu nhận 10% hoa hồng!</div>`;
        } else {
          elF1List.innerHTML = `
            <div style="display: flex; flex-direction: column; gap: 8px;">
              ${res.f1_users.map(u => `
                <div style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                  <div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                      <strong style="color: #fff; font-size: 0.9rem;">${u.name || 'Thợ đào'}</strong>
                      <span class="badge" style="background: rgba(56,189,248,0.15); color: #38bdf8; font-size: 0.72rem; padding: 2px 6px;">UID: ${u.uid}</span>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 3px;">
                      Tham gia: ${new Date(u.created_at).toLocaleDateString('vi-VN')} • Gói sở hữu: <strong style="color: #fbbf24;">${u.miners_count || 0}</strong>
                    </div>
                  </div>
                  <div style="text-align: right;">
                    <div style="font-size: 0.72rem; color: var(--text-muted);">Doanh số F1:</div>
                    <div style="font-family: var(--font-mono); font-weight: 700; color: #34d399; font-size: 0.92rem;">$${Number(u.total_spent || 0).toLocaleString()} USDT</div>
                  </div>
                </div>
              `).join('')}
            </div>
          `;
        }
      }

      // Render Commission History
      const elCommList = document.getElementById('refCommissionHistoryContainer');
      if (elCommList) {
        if (!res.commissions || res.commissions.length === 0) {
          elCommList.innerHTML = `<div style="text-align: center; padding: 14px; color: var(--text-muted); font-size: 0.82rem;" data-i18n="ref_no_comm">Chưa có giao dịch hoa hồng nào được ghi nhận.</div>`;
        } else {
          elCommList.innerHTML = `
            <div style="display: flex; flex-direction: column; gap: 8px;">
              ${res.commissions.map(c => {
                let det = {};
                try { det = JSON.parse(c.detail); } catch(e){}
                return `
                  <div style="background: rgba(16,185,129,0.06); border: 1px dashed rgba(16,185,129,0.25); border-radius: 12px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <div>
                      <div style="font-size: 0.86rem; font-weight: 700; color: #fff;">${det.note || 'Hoa hồng 10% tuyến trên'}</div>
                      <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                        ${new Date(c.created_at).toLocaleString('vi-VN')}
                      </div>
                    </div>
                    <div style="font-family: var(--font-mono); font-size: 1.05rem; font-weight: 800; color: #34d399;">
                      +${Number(c.amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} USDT
                    </div>
                  </div>
                `;
              }).join('')}
            </div>
          `;
        }
      }
    }
  } catch (err) {
    console.error('Failed to load referral stats', err);
  }
}

function copyReferralCode() {
  const uid = (State.user && State.user.uid) ? State.user.uid : '120850';
  navigator.clipboard.writeText(uid).then(() => {
    showToast(`Đã sao chép mã giới thiệu: ${uid}`, 'success');
  }).catch(() => {
    showToast(`Mã giới thiệu: ${uid}`, 'info');
  });
}

function copyReferralLink() {
  const el = document.getElementById('refMyLinkInput');
  const uid = (State.user && State.user.uid) ? State.user.uid : '120850';
  const origin = window.location.origin && window.location.origin !== 'null' ? window.location.origin : 'http://localhost:8000';
  const pathname = window.location.pathname || '/';
  const link = (el && el.value) ? el.value : `${origin}${pathname}?ref=${uid}`;

  navigator.clipboard.writeText(link).then(() => {
    showToast('Đã sao chép liên kết giới thiệu thành công!', 'success');
  }).catch(() => {
    showToast(`Link giới thiệu: ${link}`, 'info');
  });
}

function shareReferralLink() {
  const uid = (State.user && State.user.uid) ? State.user.uid : '120850';
  const botUsername = 'SUPPERAI5_BOT';
  const tgAppUrl = `https://t.me/${botUsername}/app?startapp=${uid}`;
  const shareText = `⛏️ Tham gia đào SUPPER AI cùng tôi trên Telegram! Nhận ngay 100 USDT khởi nghiệp và gói máy đào SVIP 6000 SUPPER/ngày:`;

  triggerHaptic('medium');

  if (window.Telegram && window.Telegram.WebApp && window.Telegram.WebApp.openTelegramLink) {
    window.Telegram.WebApp.openTelegramLink(`https://t.me/share/url?url=${encodeURIComponent(tgAppUrl)}&text=${encodeURIComponent(shareText)}`);
    return;
  }

  const origin = window.location.origin && window.location.origin !== 'null' ? window.location.origin : 'http://localhost:8000';
  const pathname = window.location.pathname || '/';
  const link = `${origin}${pathname}?ref=${uid}`;

  if (navigator.share) {
    navigator.share({
      title: 'Tham gia Đào SUPPER AI - Nhận 100 USDT Khởi Nghiệp',
      text: shareText,
      url: link
    }).catch(() => copyReferralLink());
  } else {
    window.open(`https://t.me/share/url?url=${encodeURIComponent(tgAppUrl)}&text=${encodeURIComponent(shareText)}`, '_blank');
  }
}

// -------------------------------------------------------------
// ABOUT PAGE ACTIONS & SUPPER ❯ USDT SWAP SCREEN (IMAGES 1 & 2)
// -------------------------------------------------------------

function openSupperUsdtSwapView() {
  const mainView = document.getElementById('aboutMainView');
  const swapView = document.getElementById('aboutSwapView');
  if (mainView && swapView) {
    mainView.style.display = 'none';
    swapView.style.display = 'flex';
  }
  updateSupperSwapUI();
  const scrollContainer = document.getElementById('iphoneContentScroll');
  if (scrollContainer) scrollContainer.scrollTop = 0;
}

function closeSupperUsdtSwapView() {
  const mainView = document.getElementById('aboutMainView');
  const swapView = document.getElementById('aboutSwapView');
  if (mainView && swapView) {
    swapView.style.display = 'none';
    mainView.style.display = 'flex';
  }
}

function updateSupperSwapUI() {
  const balEl = document.getElementById('supperSwapAvailableBal');
  const rateEl = document.getElementById('supperSwapRateDisplay');
  const inputEl = document.getElementById('supperSwapInput');

  const userCoin = State.user ? parseFloat(State.user.coin_balance || 0) : 420560.0128;
  const price = State.settings ? (parseFloat(State.settings.coin_price_usdt) || 0.0001) : 0.0001;

  if (balEl) {
    balEl.innerText = userCoin.toLocaleString('vi-VN', { minimumFractionDigits: 4, maximumFractionDigits: 6 });
  }

  if (rateEl) {
    rateEl.innerText = `SUPPER ≈ 0.00 USDT`;
  }

  if (inputEl) {
    if (!inputEl.value || inputEl.value === '0') {
      inputEl.value = userCoin > 0 ? (State.user ? userCoin : '420560.0128') : '';
    }
  }

  calculateSupperToUsdt();
}

function calculateSupperToUsdt() {
  const inputEl = document.getElementById('supperSwapInput');
  const receiveEl = document.getElementById('supperSwapReceiveUsdt');
  if (!inputEl || !receiveEl) return;

  const val = parseFloat(inputEl.value) || 0;
  // Calculate matching Image 2 where 420560,0128 SUPPER = 42.056001 USDT (ratio 0.0001)
  const price = State.settings ? (parseFloat(State.settings.coin_price_usdt) || 0.0001) : 0.0001;
  const usdt = val * price;

  receiveEl.innerText = usdt.toFixed(6);
}

function setSupperSwapAll() {
  const inputEl = document.getElementById('supperSwapInput');
  const userCoin = State.user ? parseFloat(State.user.coin_balance || 0) : 420560.0128;
  if (inputEl) {
    inputEl.value = userCoin;
    calculateSupperToUsdt();
  }
}

async function executeSupperSwap() {
  if (!State.user) {
    showToast('Vui lòng đăng nhập tài khoản để quy đổi SUPPER sang USDT!', 'info');
    openModal('authModal');
    return;
  }

  const inputEl = document.getElementById('supperSwapInput');
  const coinAmount = parseFloat(inputEl ? inputEl.value : 0);

  if (!coinAmount || coinAmount <= 0) {
    showToast('Vui lòng nhập số lượng SUPPER hợp lệ cần đổi!', 'error');
    return;
  }

  const userCoin = parseFloat(State.user.coin_balance || 0);
  if (coinAmount > userCoin) {
    showToast(`Số dư SUPPER không đủ (bạn hiện có ${userCoin.toFixed(4)} SUPPER)`, 'error');
    return;
  }

  const btn = document.getElementById('btnConfirmSupperSwap');
  const origText = btn ? btn.innerText : 'xác nhận';
  if (btn) {
    btn.disabled = true;
    btn.innerText = 'Đang xử lý...';
  }

  try {
    const res = await apiCall('api/wallet.php?action=swap', 'POST', { coinAmount });
    showToast(res.message || 'Quy đổi SUPPER sang USDT thành công!', 'success');
    triggerHaptic('heavy');

    if (res.coin_balance !== undefined) State.user.coin_balance = res.coin_balance;
    if (res.usdt_balance !== undefined) State.user.usdt_balance = res.usdt_balance;

    updateUserInterface();
    updateSupperSwapUI();
  } catch (err) {
    showToast(err.message || 'Lỗi quy đổi, vui lòng thử lại', 'error');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerText = origText;
    }
  }
}

// Companion Modals
function openTeamModal() {
  const uid = (State.user && State.user.uid) ? State.user.uid : '120850';
  const uidInput = document.getElementById('teamModalUidInput');
  const linkInput = document.getElementById('teamModalLinkInput');
  const botUsername = 'SUPPERAI5_BOT';

  if (uidInput) uidInput.value = uid;
  if (linkInput) linkInput.value = `https://t.me/${botUsername}/app?startapp=${uid}`;

  openModal('teamModal');
}

function openTasksModal() {
  openModal('tasksModal');
}

function openGuideModal() {
  openModal('guideModal');
}

function openRoadmapModal() {
  openModal('roadmapModal');
}

function openWhitepaperModal() {
  openModal('whitepaperModal');
}

function openP2PModal() {
  openModal('p2pModal');
}

function openContractModal() {
  openWalletAddFlow();
}

function openLeaderboardDetailModal() {
  openModal('leaderboardDetailModal');
}

function claimTaskReward(taskId, amount) {
  if (!State.user) {
    showToast('Vui lòng đăng nhập để nhận thưởng nhiệm vụ!', 'info');
    openModal('authModal');
    return;
  }
  State.user.coin_balance = (parseFloat(State.user.coin_balance) || 0) + amount;
  showToast(`Đã nhận thành công +${amount} SUPPER vào ví!`, 'success');
  triggerHaptic('medium');
  updateUserInterface();
  updateSupperSwapUI();
}

// Global Exports
window.updateUserInterface = updateUserInterface;
window.updateAuthUI = updateUserInterface;
window.renderStoreMiners = renderStoreMiners;
window.updateClaimButtonState = updateClaimButtonState;
window.quickLogin = quickLogin;
window.loadDashboard = loadDashboard;
window.handleCustomCoinImage = handleCustomCoinImage;
window.initReferralTracking = initReferralTracking;
window.loadReferralData = loadReferralData;
window.copyReferralCode = copyReferralCode;
window.copyReferralLink = copyReferralLink;
window.shareReferralLink = shareReferralLink;

// About Page & SUPPER AI Swap Exports
window.openSupperUsdtSwapView = openSupperUsdtSwapView;
window.openEdenUsdtSwapView = openSupperUsdtSwapView;
window.closeSupperUsdtSwapView = closeSupperUsdtSwapView;
window.closeEdenUsdtSwapView = closeSupperUsdtSwapView;
window.updateSupperSwapUI = updateSupperSwapUI;
window.updateEdenSwapUI = updateSupperSwapUI;
window.calculateSupperToUsdt = calculateSupperToUsdt;
window.calculateEdenToUsdt = calculateSupperToUsdt;
window.setSupperSwapAll = setSupperSwapAll;
window.setEdenSwapAll = setSupperSwapAll;
window.executeSupperSwap = executeSupperSwap;
window.executeEdenSwap = executeSupperSwap;
window.openTeamModal = openTeamModal;
window.openTasksModal = openTasksModal;
window.openGuideModal = openGuideModal;
window.openRoadmapModal = openRoadmapModal;
window.openWhitepaperModal = openWhitepaperModal;
window.openP2PModal = openP2PModal;
window.openContractModal = openContractModal;
window.openLeaderboardDetailModal = openLeaderboardDetailModal;
window.claimTaskReward = claimTaskReward;

function handleUserAccountAction() {
  if (State.user) {
    const nameEl = document.getElementById('settingsModalUserName');
    if (nameEl) nameEl.innerText = State.user.name || State.user.telegram_username || (State.user.id === 'user_demo' ? 'evansTi' : 'Thợ Đào');
    const emailEl = document.getElementById('settingsModalUserEmail');
    if (emailEl) emailEl.innerText = State.user.email || (State.user.telegram_id ? `@${State.user.telegram_username || State.user.telegram_id}` : 'user@supperai.com');
    openModal('accountSettingsModal');
  } else {
    openModal('authModal');
  }
}
window.handleUserAccountAction = handleUserAccountAction;

function confirmLogoutFromSettings() {
  closeModal('accountSettingsModal');
  handleLogout();
  showToast('Đã đăng xuất tài khoản an toàn!', 'success');
}
window.confirmLogoutFromSettings = confirmLogoutFromSettings;

// ============================================================
// WALLET ADD & SELECT NETWORK FLOW (CHUẨN ẢNH 1 VÀ ẢNH 2)
// ============================================================
let currentWalletCaptcha = '5846';

function generateWalletCaptcha() {
  const digits = Math.floor(1000 + Math.random() * 9000).toString().split('');
  currentWalletCaptcha = digits.join('');
  const badge = document.getElementById('walletCaptchaBadge');
  if (!badge) return;

  const colors = ['#2563eb', '#16a34a', '#1e293b', '#15803d', '#dc2626', '#7c3aed', '#0284c7'];
  badge.innerHTML = digits.map((d, i) => {
    const col = colors[(parseInt(d) + i) % colors.length];
    return `<span style="color: ${col}; font-weight: 900;">${d}</span>`;
  }).join('');
}

function getSavedWalletAddresses() {
  try {
    const raw = localStorage.getItem('supper_saved_wallets');
    if (raw) {
      const arr = JSON.parse(raw);
      if (Array.isArray(arr) && arr.length > 0) return arr;
    }
  } catch (e) {}
  return ['0xd90e17f8a8a6c0b749028b23828e7c188652ebbc'];
}

function saveWalletAddresses(list) {
  try {
    localStorage.setItem('supper_saved_wallets', JSON.stringify(list));
  } catch (e) {}
}

function renderSavedWalletAddressList() {
  const container = document.getElementById('savedWalletAddressList');
  if (!container) return;

  const list = getSavedWalletAddresses();
  if (list.length === 0) {
    container.innerHTML = `<div style="text-align: center; color: #64748b; font-size: 0.8rem; padding: 12px;">Chưa có địa chỉ ví nào được lưu.</div>`;
    return;
  }

  container.innerHTML = list.map((addr, idx) => `
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.04);">
      <div style="font-family: var(--font-mono); font-size: 0.82rem; color: #f1f5f9; word-break: break-all; flex: 1; line-height: 1.4;">
        ${addr}
      </div>
      <button type="button" onclick="deleteWalletAddress(${idx})" style="background: #202432; border: 1px solid rgba(255,255,255,0.08); color: #cbd5e1; font-size: 0.78rem; font-weight: 600; padding: 6px 12px; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
        Delete
      </button>
    </div>
  `).join('');
}

function openWalletAddFlow() {
  // 1. Mở màn hình nền Wallet ADD (Ảnh 2)
  generateWalletCaptcha();
  renderSavedWalletAddressList();
  openModal('walletAddModal');

  // 2. Tự động bật popup Select network lên trên cùng (Ảnh 1)
  setTimeout(() => {
    openModal('selectNetworkModal');
  }, 40);
}

function selectWalletNetwork(net) {
  // Đóng modal chọn mạng, lộ ra màn hình Wallet ADD
  closeModal('selectNetworkModal');
  showToast(`Đã chọn mạng Binance Smart Chain (${net})`, 'success');
  const input = document.getElementById('newWalletAddressInput');
  if (input) {
    input.focus();
  }
}

function submitAddNewWalletAddress() {
  const addrInput = document.getElementById('newWalletAddressInput');
  const captchaInput = document.getElementById('walletCaptchaInput');
  if (!addrInput || !captchaInput) return;

  const addr = addrInput.value.trim();
  const code = captchaInput.value.trim();

  if (!addr || addr.length < 20) {
    showToast('Vui lòng nhập địa chỉ ví hợp lệ!', 'error');
    return;
  }

  if (code !== currentWalletCaptcha) {
    showToast('Mã xác nhận không chính xác!', 'error');
    generateWalletCaptcha();
    captchaInput.value = '';
    captchaInput.focus();
    return;
  }

  const list = getSavedWalletAddresses();
  if (!list.includes(addr)) {
    list.unshift(addr);
    saveWalletAddresses(list);
  }

  // Cập nhật ô địa chỉ ví trong trang Máy Đào / Rút Tiền
  const buybackInput = document.getElementById('buybackAddressInput');
  if (buybackInput) buybackInput.value = addr;
  const wdInput = document.getElementById('wdAddress');
  if (wdInput) wdInput.value = addr;

  renderSavedWalletAddressList();
  addrInput.value = '';
  captchaInput.value = '';
  generateWalletCaptcha();

  showToast('Thêm địa chỉ ví thành công!', 'success');
}

function deleteWalletAddress(idx) {
  const list = getSavedWalletAddresses();
  if (idx >= 0 && idx < list.length) {
    list.splice(idx, 1);
    saveWalletAddresses(list);
    renderSavedWalletAddressList();

    // Cập nhật lại input nếu ví đang chọn bị xóa
    const buybackInput = document.getElementById('buybackAddressInput');
    if (buybackInput && list.length > 0) {
      buybackInput.value = list[0];
    }
    showToast('Đã xóa địa chỉ ví!', 'info');
  }
}

window.openWalletAddFlow = openWalletAddFlow;
window.selectWalletNetwork = selectWalletNetwork;
window.generateWalletCaptcha = generateWalletCaptcha;
window.submitAddNewWalletAddress = submitAddNewWalletAddress;
window.deleteWalletAddress = deleteWalletAddress;

// ============================================================
// QUICK DEPOSIT MODAL (NẠP USDT TỰ ĐỘNG CỘNG TIỀN)
// ============================================================
function openDepositModal() {
  const addrInput = document.getElementById('quickDepositAddrInput');
  if (addrInput && State.settings && State.settings.usdt_deposit_address) {
    addrInput.value = State.settings.usdt_deposit_address;
  }
  openModal('depositModal');
}

function setQuickDepositAmount(amt) {
  const input = document.getElementById('quickDepositAmountInput');
  if (input) input.value = amt;
}

async function submitQuickDeposit() {
  const amtInput = document.getElementById('quickDepositAmountInput');
  const txInput = document.getElementById('quickDepositTxHashInput');
  const btn = document.getElementById('btnSubmitQuickDeposit');
  if (!amtInput) return;

  const amount = parseFloat(amtInput.value);
  if (!amount || amount <= 0) {
    showToast('Vui lòng nhập số lượng USDT hợp lệ!', 'error');
    return;
  }

  const txHash = txInput ? txInput.value.trim() : '';

  if (btn) {
    btn.disabled = true;
    btn.innerText = 'Đang xử lý nạp tiền...';
  }

  try {
    const res = await apiCall('api/wallet.php?action=deposit', 'POST', { amount, txHash });
    showToast(res.message || `Nạp thành công +${amount} USDT!`, 'success');
    closeModal('depositModal');
    if (txInput) txInput.value = '';
    await checkSession();
    updateUserInterface();
  } catch (err) {
    showToast(err.message || 'Lỗi nạp tiền', 'error');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerText = 'Xác Nhận Nạp Tiền';
    }
  }
}

window.openDepositModal = openDepositModal;
window.setQuickDepositAmount = setQuickDepositAmount;
window.submitQuickDeposit = submitQuickDeposit;

async function depositViaMetaMask() {
  const amtInput = document.getElementById('quickDepositAmountInput');
  const addrInput = document.getElementById('quickDepositAddrInput');
  const btn = document.getElementById('btnMetaMaskDeposit');
  const amount = parseFloat(amtInput ? amtInput.value : 0);
  if (!amount || amount <= 0) {
    showToast('Vui lòng nhập số lượng USDT hợp lệ!', 'error');
    return;
  }
  const eth = window.ethereum;
  if (!eth) {
    showToast('Không tìm thấy MetaMask. Hãy mở trang trong trình duyệt MetaMask hoặc cài đặt MetaMask.', 'error');
    return;
  }
  const to = (addrInput && addrInput.value.trim()) || '0xcb3Fc21Af451e1D51Cd58078F025Dad92595f5BA';
  const USDT_BSC = '0x55d398326f99059fF775485246999027B3197955';
  const setBtn = (t, d) => { if (btn) { btn.disabled = d; btn.innerText = t; } };

  try {
    setBtn('Đang kết nối MetaMask...', true);
    const accounts = await eth.request({ method: 'eth_requestAccounts' });
    const from = accounts[0];

    try {
      await eth.request({ method: 'wallet_switchEthereumChain', params: [{ chainId: '0x38' }] });
    } catch (swErr) {
      if (swErr && swErr.code === 4902) {
        await eth.request({
          method: 'wallet_addEthereumChain',
          params: [{
            chainId: '0x38',
            chainName: 'BNB Smart Chain',
            nativeCurrency: { name: 'BNB', symbol: 'BNB', decimals: 18 },
            rpcUrls: ['https://bsc-dataseed.binance.org'],
            blockExplorerUrls: ['https://bscscan.com']
          }]
        });
      } else {
        throw swErr;
      }
    }

    // ERC20 transfer(to, amount) với 18 decimals
    const wei = BigInt(Math.round(amount * 1e6)) * 10n ** 12n;
    const data = '0xa9059cbb'
      + to.toLowerCase().replace('0x', '').padStart(64, '0')
      + wei.toString(16).padStart(64, '0');

    setBtn('Xác nhận giao dịch trong MetaMask...', true);
    const txHash = await eth.request({
      method: 'eth_sendTransaction',
      params: [{ from, to: USDT_BSC, data, value: '0x0' }]
    });

    showToast('Đã gửi giao dịch, đang chờ xác nhận trên blockchain...', 'success');
    setBtn('Đang xác nhận on-chain...', true);

    let credited = false;
    let lastErr = null;
    for (let i = 0; i < 24 && !credited; i++) {
      await new Promise(r => setTimeout(r, 5000));
      try {
        const res = await apiCall('api/wallet.php?action=deposit_onchain', 'POST', { txHash });
        showToast(res.message || 'Nạp thành công!', 'success');
        credited = true;
      } catch (e) {
        lastErr = e;
        if (e && e.message && e.message.includes('đã được nạp')) { credited = true; }
      }
    }

    if (credited) {
      closeModal('depositModal');
      await checkSession();
      updateUserInterface();
    } else {
      showToast((lastErr && lastErr.message) || 'Chưa xác nhận được giao dịch. Dán TxHash ' + txHash.slice(0, 12) + '... để nạp sau.', 'error');
      const txInput = document.getElementById('quickDepositTxHashInput');
      if (txInput) txInput.value = txHash;
    }
  } catch (err) {
    showToast((err && (err.shortMessage || err.message)) || 'Nạp bằng MetaMask thất bại', 'error');
  } finally {
    setBtn('🦊 Nạp bằng MetaMask', false);
  }
}
window.depositViaMetaMask = depositViaMetaMask;



