<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in.";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = mysqli_real_escape_string($conn, $_POST['event_id']);
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $budget_id = isset($_POST['budget_id']) ? mysqli_real_escape_string($conn, $_POST['budget_id']) : null;

    if (empty($event_id) || empty($item_name) || empty($amount)) {
        $error = "All fields are required.";
    } elseif (!is_numeric($amount) || $amount < 0) {
        $error = "Amount must be a positive number.";
    } else {
        if ($budget_id) {
            $query = "UPDATE budget SET event_id = '$event_id', item_name = '$item_name', amount = '$amount' WHERE budget_id = '$budget_id' AND event_id IN (SELECT event_id FROM events WHERE user_id = '$user_id')";
            $msg = "Budget item updated successfully!";
        } else {
            $query = "INSERT INTO budget (event_id, item_name, amount) VALUES ('$event_id', '$item_name', '$amount')";
            $msg = "Budget item added successfully!";
        }
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = $msg;
            header("Location: budget.php");
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['delete'])) {
    $budget_id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM budget WHERE budget_id = '$budget_id' AND event_id IN (SELECT event_id FROM events WHERE user_id = '$user_id')");
    $_SESSION['success'] = "Budget item deleted successfully!";
    header("Location: budget.php");
    exit();
}

$budget_query = "SELECT b.budget_id, b.event_id, b.item_name, b.amount, e.title FROM budget b JOIN events e ON b.event_id = e.event_id WHERE e.user_id = '$user_id'";
$budget_result = mysqli_query($conn, $budget_query);
if ($budget_result === false) {
    die("Budget query failed: " . mysqli_error($conn));
}
$budget_items = [];
while ($row = mysqli_fetch_assoc($budget_result)) {
    $budget_items[] = $row;
}

$events_query = "SELECT event_id, title FROM events WHERE user_id = '$user_id'";
$events_result = mysqli_query($conn, $events_query);
if ($events_result === false) {
    die("Events query failed: " . mysqli_error($conn));
}
$events = [];
while ($row = mysqli_fetch_assoc($events_result)) {
    $events[] = $row;
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
    <title>Budget - EventPulse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: '#1A2A44', secondary: '#FF6F61' }, borderRadius: { 'button': '8px' } } } }
    </script>
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
                        <a href="budget.php" class="px-3 py-2 rounded-md text-sm font-medium bg-secondary">Budget</a>
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
        <h1 class="text-2xl font-bold text-primary mb-6">Budget Management</h1>

        <?php if (isset($error)): ?>
            <p class="text-red-500 mb-4"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="text-green-500 mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-lg font-semibold text-primary mb-4"><?php echo isset($_GET['edit']) ? 'Edit Budget Item' : 'Add Budget Item'; ?></h2>
            <?php if (isset($_GET['edit'])): 
                $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
                $edit_query = "SELECT * FROM budget WHERE budget_id = '$edit_id' AND event_id IN (SELECT event_id FROM events WHERE user_id = '$user_id')";
                $edit_result = mysqli_fetch_assoc(mysqli_query($conn, $edit_query));
            ?>
                <form method="POST" action="">
                    <input type="hidden" name="budget_id" value="<?php echo $edit_id; ?>">
                    <div class="mb-4">
                        <label for="event_id" class="block text-primary font-medium mb-2">Event</label>
                        <select name="event_id" id="event_id" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-secondary" required>
                            <?php foreach ($events as $event): ?>
                                <option value="<?php echo $event['event_id']; ?>" <?php echo $edit_result['event_id'] == $event['event_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($event['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="item_name" class="block text-primary font-medium mb-2">Item Name</label>
                        <input type="text" name="item_name" id="item_name" value="<?php echo htmlspecialchars($edit_result['item_name']); ?>" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-secondary" required>
                    </div>
                    <div class="mb-4">
                        <label for="amount" class="block text-primary font-medium mb-2">Amount (₹)</label>
                        <input type="number" name="amount" id="amount" value="<?php echo $edit_result['amount']; ?>" step="0.01" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-secondary" required>
                    </div>
                    <div class="flex space-x-4">
                        <button type="submit" class="w-full bg-secondary text-white py-3 rounded hover:bg-opacity-90 font-medium">Update Item</button>
                        <a href="budget.php" class="w-full text-center bg-gray-200 text-gray-700 py-3 rounded hover:bg-gray-300 font-medium">Cancel</a>
                    </div>
                </form>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="event_id" class="block text-primary font-medium mb-2">Event</label>
                        <select name="event_id" id="event_id" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-secondary" required>
                            <option value="">Select an event</option>
                            <?php foreach ($events as $event): ?>
                                <option value="<?php echo $event['event_id']; ?>"><?php echo htmlspecialchars($event['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="item_name" class="block text-primary font-medium mb-2">Item Name</label>
                        <input type="text" name="item_name" id="item_name" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-secondary" required>
                    </div>
                    <div class="mb-4">
                        <label for="amount" class="block text-primary font-medium mb-2">Amount (₹)</label>
                        <input type="number" name="amount" id="amount" step="0.01" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-secondary" required>
                    </div>
                    <button type="submit" class="w-full bg-secondary text-white py-3 rounded hover:bg-opacity-90 font-medium">Add Item</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold text-primary mb-4">Budget Items</h2>
            <div class="space-y-4">
                <?php if (empty($budget_items)): ?>
                    <p class="text-gray-500">No budget items found.</p>
                <?php else: ?>
                    <?php foreach ($budget_items as $item): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($item['title']); ?> • ₹<?php echo number_format($item['amount'], 2, '.', ','); ?></p>
                            </div>
                            <div class="flex space-x-4">
                                <a href="budget.php?edit=<?php echo $item['budget_id']; ?>" class="text-secondary hover:underline">Edit</a>
                                <a href="budget.php?delete=<?php echo $item['budget_id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure?')">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        function toggleMenu() {
            document.getElementById('nav-menu').classList.toggle('hidden');
        }
    </script>
</body>
</html>