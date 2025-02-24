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

// Handle add/edit task
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = mysqli_real_escape_string($conn, $_POST['event_id']);
    $task_name = mysqli_real_escape_string($conn, $_POST['task_name']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $completed = isset($_POST['completed']) ? 1 : 0;
    $task_id = isset($_POST['task_id']) ? mysqli_real_escape_string($conn, $_POST['task_id']) : null;

    if (empty($event_id) || empty($task_name)) {
        $error = "Event and task name are required.";
    } elseif (!empty($due_date) && (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $due_date) || !strtotime($due_date))) {
        $error = "Invalid due date format. Use YYYY-MM-DD.";
    } else {
        if ($task_id) {
            $query = "UPDATE tasks SET event_id = '$event_id', task_name = '$task_name', due_date = " . ($due_date ? "'$due_date'" : "NULL") . ", completed = '$completed' WHERE task_id = '$task_id' AND event_id IN (SELECT event_id FROM events WHERE user_id = '$user_id')";
            $msg = "Task updated successfully!";
        } else {
            $query = "INSERT INTO tasks (event_id, task_name, due_date, completed) VALUES ('$event_id', '$task_name', " . ($due_date ? "'$due_date'" : "NULL") . ", '$completed')";
            $msg = "Task added successfully!";
        }
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = $msg;
            header("Location: tasks.php");
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $task_id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM tasks WHERE task_id = '$task_id' AND event_id IN (SELECT event_id FROM events WHERE user_id = '$user_id')");
    $_SESSION['success'] = "Task deleted successfully!";
    header("Location: tasks.php");
    exit();
}

// Handle toggle completion
if (isset($_GET['toggle'])) {
    $task_id = mysqli_real_escape_string($conn, $_GET['toggle']);
    $current = mysqli_fetch_assoc(mysqli_query($conn, "SELECT completed FROM tasks WHERE task_id = '$task_id' AND event_id IN (SELECT event_id FROM events WHERE user_id = '$user_id')"));
    $new_status = $current['completed'] ? 0 : 1;
    mysqli_query($conn, "UPDATE tasks SET completed = '$new_status' WHERE task_id = '$task_id'");
    $_SESSION['success'] = "Task status updated!";
    header("Location: tasks.php");
    exit();
}

// Fetch tasks and events
$tasks_query = "SELECT t.task_id, t.event_id, t.task_name, t.due_date, t.completed, e.title FROM tasks t JOIN events e ON t.event_id = e.event_id WHERE e.user_id = '$user_id' ORDER BY t.due_date ASC";
$tasks_result = mysqli_query($conn, $tasks_query);
$tasks = [];
while ($row = mysqli_fetch_assoc($tasks_result)) {
    $tasks[] = $row;
}

