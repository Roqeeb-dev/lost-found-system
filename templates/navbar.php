<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
     <!-- NAVBAR -->
  <header class="bg-white/95 backdrop-blur-md border-b border-zinc-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-5 py-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center text-white text-2xl shadow-inner">
          📍
        </div>
        <div class="font-bold text-2xl tracking-tighter">
          Campus <span class="text-blue-600">L&F</span>
        </div>
      </div>

      <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-zinc-600">
        <a href="#features" class="hover:text-blue-600 transition-colors">Features</a>
        <a href="#how" class="hover:text-blue-600 transition-colors">How it works</a>
      </nav>

      <div class="flex items-center gap-3">
        <a href="auth/login.php" 
           class="hidden sm:block px-5 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-100 rounded-2xl transition">
          Log in
        </a>
        <a href="auth/register.php" 
           class="px-6 py-2.5 text-sm font-semibold bg-blue-600 text-white rounded-2xl hover:bg-blue-700 active:scale-95 transition-all">
          Get Started Free
        </a>
      </div>
    </div>
  </header>
</body>
</html>