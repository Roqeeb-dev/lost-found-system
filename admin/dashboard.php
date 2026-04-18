<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: auth/login.php");
    exit();
}

// ── MOCK DATA (replace with real PDO queries) ─────────────────────────────
$stats = [
    'total_items'     => 128,
    'pending_claims'  => 9,
    'resolved_today'  => 4,
    'total_users'     => 312,
    'active_items'    => 73,
    'items_this_week' => 22,
];

$pending_claims = [
    ['id' => 1, 'item_title' => 'Silver ID Card',    'item_id' => 12, 'claimant' => 'Adaeze Okonkwo',  'submitted_at' => '2026-04-17 10:22'],
    ['id' => 2, 'item_title' => 'Blue Backpack',     'item_id' => 8,  'claimant' => 'Chukwuemeka B.',  'submitted_at' => '2026-04-17 09:05'],
    ['id' => 3, 'item_title' => 'Samsung Galaxy A54','item_id' => 15, 'claimant' => 'Fatimah Lawal',   'submitted_at' => '2026-04-16 22:40'],
    ['id' => 4, 'item_title' => 'Physics Textbook',  'item_id' => 6,  'claimant' => 'Ibrahim Yusuf',   'submitted_at' => '2026-04-16 18:10'],
];

$recent_items = [
    ['id' => 17, 'title' => 'Black Umbrella',      'type' => 'found', 'status' => 'active',   'created_at' => '2026-04-18'],
    ['id' => 16, 'title' => 'Key Bundle',           'type' => 'found', 'status' => 'claimed',  'created_at' => '2026-04-17'],
    ['id' => 15, 'title' => 'Samsung Galaxy A54',  'type' => 'lost',  'status' => 'active',   'created_at' => '2026-04-17'],
    ['id' => 14, 'title' => 'Laptop Charger',      'type' => 'lost',  'status' => 'resolved', 'created_at' => '2026-04-16'],
    ['id' => 13, 'title' => 'Water Bottle',        'type' => 'found', 'status' => 'resolved', 'created_at' => '2026-04-16'],
];

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

  <!-- ── SIDEBAR (dark) ── -->
  <aside class="w-60 shrink-0 bg-slate-900 fixed inset-y-0 left-0 z-40 flex flex-col">

    <a href="../index.php" class="flex items-center gap-2.5 px-5 py-[18px] border-b border-white/[0.07] no-underline">
      <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-sm shrink-0">📍</div>
      <span class="text-sm font-semibold text-white">Campus L&F</span>
      <span class="ml-auto text-[9px] font-bold uppercase tracking-wider bg-blue-500/20 text-blue-300 px-2 py-0.5 rounded-full">Admin</span>
    </a>

    <nav class="flex-1 overflow-y-auto px-3 py-2">

      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-2 pt-3 pb-1.5">Overview</p>
      <a href="admin/dashboard.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-semibold text-blue-300 bg-blue-500/20 mb-0.5 no-underline">
        <span class="w-5 text-center">📊</span> Dashboard
      </a>

      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-2 pt-5 pb-1.5">Manage</p>
      <a href="admin/claims.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center">📋</span> Claims
        <?php if ($stats['pending_claims'] > 0): ?>
          <span class="ml-auto text-[11px] font-bold bg-red-500 text-white px-2 py-0.5 rounded-full leading-none badge-pulse"><?= $stats['pending_claims'] ?></span>
        <?php endif; ?>
      </a>
      <a href="admin/items.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center">📦</span> All Items
      </a>
      <a href="admin/users.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center">👥</span> Users
      </a>

      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-2 pt-5 pb-1.5">Account</p>
      <a href="admin/settings.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white no-underline transition-colors">
        <span class="w-5 text-center">⚙️</span> Settings
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
        <span>↩</span> Log out
      </a>
    </div>
  </aside>

  <!-- ── MAIN ── -->
  <div class="ml-60 flex-1 flex flex-col">

    <header class="sticky top-0 z-30 bg-white border-b border-slate-200 flex items-center gap-3 px-8 h-[60px]">
      <h1 class="font-display text-xl text-slate-800">Admin Dashboard</h1>
      <?php if ($stats['pending_claims'] > 0): ?>
        <a href="admin/claims.php" class="flex items-center gap-1.5 text-xs font-semibold text-red-600 bg-red-50 px-3 py-1.5 rounded-full no-underline hover:bg-red-100 transition-colors">
          ⚠ <?= $stats['pending_claims'] ?> pending claim<?= $stats['pending_claims'] > 1 ? 's' : '' ?>
        </a>
      <?php endif; ?>
    </header>

    <main class="p-8 space-y-6">

      <!-- STATS -->
      <div class="grid grid-cols-3 gap-4">

        <div class="bg-white border border-slate-200 rounded-xl p-5 flex items-center gap-4 hover:shadow-sm transition-shadow">
          <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center text-xl shrink-0">📦</div>
          <div>
            <p class="text-[26px] font-bold text-slate-800 leading-none tracking-tight"><?= $stats['total_items'] ?></p>
            <p class="text-sm text-slate-500 mt-1">Total Items</p>
            <p class="text-xs text-green-600 font-semibold mt-0.5">↑ <?= $stats['items_this_week'] ?> this week</p>
          </div>
        </div>

        <div class="bg-white border border-red-200 rounded-xl p-5 flex items-center gap-4 hover:shadow-sm transition-shadow">
          <div class="w-11 h-11 bg-red-50 rounded-xl flex items-center justify-center text-xl shrink-0">📋</div>
          <div>
            <p class="text-[26px] font-bold text-red-600 leading-none tracking-tight"><?= $stats['pending_claims'] ?></p>
            <p class="text-sm text-slate-500 mt-1">Pending Claims</p>
            <p class="text-xs text-amber-600 font-semibold mt-0.5">Needs review</p>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 flex items-center gap-4 hover:shadow-sm transition-shadow">
          <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center text-xl shrink-0">✅</div>
          <div>
            <p class="text-[26px] font-bold text-slate-800 leading-none tracking-tight"><?= $stats['resolved_today'] ?></p>
            <p class="text-sm text-slate-500 mt-1">Resolved Today</p>
            <p class="text-xs text-green-600 font-semibold mt-0.5">Items returned</p>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 flex items-center gap-4 hover:shadow-sm transition-shadow">
          <div class="w-11 h-11 bg-purple-50 rounded-xl flex items-center justify-center text-xl shrink-0">👥</div>
          <div>
            <p class="text-[26px] font-bold text-slate-800 leading-none tracking-tight"><?= $stats['total_users'] ?></p>
            <p class="text-sm text-slate-500 mt-1">Registered Users</p>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 flex items-center gap-4 hover:shadow-sm transition-shadow">
          <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center text-xl shrink-0">🔍</div>
          <div>
            <p class="text-[26px] font-bold text-slate-800 leading-none tracking-tight"><?= $stats['active_items'] ?></p>
            <p class="text-sm text-slate-500 mt-1">Active Listings</p>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Unresolved items</p>
          </div>
        </div>

        <!-- Quick actions card -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 flex items-center gap-4">
          <div class="w-11 h-11 bg-white/10 rounded-xl flex items-center justify-center text-xl shrink-0">⚡</div>
          <div>
            <p class="text-sm font-semibold text-white mb-2">Quick Links</p>
            <div class="flex flex-col gap-1">
              <a href="admin/items.php?status=active" class="text-xs font-semibold text-blue-400 hover:text-blue-300 no-underline transition-colors">View active items →</a>
              <a href="admin/users.php"               class="text-xs font-semibold text-blue-400 hover:text-blue-300 no-underline transition-colors">Manage users →</a>
            </div>
          </div>
        </div>

      </div>

      <!-- PENDING CLAIMS + RIGHT COL -->
      <div class="grid grid-cols-[1.4fr_1fr] gap-5">

        <!-- Pending Claims -->
        <div>
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-slate-800">Pending Claims</h2>
            <a href="admin/claims.php" class="text-xs font-medium text-blue-600 hover:underline no-underline">Manage all →</a>
          </div>
          <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <?php if (empty($pending_claims)): ?>
              <div class="py-12 text-center">
                <p class="text-3xl mb-2">✅</p>
                <p class="text-xs text-slate-400">No pending claims right now.</p>
              </div>
            <?php else: ?>
              <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                  <tr>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3">Item</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-3 py-3">Claimant</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-3 py-3">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pending_claims as $claim): ?>
                  <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors last:border-0">
                    <td class="px-5 py-3">
                      <p class="text-sm font-medium text-slate-800"><?= htmlspecialchars($claim['item_title']) ?></p>
                      <p class="text-xs text-slate-400 mt-0.5"><?= date('M j, g:i A', strtotime($claim['submitted_at'])) ?></p>
                    </td>
                    <td class="px-3 py-3 text-sm text-slate-600"><?= htmlspecialchars($claim['claimant']) ?></td>
                    <td class="px-3 py-3">
                      <div class="flex items-center gap-1.5">
                        <a href="admin/claims.php?action=approve&id=<?= $claim['id'] ?>" class="text-xs font-semibold px-2.5 py-1 rounded-md bg-green-100 text-green-700 hover:bg-green-200 no-underline transition-colors">Approve</a>
                        <a href="admin/claims.php?action=reject&id=<?= $claim['id'] ?>"  class="text-xs font-semibold px-2.5 py-1 rounded-md bg-red-100 text-red-600 hover:bg-red-200 no-underline transition-colors">Reject</a>
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
              <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                  <tr>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3">Item</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-3 py-3">Type</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-3 py-3">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recent_items as $item): ?>
                  <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors last:border-0">
                    <td class="px-5 py-3">
                      <a href="admin/items.php?view=<?= $item['id'] ?>" class="text-sm font-medium text-slate-800 hover:text-blue-600 no-underline transition-colors">
                        <?= htmlspecialchars($item['title']) ?>
                      </a>
                    </td>
                    <td class="px-3 py-3"><?= typeBadge($item['type']) ?></td>
                    <td class="px-3 py-3"><?= statusBadge($item['status']) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
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

</body>
</html>