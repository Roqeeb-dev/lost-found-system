<?php
session_start();
require_once '../config/db.php';

// ── Get item ID from URL ───────────────────────────────────────────────────
// e.g. view.php?id=5
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id === 0) {
    header("Location: list.php");
    exit();
}

// ── Fetch the item from database ───────────────────────────────────────────
// We also JOIN users so we can show who posted it
$stmt = $conn->prepare("
    SELECT items.*, users.name AS posted_by
    FROM items
    LEFT JOIN users ON users.id = items.user_id
    WHERE items.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

// If no item found, go back to list
if (!$item) {
    header("Location: list.php");
    exit();
}

// ── Check if logged-in user already submitted a claim for this item ────────
$already_claimed = false;

if (isset($_SESSION['user_id'])) {
    $claim_check = $conn->prepare("
        SELECT id FROM claims
        WHERE item_id = ? AND user_id = ?
    ");
    $claim_check->bind_param("ii", $id, $_SESSION['user_id']);
    $claim_check->execute();
    $already_claimed = (bool) $claim_check->get_result()->fetch_assoc();
}

// ── Flash message from session (e.g. after claim submitted) ───────────────
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// ── Helpers ───────────────────────────────────────────────────────────────
$is_logged_in  = isset($_SESSION['user_id']);
$is_owner      = $is_logged_in && $_SESSION['user_id'] == $item['user_id'];
$is_found_item = $item['type'] === 'found';
$is_available  = $item['status'] === 'available';

// Can this user claim this item?
// Rules: must be logged in, item must be "found", status must be "available",
// must not be the person who posted it, must not have already claimed it
$can_claim = $is_logged_in
          && $is_found_item
          && $is_available
          && !$is_owner
          && !$already_claimed;

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($item['title']) ?> | Campus L&F</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'DM Sans', sans-serif; }
    .font-display { font-family: 'Instrument Serif', Georgia, serif; }
  </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen">

  <!-- ── NAVBAR ── -->
  <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
    <div class="max-w-5xl mx-auto px-6 h-14 flex items-center justify-between">
      <a href="list.php" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium no-underline transition-colors">
        ← Back to Items
      </a>
      <a href="../index.php" class="flex items-center gap-2 no-underline">
        <div class="w-7 h-7 bg-blue-600 rounded-lg flex items-center justify-center text-xs">📍</div>
        <span class="text-sm font-semibold text-slate-800">Campus <span class="text-blue-600">L&F</span></span>
      </a>
    </div>
  </header>

  <div class="max-w-5xl mx-auto px-6 py-10">

    <!-- Flash message -->
    <?php if ($flash): ?>
      <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm font-medium px-4 py-3 rounded-xl">
        <span>✅</span> <?= htmlspecialchars($flash) ?>
      </div>
    <?php endif; ?>

    <div class="grid md:grid-cols-2 gap-8">

      <!-- ── LEFT: Image ── -->
      <div>
        <div class="rounded-2xl overflow-hidden border border-slate-200 bg-white">
          <?php if (!empty($item['image_path']) && file_exists('../' . $item['image_path'])): ?>
            <img
              src="../<?= htmlspecialchars($item['image_path']) ?>"
              alt="<?= htmlspecialchars($item['title']) ?>"
              class="w-full h-72 object-cover"
            />
          <?php else: ?>
            <!-- Placeholder when no image uploaded -->
            <div class="w-full h-72 flex flex-col items-center justify-center
                        <?= $is_found_item ? 'bg-gradient-to-br from-green-50 to-emerald-100' : 'bg-gradient-to-br from-amber-50 to-orange-100' ?>">
              <span class="text-8xl opacity-25"><?= categoryEmoji($item['category']) ?></span>
              <p class="text-xs text-slate-400 mt-3">No image uploaded</p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Posted by (shows under image) -->
        <div class="mt-4 flex items-center gap-2.5 bg-white border border-slate-200 rounded-xl px-4 py-3">
          <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-sm font-bold text-slate-500 shrink-0">
            <?= strtoupper(substr($item['posted_by'] ?? 'U', 0, 1)) ?>
          </div>
          <div>
            <p class="text-xs text-slate-400">Posted by</p>
            <p class="text-sm font-medium text-slate-700"><?= htmlspecialchars($item['posted_by'] ?? 'Unknown') ?></p>
          </div>
          <div class="ml-auto text-xs text-slate-400">
            <?= date('M j, Y', strtotime($item['created_at'])) ?>
          </div>
        </div>
      </div>

      <!-- ── RIGHT: Details ── -->
      <div class="flex flex-col gap-5">

        <!-- Title + badges -->
        <div>
          <div class="flex items-center gap-2 mb-2">
            <span class="text-xs font-bold px-2.5 py-1 rounded-full
              <?= $is_found_item ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?>">
              <?= $is_found_item ? '🎉 Found' : '😟 Lost' ?>
            </span>
            <span class="text-xs font-bold px-2.5 py-1 rounded-full
              <?= $is_available ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500' ?>">
              <?= $is_available ? 'Available' : 'Claimed' ?>
            </span>
          </div>
          <h1 class="font-display text-3xl text-slate-800 tracking-tight leading-tight">
            <?= htmlspecialchars($item['title']) ?>
          </h1>
        </div>

        <!-- Info rows -->
        <div class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-100">

          <div class="flex items-start gap-3 px-4 py-3">
            <span class="text-base mt-0.5 shrink-0">📁</span>
            <div>
              <p class="text-xs text-slate-400">Category</p>
              <p class="text-sm font-medium text-slate-700">
                <?= categoryEmoji($item['category']) ?> <?= htmlspecialchars($item['category']) ?>
              </p>
            </div>
          </div>

          <div class="flex items-start gap-3 px-4 py-3">
            <span class="text-base mt-0.5 shrink-0">📍</span>
            <div>
              <p class="text-xs text-slate-400">Location</p>
              <p class="text-sm font-medium text-slate-700"><?= htmlspecialchars($item['location']) ?></p>
            </div>
          </div>

          <div class="flex items-start gap-3 px-4 py-3">
            <span class="text-base mt-0.5 shrink-0">📅</span>
            <div>
              <p class="text-xs text-slate-400">Date</p>
              <p class="text-sm font-medium text-slate-700"><?= date('F j, Y', strtotime($item['date'])) ?></p>
            </div>
          </div>

          <div class="flex items-start gap-3 px-4 py-3">
            <span class="text-base mt-0.5 shrink-0">📝</span>
            <div>
              <p class="text-xs text-slate-400">Description</p>
              <p class="text-sm text-slate-700 leading-relaxed"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
            </div>
          </div>

        </div>

        <!-- ── ACTION AREA ── -->

        <?php if ($is_owner): ?>
          <!-- Owner sees edit/delete buttons -->
          <div class="flex gap-3">
            <a href="edit.php?id=<?= $item['id'] ?>"
               class="flex-1 text-center py-3 border border-slate-300 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-100 no-underline transition-colors">
              ✏️ Edit Item
            </a>
            <a href="delete.php?id=<?= $item['id'] ?>"
               onclick="return confirm('Are you sure you want to delete this item?')"
               class="flex-1 text-center py-3 border border-red-200 text-red-600 text-sm font-semibold rounded-xl hover:bg-red-50 no-underline transition-colors">
              🗑️ Delete
            </a>
          </div>

        <?php elseif (!$is_logged_in): ?>
          <!-- Guest sees login prompt -->
          <div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-4 text-center">
            <p class="text-sm text-slate-600 mb-3">You need to be logged in to claim this item.</p>
            <a href="../auth/login.php"
               class="inline-block px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl no-underline hover:bg-blue-700 transition-colors">
              Log in to claim
            </a>
          </div>

        <?php elseif ($already_claimed): ?>
          <!-- Already submitted a claim -->
          <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4">
            <p class="text-sm font-semibold text-amber-700">⏳ Claim already submitted</p>
            <p class="text-xs text-amber-600 mt-1">You've already submitted a claim for this item. Check <a href="../claims.php" class="underline">My Claims</a> for the status.</p>
          </div>

        <?php elseif (!$is_found_item): ?>
          <!-- Lost item — no claiming -->
          <div class="bg-slate-100 border border-slate-200 rounded-xl px-5 py-4">
            <p class="text-sm text-slate-500">This is a <strong>lost item</strong> report. If you've found it, contact the poster directly or report it as found.</p>
          </div>

        <?php elseif (!$is_available): ?>
          <!-- Already claimed by someone -->
          <div class="bg-slate-100 border border-slate-200 rounded-xl px-5 py-4">
            <p class="text-sm font-semibold text-slate-600">🔒 This item has already been claimed.</p>
          </div>

        <?php elseif ($can_claim): ?>
          <!-- ── CLAIM FORM ── -->
          <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-slate-800 mb-1">Claim this item</h2>
            <p class="text-xs text-slate-400 mb-4">
              Answer the verification question to prove ownership. Your answer goes to the admin for review.
            </p>

            <!-- The verification question -->
            <div class="bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 mb-4">
              <p class="text-xs text-slate-400 mb-1">Verification question:</p>
              <p class="text-sm font-medium text-slate-700">
                "<?= htmlspecialchars($item['verification_question']) ?>"
              </p>
            </div>

            <form method="POST" action="../claims/create.php">
              <!-- Pass the item id to the claims handler -->
              <input type="hidden" name="item_id" value="<?= $item['id'] ?>">

              <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="answer">
                  Your answer <span class="text-red-400">*</span>
                </label>
                <input
                  type="text"
                  id="answer"
                  name="answer"
                  placeholder="Type your answer here..."
                  class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all"
                  required
                />
              </div>

              <button type="submit"
                class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
                Submit Claim
              </button>
            </form>
          </div>

        <?php endif; ?>

      </div>
    </div>
  </div>

</body>
</html>