$events_query = "SELECT event_id, title FROM events WHERE user_id = '$user_id'";
$events_result = mysqli_query($conn, $events_query);
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
    <title>Tasks - EventPulse</title>
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
                    <button class="sm:hidden ml-auto text-white" onclick="toggleMenu()"><i class="ri-menu-line text-xl"></i></button>
                </div>
                <div class="sm:flex flex-col sm:flex-row items-center sm:space-x-4 w-full sm:w-auto mt-2 sm:mt-0 hidden" id="nav-menu">
                    <div class="flex flex-col sm:flex-row items-baseline space-y-2 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
                        <a href="dashboard.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Dashboard</a>
                        <a href="events.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Events</a>
                        <a href="orders.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Orders</a>
                        <a href="budget.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Budget</a>
                        <a href="tasks.php" class="px-3 py-2 rounded-md text-sm font-medium bg-secondary">Tasks</a>
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
        <h1 class="text-2xl font-bold text-primary mb-6">Task Management</h1>

        <?php if (isset($error)): ?>
            <p class="text-red-500 mb-4"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="text-green-500 mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <!-- Add/Edit Form -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-lg font-semibold text-primary mb-4"><?php echo isset($_GET['edit']) ? 'Edit Task' : 'Add Task'; ?></h2>
            <?php if (isset($_GET['edit'])): 
                $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
                $edit_query = "SELECT * FROM tasks WHERE task_id = '$edit_id' AND event_id IN (SELECT event_id FROM events WHERE user_id = '$user_id')";
                $edit_result = mysqli_fetch_assoc(mysqli_query($conn, $edit_query));
            ?>
                <form method="POST" action="">
                    <input type="hidden" name="task_id" value="<?php echo $edit_id; ?>">
                    <div class="mb-4">
                        <label for="event_id" class="block text-primary font-medium mb-2">Event</label>
                        <select name="event_id" id="event_id" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-secondary" required>
                            <?php foreach ($events as $event): ?>
                                <option value="<?php echo $event['event_id']; ?>" <?php echo $edit_result['event_id'] == $event['event_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($event['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="task_name" class="block text-primary font-medium mb-2">Task Name</label>
                        <input type="text" name="task_name" id="task_name" value="<?php echo htmlspecialchars($edit_result['task_name']); ?>" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-secondary" required>
                    </div>
                    <div class="mb-4">
                        <label for="due_date" class="block text-primary font-medium mb-2">Due Date</label>
                        <input type="date" name="due_date" id="due_date" value="<?php echo $edit_result['due_date']; ?>" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div class="mb-4">
                        <label for="completed" class="block text-primary font-medium mb-2">Completed</label>
                        <input type="checkbox" name="completed" id="completed" value="1" <?php echo $edit_result['completed'] ? 'checked' : ''; ?> class="h-4 w-4 text-primary rounded border-gray-300">
                    </div>
                    <div class="flex space-x-4">
                        <button type="submit" class="w-full bg-secondary text-white py-3 rounded hover:bg-opacity-90 font-medium">Update Task</button>
                        <a href="tasks.php" class="w-full text-center bg-gray-200 text-gray-700 py-3 rounded hover:bg-gray-300 font-medium">Cancel</a>
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
                        <label for="task_name" class="block text-primary font-medium mb-2">Task Name</label>
                        <input type="text" name="task_name" id="task_name" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-secondary" required>
                    </div>
                    <div class="mb-4">
                        <label for="due_date" class="block text-primary font-medium mb-2">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div class="mb-4">
                        <label for="completed" class="block text-primary font-medium mb-2">Completed</label>
                        <input type="checkbox" name="completed" id="completed" value="1" class="h-4 w-4 text-primary rounded border-gray-300">
                    </div>
                    <button type="submit" class="w-full bg-secondary text-white py-3 rounded hover:bg-opacity-90 font-medium">Add Task</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Tasks List -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold text-primary mb-4">Tasks</h2>
            <div class="space-y-4">
                <?php if (empty($tasks)): ?>
                    <p class="text-gray-500">No tasks found.</p>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <a href="tasks.php?toggle=<?php echo $task['task_id']; ?>" class="flex items-center">
                                    <input type="checkbox" class="h-4 w-4 text-primary rounded border-gray-300" <?php echo $task['completed'] ? 'checked' : ''; ?> disabled>
                                    <span class="ml-3 text-sm <?php echo $task['completed'] ? 'text-gray-500 line-through' : 'text-gray-900'; ?>"><?php echo htmlspecialchars($task['task_name']); ?></span>
                                </a>
                            </div>
                            <div class="flex items-center space-x-4">
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($task['title']); ?><?php echo $task['due_date'] ? ' • Due: ' . date('F j, Y', strtotime($task['due_date'])) : ''; ?></p>
                                <a href="tasks.php?edit=<?php echo $task['task_id']; ?>" class="text-secondary hover:underline">Edit</a>
                                <a href="tasks.php?delete=<?php echo $task['task_id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure?')">Delete</a>
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