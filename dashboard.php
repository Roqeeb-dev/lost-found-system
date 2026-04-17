<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <title>Dashboard</title>
</head>

<body class="bg-zinc-100 min-h-screen p-6">

  <div class="max-w-5xl mx-auto">

    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-2xl font-semibold">
        Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋
      </h1>

      <a href="logout.php" class="text-red-600 text-sm">Logout</a>
    </div>

    <!-- Actions -->
    <div class="grid md:grid-cols-3 gap-4 mb-8">
      <a href="items/create.php" class="bg-blue-600 text-white p-5 rounded-xl">
        Report Lost Item
      </a>
      <a href="items/list.php" class="bg-white p-5 rounded-xl border">
        Browse Items
      </a>
      <a href="claims.php" class="bg-white p-5 rounded-xl border">
        My Claims
      </a>
    </div>

    <!-- Placeholder -->
    <div class="bg-white p-6 rounded-xl border">
      <h2 class="font-semibold mb-2">Your Activity</h2>
      <p class="text-sm text-zinc-500">
        Your reported items and claims will appear here.
      </p>
    </div>

  </div>

</body>
</html>