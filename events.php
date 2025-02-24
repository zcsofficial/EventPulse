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

if (isset($_GET['delete'])) {
    $event_id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM events WHERE event_id = '$event_id' AND user_id = '$user_id'");
    $_SESSION['success'] = "Event deleted successfully!";
    header("Location: events.php");
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'event_date_asc';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : 'all';

$query = "SELECT event_id, title, event_date, location, description FROM events WHERE user_id = '$user_id'";
if (!empty($search)) {
    $query .= " AND (title LIKE '%$search%' OR location LIKE '%$search%' OR description LIKE '%$search%')";
}
if ($filter_status !== 'all') {
    $status_map = ['planning' => 'NOW() < event_date', 'in_progress' => 'NOW() = event_date', 'completed' => 'NOW() > event_date'];
    if (isset($status_map[$filter_status])) {
        $query .= " AND " . $status_map[$filter_status];
    }
}
switch ($sort) {
    case 'event_date_desc': $query .= " ORDER BY event_date DESC"; break;
    case 'title_asc': $query .= " ORDER BY title ASC"; break;
    case 'title_desc': $query .= " ORDER BY title DESC"; break;
    default: $query .= " ORDER BY event_date ASC"; break;
}
$result = mysqli_query($conn, $query);
if ($result === false) {
    die("Events query failed: " . mysqli_error($conn));
}
$events = [];
while ($row = mysqli_fetch_assoc($result)) {
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
    <title>Events - EventPulse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: '#1A2A44', secondary: '#FF6F61' }, borderRadius: { 'button': '8px' } } } }
    </script>
    <style>
        .event-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(26, 42, 68, 0.1); }
    </style>
</head>
<body class="bg-[#F5F6F5] min-h-screen">
    <nav class="bg-primary text-white fixed top-0 w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between h-auto py-4 sm:h-16">
                <div class="flex items-center w-full sm:w-auto">
                    <span class="text-2xl font-['Pacifico']">EventPulse</span>
                    <button class="sm:hidden ml-auto text-white" onclick="toggleMenu()"><i class="ri-menu-line text-xl"></i></button>
                </div>
                <div class="sm:flex flex-col sm:flex-row items-center sm:space-x-4 w-full sm:w-auto mt-2 sm:mt-0 hidden" id="nav-menu">
                    <div class="flex flex-col sm:flex-row items-baseline space-y-2 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
                        <a href="dashboard.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Dashboard</a>
                        <a href="events.php" class="px-3 py-2 rounded-md text-sm font-medium bg-secondary">Events</a>
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
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <h1 class="text-2xl font-bold text-primary">Your Events</h1>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 mt-2 sm:mt-0">
                <form method="GET" action="" class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search events..." class="px-4 py-2 rounded-full border focus:outline-none focus:ring-2 focus:ring-secondary">
                    <select name="filter_status" onchange="this.form.submit()" class="px-4 py-2 border rounded-button">
                        <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="planning" <?php echo $filter_status == 'planning' ? 'selected' : ''; ?>>Planning</option>
                        <option value="in_progress" <?php echo $filter_status == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                    <select name="sort" onchange="this.form.submit()" class="px-4 py-2 border rounded-button">
                        <option value="event_date_asc" <?php echo $sort == 'event_date_asc' ? 'selected' : ''; ?>>Date (Asc)</option>
                        <option value="event_date_desc" <?php echo $sort == 'event_date_desc' ? 'selected' : ''; ?>>Date (Desc)</option>
                        <option value="title_asc" <?php echo $sort == 'title_asc' ? 'selected' : ''; ?>>Title (A-Z)</option>
                        <option value="title_desc" <?php echo $sort == 'title_desc' ? 'selected' : ''; ?>>Title (Z-A)</option>
                    </select>
                </form>
                <a href="create_event.php" class="px-4 py-2 text-sm font-medium text-white bg-secondary rounded-button">Create Event</a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <p class="text-green-500 mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($events)): ?>
                <p class="text-gray-500 col-span-full text-center py-4">No events found.</p>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="event-card bg-white p-6 rounded-lg shadow transition-all duration-300">
                        <h2 class="text-xl font-bold text-primary mb-2"><?php echo htmlspecialchars($event['title']); ?></h2>
                        <p class="text-gray-600 mb-1"><i class="ri-calendar-line text-secondary mr-2"></i><?php echo date('F j, Y', strtotime($event['event_date'])); ?></p>
                        <p class="text-gray-600 mb-4"><i class="ri-map-pin-line text-secondary mr-2"></i><?php echo htmlspecialchars($event['location'] ?: 'No location'); ?></p>
                        <p class="text-gray-500 text-sm mb-4"><?php echo htmlspecialchars(substr($event['description'] ?: 'No description', 0, 100)) . (strlen($event['description'] ?: '') > 100 ? '...' : ''); ?></p>
                        <div class="flex space-x-4">
                            <a href="event_details.php?id=<?php echo $event['event_id']; ?>" class="text-secondary hover:underline">Details</a>
                            <a href="events.php?delete=<?php echo $event['event_id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function toggleMenu() {
            document.getElementById('nav-menu').classList.toggle('hidden');
        }
    </script>
</body>
</html>