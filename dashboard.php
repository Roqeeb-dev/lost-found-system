<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header("Location: auth/login.php");
    exit();
}

// ── MOCK DATA 
$stats = [
    'items_posted'   => 3,
    'active_claims'  => 1,
    'items_resolved' => 2,
    'items_found'    => 47,
];

$my_items = [
    ['id' => 1, 'title' => 'Black Backpack',    'type' => 'lost',  'status' => 'active',   'location' => 'Engineering Block', 'created_at' => '2026-04-15'],
    ['id' => 2, 'title' => 'Samsung Earbuds',   'type' => 'lost',  'status' => 'claimed',  'location' => 'Library',           'created_at' => '2026-04-10'],
    ['id' => 3, 'title' => 'Blue Water Bottle', 'type' => 'found', 'status' => 'resolved', 'location' => 'Cafeteria',         'created_at' => '2026-04-08'],
];

$my_claims = [
    ['id' => 1, 'item_title' => 'Silver ID Card', 'item_id' => 12, 'status' => 'pending',  'submitted_at' => '2026-04-16'],
    ['id' => 2, 'item_title' => 'Maths Textbook', 'item_id' => 9,  'status' => 'rejected', 'submitted_at' => '2026-04-11'],
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

$first_name    = explode(' ', $_SESSION['user_name'])[0];
$avatar_letter = strtoupper(substr($_SESSION['user_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard | Campus L&F</title>
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

  <!-- ── SIDEBAR ── -->
  <aside class="w-60 shrink-0 bg-white border-r border-slate-200 fixed inset-y-0 left-0 z-40 flex flex-col">

    <a href="../index.php" class="flex items-center gap-2.5 px-5 py-[18px] border-b border-slate-200 no-underline">
      <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-sm shrink-0">📍</div>
      <span class="text-sm font-semibold text-slate-800">Campus <span class="text-blue-600">L&F</span></span>
    </a>

    <nav class="flex-1 overflow-y-auto px-3 py-2">

      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 px-2 pt-3 pb-1.5">Menu</p>
      <a href="dashboard.php"    class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-semibold text-blue-600 bg-blue-50 mb-0.5 no-underline">
        <span class="w-5 text-center">🏠</span> Dashboard
      </a>
      <a href="items/list.php"   class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-800 mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center">🔍</span> Browse Items
      </a>
      <a href="items/create.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-800 mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center">＋</span> Report Item
      </a>

      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 px-2 pt-5 pb-1.5">My Activity</p>
      <a href="my-items.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-800 mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center">📦</span> My Items
      </a>
      <a href="claims.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-800 mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center">📋</span> My Claims
        <?php if ($stats['active_claims'] > 0): ?>
          <span class="ml-auto text-[11px] font-bold bg-blue-600 text-white px-2 py-0.5 rounded-full leading-none"><?= $stats['active_claims'] ?></span>
        <?php endif; ?>
      </a>

      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 px-2 pt-5 pb-1.5">Account</p>
      <a href="profile.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-800 no-underline transition-colors">
        <span class="w-5 text-center">👤</span> Profile
      </a>

    </nav>

    <div class="px-3 pb-4 pt-3 border-t border-slate-200">
      <div class="flex items-center gap-2.5 px-2 py-1.5">
        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold shrink-0">
          <?= $avatar_letter ?>
        </div>
        <div class="min-w-0">
          <p class="text-sm font-semibold text-slate-800 truncate"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
          <p class="text-xs text-slate-400">Student</p>
        </div>
      </div>
      <a href="auth/logout.php" class="flex items-center gap-2 px-2.5 py-2 mt-1 rounded-lg text-sm font-medium text-red-500 hover:bg-red-50 no-underline transition-colors">
        <span>↩</span> Log out
      </a>
    </div>
  </aside>

  <!-- ── MAIN ── -->
  <div class="ml-60 flex-1 flex flex-col">

    <header class="sticky top-0 z-30 bg-white border-b border-slate-200 flex items-center justify-between px-8 h-[60px]">
      <h1 class="font-display text-xl text-slate-800">Good day, <?= htmlspecialchars($first_name) ?> 👋</h1>
      <a href="items/create.php" class="flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors no-underline">
        <span>＋</span> Report Item
      </a>
    </header>

    <main class="p-8 space-y-6">

      <!-- STATS -->
      <div class="grid grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-5 hover:shadow-sm transition-shadow">
          <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center mb-3">📦</div>
          <p class="text-[28px] font-bold text-slate-800 leading-none tracking-tight"><?= $stats['items_posted'] ?></p>
          <p class="text-sm text-slate-500 mt-1">Items Posted</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 hover:shadow-sm transition-shadow">
          <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center mb-3">📋</div>
          <p class="text-[28px] font-bold text-slate-800 leading-none tracking-tight"><?= $stats['active_claims'] ?></p>
          <p class="text-sm text-slate-500 mt-1">Active Claims</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 hover:shadow-sm transition-shadow">
          <div class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center mb-3">✅</div>
          <p class="text-[28px] font-bold text-slate-800 leading-none tracking-tight"><?= $stats['items_resolved'] ?></p>
          <p class="text-sm text-slate-500 mt-1">Items Resolved</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 hover:shadow-sm transition-shadow">
          <div class="w-9 h-9 bg-purple-50 rounded-lg flex items-center justify-center mb-3">🔍</div>
          <p class="text-[28px] font-bold text-slate-800 leading-none tracking-tight"><?= $stats['items_found'] ?></p>
          <p class="text-sm text-slate-500 mt-1">Found on Campus Today</p>
        </div>
      </div>

      <!-- TABLES -->
      <div class="grid grid-cols-2 gap-5">

        <!-- My Items -->
        <div>
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-slate-800">My Items</h2>
            <a href="my-items.php" class="text-xs font-medium text-blue-600 hover:underline no-underline">View all →</a>
          </div>
          <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <?php if (empty($my_items)): ?>
              <div class="py-12 text-center">
                <p class="text-3xl mb-2">📭</p>
                <p class="text-xs text-slate-400">No items posted yet.</p>
              </div>
            <?php else: ?>
              <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                  <tr>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3">Item</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-3 py-3">Type</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-3 py-3">Status</th>
                    <th class="px-3 py-3"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($my_items as $item): ?>
                  <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors last:border-0">
                    <td class="px-5 py-3 text-sm font-medium text-slate-800"><?= htmlspecialchars($item['title']) ?></td>
                    <td class="px-3 py-3"><?= typeBadge($item['type']) ?></td>
                    <td class="px-3 py-3"><?= statusBadge($item['status']) ?></td>
                    <td class="px-3 py-3 text-right">
                      <a href="items/view.php?id=<?= $item['id'] ?>" class="text-xs font-medium text-blue-600 hover:underline no-underline">View</a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>

        <!-- My Claims -->
        <div>
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-slate-800">My Claims</h2>
            <a href="claims.php" class="text-xs font-medium text-blue-600 hover:underline no-underline">View all →</a>
          </div>
          <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <?php if (empty($my_claims)): ?>
              <div class="py-12 text-center">
                <p class="text-3xl mb-2">📋</p>
                <p class="text-xs text-slate-400">No claims submitted yet.</p>
              </div>
            <?php else: ?>
              <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                  <tr>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-5 py-3">Item</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-3 py-3">Status</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-3 py-3">Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($my_claims as $claim): ?>
                  <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors last:border-0">
                    <td class="px-5 py-3">
                      <a href="items/view.php?id=<?= $claim['item_id'] ?>" class="text-sm font-medium text-slate-800 hover:text-blue-600 no-underline transition-colors">
                        <?= htmlspecialchars($claim['item_title']) ?>
                      </a>
                    </td>
                    <td class="px-3 py-3"><?= statusBadge($claim['status']) ?></td>
                    <td class="px-3 py-3 text-sm text-slate-500"><?= date('M j', strtotime($claim['submitted_at'])) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- QUICK ACTIONS -->
      <div class="grid grid-cols-3 gap-4">

        <a href="items/list.php" class="group flex items-center gap-4 bg-white border border-slate-200 rounded-xl p-5 hover:border-blue-200 hover:shadow-sm transition-all no-underline">
          <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-xl shrink-0">🔍</div>
          <div>
            <p class="text-sm font-semibold text-slate-800 group-hover:text-blue-600 transition-colors">Browse Items</p>
            <p class="text-xs text-slate-400 mt-0.5">Search all found items on campus</p>
          </div>
        </a>

        <a href="items/create.php" class="group flex items-center gap-4 bg-white border border-slate-200 rounded-xl p-5 hover:border-blue-200 hover:shadow-sm transition-all no-underline">
          <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center text-xl shrink-0">＋</div>
          <div>
            <p class="text-sm font-semibold text-slate-800 group-hover:text-blue-600 transition-colors">Report Lost Item</p>
            <p class="text-xs text-slate-400 mt-0.5">Post something you've lost</p>
          </div>
        </a>

        <a href="items/create.php?type=found" class="group flex items-center gap-4 bg-white border border-slate-200 rounded-xl p-5 hover:border-blue-200 hover:shadow-sm transition-all no-underline">
          <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-xl shrink-0">📦</div>
          <div>
            <p class="text-sm font-semibold text-slate-800 group-hover:text-blue-600 transition-colors">Report Found Item</p>
            <p class="text-xs text-slate-400 mt-0.5">You found something? Post it here</p>
          </div>
        </a>

      </div>

    </main>
  </div>

</body>
</html>