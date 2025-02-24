<?php
// Start session
session_start();

// Include database config
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to view event details.";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

// Check if event ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid event ID.";
    header("Location: dashboard.php");
    exit();
}

$event_id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch event details
$event_query = "SELECT title, event_date, location, description FROM events WHERE event_id = '$event_id' AND user_id = '$user_id'";
$event_result = mysqli_query($conn, $event_query);
if ($event_result === false) {
    die("Event query failed: " . mysqli_error($conn));
}
$event = mysqli_fetch_assoc($event_result);
if (!$event) {
    $_SESSION['error'] = "Event not found or you don’t have permission to view it.";
    header("Location: dashboard.php");
    exit();
}

// Handle delete actions
if (isset($_GET['delete_task'])) {
    $task_id = mysqli_real_escape_string($conn, $_GET['delete_task']);
    $delete_query = "DELETE FROM tasks WHERE task_id = '$task_id' AND event_id = '$event_id'";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success'] = "Task deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete task: " . mysqli_error($conn);
    }
    header("Location: event_details.php?id=$event_id");
    exit();
}

if (isset($_GET['delete_budget'])) {
    $budget_id = mysqli_real_escape_string($conn, $_GET['delete_budget']);
    $delete_query = "DELETE FROM budget WHERE budget_id = '$budget_id' AND event_id = '$event_id'";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success'] = "Budget item deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete budget item: " . mysqli_error($conn);
    }
    header("Location: event_details.php?id=$event_id");
    exit();
}

if (isset($_GET['delete_order'])) {
    $order_id = mysqli_real_escape_string($conn, $_GET['delete_order']);
    $delete_query = "DELETE FROM orders WHERE order_id = '$order_id' AND event_id = '$event_id'";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success'] = "Order deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete order: " . mysqli_error($conn);
    }
    header("Location: event_details.php?id=$event_id");
    exit();
}

// Fetch tasks
$tasks_query = "SELECT task_id, task_name, completed, due_date FROM tasks WHERE event_id = '$event_id'";
$tasks_result = mysqli_query($conn, $tasks_query);
if ($tasks_result === false) {
    die("Tasks query failed: " . mysqli_error($conn));
}
$tasks = [];
while ($row = mysqli_fetch_assoc($tasks_result)) {
    $tasks[] = $row;
}

// Fetch budget items
$budget_query = "SELECT budget_id, item_name, amount FROM budget WHERE event_id = '$event_id'";
$budget_result = mysqli_query($conn, $budget_query);
if ($budget_result === false) {
    die("Budget query failed: " . mysqli_error($conn));
}
$budget_items = [];
while ($row = mysqli_fetch_assoc($budget_result)) {
    $budget_items[] = $row;
}

// Fetch orders
$orders_query = "SELECT order_id, item_name, quantity, unit_price, status FROM orders WHERE event_id = '$event_id'";
$orders_result = mysqli_query($conn, $orders_query);
if ($orders_result === false) {
    die("Orders query failed: " . mysqli_error($conn));
}
$orders = [];
while ($row = mysqli_fetch_assoc($orders_result)) {
    $orders[] = $row;
}

// Handle logout
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
    <title>Event Details - EventPulse</title>
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
        .section-card:hover { box-shadow: 0 10px 20px rgba(26, 42, 68, 0.1); }
    </style>
</head>
<body class="bg-[#F5F6F5] min-h-screen">
    <!-- Navbar -->
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

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-8">
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
                <h1 class="text-2xl font-bold text-primary"><?php echo htmlspecialchars($event['title']); ?></h1>
                <a href="dashboard.php" class="mt-2 sm:mt-0 px-4 py-2 text-sm font-medium text-white bg-secondary rounded-button hover:bg-opacity-90">Back to Dashboard</a>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <p class="text-green-500 mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="text-red-500 mb-4"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600"><i class="ri-calendar-line text-secondary mr-2"></i><strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['event_date'])); ?></p>
                    <p class="text-gray-600"><i class="ri-map-pin-line text-secondary mr-2"></i><strong>Location:</strong> <?php echo htmlspecialchars($event['location'] ?: 'Not specified'); ?></p>
                </div>
                <div>
                    <p class="text-gray-600"><i class="ri-file-text-line text-secondary mr-2"></i><strong>Description:</strong> <?php echo htmlspecialchars($event['description'] ?: 'No description'); ?></p>
                </div>
            </div>
        </div>

        <!-- Tasks Section -->
        <div class="bg-white p-6 rounded-lg shadow mb-6 section-card transition-all duration-300">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-primary">Tasks</h2>
                <a href="tasks.php" class="text-secondary hover:underline">Manage Tasks</a>
            </div>
            <div class="space-y-4">
                <?php if (empty($tasks)): ?>
                    <p class="text-gray-500">No tasks for this event.</p>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <input type="checkbox" class="h-4 w-4 text-primary rounded border-gray-300" <?php echo $task['completed'] ? 'checked' : ''; ?> disabled>
                                <span class="ml-3 text-sm <?php echo $task['completed'] ? 'text-gray-500 line-through' : 'text-gray-900'; ?>">
                                    <?php echo htmlspecialchars($task['task_name']); ?>
                                    <?php echo $task['due_date'] ? " (Due: " . date('F j, Y', strtotime($task['due_date'])) . ")" : ''; ?>
                                </span>
                            </div>
                            <a href="event_details.php?id=<?php echo $event_id; ?>&delete_task=<?php echo $task['task_id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure?')">Delete</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Budget Section -->
        <div class="bg-white p-6 rounded-lg shadow mb-6 section-card transition-all duration-300">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-primary">Budget Items</h2>
                <a href="budget.php" class="text-secondary hover:underline">Manage Budget</a>
            </div>
            <div class="space-y-4">
                <?php if (empty($budget_items)): ?>
                    <p class="text-gray-500">No budget items for this event.</p>
                <?php else: ?>
                    <?php foreach ($budget_items as $item): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                <p class="text-sm text-gray-500">$<?php echo number_format($item['amount'], 2); ?></p>
                            </div>
                            <a href="event_details.php?id=<?php echo $event_id; ?>&delete_budget=<?php echo $item['budget_id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure?')">Delete</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Orders Section -->
        <div class="bg-white p-6 rounded-lg shadow section-card transition-all duration-300">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-primary">Orders</h2>
                <a href="orders.php" class="text-secondary hover:underline">Manage Orders</a>
            </div>
            <div class="space-y-4">
                <?php if (empty($orders)): ?>
                    <p class="text-gray-500">No orders for this event.</p>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($order['item_name']); ?></h3>
                                <p class="text-sm text-gray-500"><?php echo $order['quantity']; ?> x $<?php echo number_format($order['unit_price'], 2); ?> = $<?php echo number_format($order['quantity'] * $order['unit_price'], 2); ?></p>
                                <p class="text-sm text-<?php echo $order['status'] == 'pending' ? 'yellow' : ($order['status'] == 'completed' ? 'green' : 'red'); ?>-500"><?php echo ucfirst($order['status']); ?></p>
                            </div>
                            <a href="event_details.php?id=<?php echo $event_id; ?>&delete_order=<?php echo $order['order_id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure?')">Delete</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Mobile Menu Toggle Script -->
    <script>
        function toggleMenu() {
            const menu = document.getElementById('nav-menu');
            menu.classList.toggle('hidden');
        }
    </script>
</body>
</html>