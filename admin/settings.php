<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db.php';

// Fetch platform stats
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM users");
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM items");
$stmt->execute();
$total_items = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM claims");
$stmt->execute();
$total_claims = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM items WHERE created_at >= CURDATE()");
$stmt->execute();
$today_items = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

$avatar_letter = strtoupper(substr($_SESSION['user_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Settings | Admin | Campus L&F</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'DM Sans', sans-serif; }
    .font-display { font-family: 'Instrument Serif', Georgia, serif; }
  </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex">

  <!-- Mobile menu toggle -->
  <button id="sidebarToggle" class="md:hidden fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded-lg">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
  </button>

  <!-- ── SIDEBAR ── -->
  <aside id="sidebar" class="w-60 shrink-0 bg-slate-900 fixed inset-y-0 left-0 z-40 flex flex-col -translate-x-full md:translate-x-0 transition-transform">
    <a href="../index.php" class="flex items-center gap-2.5 px-5 py-[18px] border-b border-white/[0.07] no-underline">
      <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-sm shrink-0">L&F</div>
      <span class="text-sm font-semibold text-white">Campus L&F</span>
      <span class="ml-auto text-[9px] font-bold uppercase tracking-wider bg-blue-500/20 text-blue-300 px-2 py-0.5 rounded-full">Admin</span>
    </a>

    <nav class="flex-1 overflow-y-auto px-3 py-2">
      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-2 pt-3 pb-1.5">Overview</p>
      <a href="dashboard.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center font-bold">•</span> Dashboard
      </a>

      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-2 pt-5 pb-1.5">Manage</p>
      <a href="claims.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center font-bold">•</span> Claims
      </a>
      <a href="items.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center font-bold">•</span> Items
      </a>
      <a href="users.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center font-bold">•</span> Users
      </a>

      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-2 pt-5 pb-1.5">Account</p>
      <a href="settings.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-semibold text-blue-300 bg-blue-500/20 no-underline">
        <span class="w-5 text-center font-bold">•</span> Settings
      </a>
    </nav>

    <div class="px-3 pb-4 pt-3 border-t border-white/[0.07]">
      <div class="flex items-center gap-2.5 px-2 py-1.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center text-sm font-bold shrink-0">
          <?= $avatar_letter ?>
        </div>
        <div class="min-w-0">
          <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
          <p class="text-xs text-slate-500">Administrator</p>
        </div>
      </div>
      <a href="../auth/logout.php" class="flex items-center gap-2 px-2.5 py-2 mt-1 rounded-lg text-sm font-medium text-red-400 hover:bg-red-500/10 no-underline transition-colors">
        Log out
      </a>
    </div>
  </aside>

  <!-- ── MAIN ── -->
  <div class="md:ml-60 flex-1 flex flex-col">
    <header class="sticky top-0 z-30 bg-white border-b border-slate-200 flex items-center gap-3 px-4 md:px-8 h-[60px]">
      <h1 class="font-display text-lg md:text-xl text-slate-800">Settings</h1>
    </header>

    <main class="p-4 md:p-8 space-y-6 md:space-y-8 max-w-4xl">

      <!-- Platform Info -->
      <div class="bg-white border border-slate-200 rounded-xl p-5 md:p-6">
        <h2 class="text-sm font-semibold text-slate-800 mb-4">Platform Overview</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="p-3 md:p-4 bg-slate-50 rounded-lg">
            <p class="text-xs text-slate-500 mb-1">Total Users</p>
            <p class="text-xl md:text-2xl font-bold text-slate-800"><?= $total_users ?></p>
          </div>
          <div class="p-3 md:p-4 bg-slate-50 rounded-lg">
            <p class="text-xs text-slate-500 mb-1">Total Items</p>
            <p class="text-xl md:text-2xl font-bold text-slate-800"><?= $total_items ?></p>
          </div>
          <div class="p-3 md:p-4 bg-slate-50 rounded-lg">
            <p class="text-xs text-slate-500 mb-1">Total Claims</p>
            <p class="text-xl md:text-2xl font-bold text-slate-800"><?= $total_claims ?></p>
          </div>
          <div class="p-3 md:p-4 bg-slate-50 rounded-lg">
            <p class="text-xs text-slate-500 mb-1">Added Today</p>
            <p class="text-xl md:text-2xl font-bold text-slate-800"><?= $today_items ?></p>
          </div>
        </div>
      </div>

      <!-- Account Section -->
      <div class="bg-white border border-slate-200 rounded-xl p-5 md:p-6">
        <h2 class="text-sm font-semibold text-slate-800 mb-4">Your Account</h2>
        <div class="space-y-3">
          <div class="flex items-center justify-between py-2">
            <span class="text-sm text-slate-600">Name</span>
            <span class="text-sm font-medium text-slate-800"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
          </div>
          <div class="border-t border-slate-100"></div>
          <div class="flex items-center justify-between py-2">
            <span class="text-sm text-slate-600">Role</span>
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700">Administrator</span>
          </div>
          <div class="border-t border-slate-100"></div>
          <div class="flex items-center justify-between py-2">
            <span class="text-sm text-slate-600">Session ID</span>
            <span class="text-xs font-mono text-slate-500"><?= substr($_SESSION['user_id'], 0, 8) ?>...</span>
          </div>
        </div>
      </div>

      <!-- System Info -->
      <div class="bg-white border border-slate-200 rounded-xl p-5 md:p-6">
        <h2 class="text-sm font-semibold text-slate-800 mb-4">System Information</h2>
        <div class="space-y-3">
          <div class="flex items-center justify-between py-2">
            <span class="text-sm text-slate-600">Platform</span>
            <span class="text-sm font-medium text-slate-800">Campus Lost & Found</span>
          </div>
          <div class="border-t border-slate-100"></div>
          <div class="flex items-center justify-between py-2">
            <span class="text-sm text-slate-600">PHP Version</span>
            <span class="text-sm font-mono text-slate-500"><?= phpversion() ?></span>
          </div>
          <div class="border-t border-slate-100"></div>
          <div class="flex items-center justify-between py-2">
            <span class="text-sm text-slate-600">Current Date</span>
            <span class="text-sm font-medium text-slate-800"><?= date('F j, Y \a\t g:i A') ?></span>
          </div>
          <div class="border-t border-slate-100"></div>
          <div class="flex items-center justify-between py-2">
            <span class="text-sm text-slate-600">Server</span>
            <span class="text-sm font-mono text-slate-500"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></span>
          </div>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="bg-red-50 border border-red-200 rounded-xl p-5 md:p-6">
        <h2 class="text-sm font-semibold text-red-800 mb-3">Danger Zone</h2>
        <p class="text-xs text-red-700 mb-4">Destructive actions cannot be undone.</p>
        <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-50" disabled>
          Clear All Data
        </button>
        <p class="text-xs text-red-600 mt-2">Feature disabled in this version</p>
      </div>

    </main>
  </div>

  <script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    sidebarToggle?.addEventListener('click', () => {
      sidebar.classList.toggle('-translate-x-full');
    });
    sidebar?.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth < 768) {
          sidebar.classList.add('-translate-x-full');
        }
      });
    });
  </script>
</body>
</html>
