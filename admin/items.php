<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db.php';

// ── Handle actions ───────────────────────────────────────────────────────
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $item_id = (int) $_GET['id'];

    if ($action === 'delete') {
        // Get item to delete image file
        $stmt = $conn->prepare("SELECT image_path FROM items WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();

        if ($item) {
            // Delete image file if exists
            if ($item['image_path'] && file_exists('../' . $item['image_path'])) {
                unlink('../' . $item['image_path']);
            }

            // Delete item
            $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
            $stmt->bind_param("i", $item_id);
            $stmt->execute();

            $_SESSION['flash'] = 'Item deleted successfully.';
        }
    } elseif ($action === 'resolve') {
        $stmt = $conn->prepare("UPDATE items SET status = 'resolved' WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $_SESSION['flash'] = 'Item marked as resolved.';
    }

    header("Location: items.php");
    exit();
}

// ── Build query with filters ─────────────────────────────────────────────
$where = [];
$params = [];
$types = '';

$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';
$search = trim($_GET['search'] ?? '');

if ($status_filter) {
    $where[] = "items.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($type_filter) {
    $where[] = "items.type = ?";
    $params[] = $type_filter;
    $types .= 's';
}

if ($search) {
    $where[] = "(items.title LIKE ? OR items.description LIKE ? OR users.name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ── Fetch items with user details ────────────────────────────────────────
$query = "
    SELECT
        items.*,
        users.name AS posted_by
    FROM items
    LEFT JOIN users ON users.id = items.user_id
    $where_clause
    ORDER BY items.created_at DESC
";

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);

function typeBadge($type) {
    if ($type === 'found') return '<span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-green-100 text-green-700">Found</span>';
    return '<span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-700">Lost</span>';
}

function statusBadge($status) {
    $map = [
        'active'   => 'bg-blue-100 text-blue-700',
        'claimed'  => 'bg-purple-100 text-purple-700',
        'resolved' => 'bg-green-100 text-green-700',
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
  <title>All Items | Campus L&F</title>
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
      <a href="claims.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/[0.06] hover:text-white mb-0.5 no-underline transition-colors">
        <span class="w-5 text-center">📋</span> Claims
      </a>
      <a href="items.php" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-semibold text-blue-300 bg-blue-500/20 mb-0.5 no-underline">
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

    <header class="sticky top-0 z-30 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 h-[60px]">
      <h1 class="font-display text-lg md:text-xl text-slate-800">All Items</h1>
      <span class="text-sm text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
        <?= count($items) ?> items
      </span>
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

      <!-- Filters -->
      <div class="bg-white border border-slate-200 rounded-lg p-4 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search items..."
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div class="flex gap-2">
            <select name="status" class="px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">All Status</option>
              <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
              <option value="claimed" <?= $status_filter === 'claimed' ? 'selected' : '' ?>>Claimed</option>
              <option value="resolved" <?= $status_filter === 'resolved' ? 'selected' : '' ?>>Resolved</option>
            </select>
            <select name="type" class="px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">All Types</option>
              <option value="lost" <?= $type_filter === 'lost' ? 'selected' : '' ?>>Lost</option>
              <option value="found" <?= $type_filter === 'found' ? 'selected' : '' ?>>Found</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
              Filter
            </button>
            <?php if ($status_filter || $type_filter || $search): ?>
              <a href="items.php" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors no-underline">
                Clear
              </a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <?php if (empty($items)): ?>
        <!-- Empty State -->
        <div class="text-center py-16">
          <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl">📦</span>
          </div>
          <h3 class="text-lg font-semibold text-slate-800 mb-2">No items found</h3>
          <p class="text-slate-500 max-w-md mx-auto">
            <?php if ($status_filter || $type_filter || $search): ?>
              No items match your current filters. Try adjusting your search criteria.
            <?php else: ?>
              No items have been reported yet.
            <?php endif; ?>
          </p>
        </div>
      <?php else: ?>
        <!-- Items List -->
        <div class="space-y-4">
          <?php foreach ($items as $item): ?>
            <div class="bg-white border border-slate-200 rounded-lg p-6 hover:shadow-sm transition-shadow">
              <div class="flex flex-col md:flex-row md:items-center gap-4">
                <!-- Item Image -->
                <div class="flex-shrink-0">
                  <?php if ($item['image_path']): ?>
                    <img src="../<?= htmlspecialchars($item['image_path']) ?>" alt="Item" class="w-16 h-16 object-cover rounded-lg">
                  <?php else: ?>
                    <div class="w-16 h-16 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400">
                      📦
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Item Details -->
                <div class="flex-1 min-w-0">
                  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                    <h3 class="font-semibold text-slate-800 truncate">
                      <a href="../items/view.php?id=<?= $item['id'] ?>" class="hover:text-blue-600 transition-colors no-underline">
                        <?= htmlspecialchars($item['title']) ?>
                      </a>
                    </h3>
                    <div class="flex items-center gap-2">
                      <?= typeBadge($item['type']) ?>
                      <?= statusBadge($item['status']) ?>
                    </div>
                  </div>

                  <p class="text-sm text-slate-500 mb-2">
                    Posted by <?= htmlspecialchars($item['posted_by']) ?> • <?= htmlspecialchars($item['location']) ?> • <?= date('M j, Y', strtotime($item['created_at'])) ?>
                  </p>

                  <p class="text-sm text-slate-600 line-clamp-2">
                    <?= htmlspecialchars(substr($item['description'], 0, 150)) ?><?= strlen($item['description']) > 150 ? '...' : '' ?>
                  </p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 flex-shrink-0">
                  <a href="../items/view.php?id=<?= $item['id'] ?>" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded transition-colors no-underline">
                    View
                  </a>
                  <?php if ($item['status'] !== 'resolved'): ?>
                    <a href="?action=resolve&id=<?= $item['id'] ?>"
                       onclick="return confirm('Mark this item as resolved?')"
                       class="px-3 py-1.5 text-xs font-medium text-green-600 hover:bg-green-50 rounded transition-colors no-underline">
                      Resolve
                    </a>
                  <?php endif; ?>
                  <button onclick="confirmDelete(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['title'])) ?>')" class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded transition-colors">
                    Delete
                  </button>
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

    // Confirm delete function
    function confirmDelete(itemId, itemTitle) {
      if (confirm(`Are you sure you want to delete "${itemTitle}"? This action cannot be undone.`)) {
        window.location.href = `?action=delete&id=${itemId}`;
      }
    }
  </script>
</body>
</html>