<?php
session_start();

$email = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $email = trim($_POST["email"]);
  $password = $_POST["password"];

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format";
  } else {

    $conn = new mysqli("localhost", "root", "", "lost-found-db");

    if ($conn->connect_error) {
      die("DB Error");
    }

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();

      if (password_verify($password, $user['password'])) {
        // Login success → create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'] ?? 'user';
        
        $redirect = ($_SESSION['user_role'] === 'admin') ? "../admin/dashboard.php" : "../dashboard.php";
        header("Location: $redirect");
        exit();
      } else {
        $error = "Incorrect password";
      }

    } else {
      $error = "User not found";
    }

    $stmt->close();
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
  <title>Login | Campus L&F</title>
</head>

<body class="min-h-screen flex items-center justify-center bg-zinc-100">

<div class="w-full max-w-md px-6">

  <div class="bg-white shadow-2xl rounded-2xl p-8 border border-zinc-100">

    <h2 class="text-2xl font-semibold text-zinc-800">Welcome back</h2>
    <p class="text-sm text-zinc-500 mt-1">Login to continue</p>

    <?php if ($error): ?>
      <div class="mt-4 p-3 text-sm bg-red-100 text-red-600 rounded-lg">
        <?php echo $error; ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="mt-6 space-y-5">

      <!-- Email -->
      <div>
        <label class="text-sm text-zinc-600">Email</label>
        <input
          type="email"
          name="email"
          value="<?php echo htmlspecialchars($email); ?>"
          class="w-full mt-1 px-4 py-3 rounded-xl border border-zinc-200 focus:ring-2 focus:ring-blue-500"
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
          class="w-full mt-1 px-4 py-3 rounded-xl border border-zinc-200 focus:ring-2 focus:ring-blue-500"
          autocomplete="current-password"
          required
        />
      </div>

      <button
        type="submit"
        class="w-full bg-blue-600 text-white py-3 rounded-xl font-medium hover:bg-blue-700 transition active:scale-95"
      >
        Sign in
      </button>

    </form>

    <p class="text-sm text-center text-zinc-500 mt-6">
      Don’t have an account?
      <a href="register.php" class="text-blue-600 font-medium hover:underline">Create account</a>
    </p>

  </div>

</div>

</body>
</html>
