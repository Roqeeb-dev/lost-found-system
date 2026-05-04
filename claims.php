<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header("Location: auth/login.php");
    exit();
}

require_once 'config/db.php';

// ── Fetch user's claims with item details ───────────────────────────────
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT
        claims.*,
        items.title AS item_title,
        items.image_path,
        items.verification_question
    FROM claims
    LEFT JOIN items ON items.id = claims.item_id
    WHERE claims.user_id = ?
    ORDER BY claims.submitted_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$claims = $result->fetch_all(MYSQLI_ASSOC);

function statusBadge($status) {
    $map = [
        'pending'  => 'bg-amber-100 text-amber-700',
        'approved' => 'bg-green-100 text-green-700',
        'rejected' => 'bg-red-100 text-red-700',
    ];
    $cls = $map[$status] ?? 'bg-slate-100 text-slate-600';
    return "<span class=\"text-xs font-semibold px-2.5 py-0.5 rounded-full $cls\">" . ucfirst($status) . "</span>";
}

$first_name = explode(' ', $_SESSION['user_name'])[0];
$avatar_letter = strtoupper(substr($_SESSION['user_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Claims | Campus L&F</title>
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
  <aside id="sidebar" class="w-60 shrink-0 bg-white border-r border-slate-200 fixed inset-y-0 left-0 z-40 flex flex-col -translate-x-full md:translate-x-0 transition-transform">

    <a href="index.php" class="flex items-center gap-2.5 px-5 py-[18px] border-b border-slate-200 no-underline">
      <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-sm shrink-0">📍</div>
      <span class="text-sm font-semibold text-slate-800">Campus <span class="text-blue-600">L&F</span></span>
    </a>

    <nav class="flex-1 overflow-y-auto px-3 py-2">

      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 px-2 pt-3 pb-1.5">Menu</p>
      <a href="dashboard.php"    class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-800 mb-0.5 no-underline transition-colors">
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
      <a href="claims.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-semibold text-blue-600 bg-blue-50 mb-0.5 no-underline">
        <span class="w-5 text-center">📋</span> My Claims
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
  <div class="md:ml-60 flex-1 flex flex-col">

    <header class="sticky top-0 z-30 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 h-[60px]">
      <h1 class="font-display text-lg md:text-xl text-slate-800">My Claims</h1>
      <a href="items/list.php" class="hidden sm:flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors no-underline">
        <span>＋</span> Browse Items
      </a>
    </header>

    <main class="p-4 md:p-8">

      <!-- Flash Messages -->
      <?php
      $flash = $_SESSION['flash'] ?? null;
      $flash_error = $_SESSION['flash_error'] ?? null;
      unset($_SESSION['flash'], $_SESSION['flash_error']);
      ?>
      <?php if ($flash): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3 mb-6">
          <div class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center text-white text-sm">✓</div>
          <p class="text-sm text-green-800 font-medium"><?= htmlspecialchars($flash) ?></p>
        </div>
      <?php endif; ?>
      <?php if ($flash_error): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-center gap-3 mb-6">
          <div class="w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-white text-sm">!</div>
          <p class="text-sm text-red-800 font-medium"><?= htmlspecialchars($flash_error) ?></p>
        </div>
      <?php endif; ?>

      <?php if (empty($claims)): ?>
        <!-- Empty State -->
        <div class="text-center py-16">
          <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl">📋</span>
          </div>
          <h3 class="text-lg font-semibold text-slate-800 mb-2">No claims yet</h3>
          <p class="text-slate-500 mb-6 max-w-md mx-auto">You haven't submitted any claims for found items. Browse available items to find something you've lost.</p>
          <a href="items/list.php" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors no-underline">
            <span>🔍</span> Browse Found Items
          </a>
        </div>
      <?php else: ?>
        <!-- Claims List -->
        <div class="space-y-4">
          <?php foreach ($claims as $claim): ?>
            <div class="bg-white border border-slate-200 rounded-lg p-6 hover:shadow-sm transition-shadow">
              <div class="flex flex-col md:flex-row md:items-center gap-4">
                <!-- Item Image -->
                <div class="flex-shrink-0">
                  <?php if ($claim['image_path']): ?>
                    <img src="<?= htmlspecialchars($claim['image_path']) ?>" alt="Item" class="w-16 h-16 object-cover rounded-lg">
                  <?php else: ?>
                    <div class="w-16 h-16 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400">
                      📦
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Claim Details -->
                <div class="flex-1 min-w-0">
                  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                    <h3 class="font-semibold text-slate-800 truncate">
                      <a href="items/view.php?id=<?= $claim['item_id'] ?>" class="hover:text-blue-600 transition-colors no-underline">
                        <?= htmlspecialchars($claim['item_title']) ?>
                      </a>
                    </h3>
                    <?= statusBadge($claim['status']) ?>
                  </div>

                  <p class="text-sm text-slate-500 mb-2">
                    Submitted on <?= date('M j, Y \a\t g:i A', strtotime($claim['submitted_at'])) ?>
                  </p>

                  <?php if ($claim['status'] === 'pending'): ?>
                    <p class="text-sm text-amber-600">
                      Your claim is being reviewed by an administrator.
                    </p>
                  <?php elseif ($claim['status'] === 'approved'): ?>
                    <p class="text-sm text-green-600">
                      Your claim has been approved! Contact the item owner to arrange pickup.
                    </p>
                  <?php elseif ($claim['status'] === 'rejected'): ?>
                    <p class="text-sm text-red-600">
                      Your claim was not approved. The verification answer didn't match.
                    </p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

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