<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Campus Lost & Found System</title>
</head>

<body class="bg-gray-50 text-gray-800">

<!-- Navbar -->
<header class="flex justify-between items-center px-8 py-4 bg-white shadow-md sticky top-0">

    <div class="text-xl font-bold text-blue-600">
        Campus L&F
    </div>

        <nav>
        <ul class="flex gap-6 text-gray-600">
            <li><a href="#" class="hover:text-blue-500">Home</a></li>
            <li><a href="#about" class="hover:text-blue-500">About</a></li>
            <li><a href="#how" class="hover:text-blue-500">How it works</a></li>
            <li><a href="#contact" class="hover:text-blue-500">Contact</a></li>
        </ul>
    </nav>

    <div class="flex gap-3">
        <a href="auth/login.php" class="px-4 py-2 border border-blue-600 text-blue-600 rounded hover:bg-blue-50">
            Login
        </a>
        <a href="auth/register.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Sign Up
        </a>
    </div>

</header>

<!-- Hero Section -->
<main class="text-center py-20 px-6">

    <h1 class="text-4xl md:text-5xl font-bold mb-4">
        Lost Something on Campus?
    </h1>

    <p class="text-gray-600 max-w-2xl mx-auto mb-8">
        A simple and secure platform where students can report lost items, find missing belongings,
        and verify ownership through a smart claim system.
    </p>

    <div class="flex justify-center gap-4">
        <a href="items/list.php" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Browse Items
        </a>

        <a href="items/create.php" class="px-6 py-3 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50">
            Report Item
        </a>
    </div>

</main>

<!-- About Section -->
<section id="about" class="bg-white py-16 px-8 text-center">

    <h2 class="text-3xl font-bold mb-6">About the System</h2>

    <p class="max-w-3xl mx-auto text-gray-600">
        This platform helps students recover lost items and return found belongings in a structured way.
        Every claim is verified using security questions to ensure only the rightful owner can retrieve items.
    </p>

</section>

<!-- How it works -->
<section id="how" class="py-16 px-8 bg-gray-50">

    <h2 class="text-3xl font-bold text-center mb-10">How It Works</h2>

    <div class="grid md:grid-cols-3 gap-8 text-center">

        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-bold mb-2">1. Report Item</h3>
            <p class="text-gray-600">Users submit details of lost or found items.</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-bold mb-2">2. Claim Item</h3>
            <p class="text-gray-600">Users submit a claim with a security verification answer.</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-bold mb-2">3. Admin Approval</h3>
            <p class="text-gray-600">Admin verifies and approves or rejects claims.</p>
        </div>

    </div>

</section>

<!-- Contact -->
<section id="contact" class="py-16 px-8 bg-white text-center">

    <h2 class="text-3xl font-bold mb-6">Contact</h2>

    <p class="text-gray-600 mb-4">
        Need help or found a bug? Reach out to the admin team.
    </p>

    <p class="text-blue-600">support@campuslf.com</p>

</section>

<!-- Footer -->
<footer class="bg-gray-900 text-white text-center py-6 mt-10">

    <p>&copy; 2026 Campus Lost & Found System. All rights reserved.</p>

</footer>

</body>
</html>