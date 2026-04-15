<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <title>Campus Lost & Found</title>
</head>

<body class="bg-gray-50 text-gray-800">

<!-- NAVBAR -->
<header class="flex justify-between items-center px-8 py-5 bg-white shadow-sm sticky top-0 z-50">

  <div class="font-bold text-xl text-blue-600">
    Campus L&F
  </div>

  <nav class="hidden md:flex gap-8 text-sm text-gray-600">
    <a href="#features" class="hover:text-blue-600">Features</a>
    <a href="#how" class="hover:text-blue-600">How it works</a>
    <a href="#about" class="hover:text-blue-600">About</a>
  </nav>

  <div class="flex gap-3">
    <a href="auth/login.php" class="px-4 py-2 text-sm border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50">
      Login
    </a>
    <a href="auth/register.php" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
      Get Started
    </a>
  </div>

</header>

<!-- HERO SECTION -->
<section class="px-6 md:px-20 py-20 flex flex-col md:flex-row items-center gap-12">

  <!-- Left -->
  <div class="flex-1">

    <h1 class="text-4xl md:text-5xl font-bold leading-tight">
      Find Lost Items on Campus — <span class="text-blue-600">Fast & Secure</span>
    </h1>

    <p class="mt-6 text-gray-600 text-lg">
      A trusted system where students report lost items, discover found items,
      and verify ownership through a secure claim process.
    </p>

    <div class="mt-8 flex gap-4">
      <a href="items/list.php" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        Browse Items
      </a>

      <a href="items/create.php" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-white">
        Report Item
      </a>
    </div>

    <p class="mt-6 text-sm text-gray-500">
      No spam. No confusion. Just real recovery.
    </p>

  </div>

  <!-- Right -->
  <div class="flex-1">
    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-10 text-white shadow-lg">
      <h2 class="text-2xl font-bold">How it works</h2>

      <div class="mt-6 space-y-4 text-sm">

        <div class="bg-white/10 p-3 rounded-lg">
          1. Report lost or found item
        </div>

        <div class="bg-white/10 p-3 rounded-lg">
          2. Submit claim with verification answer
        </div>

        <div class="bg-white/10 p-3 rounded-lg">
          3. Admin approves or rejects claim
        </div>

      </div>
    </div>
  </div>

</section>

<!-- FEATURES -->
<section id="features" class="px-6 md:px-20 py-20 bg-white">

  <h2 class="text-3xl font-bold text-center mb-12">Key Features</h2>

  <div class="grid md:grid-cols-3 gap-8">

    <div class="p-6 bg-gray-50 rounded-xl">
      <h3 class="font-bold text-lg text-blue-600">Secure Claims</h3>
      <p class="text-gray-600 mt-2 text-sm">
        Only rightful owners can retrieve items using verification questions.
      </p>
    </div>

    <div class="p-6 bg-gray-50 rounded-xl">
      <h3 class="font-bold text-lg text-blue-600">Easy Reporting</h3>
      <p class="text-gray-600 mt-2 text-sm">
        Quickly post lost or found items with full details and images.
      </p>
    </div>

    <div class="p-6 bg-gray-50 rounded-xl">
      <h3 class="font-bold text-lg text-blue-600">Admin Control</h3>
      <p class="text-gray-600 mt-2 text-sm">
        Admins manage all claims and ensure proper verification.
      </p>
    </div>

  </div>

</section>

<!-- HOW IT WORKS -->
<section id="how" class="px-6 md:px-20 py-20 bg-gray-50">

  <h2 class="text-3xl font-bold text-center mb-12">How It Works</h2>

  <div class="grid md:grid-cols-3 gap-6 text-center">

    <div class="bg-white p-6 rounded-xl shadow-sm">
      <h3 class="font-bold">Step 1</h3>
      <p class="text-gray-600 mt-2 text-sm">User reports lost or found item</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm">
      <h3 class="font-bold">Step 2</h3>
      <p class="text-gray-600 mt-2 text-sm">Another user submits claim with answer</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm">
      <h3 class="font-bold">Step 3</h3>
      <p class="text-gray-600 mt-2 text-sm">Admin verifies and approves request</p>
    </div>

  </div>

</section>

<!-- ABOUT -->
<section id="about" class="px-6 md:px-20 py-20 bg-white text-center">

  <h2 class="text-3xl font-bold">About the System</h2>

  <p class="mt-6 text-gray-600 max-w-3xl mx-auto">
    Campus Lost & Found is designed to reduce loss of personal items in school environments
    by providing a structured reporting and verification system that ensures transparency and trust.
  </p>

</section>

<!-- CTA -->
<section class="px-6 md:px-20 py-20 bg-blue-600 text-white text-center rounded-none">

  <h2 class="text-3xl font-bold">Ready to recover your lost items?</h2>

  <p class="mt-4 text-blue-100">
    Start using the system today — report, search, and claim securely.
  </p>

  <div class="mt-8 flex justify-center gap-4">
    <a href="auth/register.php" class="px-6 py-3 bg-white text-blue-600 rounded-lg font-medium">
      Get Started
    </a>

    <a href="auth/login.php" class="px-6 py-3 border border-white rounded-lg">
      Login
    </a>
  </div>

</section>

<!-- FOOTER -->
<footer class="bg-gray-900 text-gray-300 text-center py-6">

  <p class="text-sm">
    © 2026 Campus Lost & Found System. Built for students.
  </p>

</footer>

</body>
</html>