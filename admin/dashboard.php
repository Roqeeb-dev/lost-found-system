<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: auth/login.php");
    exit();
}

require_once '../config/db.php';

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM items");
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stats['total_items'] = $stats['count'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM claims WHERE status = 'pending'");
$stmt->execute();
$pending = $stmt->get_result()->fetch_assoc();
$stats['pending_claims'] = $pending['count'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM claims WHERE status = 'approved' AND submitted_at >= CURDATE()");
$stmt->execute();
$resolved_today = $stmt->get_result()->fetch_assoc();
$stats['resolved_today'] = $resolved_today['count'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM users");
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc();
$stats['total_users'] = $total_users['count'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM items WHERE status = 'active'");
$stmt->execute();
$active_items = $stmt->get_result()->fetch_assoc();
$stats['active_items'] = $active_items['count'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM items WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$stmt->execute();
$items_this_week = $stmt->get_result()->fetch_assoc();
$stats['items_this_week'] = $items_this_week['count'] ?? 0;

$stmt = $conn->prepare("SELECT claims.id, claims.item_id, items.title AS item_title, users.name AS claimant, claims.submitted_at FROM claims LEFT JOIN items ON items.id = claims.item_id LEFT JOIN users ON users.id = claims.user_id WHERE claims.status = 'pending' ORDER BY claims.submitted_at DESC LIMIT 5");
$stmt->execute();
$pending_claims = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT id, title, type, status FROM items ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

function typeBadge($type) {
    if ($type === 'found') return '<span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-green-100 text-green-700">Found</span>';
    return '<span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-700">Lost</span>';
}

function statusBadge($status) {
    $map = [
        'active'   => 'bg-blue-100 text-blue-700',
        'claimed'  => 'bg-purple-100 text-purple-700',
        'resolved' => 'bg-green-100 text-green-700',
        'pending'  => 'bg-amber-100 text-amber-700',
        'rejected' => 'bg-red-100 text-red-700',
    ];
    $cls = $map[$status] ?? 'bg-slate-100 text-slate-600';
    return "<span class=\"text-xs font-semibold px-2.5 py-0.5 rounded-full $cls\">" . ucfirst($status) . "</span>";
}

$avatar_letter = strtoupper(substr($_SESSION['user_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard | Campus L&F</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'DM Sans', sans-serif; }
    .font-display { font-family: 'Instrument Serif', Georgia, serif; }
    @keyframes pulse-badge {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.6; }
    }
    .badge-pulse { animation: pulse-badge 2s ease-in-out infinite; }
  </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex">

  <!-- Mobile menu toggle -->
  <button id="sidebarToggle" class="md:hidden fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded-lg">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
  </button>

  <!-- ── SIDEBAR (dark) ── -->
  <aside id="sidebar" class="w-60 shrink-0 bg-slate-900 fixed inset-y-0 left-0 z-40 flex flex-col -translate-x-full md:translate-x-0 transition-transform">

    <a href="../index.php" class="flex items-center gap-2.5 px-5 py-[18px] border-b border-white/[0.07] no-underline">
      <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-sm shrink-0">L&F</div>
      <span class="text-sm font-semibold text-white">Campus L&F</span>
      <span class="ml-auto text-[9px] font-bold uppercase tracking-wider bg-blue-500/20 text-blue-300 px-2 py-0.5 rounded-full">Admin</span>
    </a>

    <nav class="flex-1 overflow-y-auto px-3 py-2">

      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-2 pt-3 pb-1.5">Overview</p>
      <a href="dashboard.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-semibold text-blue-300 bg-blue-500/20 mb-0.5 no-underline">
        <span class="w-5 text-center font-bold">•</span> Dashboard
      </a>

      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-2 pt-5 pb-1.5">Manage</p>
      <a href="claims.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center font-bold">•</span> Claims
        <?php if ($stats['pending_claims'] > 0): ?>
          <span class="ml-auto text-[11px] font-bold bg-red-500 text-white px-2 py-0.5 rounded-full leading-none badge-pulse"><?= $stats['pending_claims'] ?></span>
        <?php endif; ?>
      </a>
      <a href="items.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center font-bold">•</span> All Items
      </a>
      <a href="users.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white mb-0.5 no-underline transition-colors">
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
      <a href="auth/logout.php" class="flex items-center gap-2 px-2.5 py-2 mt-1 rounded-lg text-sm font-medium text-red-400 hover:bg-red-500/10 no-underline transition-colors">
        Log out
      </a>
    </div>
  </aside>

  <!-- ── MAIN ── -->
  <div class="md:ml-60 flex-1 flex flex-col">

    <header class="sticky top-0 z-30 bg-white border-b border-slate-200 flex items-center gap-3 px-4 md:px-8 h-[60px]">
      <h1 class="font-display text-lg md:text-xl text-slate-800">Admin Dashboard</h1>
      <?php if ($stats['pending_claims'] > 0): ?>
        <a href="claims.php" class="flex items-center gap-1.5 text-xs font-semibold text-red-600 bg-red-50 px-3 py-1.5 rounded-full no-underline hover:bg-red-100 transition-colors">
          <?= $stats['pending_claims'] ?> pending claim<?= $stats['pending_claims'] > 1 ? 's' : '' ?>
        </a>
      <?php endif; ?>
    </header>

    <main class="p-4 md:p-8 space-y-4 md:space-y-6">

      <!-- Flash Messages -->
      <?php
      $flash = $_SESSION['flash'] ?? null;
      $flash_error = $_SESSION['flash_error'] ?? null;
      unset($_SESSION['flash'], $_SESSION['flash_error']);
      ?>
      <?php if ($flash): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3">
          <div class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center text-white text-sm">✓</div>
          <p class="text-sm text-green-800 font-medium"><?= htmlspecialchars($flash) ?></p>
        </div>
      <?php endif; ?>
      <?php if ($flash_error): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-center gap-3">
          <div class="w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-white text-sm">!</div>
          <p class="text-sm text-red-800 font-medium"><?= htmlspecialchars($flash_error) ?></p>
        </div>
      <?php endif; ?>

      <!-- STATS -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">

        <div class="bg-white border border-slate-200 rounded-lg md:rounded-xl p-4 md:p-5 flex flex-col md:flex-row md:items-center md:gap-4 hover:shadow-sm transition-shadow">
          <div class="w-10 md:w-11 h-10 md:h-11 bg-blue-50 rounded-lg md:rounded-xl flex items-center justify-center shrink-0"></div>
          <div>
            <p class="text-xl md:text-[26px] font-bold text-slate-800 leading-none tracking-tight"><?= $stats['total_items'] ?></p>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Total Items</p>
            <p class="text-[11px] md:text-xs text-green-600 font-semibold mt-0.5">↑ <?= $stats['items_this_week'] ?> this week</p>
          </div>
        </div>

        <div class="bg-white border border-red-200 rounded-xl p-5 flex items-center gap-4 hover:shadow-sm transition-shadow">
          <div class="w-11 h-11 bg-red-50 rounded-xl flex items-center justify-center shrink-0"></div>
          <div>
            <p class="text-[26px] font-bold text-red-600 leading-none tracking-tight"><?= $stats['pending_claims'] ?></p>
            <p class="text-sm text-slate-500 mt-1">Pending Claims</p>
            <p class="text-xs text-amber-600 font-semibold mt-0.5">Needs review</p>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 flex items-center gap-4 hover:shadow-sm transition-shadow">
          <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center shrink-0"></div>
          <div>
            <p class="text-[26px] font-bold text-slate-800 leading-none tracking-tight"><?= $stats['resolved_today'] ?></p>
            <p class="text-sm text-slate-500 mt-1">Resolved Today</p>
            <p class="text-xs text-green-600 font-semibold mt-0.5">Items returned</p>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 flex items-center gap-4 hover:shadow-sm transition-shadow">
          <div class="w-11 h-11 bg-purple-50 rounded-xl flex items-center justify-center shrink-0"></div>
          <div>
            <p class="text-[26px] font-bold text-slate-800 leading-none tracking-tight"><?= $stats['total_users'] ?></p>
            <p class="text-sm text-slate-500 mt-1">Registered Users</p>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 flex items-center gap-4 hover:shadow-sm transition-shadow">
          <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center shrink-0"></div>
          <div>
            <p class="text-[26px] font-bold text-slate-800 leading-none tracking-tight"><?= $stats['active_items'] ?></p>
            <p class="text-sm text-slate-500 mt-1">Active Listings</p>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Unresolved items</p>
          </div>
        </div>

        <!-- Quick actions card -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 flex items-center gap-4">
          <div class="w-11 h-11 bg-white/10 rounded-xl flex items-center justify-center shrink-0"></div>
          <div>
            <p class="text-sm font-semibold text-white mb-2">Quick Links</p>
            <div class="flex flex-col gap-1">
              <a href="items.php?status=active" class="text-xs font-semibold text-blue-400 hover:text-blue-300 no-underline transition-colors">View active items →</a>
              <a href="users.php"               class="text-xs font-semibold text-blue-400 hover:text-blue-300 no-underline transition-colors">Manage users →</a>
            </div>
          </div>
        </div>

      </div>

      <!-- PENDING CLAIMS + RIGHT COL -->
      <div class="grid grid-cols-1 lg:grid-cols-[1.4fr_1fr] gap-4 md:gap-5">

        <!-- Pending Claims -->
        <div>
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-slate-800">Pending Claims</h2>
            <a href="claims.php" class="text-xs font-medium text-blue-600 hover:underline no-underline">Manage all →</a>
          </div>
          <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <?php if (empty($pending_claims)): ?>
              <div class="py-12 text-center">
                <div class="mx-auto mb-4 w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">No</div>
                <p class="text-sm font-semibold text-slate-800">No pending claims</p>
                <p class="text-xs text-slate-400">Nothing is awaiting review at the moment.</p>
              </div>
            <?php else: ?>
              <table class="w-full min-w-[400px] md:min-w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                  <tr>
                    <th class="text-left text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-3 md:px-5 py-2 md:py-3">Item</th>
                    <th class="text-left text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-2 md:px-3 py-2 md:py-3">Claimant</th>
                    <th class="text-left text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-2 md:px-3 py-2 md:py-3">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pending_claims as $claim): ?>
                  <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors last:border-0">
                    <td class="px-3 md:px-5 py-2 md:py-3">
                      <p class="text-xs md:text-sm font-medium text-slate-800"><?= htmlspecialchars($claim['item_title']) ?></p>
                      <p class="text-[10px] md:text-xs text-slate-400 mt-0.5"><?= date('M j, g:i A', strtotime($claim['submitted_at'])) ?></p>
                    </td>
                    <td class="px-2 md:px-3 py-2 md:py-3 text-xs md:text-sm text-slate-600"><?= htmlspecialchars($claim['claimant']) ?></td>
                    <td class="px-2 md:px-3 py-2 md:py-3">
                      <div class="flex items-center gap-1">
                        <a href="claims.php?action=approve&id=<?= $claim['id'] ?>" class="text-[10px] md:text-xs font-semibold px-2 md:px-2.5 py-1 rounded-md bg-green-100 text-green-700 hover:bg-green-200 no-underline transition-colors">Approve</a>
                        <a href="claims.php?action=reject&id=<?= $claim['id'] ?>"  class="text-[10px] md:text-xs font-semibold px-2 md:px-2.5 py-1 rounded-md bg-red-100 text-red-600 hover:bg-red-200 no-underline transition-colors">Reject</a>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>

        <!-- Right: Recent Items + Overview -->
        <div class="flex flex-col gap-5">

          <!-- Recent Items -->
          <div>
            <div class="flex items-center justify-between mb-3">
              <h2 class="text-sm font-semibold text-slate-800">Recent Items</h2>
              <a href="admin/items.php" class="text-xs font-medium text-blue-600 hover:underline no-underline">View all →</a>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
              <?php if (empty($recent_items)): ?>
                <div class="py-12 text-center">
                  <div class="mx-auto mb-4 w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">No</div>
                  <p class="text-sm font-semibold text-slate-800">No recent items</p>
                  <p class="text-xs text-slate-400">No items have been added yet.</p>
                </div>
              <?php else: ?>
              <table class="w-full min-w-[300px] md:min-w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                  <tr>
                    <th class="text-left text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-3 md:px-5 py-2 md:py-3">Item</th>
                    <th class="text-left text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-2 md:px-3 py-2 md:py-3">Type</th>
                    <th class="text-left text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-2 md:px-3 py-2 md:py-3">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recent_items as $item): ?>
                  <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors last:border-0">
                    <td class="px-3 md:px-5 py-2 md:py-3">
                      <a href="items.php?view=<?= $item['id'] ?>" class="text-xs md:text-sm font-medium text-slate-800 hover:text-blue-600 no-underline transition-colors">
                        <?= htmlspecialchars($item['title']) ?>
                      </a>
                    </td>
                    <td class="px-2 md:px-3 py-2 md:py-3"><?= typeBadge($item['type']) ?></td>
                    <td class="px-2 md:px-3 py-2 md:py-3"><?= statusBadge($item['status']) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <?php endif; ?>
            </div>
          </div>

          <!-- Platform Overview -->
          <div>
            <h2 class="text-sm font-semibold text-slate-800 mb-3">Platform Overview</h2>
            <div class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-100">
              <div class="flex items-center justify-between px-5 py-3">
                <span class="text-sm text-slate-500">Active listings</span>
                <span class="text-sm font-semibold text-slate-800"><?= $stats['active_items'] ?></span>
              </div>
              <div class="flex items-center justify-between px-5 py-3">
                <span class="text-sm text-slate-500">Posted this week</span>
                <span class="text-sm font-semibold text-slate-800"><?= $stats['items_this_week'] ?></span>
              </div>
              <div class="flex items-center justify-between px-5 py-3">
                <span class="text-sm text-slate-500">Resolved today</span>
                <span class="text-sm font-semibold text-green-600"><?= $stats['resolved_today'] ?></span>
              </div>
              <div class="flex items-center justify-between px-5 py-3">
                <span class="text-sm text-slate-500">Pending claims</span>
                <span class="text-sm font-semibold text-red-600"><?= $stats['pending_claims'] ?></span>
              </div>
              <div class="flex items-center justify-between px-5 py-3">
                <span class="text-sm text-slate-500">Total users</span>
                <span class="text-sm font-semibold text-slate-800"><?= $stats['total_users'] ?></span>
              </div>
            </div>
          </div>

        </div>
      </div>

    </main>
  </div>

  <script>
    // Mobile sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    sidebarToggle?.addEventListener('click', () => {
      sidebar.classList.toggle('-translate-x-full');
    });

    // Close sidebar when clicking a link on mobile
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