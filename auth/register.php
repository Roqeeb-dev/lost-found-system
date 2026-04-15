<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Register | Campus L&F</title>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <!-- Container -->
    <div class="w-full max-w-md bg-white shadow-lg rounded-xl p-8">

        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-blue-600">Create Account</h1>
            <p class="text-gray-500 text-sm mt-1">
                Join Campus Lost & Found System
            </p>
        </div>

        <!-- Form -->
        <form class="space-y-4">

            <!-- Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                <input 
                    type="text" 
                    placeholder="Enter your name"
                    class="w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input 
                    type="email" 
                    placeholder="Enter your email"
                    class="w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input 
                    type="password" 
                    placeholder="Create password"
                    class="w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input 
                    type="password" 
                    placeholder="Confirm password"
                    class="w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Button -->
            <button 
                type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition"
            >
                Sign Up
            </button>

        </form>

        <!-- Login link -->
        <p class="text-center text-sm text-gray-500 mt-6">
            Already have an account? 
            <a href="login.php" class="text-blue-600 hover:underline">Login</a>
        </p>

    </div>

</body>
</html>