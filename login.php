<?php
// Start session
session_start();

// Include database config
require_once 'config.php';

// Redirect if already logged in (optional, adjust based on your needs)
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php"); // Assumes a dashboard page exists
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Basic validation
    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        // Query the user
        $query = "SELECT user_id, email, password, full_name FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['success'] = "Welcome back, " . $user['full_name'] . "!";
                header("Location: dashboard.php"); // Redirect to dashboard
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with that email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EventPulse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #1A2A44 0%, #0F1829 100%);
        }
    </style>
</head>
<body class="bg-[#F5F6F5] min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-auto p-6 bg-white rounded-lg shadow-lg">
        <h2 class="text-3xl font-bold text-[#1A2A44] mb-6 text-center">Sign In to EventPulse</h2>
        
        <!-- Display success/error messages -->
        <?php if (isset($error)): ?>
            <p class="text-red-500 text-center mb-4"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="text-green-500 text-center mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <!-- Login form -->
        <form method="POST" action="">
            <div class="mb-4">
                <label for="email" class="block text-[#1A2A44] font-medium mb-2">Email</label>
                <input type="email" name="email" id="email" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#FF6F61]" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-[#1A2A44] font-medium mb-2">Password</label>
                <input type="password" name="password" id="password" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#FF6F61]" required>
            </div>
            <button type="submit" class="w-full bg-[#FF6F61] text-white py-3 rounded hover:bg-opacity-90 font-medium">Sign In</button>
        </form>
        
        <p class="text-center text-gray-600 mt-4">Don’t have an account? <a href="register.php" class="text-[#FF6F61] hover:underline">Register</a></p>
    </div>
</body>
</html>