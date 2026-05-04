<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db.php';

// ── Handle approve/reject actions ────────────────────────────────────────
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $claim_id = (int) $_GET['id'];

    if (in_array($action, ['approve', 'reject'])) {
        // Get claim details
        $stmt = $conn->prepare("SELECT * FROM claims WHERE id = ?");
        $stmt->bind_param("i", $claim_id);
        $stmt->execute();
        $claim = $stmt->get_result()->fetch_assoc();

        if ($claim) {
            $new_status = $action === 'approve' ? 'approved' : 'rejected';
            $item_status = $action === 'approve' ? 'resolved' : 'active';

            // Update claim status
            $stmt = $conn->prepare("UPDATE claims SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $claim_id);
            $stmt->execute();

            // Update item status
            $stmt = $conn->prepare("UPDATE items SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $item_status, $claim['item_id']);
            $stmt->execute();

            $_SESSION['flash'] = "Claim " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully.";
        }
    }

    header("Location: claims.php");
    exit();
}

// ── Fetch all claims with item and user details ──────────────────────────
$stmt = $conn->prepare("
    SELECT
        claims.*,
        items.title AS item_title,
        items.image_path,
        items.verification_question,
        items.user_id AS item_owner_id,
        users.name AS claimant_name
    FROM claims
    LEFT JOIN items ON items.id = claims.item_id
    LEFT JOIN users ON users.id = claims.user_id
    ORDER BY claims.submitted_at DESC
");
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

$avatar_letter = strtoupper(substr($_SESSION['user_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Claims Management | Campus L&F</title>
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

  <!-- ── SIDEBAR (dark) ── -->
  <aside id="sidebar" class="w-60 shrink-0 bg-slate-900 fixed inset-y-0 left-0 z-40 flex flex-col -translate-x-full md:translate-x-0 transition-transform">

    <a href="../index.php" class="flex items-center gap-2.5 px-5 py-[18px] border-b border-white/[0.07] no-underline">
      <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-sm shrink-0">📍</div>
      <span class="text-sm font-semibold text-white">Campus L&F</span>
      <span class="ml-auto text-[9px] font-bold uppercase tracking-wider bg-blue-500/20 text-blue-300 px-2 py-0.5 rounded-full">Admin</span>
    </a>

    <nav class="flex-1 overflow-y-auto px-3 py-2">

      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-2 pt-3 pb-1.5">Overview</p>
      <a href="dashboard.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center">📊</span> Dashboard
      </a>

      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-2 pt-5 pb-1.5">Manage</p>
      <a href="claims.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-semibold text-blue-300 bg-blue-500/20 mb-0.5 no-underline">
        <span class="w-5 text-center">📋</span> Claims
      </a>
      <a href="items.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center">📦</span> All Items
      </a>
      <a href="users.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center">👥</span> Users
      </a>

      <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-2 pt-5 pb-1.5">Account</p>
      <a href="settings.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white no-underline transition-colors">
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
      <a href="../auth/logout.php" class="flex items-center gap-2 px-2.5 py-2 mt-1 rounded-lg text-sm font-medium text-red-400 hover:bg-red-500/10 no-underline transition-colors">
        <span>↩</span> Log out
      </a>
    </div>
  </aside>

  <!-- ── MAIN ── -->
  <div class="md:ml-60 flex-1 flex flex-col">

    <header class="sticky top-0 z-30 bg-white border-b border-slate-200 flex items-center gap-3 px-4 md:px-8 h-[60px]">
      <h1 class="font-display text-lg md:text-xl text-slate-800">Claims Management</h1>
      <?php
      $pending_count = count(array_filter($claims, fn($c) => $c['status'] === 'pending'));
      if ($pending_count > 0):
      ?>
        <span class="text-xs font-semibold text-red-600 bg-red-50 px-3 py-1.5 rounded-full">
          <?= $pending_count ?> pending
        </span>
      <?php endif; ?>
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
          <p class="text-slate-500 max-w-md mx-auto">Claims will appear here once students start submitting them for found items.</p>
        </div>
      <?php else: ?>
        <!-- Claims List -->
        <div class="space-y-6">
          <?php foreach ($claims as $claim): ?>
            <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
              <!-- Header -->
              <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                  <div class="flex items-center gap-3">
                    <?php if ($claim['image_path']): ?>
                      <img src="../<?= htmlspecialchars($claim['image_path']) ?>" alt="Item" class="w-10 h-10 object-cover rounded-lg">
                    <?php else: ?>
                      <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 text-sm">
                        📦
                      </div>
                    <?php endif; ?>
                    <div>
                      <h3 class="font-semibold text-slate-800">
                        <a href="../items/view.php?id=<?= $claim['item_id'] ?>" class="hover:text-blue-600 transition-colors no-underline">
                          <?= htmlspecialchars($claim['item_title']) ?>
                        </a>
                      </h3>
                      <p class="text-sm text-slate-500">Claimed by <?= htmlspecialchars($claim['claimant_name']) ?> • <?= date('M j, Y \a\t g:i A', strtotime($claim['submitted_at'])) ?></p>
                    </div>
                  </div>
                  <?= statusBadge($claim['status']) ?>
                </div>
              </div>

              <!-- Verification Details -->
              <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Question -->
                  <div>
                    <h4 class="text-sm font-semibold text-slate-700 mb-2">Verification Question</h4>
                    <div class="bg-slate-50 rounded-lg p-3">
                      <p class="text-sm text-slate-600"><?= htmlspecialchars($claim['verification_question']) ?></p>
                    </div>
                  </div>

                  <!-- Answer -->
                  <div>
                    <h4 class="text-sm font-semibold text-slate-700 mb-2">Given Answer</h4>
                    <div class="bg-blue-50 rounded-lg p-3">
                      <p class="text-sm text-blue-800 font-medium"><?= htmlspecialchars($claim['answer_given']) ?></p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Actions (only for pending claims) -->
              <?php if ($claim['status'] === 'pending'): ?>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                  <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-600">Review this claim and decide whether to approve or reject it.</p>
                    <div class="flex items-center gap-3">
                      <a href="?action=reject&id=<?= $claim['id'] ?>"
                         onclick="return confirm('Are you sure you want to reject this claim?')"
                         class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors no-underline">
                        Reject
                      </a>
                      <a href="?action=approve&id=<?= $claim['id'] ?>"
                         onclick="return confirm('Are you sure you want to approve this claim? This will mark the item as resolved.')"
                         class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors no-underline">
                        Approve
                      </a>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
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