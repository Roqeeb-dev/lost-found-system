<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../config/db.php';

    // Collect
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

    // ── Image Upload ──────────────────────────────────────────────────────
    $image_path = null;

    if (empty($_FILES['image']['name'])) {
        $errors['image'] = 'Please upload an image of the item.';
    } else {
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
                $image_path = 'uploads/items/' . $filename;
            } else {
                $errors['image'] = 'Failed to upload image. Please try again.';
            }
        }
    }

    // ── Insert ────────────────────────────────────────────────────────────
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO items
                (user_id, title, description, category, type, location, date, image_path, verification_question, status)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, 'available')
        ");

        $stmt->execute([
            $_SESSION['user_id'],
            $title,
            $description,
            $category,
            $type,
            $location,
            $date,
            $image_path,
            $verification_question,
        ]);

        $_SESSION['flash'] = 'Item reported successfully.';
        header('Location: ../dashboard.php');
        exit();
    }
}

// Pre-select type from query string e.g. create.php?type=found
$preselect_type = $_GET['type'] ?? ($old['type'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Report Item | Campus L&F</title>
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

  <!-- ── TOPBAR ── -->
  <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
    <div class="max-w-3xl mx-auto px-6 h-14 flex items-center justify-between">
      <a href="../dashboard.php" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium no-underline transition-colors">
        ← Back to Dashboard
      </a>
      <a href="../index.php" class="flex items-center gap-2 no-underline">
        <div class="w-7 h-7 bg-blue-600 rounded-lg flex items-center justify-center text-xs">📍</div>
        <span class="text-sm font-semibold text-slate-800">Campus <span class="text-blue-600">L&F</span></span>
      </a>
    </div>
  </header>

  <div class="max-w-3xl mx-auto px-6 py-10">

    <!-- Page heading -->
    <div class="mb-8">
      <h1 class="font-display text-3xl text-slate-800 tracking-tight">Report an Item</h1>
      <p class="text-slate-500 mt-1.5 text-sm">Fill in the details below. The more specific you are, the faster it gets resolved.</p>
    </div>

    <!-- ── TYPE TOGGLE  ── -->
    <div class="mb-8">
      <div class="inline-flex bg-slate-100 p-1 rounded-xl gap-1">
        <button type="button" id="toggle-lost"
          onclick="setType('lost')"
          class="px-6 py-2 rounded-lg text-sm font-semibold transition-all <?= $preselect_type === 'found' ? 'text-slate-500 hover:text-slate-700' : 'bg-white text-slate-800 shadow-sm' ?>">
          😟 I lost something
        </button>
        <button type="button" id="toggle-found"
          onclick="setType('found')"
          class="px-6 py-2 rounded-lg text-sm font-semibold transition-all <?= $preselect_type === 'found' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' ?>">
          🎉 I found something
        </button>
      </div>
    </div>

    <form method="POST" enctype="multipart/form-data" novalidate>

      <!-- Hidden type field -->
      <input type="hidden" name="type" id="type-input" value="<?= htmlspecialchars($preselect_type ?: 'lost') ?>">

      <?php if (isset($errors['type'])): ?>
        <p class="text-xs text-red-500 mb-4"><?= $errors['type'] ?></p>
      <?php endif; ?>

      <!-- ── SECTION: Item Details ── -->
      <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-5">
        <h2 class="text-sm font-semibold text-slate-800 mb-5 pb-3 border-b border-slate-100">Item Details</h2>

        <div class="space-y-5">

          <!-- Title -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="title">
              Item Name <span class="text-red-400">*</span>
            </label>
            <input type="text" id="title" name="title"
              value="<?= htmlspecialchars($old['title'] ?? '') ?>"
              placeholder="e.g. Black Backpack"
              class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-all <?= isset($errors['title']) ? 'border-red-400 focus:ring-2 focus:ring-red-200' : 'border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100' ?>"
            />
            <?php if (isset($errors['title'])): ?>
              <p class="text-xs text-red-500 mt-1.5"><?= $errors['title'] ?></p>
            <?php endif; ?>
          </div>

          <!-- Category -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="category">
              Category <span class="text-red-400">*</span>
            </label>
            <select id="category" name="category"
              class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-all <?= isset($errors['category']) ? 'border-red-400 focus:ring-2 focus:ring-red-200' : 'border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100' ?>">
              <option value="">Select a category</option>
              <?php
              $categories = ['Phone','Laptop','Bag','ID Card','Keys','Wallet','Accessories','Books','Others'];
              foreach ($categories as $cat):
                $sel = (($old['category'] ?? '') === $cat) ? 'selected' : '';
              ?>
                <option value="<?= $cat ?>" <?= $sel ?>><?= $cat ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($errors['category'])): ?>
              <p class="text-xs text-red-500 mt-1.5"><?= $errors['category'] ?></p>
            <?php endif; ?>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="description">
              Description <span class="text-red-400">*</span>
            </label>
            <textarea id="description" name="description" rows="3"
              placeholder="Describe the item in detail — color, size, condition, markings..."
              class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-all resize-none <?= isset($errors['description']) ? 'border-red-400 focus:ring-2 focus:ring-red-200' : 'border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100' ?>"
            ><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            <?php if (isset($errors['description'])): ?>
              <p class="text-xs text-red-500 mt-1.5"><?= $errors['description'] ?></p>
            <?php endif; ?>
          </div>

        </div>
      </div>

      <!-- ── SECTION: Location & Date ── -->
      <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-5">
        <h2 class="text-sm font-semibold text-slate-800 mb-5 pb-3 border-b border-slate-100">Location & Date</h2>

        <div class="space-y-5">

          <!-- Location -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="location">
              Location <span class="text-red-400">*</span>
              <span class="text-slate-400 font-normal" id="location-hint"> — where was it lost?</span>
            </label>
            <input type="text" id="location" name="location"
              value="<?= htmlspecialchars($old['location'] ?? '') ?>"
              placeholder="e.g. Engineering Block, Gate B"
              class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-all <?= isset($errors['location']) ? 'border-red-400 focus:ring-2 focus:ring-red-200' : 'border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100' ?>"
            />
            <?php if (isset($errors['location'])): ?>
              <p class="text-xs text-red-500 mt-1.5"><?= $errors['location'] ?></p>
            <?php endif; ?>
          </div>

          <!-- Date -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="date">
              Date <span class="text-red-400">*</span>
            </label>
            <input type="date" id="date" name="date"
              value="<?= htmlspecialchars($old['date'] ?? '') ?>"
              max="<?= date('Y-m-d') ?>"
              class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-all <?= isset($errors['date']) ? 'border-red-400 focus:ring-2 focus:ring-red-200' : 'border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100' ?>"
            />
            <?php if (isset($errors['date'])): ?>
              <p class="text-xs text-red-500 mt-1.5"><?= $errors['date'] ?></p>
            <?php endif; ?>
          </div>

        </div>
      </div>

      <!-- ── SECTION: Image ── -->
      <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-5">
        <h2 class="text-sm font-semibold text-slate-800 mb-1">Item Photo</h2>
        <p class="text-xs text-slate-400 mb-5 pb-3 border-b border-slate-100">A clear photo helps owners recognise their item faster.</p>

        <!-- Drop zone -->
        <label for="image"
          class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-slate-200 rounded-xl px-6 py-10 cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-all group <?= isset($errors['image']) ? 'border-red-400 bg-red-50/40' : '' ?>"
          id="drop-label">
          <span class="text-3xl" id="drop-icon">🖼️</span>
          <span class="text-sm font-medium text-slate-600 group-hover:text-blue-600 transition-colors" id="drop-text">
            Click to upload or drag & drop
          </span>
          <span class="text-xs text-slate-400">JPG, PNG, WEBP — max 3MB</span>
          <input type="file" id="image" name="image" accept="image/*" class="hidden" onchange="previewImage(this)" />
        </label>

        <!-- Preview -->
        <div id="image-preview" class="mt-4 hidden">
          <img id="preview-img" src="" alt="Preview" class="h-40 rounded-xl object-cover border border-slate-200" />
          <button type="button" onclick="clearImage()" class="mt-2 text-xs text-red-500 hover:underline">Remove</button>
        </div>

        <?php if (isset($errors['image'])): ?>
          <p class="text-xs text-red-500 mt-2"><?= $errors['image'] ?></p>
        <?php endif; ?>
      </div>

      <!-- ── SECTION: Verification ── -->
      <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-7">
        <h2 class="text-sm font-semibold text-slate-800 mb-1">Verification Question</h2>
        <p class="text-xs text-slate-400 mb-5 pb-3 border-b border-slate-100">
          Set a question only the real owner can answer. This is how claims are verified before admin approval.
        </p>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5" for="verification_question">
            Your verification question <span class="text-red-400">*</span>
          </label>
          <input type="text" id="verification_question" name="verification_question"
            value="<?= htmlspecialchars($old['verification_question'] ?? '') ?>"
            placeholder='e.g. "What sticker is on the front pocket?"'
            class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-all <?= isset($errors['verification_question']) ? 'border-red-400 focus:ring-2 focus:ring-red-200' : 'border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100' ?>"
          />
          <?php if (isset($errors['verification_question'])): ?>
            <p class="text-xs text-red-500 mt-1.5"><?= $errors['verification_question'] ?></p>
          <?php endif; ?>
          <p class="text-xs text-slate-400 mt-2">
            💡 Good examples: a specific mark, what's inside the bag, a name written on it.
          </p>
        </div>
      </div>

      <!-- SUBMIT -->
      <button type="submit"
        class="w-full py-3 bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white font-semibold text-sm rounded-xl transition-all shadow-sm">
        Submit Report
      </button>

      <p class="text-center text-xs text-slate-400 mt-4">
        Your report will be reviewed and published after admin verification.
      </p>

    </form>
  </div>

  <script>
    // ── Type toggle ────────────────────────────────────────────────────────
    function setType(type) {
      document.getElementById('type-input').value = type;

      const lostBtn  = document.getElementById('toggle-lost');
      const foundBtn = document.getElementById('toggle-found');
      const hint     = document.getElementById('location-hint');

      const activeClass   = 'bg-white text-slate-800 shadow-sm';
      const inactiveClass = 'text-slate-500 hover:text-slate-700';

      if (type === 'lost') {
        lostBtn.className  = lostBtn.className.replace(inactiveClass, activeClass);
        foundBtn.className = foundBtn.className.replace(activeClass, inactiveClass);
        hint.textContent   = ' — where was it lost?';
      } else {
        foundBtn.className = foundBtn.className.replace(inactiveClass, activeClass);
        lostBtn.className  = lostBtn.className.replace(activeClass, inactiveClass);
        hint.textContent   = ' — where did you find it?';
      }
    }

    // ── Image preview ──────────────────────────────────────────────────────
    function previewImage(input) {
      if (!input.files || !input.files[0]) return;
      const reader = new FileReader();
      reader.onload = e => {
        document.getElementById('preview-img').src = e.target.result;
        document.getElementById('image-preview').classList.remove('hidden');
        document.getElementById('drop-icon').textContent = '✅';
        document.getElementById('drop-text').textContent = input.files[0].name;
      };
      reader.readAsDataURL(input.files[0]);
    }

    function clearImage() {
      document.getElementById('image').value = '';
      document.getElementById('image-preview').classList.add('hidden');
      document.getElementById('drop-icon').textContent = '🖼️';
      document.getElementById('drop-text').textContent = 'Click to upload or drag & drop';
    }
  </script>

</body>
</html>