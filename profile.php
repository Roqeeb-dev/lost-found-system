<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header("Location: auth/login.php");
    exit();
}

require_once __DIR__ . '/config/db.php';

$user = null;
$error = '';
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    $error = 'Unable to load your profile. Please try again later.';
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <title>Profile | Campus L&F</title>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
  <div class="max-w-4xl mx-auto px-4 py-10">
    <div class="mb-6 flex items-center justify-between gap-4">
      <div>
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Account</p>
        <h1 class="text-3xl font-semibold text-slate-900">My Profile</h1>
      </div>
      <a href="dashboard.php" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">Back to Dashboard</a>
    </div>

    <?php if ($error): ?>
      <div class="rounded-2xl border border-red-200 bg-red-50 p-6 text-red-700">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php else: ?>
      <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <div class="rounded-3xl bg-white p-8 shadow-lg border border-slate-200">
          <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-blue-100 text-3xl font-semibold text-blue-700">
              <?= htmlspecialchars(strtoupper(substr($user['name'], 0, 1))) ?>
            </div>
            <div>
              <p class="text-sm uppercase tracking-[0.18em] text-slate-400">Logged in as</p>
              <h2 class="text-2xl font-semibold text-slate-900"><?= htmlspecialchars($user['name']) ?></h2>
            </div>
          </div>

          <div class="mt-8 space-y-5">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
              <p class="text-sm text-slate-500">Full name</p>
              <p class="mt-2 text-lg font-medium text-slate-900"><?= htmlspecialchars($user['name']) ?></p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
              <p class="text-sm text-slate-500">Email address</p>
              <p class="mt-2 text-lg font-medium text-slate-900"><?= htmlspecialchars($user['email']) ?></p>
            </div>
          </div>
        </div>

        <aside class="rounded-3xl bg-white p-8 shadow-lg border border-slate-200">
          <p class="text-sm uppercase tracking-[0.18em] text-slate-400">Profile details</p>
          <div class="mt-6 space-y-4 text-sm text-slate-600">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-slate-500">Account type</p>
              <p class="mt-1 font-medium text-slate-900">Student</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-slate-500">Role</p>
              <p class="mt-1 font-medium text-slate-900">User</p>
            </div>
          </div>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
