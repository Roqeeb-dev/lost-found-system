<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <script src="https://unpkg.com/lucide@latest"></script>
  
  <title>Campus L&F • Lost & Found Made Simple</title>

  <style>
    .hero-bg {
      background: radial-gradient(circle at 30% 20%, #1e40af, #0f172a 70%);
    }
    .glass {
      background: rgba(255, 255, 255, 0.09);
      backdrop-filter: blur(16px);
      border: 1px solid rgba(255,255,255,0.15);
    }
  </style>
</head>

<body class="bg-zinc-50 text-zinc-800 antialiased">

 <?php 
  include('./templates/navbar.php')
 ?>

  <!-- HERO -->
  <section class="hero-bg text-white py-12 md:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-5 grid md:grid-cols-2 gap-8 md:gap-14 items-center">
      
      <!-- Left Content -->
      <div class="space-y-8">
        <div class="inline-flex items-center gap-2 bg-white/10 px-4 py-2 rounded-full text-sm backdrop-blur-md">
          <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
          </span>
          Trusted by students at 40+ campuses
        </div>

        <h1 class="text-3xl sm:text-4xl md:text-6xl font-semibold leading-tight tracking-tighter">
          Never lose anything<br>on campus again.
        </h1>

        <p class="text-base sm:text-lg md:text-xl text-blue-100 max-w-lg">
          The modern lost &amp; found system for universities. 
          Report items, search found ones, and reclaim with secure verification.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
          <a href="items/list.php" 
             class="px-6 sm:px-8 py-3 sm:py-4 bg-white text-blue-700 font-semibold rounded-3xl hover:bg-zinc-100 transition-all text-center text-base sm:text-lg">
            Browse Found Items
          </a>
          <a href="items/create.php" 
             class="px-6 sm:px-8 py-3 sm:py-4 border border-white/50 hover:border-white font-medium rounded-3xl transition-all text-center text-base sm:text-lg">
            Report Lost Item
          </a>
        </div>

        <div class="flex items-center gap-8 text-sm">
          <div class="flex -space-x-4">
            <div class="w-9 h-9 bg-white/20 rounded-2xl flex items-center justify-center ring-2 ring-white text-lg">👟</div>
            <div class="w-9 h-9 bg-white/20 rounded-2xl flex items-center justify-center ring-2 ring-white text-lg">📱</div>
            <div class="w-9 h-9 bg-white/20 rounded-2xl flex items-center justify-center ring-2 ring-white text-lg">🎒</div>
          </div>
          <div>
            <p class="font-medium">1,284 items recovered this month</p>
            <p class="text-blue-200 text-xs">Real students. Real recoveries.</p>
          </div>
        </div>
      </div>

      <!-- Right Visual -->
      <div class="relative flex justify-center mt-8 md:mt-0">
        <div class="glass p-3 sm:p-4 rounded-2xl sm:rounded-3xl shadow-2xl max-w-md w-full">
          <div class="bg-zinc-900 rounded-2xl overflow-hidden">
            <img src="https://picsum.photos/id/1015/800/620" 
                 alt="Campus L&F Dashboard" 
                 class="w-full rounded-lg sm:rounded-2xl">
          </div>
        </div>

        <!-- Floating Success Badge -->
        <div class="absolute -top-4 -right-4 sm:-top-6 sm:-right-6 glass px-4 sm:px-6 py-3 sm:py-4 rounded-2xl sm:rounded-3xl text-center shadow-xl">
          <div class="text-3xl sm:text-4xl font-bold text-white leading-none">98%</div>
          <div class="text-[10px] sm:text-xs tracking-widest text-blue-200 mt-1">SUCCESS RATE</div>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURES -->
  <section id="features" class="py-12 md:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-5">
      <div class="text-center mb-10 md:mb-16">
        <span class="text-blue-600 font-medium tracking-widest text-xs sm:text-sm">WHY STUDENTS LOVE IT</span>
        <h2 class="text-2xl sm:text-3xl md:text-4xl font-semibold mt-2 md:mt-3">Simple. Fast. Secure.</h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 md:gap-8">
        <div class="group bg-white border border-zinc-100 hover:border-blue-200 p-8 rounded-3xl transition-all hover:-translate-y-2">
          <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-3xl mb-6">
            🔒
          </div>
          <h3 class="font-semibold text-2xl mb-3">Secure Claims</h3>
          <p class="text-zinc-600">Only the real owner can claim an item using a custom verification question they set.</p>
        </div>

        <div class="group bg-white border border-zinc-100 hover:border-blue-200 p-8 rounded-3xl transition-all hover:-translate-y-2">
          <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-3xl mb-6">
            📸
          </div>
          <h3 class="font-semibold text-2xl mb-3">Quick Reporting</h3>
          <p class="text-zinc-600">Upload photos, add location and details in under 30 seconds.</p>
        </div>

        <div class="group bg-white border border-zinc-100 hover:border-blue-200 p-8 rounded-3xl transition-all hover:-translate-y-2">
          <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-3xl mb-6">
            ⚡
          </div>
          <h3 class="font-semibold text-2xl mb-3">Fast Recovery</h3>
          <p class="text-zinc-600">Smart matching and admin approval system gets your items back quicker.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS (Added for better flow) -->
  <section id="how" class="py-12 md:py-20 bg-zinc-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-5">
      <div class="text-center mb-10 md:mb-16">
        <h2 class="text-2xl sm:text-3xl md:text-4xl font-semibold">How it works in 3 steps</h2>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-10 max-w-5xl mx-auto">
        <div class="text-center">
          <div class="mx-auto w-20 h-20 bg-white shadow rounded-3xl flex items-center justify-center text-5xl mb-6">1</div>
          <h3 class="font-semibold text-xl mb-2">Report or Browse</h3>
          <p class="text-zinc-600">Post a lost item or search through recently found ones.</p>
        </div>
        <div class="text-center">
          <div class="mx-auto w-20 h-20 bg-white shadow rounded-3xl flex items-center justify-center text-5xl mb-6">2</div>
          <h3 class="font-semibold text-xl mb-2">Submit Claim</h3>
          <p class="text-zinc-600">Answer the verification question to prove ownership.</p>
        </div>
        <div class="text-center">
          <div class="mx-auto w-20 h-20 bg-white shadow rounded-3xl flex items-center justify-center text-5xl mb-6">3</div>
          <h3 class="font-semibold text-xl mb-2">Get It Back</h3>
          <p class="text-zinc-600">Admin verifies and you safely recover your item.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- FINAL CTA -->
  <section class="py-16 md:py-24 bg-gradient-to-br from-blue-600 to-indigo-700 text-white">
    <div class="max-w-2xl mx-auto text-center px-4 sm:px-5">
      <h2 class="text-2xl sm:text-3xl md:text-5xl font-semibold tracking-tight mb-4 md:mb-6">
        Ready to bring your stuff home?
      </h2>
      <p class="text-blue-100 text-base sm:text-lg mb-6 md:mb-10">
        Join thousands of students who are already recovering their lost belongings every week.
      </p>
      
      <a href="auth/register.php" 
         class="inline-block px-8 sm:px-10 py-3 sm:py-4 bg-white text-blue-700 font-semibold text-base sm:text-lg rounded-3xl hover:bg-zinc-100 transition-all">
        Create Your Free Account
      </a>
    </div>
  </section>

  <!-- FOOTER -->
  <?php 
   include('./templates/footer.php')
  ?>

  <script>
    lucide.createIcons();
  </script>
</body>
</html>