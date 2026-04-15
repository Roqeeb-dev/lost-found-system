<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <title>Register | Campus L&F</title>
</head>

<body class="min-h-screen flex">

  <!-- Left Side -->
  <div class="hidden md:flex w-1/2 bg-gradient-to-br from-indigo-700 to-purple-700 text-white p-12 flex-col justify-between">
    
    <div>
      <h1 class="text-3xl font-bold">Join Campus L&F</h1>
      <p class="mt-4 text-purple-100">
        Report lost items, find belongings, and help others recover theirs.
      </p>
    </div>

    <div class="text-sm text-purple-100">
      “Community powered recovery system”
    </div>

  </div>

  <!-- Form Side -->
  <div class="w-full md:w-1/2 flex items-center justify-center bg-gray-50 p-6">

    <div class="w-full max-w-md">

      <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100">

        <h2 class="text-2xl font-bold text-gray-800">Create account 🚀</h2>
        <p class="text-sm text-gray-500 mt-1">
          It only takes a minute
        </p>

        <form class="mt-6 space-y-5">

          <!-- Name -->
          <div>
            <label class="text-sm text-gray-600">Full Name</label>
            <input
              type="text"
              placeholder="John Doe"
              class="w-full mt-1 px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <!-- Email -->
          <div>
            <label class="text-sm text-gray-600">Email</label>
            <input
              type="email"
              placeholder="you@example.com"
              class="w-full mt-1 px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <!-- Password -->
          <div>
            <label class="text-sm text-gray-600">Password</label>
            <input
              type="password"
              placeholder="••••••••"
              class="w-full mt-1 px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <!-- Button -->
          <button
            type="submit"
            class="w-full bg-indigo-600 text-white py-3 rounded-lg font-medium hover:bg-indigo-700 transition"
          >
            Create account
          </button>

        </form>

        <p class="text-sm text-center text-gray-500 mt-6">
          Already have an account?
          <a href="login.php" class="text-indigo-600 font-medium hover:underline">
            Login
          </a>
        </p>

      </div>

    </div>

  </div>

</body>
</html>