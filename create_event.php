<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to create an event.";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    if (empty($title) || empty($event_date)) {
        $error = "Title and date are required.";
    } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $event_date) || !strtotime($event_date)) {
        $error = "Invalid date format. Use YYYY-MM-DD.";
    } else {
        $query = "INSERT INTO events (user_id, title, event_date, location, description) 
                  VALUES ('$user_id', '$title', '$event_date', '$location', '$description')";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Event created successfully!";
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Error creating event: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - EventPulse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1A2A44',
                        secondary: '#FF6F61'
                    },
                    borderRadius: {
                        'none': '0px', 'sm': '4px', DEFAULT: '8px', 'md': '12px', 'lg': '16px', 'xl': '20px', '2xl': '24px', '3xl': '32px', 'full': '9999px', 'button': '8px'
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #1A2A44 0%, #0F1829 100%);
        }
    </style>
</head>
<body class="bg-[#F5F6F5] min-h-screen">
    <nav class="bg-primary text-white fixed top-0 w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between h-auto py-4 sm:h-16">
                <div class="flex items-center w-full sm:w-auto">
                    <span class="text-2xl font-['Pacifico']">EventPulse</span>
                    <button class="sm:hidden ml-auto text-white" onclick="toggleMenu()">
                        <i class="ri-menu-line text-xl"></i>
                    </button>
                </div>
                <div class="sm:flex flex-col sm:flex-row items-center sm:space-x-4 w-full sm:w-auto mt-2 sm:mt-0 hidden" id="nav-menu">
                    <div class="flex flex-col sm:flex-row items-baseline space-y-2 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
                        <a href="dashboard.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Dashboard</a>
                        <a href="events.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Events</a>
                        <a href="orders.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Orders</a>
                        <a href="budget.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Budget</a>
                        <a href="tasks.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Tasks</a>
                    </div>
                    <div class="flex items-center mt-2 sm:mt-0">
                        <span class="text-sm"><?php echo htmlspecialchars($full_name); ?></span>
                        <a href="?logout=true" class="ml-2 text-white hover:text-[#F8DDA4]"><i class="ri-logout-box-line"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-8">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold text-primary mb-6">Create a New Event</h1>

            <?php if (isset($error)): ?>
                <p class="text-red-500 mb-4"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <p class="text-green-500 mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label for="title" class="block text-primary font-medium mb-2">Event Title</label>
                    <input type="text" name="title" id="title" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-secondary" required>
                </div>
                <div class="mb-4">
                    <label for="event_date" class="block text-primary font-medium mb-2">Event Date</label>
                    <input type="date" name="event_date" id="event_date" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-secondary" required>
                </div>
                <div class="mb-4">
                    <label for="location" class="block text-primary font-medium mb-2">Location</label>
                    <input type="text" name="location" id="location" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-secondary">
                </div>
                <div class="mb-6">
                    <label for="description" class="block text-primary font-medium mb-2">Description</label>
                    <textarea name="description" id="description" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-secondary" rows="4"></textarea>
                </div>
                <div class="flex space-x-4">
                    <button type="submit" class="w-full bg-secondary text-white py-3 rounded hover:bg-opacity-90 font-medium">Create Event</button>
                    <a href="dashboard.php" class="w-full text-center bg-gray-200 text-gray-700 py-3 rounded hover:bg-gray-300 font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        function toggleMenu() {
            const menu = document.getElementById('nav-menu');
            menu.classList.toggle('hidden');
        }
    </script>
</body>
</html>