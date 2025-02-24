<?php
// Start session (for success/error messages)
session_start();

// Include database config
require_once 'config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);

    // Basic validation
    if (empty($email) || empty($password) || empty($full_name)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email already exists
        $check_query = "SELECT email FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($result) > 0) {
            $error = "Email already registered.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user into database
            $query = "INSERT INTO users (email, password, full_name) VALUES ('$email', '$hashed_password', '$full_name')";
            if (mysqli_query($conn, $query)) {
                $_SESSION['success'] = "Registration successful! Please log in.";
                header("Location: login.php"); // Redirect to login page (create this later if needed)
                exit();
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EventPulse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #1A2A44 0%, #0F1829 100%);
        }
    </style>
</head>
<body class="bg-[#F5F6F5] min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-auto p-6 bg-white rounded-lg shadow-lg">
        <h2 class="text-3xl font-bold text-[#1A2A44] mb-6 text-center">Join EventPulse</h2>
        
        <!-- Display success/error messages -->
        <?php if (isset($error)): ?>
            <p class="text-red-500 text-center mb-4"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="text-green-500 text-center mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <!-- Registration form -->
        <form method="POST" action="">
            <div class="mb-4">
                <label for="full_name" class="block text-[#1A2A44] font-medium mb-2">Full Name</label>
                <input type="text" name="full_name" id="full_name" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#FF6F61]" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-[#1A2A44] font-medium mb-2">Email</label>
                <input type="email" name="email" id="email" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#FF6F61]" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-[#1A2A44] font-medium mb-2">Password</label>
                <input type="password" name="password" id="password" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#FF6F61]" required>
            </div>
            <button type="submit" class="w-full bg-[#FF6F61] text-white py-3 rounded hover:bg-opacity-90 font-medium">Register</button>
        </form>
        
        <p class="text-center text-gray-600 mt-4">Already have an account? <a href="login.php" class="text-[#FF6F61] hover:underline">Sign In</a></p>
    </div>
</body>
</html>