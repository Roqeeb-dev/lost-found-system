<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db.php';

// Fetch all users with stats
$stmt = $conn->prepare("
    SELECT u.id, u.name, u.email, u.role, u.created_at,
           (SELECT COUNT(*) FROM items WHERE user_id = u.id) AS items_count,
           (SELECT COUNT(*) FROM claims WHERE user_id = u.id) AS claims_count
    FROM users u
    ORDER BY u.created_at DESC
");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM users");
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM users WHERE role = 'admin'");
$stmt->execute();
$admin_count = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

$avatar_letter = strtoupper(substr($_SESSION['user_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Users | Campus L&F</title>
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
      <a href="users.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-semibold text-blue-300 bg-blue-500/20 mb-0.5 no-underline">
        <span class="w-5 text-center font-bold">•</span> Users
      </a>

      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-2 pt-5 pb-1.5">Account</p>
      <a href="settings.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white no-underline transition-colors">
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
      <h1 class="font-display text-lg md:text-xl text-slate-800">Manage Users</h1>
    </header>

    <main class="p-4 md:p-8 space-y-4 md:space-y-6">

      <!-- Overview Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
        <div class="bg-white border border-slate-200 rounded-lg md:rounded-xl p-4 md:p-5">
          <p class="text-3xl md:text-[32px] font-bold text-slate-800 leading-none"><?= $total_users ?></p>
          <p class="text-xs md:text-sm text-slate-500 mt-1">Total Users</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-lg md:rounded-xl p-4 md:p-5">
          <p class="text-3xl md:text-[32px] font-bold text-slate-800 leading-none"><?= $admin_count ?></p>
          <p class="text-xs md:text-sm text-slate-500 mt-1">Administrators</p>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <table class="w-full min-w-full">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
              <th class="text-left text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-4 md:px-5 py-3">Name</th>
              <th class="text-left text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-3 py-3">Email</th>
              <th class="text-left text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-3 py-3">Role</th>
              <th class="text-center text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-3 py-3">Items</th>
              <th class="text-center text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-3 py-3">Claims</th>
              <th class="text-left text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-3 py-3">Joined</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors last:border-0">
              <td class="px-4 md:px-5 py-3">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                  </div>
                  <span class="text-xs md:text-sm font-medium text-slate-800"><?= htmlspecialchars($user['name']) ?></span>
                </div>
              </td>
              <td class="px-3 py-3 text-xs md:text-sm text-slate-500"><?= htmlspecialchars($user['email']) ?></td>
              <td class="px-3 py-3">
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full <?= $user['role'] === 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700' ?>">
                  <?= ucfirst($user['role']) ?>
                </span>
              </td>
              <td class="px-3 py-3 text-center text-xs md:text-sm font-medium text-slate-800"><?= $user['items_count'] ?></td>
              <td class="px-3 py-3 text-center text-xs md:text-sm font-medium text-slate-800"><?= $user['claims_count'] ?></td>
              <td class="px-3 py-3 text-xs md:text-sm text-slate-500"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
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
