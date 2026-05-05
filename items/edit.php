<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db.php';

// ── Get item ID and validate ownership ───────────────────────────────────
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id === 0) {
    header("Location: ../my-items.php");
    exit();
}

// ── Fetch item and check ownership ───────────────────────────────────────
$stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    $_SESSION['flash_error'] = 'Item not found.';
    header("Location: ../my-items.php");
    exit();
}

if ($item['user_id'] != $_SESSION['user_id']) {
    $_SESSION['flash_error'] = 'You can only edit your own items.';
    header("Location: ../my-items.php");
    exit();
}

// ── Handle form submission ───────────────────────────────────────────────
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize inputs
    $title                 = trim($_POST['title'] ?? '');
    $description           = trim($_POST['description'] ?? '');
    $category              = trim($_POST['category'] ?? '');
    $type                  = $_POST['type'] ?? '';
    $location              = trim($_POST['location'] ?? '');
    $date                  = $_POST['date'] ?? '';
    $verification_question = trim($_POST['verification_question'] ?? '');

    $old = compact('title','description','category','type','location','date','verification_question');

    // ── Validation ───────────────────────────────────────────────────────
    if (empty($title))                       $errors['title']       = 'Item name is required.';
    if (empty($description))                 $errors['description'] = 'Description is required.';
    if (empty($category))                    $errors['category']    = 'Please select a category.';
    if (!in_array($type, ['lost','found']))  $errors['type']        = 'Please select a type.';
    if (empty($location))                    $errors['location']    = 'Location is required.';

    if (empty($date)) {
        $errors['date'] = 'Date is required.';
    } elseif ($date > date('Y-m-d')) {
        $errors['date'] = 'Date cannot be in the future.';
    }

    if (empty($verification_question))
        $errors['verification_question'] = 'Verification question is required.';

    // ── Image Upload (optional) ───────────────────────────────────────────
    $image_path = $item['image_path']; // Keep existing image by default

    if (!empty($_FILES['image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $max_size      = 3 * 1024 * 1024; // 3MB
        $file          = $_FILES['image'];

        if (!in_array($file['type'], $allowed_types)) {
            $errors['image'] = 'Only JPG, PNG, WEBP, or GIF images are allowed.';
        } elseif ($file['size'] > $max_size) {
            $errors['image'] = 'Image must be under 3MB.';
        } else {
            $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename   = uniqid('item_', true) . '.' . $ext;
            $upload_dir = '../uploads/items/';

            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                // Delete old image if it exists
                if ($item['image_path'] && file_exists('../' . $item['image_path'])) {
                    unlink('../' . $item['image_path']);
                }
                $image_path = 'uploads/items/' . $filename;
            } else {
                $errors['image'] = 'Failed to upload image. Please try again.';
            }
        }
    }

    // ── Update item ───────────────────────────────────────────────────────
    if (empty($errors)) {
        $stmt = $conn->prepare("
            UPDATE items SET
                title = ?,
                description = ?,
                category = ?,
                type = ?,
                location = ?,
                date = ?,
                image_path = ?,
                verification_question = ?
            WHERE id = ? AND user_id = ?
        ");

        $stmt->bind_param(
            "ssssssssii",
            $title,
            $description,
            $category,
            $type,
            $location,
            $date,
            $image_path,
            $verification_question,
            $id,
            $_SESSION['user_id']
        );

        if ($stmt->execute()) {
            $_SESSION['flash'] = 'Item updated successfully.';
            header("Location: ../my-items.php");
            exit();
        } else {
            $errors['general'] = 'Failed to update item. Please try again.';
        }
    }
} else {
    // Pre-fill form with existing data
    $old = [
        'title' => $item['title'],
        'description' => $item['description'],
        'category' => $item['category'],
        'type' => $item['type'],
        'location' => $item['location'],
        'date' => $item['date'],
        'verification_question' => $item['verification_question'],
    ];
}

// Pre-select type from query string or existing data
$preselect_type = $_GET['type'] ?? ($old['type'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Item | Campus L&F</title>
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

  <!-- ── HEADER ── -->
  <header class="bg-white border-b border-slate-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center gap-4">
          <a href="../my-items.php" class="flex items-center gap-2 text-slate-600 hover:text-slate-800 no-underline">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to My Items
          </a>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white text-sm">📍</div>
          <span class="text-sm font-semibold text-slate-800">Campus <span class="text-blue-600">L&F</span></span>
        </div>
      </div>
    </div>
  </header>

  <!-- ── MAIN ── -->
  <main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
      <h1 class="text-2xl font-semibold text-slate-800 mb-2">Edit Item</h1>
      <p class="text-slate-500">Update the details of your reported item.</p>
    </div>

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

    <form method="POST" enctype="multipart/form-data" class="bg-white border border-slate-200 rounded-lg p-6 space-y-6">

      <!-- Title -->
      <div>
        <label for="title" class="block text-sm font-medium text-slate-700 mb-2">Item Name *</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>" required
               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <?php if (isset($errors['title'])): ?>
          <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['title']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Description -->
      <div>
        <label for="description" class="block text-sm font-medium text-slate-700 mb-2">Description *</label>
        <textarea id="description" name="description" rows="3" required
                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
        <?php if (isset($errors['description'])): ?>
          <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['description']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Category and Type -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="category" class="block text-sm font-medium text-slate-700 mb-2">Category *</label>
          <select id="category" name="category" required
                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select category</option>
            <option value="Phone" <?= ($old['category'] ?? '') === 'Phone' ? 'selected' : '' ?>>Phone</option>
            <option value="Laptop" <?= ($old['category'] ?? '') === 'Laptop' ? 'selected' : '' ?>>Laptop</option>
            <option value="Bag" <?= ($old['category'] ?? '') === 'Bag' ? 'selected' : '' ?>>Bag</option>
            <option value="ID Card" <?= ($old['category'] ?? '') === 'ID Card' ? 'selected' : '' ?>>ID Card</option>
            <option value="Keys" <?= ($old['category'] ?? '') === 'Keys' ? 'selected' : '' ?>>Keys</option>
            <option value="Wallet" <?= ($old['category'] ?? '') === 'Wallet' ? 'selected' : '' ?>>Wallet</option>
            <option value="Accessories" <?= ($old['category'] ?? '') === 'Accessories' ? 'selected' : '' ?>>Accessories</option>
            <option value="Books" <?= ($old['category'] ?? '') === 'Books' ? 'selected' : '' ?>>Books</option>
          </select>
          <?php if (isset($errors['category'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['category']) ?></p>
          <?php endif; ?>
        </div>

        <div>
          <label for="type" class="block text-sm font-medium text-slate-700 mb-2">Type *</label>
          <div class="flex gap-4">
            <label class="flex items-center">
              <input type="radio" name="type" value="lost" <?= ($old['type'] ?? '') === 'lost' ? 'checked' : '' ?> required
                     class="text-blue-600 focus:ring-blue-500">
              <span class="ml-2 text-sm text-slate-700">Lost</span>
            </label>
            <label class="flex items-center">
              <input type="radio" name="type" value="found" <?= ($old['type'] ?? '') === 'found' ? 'checked' : '' ?>
                     class="text-blue-600 focus:ring-blue-500">
              <span class="ml-2 text-sm text-slate-700">Found</span>
            </label>
          </div>
          <?php if (isset($errors['type'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['type']) ?></p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Location and Date -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="location" class="block text-sm font-medium text-slate-700 mb-2">Location *</label>
          <input type="text" id="location" name="location" value="<?= htmlspecialchars($old['location'] ?? '') ?>" required
                 class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <?php if (isset($errors['location'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['location']) ?></p>
          <?php endif; ?>
        </div>

        <div>
          <label for="date" class="block text-sm font-medium text-slate-700 mb-2">Date *</label>
          <input type="date" id="date" name="date" value="<?= htmlspecialchars($old['date'] ?? '') ?>" required
                 class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <?php if (isset($errors['date'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['date']) ?></p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Verification Question -->
      <div>
        <label for="verification_question" class="block text-sm font-medium text-slate-700 mb-2">Verification Question *</label>
        <input type="text" id="verification_question" name="verification_question" value="<?= htmlspecialchars($old['verification_question'] ?? '') ?>" required
               placeholder="e.g., What color was the strap?"
               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <p class="mt-1 text-sm text-slate-500">This question will be asked to anyone claiming this item to verify ownership.</p>
        <?php if (isset($errors['verification_question'])): ?>
          <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['verification_question']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Current Image -->
      <?php if ($item['image_path']): ?>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Current Image</label>
          <div class="flex items-center gap-4">
            <img src="../<?= htmlspecialchars($item['image_path']) ?>" alt="Current item image" class="w-20 h-20 object-cover rounded-lg">
            <p class="text-sm text-slate-500">Leave blank to keep current image</p>
          </div>
        </div>
      <?php endif; ?>

      <!-- Image Upload -->
      <div>
        <label for="image" class="block text-sm font-medium text-slate-700 mb-2">Update Image</label>
        <input type="file" id="image" name="image" accept="image/*"
               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        <p class="mt-1 text-sm text-slate-500">Optional. JPG, PNG, WEBP, or GIF. Max 3MB.</p>
        <?php if (isset($errors['image'])): ?>
          <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['image']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Submit -->
      <div class="flex gap-4 pt-4">
        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
          Update Item
        </button>
        <a href="../my-items.php" class="px-6 py-3 border border-slate-300 text-slate-700 font-medium rounded-lg hover:bg-slate-50 transition-colors no-underline">
          Cancel
        </a>
      </div>

    </form>

  </main>

</body>
</html>