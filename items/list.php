<?php
session_start();
require_once '../config/db.php';

// ── FILTER PARAMS ──────────────────────────────────────────────────────────
$search   = trim($_GET['q']        ?? '');
$type     = $_GET['type']          ?? '';  
$category = $_GET['category']      ?? '';
$status   = $_GET['status']        ?? '';  
$sort     = $_GET['sort']          ?? 'newest';
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;
$offset   = ($page - 1) * $per_page;

// ── BUILD QUERY ────────────────────────────────────────────────────────────
$where_conditions = [];
$params = [];
$types = '';

if ($search !== '') {
    $where_conditions[] = '(i.title LIKE ? OR i.description LIKE ? OR i.location LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

if (in_array($type, ['lost', 'found'])) {
    $where_conditions[] = 'i.type = ?';
    $params[] = $type;
    $types .= 's';
}

if ($category !== '') {
    $where_conditions[] = 'i.category = ?';
    $params[] = $category;
    $types .= 's';
}

if (in_array($status, ['available', 'claimed'])) {
    $where_conditions[] = 'i.status = ?';
    $params[] = $status;
    $types .= 's';
}

$where_sql = empty($where_conditions) ? '1=1' : implode(' AND ', $where_conditions);
$order_sql = $sort === 'oldest' ? 'i.created_at ASC' : 'i.created_at DESC';

function bindStmtParams(mysqli_stmt $stmt, string $types, array $values): bool {
    $refs = [];
    foreach ($values as $key => $value) {
        $refs[$key] = &$values[$key];
    }
    array_unshift($refs, $types);
    return $stmt->bind_param(...$refs);
}

// Count for pagination
$count_query = "SELECT COUNT(*) AS count FROM items i WHERE $where_sql";
$count_stmt = $conn->prepare($count_query);
if ($types) {
    bindStmtParams($count_stmt, $types, $params);
}
$count_stmt->execute();
$total_items = $count_stmt->get_result()->fetch_assoc()['count'] ?? 0;
$total_pages = max(1, ceil($total_items / $per_page));

// Fetch items with poster name
$query = "SELECT i.id, i.title, i.description, i.category, i.type, i.status, i.location, i.date, i.created_at, i.image_path, u.name AS posted_by
          FROM items i
          LEFT JOIN users u ON u.id = i.user_id
          WHERE $where_sql
          ORDER BY $order_sql
          LIMIT ? OFFSET ?";

$items_stmt = $conn->prepare($query);
if ($types) {
    $all_values = array_merge($params, [$per_page, $offset]);
    bindStmtParams($items_stmt, $types . 'ii', $all_values);
} else {
    bindStmtParams($items_stmt, 'ii', [$per_page, $offset]);
}
$items_stmt->execute();
$items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── HELPERS
$categories = ['Phone','Laptop','Bag','ID Card','Keys','Wallet','Accessories','Books','Others'];

function buildQuery(array $overrides = []): string {
    $params = array_merge([
        'q'        => $_GET['q']        ?? '',
        'type'     => $_GET['type']     ?? '',
        'category' => $_GET['category'] ?? '',
        'status'   => $_GET['status']   ?? '',
        'sort'     => $_GET['sort']     ?? 'newest',
        'page'     => $_GET['page']     ?? 1,
    ], $overrides);
    return '?' . http_build_query(array_filter($params, fn($v) => $v !== '' && $v !== null));
}

function categoryEmoji(string $cat): string {
    return match($cat) {
        'Phone'       => '📱',
        'Laptop'      => '💻',
        'Bag'         => '🎒',
        'ID Card'     => '🪪',
        'Keys'        => '🔑',
        'Wallet'      => '👛',
        'Accessories' => '🎧',
        'Books'       => '📚',
        default       => '📦',
    };
}

$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Browse Items | Campus L&F</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'DM Sans', sans-serif; }
    .font-display { font-family: 'Instrument Serif', Georgia, serif; }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(10px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .item-card { animation: fadeUp 0.35s ease both; }
    .item-card:nth-child(1)  { animation-delay: .04s }
    .item-card:nth-child(2)  { animation-delay: .08s }
    .item-card:nth-child(3)  { animation-delay: .12s }
    .item-card:nth-child(4)  { animation-delay: .16s }
    .item-card:nth-child(5)  { animation-delay: .20s }
    .item-card:nth-child(6)  { animation-delay: .24s }
    .item-card:nth-child(7)  { animation-delay: .28s }
    .item-card:nth-child(8)  { animation-delay: .32s }
    .item-card:nth-child(9)  { animation-delay: .36s }
    .item-card:nth-child(10) { animation-delay: .40s }
    .item-card:nth-child(11) { animation-delay: .44s }
    .item-card:nth-child(12) { animation-delay: .48s }
  </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen">

  <!-- ── NAVBAR ── -->
  <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-6 h-14 flex items-center justify-between gap-4">

      <a href="../index.php" class="flex items-center gap-2 no-underline shrink-0">
        <div class="w-7 h-7 bg-blue-600 rounded-lg flex items-center justify-center text-xs">📍</div>
        <span class="text-sm font-semibold text-slate-800">Campus <span class="text-blue-600">L&F</span></span>
      </a>

      <!-- Search (desktop) -->
      <form method="GET" action="" class="hidden md:flex flex-1 max-w-lg">
        <div class="relative w-full">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">🔍</span>
          <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
            placeholder="Search items, locations..."
            class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
          />
          <?php if ($type):     ?><input type="hidden" name="type"     value="<?= htmlspecialchars($type) ?>"/><?php endif; ?>
          <?php if ($category): ?><input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>"/><?php endif; ?>
          <?php if ($status):   ?><input type="hidden" name="status"   value="<?= htmlspecialchars($status) ?>"/><?php endif; ?>
        </div>
      </form>

      <div class="flex items-center gap-2 shrink-0">
        <?php if ($is_logged_in): ?>
          <a href="../dashboard.php" class="text-sm font-medium text-slate-500 hover:text-slate-800 no-underline transition-colors hidden sm:block">Dashboard</a>
          <a href="create.php" class="flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors no-underline">
            <span>＋</span> Report
          </a>
        <?php else: ?>
          <a href="../auth/login.php"    class="text-sm font-medium text-slate-500 hover:text-slate-800 no-underline hidden sm:block">Log in</a>
          <a href="../auth/register.php" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl no-underline transition-colors">Get started</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <div class="max-w-7xl mx-auto px-6 py-8">

    <!-- ── PAGE HEADER ── -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-7">
      <div>
        <h1 class="font-display text-3xl text-slate-800 tracking-tight">
          <?php if ($search !== ''): ?>Results for "<?= htmlspecialchars($search) ?>"
          <?php elseif ($type === 'lost'): ?>Lost Items
          <?php elseif ($type === 'found'): ?>Found Items
          <?php else: ?>All Items<?php endif; ?>
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          <?= number_format($total_items) ?> item<?= $total_items !== 1 ? 's' : '' ?>
          <?php if ($search || $type || $category || $status): ?>
            · <a href="list.php" class="text-blue-600 hover:underline no-underline">Clear all filters</a>
          <?php endif; ?>
        </p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <span class="text-xs text-slate-400 font-medium hidden sm:block">Sort:</span>
        <a href="<?= buildQuery(['sort' => 'newest', 'page' => 1]) ?>"
           class="text-xs font-semibold px-3 py-1.5 rounded-lg no-underline transition-colors <?= $sort !== 'oldest' ? 'bg-slate-800 text-white' : 'bg-white border border-slate-200 text-slate-500 hover:text-slate-800' ?>">
          Newest
        </a>
        <a href="<?= buildQuery(['sort' => 'oldest', 'page' => 1]) ?>"
           class="text-xs font-semibold px-3 py-1.5 rounded-lg no-underline transition-colors <?= $sort === 'oldest' ? 'bg-slate-800 text-white' : 'bg-white border border-slate-200 text-slate-500 hover:text-slate-800' ?>">
          Oldest
        </a>
      </div>
    </div>

    <div class="flex gap-7">

      <!-- ── SIDEBAR FILTERS (desktop) ── -->
      <aside class="w-52 shrink-0 hidden lg:block">

        <div class="mb-6">
          <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Type</p>
          <div class="space-y-0.5">
            <?php foreach (['' => 'All Items', 'lost' => '😟 Lost', 'found' => '🎉 Found'] as $val => $label): ?>
            <a href="<?= buildQuery(['type' => $val, 'page' => 1]) ?>"
               class="flex items-center px-3 py-2 rounded-lg text-sm no-underline transition-colors <?= $type === $val ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' ?>">
              <?= $label ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="mb-6">
          <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Status</p>
          <div class="space-y-0.5">
            <?php foreach (['' => 'All Statuses', 'available' => '🟢 Available', 'claimed' => '🔵 Claimed'] as $val => $label): ?>
            <a href="<?= buildQuery(['status' => $val, 'page' => 1]) ?>"
               class="flex items-center px-3 py-2 rounded-lg text-sm no-underline transition-colors <?= $status === $val ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' ?>">
              <?= $label ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>

        <div>
          <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Category</p>
          <div class="space-y-0.5">
            <a href="<?= buildQuery(['category' => '', 'page' => 1]) ?>"
               class="flex items-center px-3 py-2 rounded-lg text-sm no-underline transition-colors <?= $category === '' ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' ?>">
              All Categories
            </a>
            <?php foreach ($categories as $cat): ?>
            <a href="<?= buildQuery(['category' => $cat, 'page' => 1]) ?>"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm no-underline transition-colors <?= $category === $cat ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' ?>">
              <span><?= categoryEmoji($cat) ?></span><?= $cat ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>

      </aside>

      <!-- ── CONTENT ── -->
      <div class="flex-1 min-w-0">

        <!-- Mobile search -->
        <form method="GET" action="" class="md:hidden mb-4">
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">🔍</span>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
              placeholder="Search items..."
              class="w-full pl-9 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
            />
          </div>
        </form>

        <!-- Mobile filter pills -->
        <div class="flex gap-2 overflow-x-auto pb-2 mb-5 lg:hidden">
          <a href="<?= buildQuery(['type' => '',      'page' => 1]) ?>" class="shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full no-underline transition-colors <?= $type === ''      ? 'bg-slate-800 text-white' : 'bg-white border border-slate-200 text-slate-500' ?>">All</a>
          <a href="<?= buildQuery(['type' => 'lost',  'page' => 1]) ?>" class="shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full no-underline transition-colors <?= $type === 'lost'  ? 'bg-slate-800 text-white' : 'bg-white border border-slate-200 text-slate-500' ?>">😟 Lost</a>
          <a href="<?= buildQuery(['type' => 'found', 'page' => 1]) ?>" class="shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full no-underline transition-colors <?= $type === 'found' ? 'bg-slate-800 text-white' : 'bg-white border border-slate-200 text-slate-500' ?>">🎉 Found</a>
          <?php foreach ($categories as $cat): ?>
          <a href="<?= buildQuery(['category' => ($category === $cat ? '' : $cat), 'page' => 1]) ?>"
             class="shrink-0 flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full no-underline transition-colors <?= $category === $cat ? 'bg-slate-800 text-white' : 'bg-white border border-slate-200 text-slate-500' ?>">
            <?= categoryEmoji($cat) ?> <?= $cat ?>
          </a>
          <?php endforeach; ?>
        </div>

        <!-- Active filter chips -->
        <?php if ($search || $type || $category || $status): ?>
        <div class="flex flex-wrap gap-2 mb-5">
          <?php if ($search): ?>
            <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
              🔍 "<?= htmlspecialchars($search) ?>"
              <a href="<?= buildQuery(['q' => '', 'page' => 1]) ?>" class="text-blue-400 hover:text-blue-700 no-underline leading-none font-bold">×</a>
            </span>
          <?php endif; ?>
          <?php if ($type): ?>
            <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
              <?= $type === 'lost' ? '😟' : '🎉' ?> <?= ucfirst($type) ?>
              <a href="<?= buildQuery(['type' => '', 'page' => 1]) ?>" class="text-blue-400 hover:text-blue-700 no-underline font-bold">×</a>
            </span>
          <?php endif; ?>
          <?php if ($category): ?>
            <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
              <?= categoryEmoji($category) ?> <?= htmlspecialchars($category) ?>
              <a href="<?= buildQuery(['category' => '', 'page' => 1]) ?>" class="text-blue-400 hover:text-blue-700 no-underline font-bold">×</a>
            </span>
          <?php endif; ?>
          <?php if ($status): ?>
            <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
              <?= $status === 'available' ? '🟢' : '🔵' ?> <?= ucfirst($status) ?>
              <a href="<?= buildQuery(['status' => '', 'page' => 1]) ?>" class="text-blue-400 hover:text-blue-700 no-underline font-bold">×</a>
            </span>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ── ITEMS GRID ── -->
        <?php if (empty($items)): ?>
          <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="text-6xl mb-4">🕵️</div>
            <h3 class="font-display text-2xl text-slate-700 mb-2">Nothing found</h3>
            <p class="text-sm text-slate-400 max-w-xs mb-6">
              No items match your filters. Try broadening your search or
              <a href="list.php" class="text-blue-600 hover:underline no-underline">clear all filters</a>.
            </p>
            <?php if ($is_logged_in): ?>
              <a href="create.php" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl no-underline hover:bg-blue-700 transition-colors">
                Report a new item
              </a>
            <?php endif; ?>
          </div>

        <?php else: ?>

          <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            <?php foreach ($items as $item):
              $is_found     = $item['type'] === 'found';
              $is_available = $item['status'] === 'available';
              $is_claimed   = $item['status'] === 'claimed';
              $date_fmt     = date('M j, Y', strtotime($item['date']));
              $cat_emoji    = categoryEmoji($item['category'] ?? 'Others');
            ?>

            <div class="item-card group bg-white border border-slate-200 rounded-2xl overflow-hidden hover:border-blue-200 hover:shadow-md transition-all">

              <!-- Image / placeholder -->
              <div class="relative h-44 overflow-hidden <?= $is_found ? 'bg-gradient-to-br from-green-50 to-emerald-100' : 'bg-gradient-to-br from-amber-50 to-orange-100' ?>">

                <?php if (!empty($item['image_path']) && file_exists('../' . $item['image_path'])): ?>
                  <img src="../<?= htmlspecialchars($item['image_path']) ?>"
                       alt="<?= htmlspecialchars($item['title']) ?>"
                       class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                <?php else: ?>
                  <div class="w-full h-full flex items-center justify-center">
                    <span class="text-7xl opacity-20"><?= $cat_emoji ?></span>
                  </div>
                <?php endif; ?>

                <!-- Type badge -->
                <div class="absolute top-3 left-3">
                  <span class="text-xs font-bold px-2.5 py-1 rounded-full <?= $is_found ? 'bg-green-500 text-white' : 'bg-amber-500 text-white' ?>">
                    <?= $is_found ? '🎉 Found' : '😟 Lost' ?>
                  </span>
                </div>

                <!-- Claimed overlay badge -->
                <?php if ($is_claimed): ?>
                <div class="absolute top-3 right-3">
                  <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-800/90 text-white">
                    Claimed
                  </span>
                </div>
                <?php endif; ?>

                <!-- subtle hover overlay -->
                <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/5 transition-all duration-300 pointer-events-none"></div>
              </div>

              <!-- Card body -->
              <div class="p-4">
                <div class="flex items-start justify-between gap-2 mb-1.5">
                  <h3 class="text-sm font-semibold text-slate-800 leading-snug line-clamp-1 flex-1">
                    <?= htmlspecialchars($item['title']) ?>
                  </h3>
                  <span class="text-base shrink-0 leading-none"><?= $cat_emoji ?></span>
                </div>

                <p class="text-xs text-slate-500 leading-relaxed line-clamp-2 mb-3">
                  <?= htmlspecialchars($item['description']) ?>
                </p>

                <div class="space-y-1 mb-4">
                  <div class="flex items-center gap-1.5 text-xs text-slate-400">
                    <span class="shrink-0">📍</span>
                    <span class="truncate"><?= htmlspecialchars($item['location']) ?></span>
                  </div>
                  <div class="flex items-center gap-1.5 text-xs text-slate-400">
                    <span class="shrink-0">📅</span>
                    <span><?= $date_fmt ?></span>
                  </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                  <div class="flex items-center gap-1.5 min-w-0">
                    <div class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500 shrink-0">
                      <?= strtoupper(substr($item['posted_by'] ?? 'U', 0, 1)) ?>
                    </div>
                    <span class="text-xs text-slate-400 truncate">
                      <?= htmlspecialchars($item['posted_by'] ?? 'Unknown') ?>
                    </span>
                  </div>

                  <a href="view.php?id=<?= $item['id'] ?>"
                     class="shrink-0 text-xs font-semibold px-3 py-1.5 rounded-lg no-underline transition-colors
                            <?= ($is_found && $is_available && $is_logged_in)
                                ? 'bg-blue-600 text-white hover:bg-blue-700'
                                : 'text-blue-600 hover:text-blue-700' ?>">
                    <?= ($is_found && $is_available && $is_logged_in) ? 'Claim →' : 'View →' ?>
                  </a>
                </div>
              </div>
            </div>

            <?php endforeach; ?>
          </div>

          <!-- ── PAGINATION ── -->
          <?php if ($total_pages > 1): ?>
          <div class="flex items-center justify-center gap-1.5 mt-10">
            <?php if ($page > 1): ?>
              <a href="<?= buildQuery(['page' => $page - 1]) ?>"
                 class="flex items-center gap-1 px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 no-underline transition-colors">
                ← Prev
              </a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end   = min($total_pages, $page + 2);
            for ($p = $start; $p <= $end; $p++):
            ?>
              <a href="<?= buildQuery(['page' => $p]) ?>"
                 class="flex items-center justify-center w-9 h-9 text-sm font-semibold rounded-xl no-underline transition-colors
                        <?= $p === $page ? 'bg-blue-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                <?= $p ?>
              </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
              <a href="<?= buildQuery(['page' => $page + 1]) ?>"
                 class="flex items-center gap-1 px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 no-underline transition-colors">
                Next →
              </a>
            <?php endif; ?>
          </div>
          <p class="text-center text-xs text-slate-400 mt-3">
            Page <?= $page ?> of <?= $total_pages ?> · <?= number_format($total_items) ?> items total
          </p>
          <?php endif; ?>

        <?php endif; ?>

      </div>
    </div>
  </div>

</body>
</html>