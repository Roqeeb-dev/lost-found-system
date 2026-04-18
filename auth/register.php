<?php
$name = $email = "";
$success = false;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $name = trim($_POST["name"]);
  $email = trim($_POST["email"]);
  $passwordRaw = $_POST["password"];

  // Basic backend validation 
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format";
  } elseif (strlen($passwordRaw) < 6) {
    $error = "Password too short";
  } else {
    $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

    $conn = new mysqli("localhost", "root", "", "lost-found-db");

    if ($conn->connect_error) {
      die("DB Error");
    }

    // Check duplicate email
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
      $error = "Email already exists";
    } else {
      $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $name, $email, $password);
      $stmt->execute();

      $stmt->close();
      $check->close();
      $conn->close();

      header("Location: login.php");
      exit();
    }

    $check->close();
    $conn->close();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <title>Register | Campus L&F</title>
</head>

<body class="min-h-screen flex items-center justify-center bg-zinc-100">

<div class="w-full max-w-md px-6">

  <div class="bg-white shadow-2xl rounded-2xl p-8 border border-zinc-100">

    <h2 class="text-2xl font-semibold text-zinc-800">Create account</h2>
    <p class="text-sm text-zinc-500 mt-1">Get started in seconds</p>

    <?php if ($success): ?>
      <div class="mt-4 p-3 text-sm bg-green-100 text-green-700 rounded-lg">
        Account created successfully 🎉
      </div>
    <?php elseif ($error): ?>
      <div class="mt-4 p-3 text-sm bg-red-100 text-red-600 rounded-lg">
        <?php echo $error; ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="mt-6 space-y-5">

      <!-- Name -->
      <div>
        <label class="text-sm text-zinc-600">Full Name</label>
        <input
          type="text"
          name="name"
          value="<?php echo htmlspecialchars($name); ?>"
          class="w-full mt-1 px-4 py-3 rounded-xl border border-zinc-200 focus:ring-2 focus:ring-indigo-500"
          autocomplete="name"
          required
        />
      </div>

      <!-- Email -->
      <div>
        <label class="text-sm text-zinc-600">Email</label>
        <input
          type="email"
          name="email"
          value="<?php echo htmlspecialchars($email); ?>"
          class="w-full mt-1 px-4 py-3 rounded-xl border border-zinc-200 focus:ring-2 focus:ring-indigo-500"
          autocomplete="email"
          required
        />
      </div>

      <!-- Password -->
      <div>
        <label class="text-sm text-zinc-600">Password</label>
        <input
          type="password"
          name="password"
          class="w-full mt-1 px-4 py-3 rounded-xl border border-zinc-200 focus:ring-2 focus:ring-indigo-500"
          autocomplete="new-password"
          required
        />
      </div>

      <button
        type="submit"
        class="w-full bg-indigo-600 text-white py-3 rounded-xl font-medium hover:bg-indigo-700 transition active:scale-95"
      >
        Create account
      </button>

    </form>

    <p class="text-sm text-center text-zinc-500 mt-6">
      Already have an account?
      <a href="login.php" class="text-indigo-600 font-medium hover:underline">Login</a>
    </p>

  </div>

</div>

</body>
</html>
