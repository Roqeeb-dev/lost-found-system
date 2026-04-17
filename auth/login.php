<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <title>Login | Campus L&F</title>
</head>

<body class="min-h-screen flex">

<?php 
  $email = $password = ""
  $emailErr = $passwordErr = ""

?>

  <!-- Left Visual Side -->
  <div class="hidden md:flex w-1/2 bg-gradient-to-br from-blue-600 to-indigo-700 text-white p-12 flex-col justify-between">
    
    <div>
      <h1 class="text-3xl font-bold">Campus Lost & Found</h1>
      <p class="mt-4 text-blue-100">
        Find what you lost. Return what you found. Built for students.
      </p>
    </div>

    <div class="text-sm text-blue-100">
      “Every lost item has a story — help complete it.”
    </div>

  </div>

  <!-- Right Form Side -->
  <div class="w-full md:w-1/2 flex items-center justify-center bg-gray-50 p-6">

    <div class="w-full max-w-md">

      <!-- Card -->
      <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100">

        <h2 class="text-2xl font-bold text-gray-800">Welcome back 👋</h2>
        <p class="text-sm text-gray-500 mt-1">
          Login to continue
        </p>

        <form class="mt-6 space-y-5">

          <!-- Email -->
          <div>
            <label class="text-sm text-gray-600">Email</label>
            <input
              type="email"
              placeholder="you@example.com"
              class="w-full mt-1 px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>

          <!-- Password -->
          <div>
            <label class="text-sm text-gray-600">Password</label>
            <input
              type="password"
              placeholder="••••••••"
              class="w-full mt-1 px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>

          <!-- Options -->
          <div class="flex items-center justify-between text-sm">           
            <a href="#" class="text-blue-600 hover:underline">
              Forgot password?
            </a>
          </div>

          <!-- Button -->
          <button
            type="submit"
            class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition"
          >
            Sign in
          </button>

        </form>

        <!-- Footer -->
        <p class="text-sm text-center text-gray-500 mt-6">
          Don’t have an account?
          <a href="register.php" class="text-blue-600 font-medium hover:underline">
            Create account
          </a>
        </p>

      </div>

    </div>

  </div>

</body>
</html>