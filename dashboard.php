<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to access the dashboard.";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'event_date_asc';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : 'all';

$events_query = "SELECT event_id, title, event_date, location, description FROM events WHERE user_id = '$user_id'";
if (!empty($search)) {
    $events_query .= " AND (title LIKE '%$search%' OR location LIKE '%$search%' OR description LIKE '%$search%')";
}
if ($filter_status !== 'all') {
    $status_map = ['planning' => 'NOW() < event_date', 'in_progress' => 'NOW() = event_date', 'completed' => 'NOW() > event_date'];
    if (isset($status_map[$filter_status])) {
        $events_query .= " AND " . $status_map[$filter_status];
    }
}
switch ($sort) {
    case 'event_date_desc': $events_query .= " ORDER BY event_date DESC"; break;
    case 'title_asc': $events_query .= " ORDER BY title ASC"; break;
    case 'title_desc': $events_query .= " ORDER BY title DESC"; break;
    default: $events_query .= " ORDER BY event_date ASC"; break;
}
$events_result = mysqli_query($conn, $events_query);
$events = [];
if ($events_result === false) {
    die("Event query failed: " . mysqli_error($conn) . " Query: $events_query");
}
while ($row = mysqli_fetch_assoc($events_result)) {
    $events[] = $row;
}

$active_events_query = "SELECT COUNT(*) as count FROM events WHERE user_id = '$user_id' AND event_date >= CURDATE()";
$active_events_result = mysqli_query($conn, $active_events_query);
if ($active_events_result === false) {
    die("Active events query failed: " . mysqli_error($conn));
}
$active_events = mysqli_fetch_assoc($active_events_result)['count'] ?? 0;

$pending_orders_query = "SELECT COUNT(*) as count FROM orders WHERE event_id IN (SELECT event_id FROM events WHERE user_id = '$user_id') AND status = 'pending'";
$pending_orders_result = mysqli_query($conn, $pending_orders_query);
if ($pending_orders_result === false) {
    die("Pending orders query failed: " . mysqli_error($conn));
}
$pending_orders = mysqli_fetch_assoc($pending_orders_result)['count'] ?? 0;

$total_budget_query = "SELECT SUM(amount) as total FROM budget WHERE event_id IN (SELECT event_id FROM events WHERE user_id = '$user_id')";
$total_budget_result = mysqli_query($conn, $total_budget_query);
if ($total_budget_result === false) {
    die("Total budget query failed: " . mysqli_error($conn));
}
$total_budget = mysqli_fetch_assoc($total_budget_result)['total'] ?? 0;

$spent_budget_query = "SELECT SUM(amount) as spent FROM budget WHERE event_id IN (SELECT event_id FROM events WHERE user_id = '$user_id') AND created_at <= NOW()";
$spent_budget_result = mysqli_query($conn, $spent_budget_query);
if ($spent_budget_result === false) {
    die("Spent budget query failed: " . mysqli_error($conn));
}
$spent_budget = mysqli_fetch_assoc($spent_budget_result)['spent'] ?? 0;
$remaining_budget = $total_budget - $spent_budget;

$total_rsvps = $active_events * 50;

$tasks_query = "SELECT t.task_id, t.task_name, t.completed, e.title as event_title 
                FROM tasks t 
                JOIN events e ON t.event_id = e.event_id 
                WHERE e.user_id = '$user_id' 
                ORDER BY t.created_at DESC 
                LIMIT 5";
$tasks_result = mysqli_query($conn, $tasks_query);
$tasks = [];
if ($tasks_result === false) {
    die("Tasks query failed: " . mysqli_error($conn) . " Query: $tasks_query");
}
while ($row = mysqli_fetch_assoc($tasks_result)) {
    $tasks[] = $row;
}

$event_chart_data = ['dates' => [], 'events' => [], 'rsvps' => []];
for ($i = 5; $i >= 0; $i--) {
    $month = date('M', strtotime("-$i months"));
    $start = date('Y-m-01', strtotime("-$i months"));
    $end = date('Y-m-t', strtotime("-$i months"));
    $count_query = "SELECT COUNT(*) as count FROM events WHERE user_id = '$user_id' AND event_date BETWEEN '$start' AND '$end'";
    $count_result = mysqli_query($conn, $count_query);
    if ($count_result === false) {
        die("Chart count query failed: " . mysqli_error($conn));
    }
    $count = mysqli_fetch_assoc($count_result)['count'] ?? 0;
    $event_chart_data['dates'][] = $month;
    $event_chart_data['events'][] = (int)$count;
    $event_chart_data['rsvps'][] = $count * 50;
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
    <title>EventPulse Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
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
        .chart { min-height: 300px; }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
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
                        <a href="dashboard.php" class="px-3 py-2 rounded-md text-sm font-medium bg-secondary">Dashboard</a>
                        <a href="events.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Events</a>
                        <a href="orders.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Orders</a>
                        <a href="budget.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Budget</a>
                        <a href="tasks.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Tasks</a>
                    </div>
                    <div class="flex items-center mt-2 sm:mt-0 w-full sm:w-auto">
                        <div class="relative w-full sm:w-64">
                            <form method="GET" action="">
                                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search events..." class="w-full px-4 py-2 rounded-full text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-secondary">
                                <button type="submit" class="absolute right-3 top-2.5 text-gray-400"><i class="ri-search-line"></i></button>
                            </form>
                        </div>
                        <div class="ml-0 sm:ml-4 mt-2 sm:mt-0 flex items-center">
                            <img class="h-8 w-8 rounded-full" src="https://public.readdy.ai/ai/img_res/d474a1d561bb3465cb8f068d4a5d01cf.jpg" alt="Profile">
                            <span class="ml-2"><?php echo htmlspecialchars($full_name); ?></span>
                            <a href="?logout=true" class="ml-2 text-white hover:text-[#F8DDA4]"><i class="ri-logout-box-line"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                        <i class="ri-calendar-event-line text-xl text-primary"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Active Events</h3>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $active_events; ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-secondary/10 rounded-full flex items-center justify-center">
                        <i class="ri-shopping-bag-line text-xl text-secondary"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Pending Orders</h3>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $pending_orders; ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-[#F8DDA4]/20 rounded-full flex items-center justify-center">
                        <i class="ri-money-dollar-circle-line text-xl text-[#F8DDA4]"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Total Budget</h3>
                        <p class="text-2xl font-semibold text-gray-900">₹<?php echo number_format($total_budget, 2, '.', ','); ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                        <i class="ri-team-line text-xl text-primary"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Total RSVPs</h3>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($total_rsvps, 0, '.', ','); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8">
                <div class="bg-white p-6 rounded-lg shadow mb-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900">Event Overview</h2>
                        <div class="flex space-x-2 mt-2 sm:mt-0">
                            <button class="px-4 py-2 text-sm font-medium text-primary border border-primary rounded-button hover:bg-primary/5">Weekly</button>
                            <button class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-button">Monthly</button>
                        </div>
                    </div>
                    <div id="eventChart" class="chart"></div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900">Upcoming Events</h2>
                        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 mt-2 sm:mt-0">
                            <select name="filter_status" onchange="this.form.submit()" form="eventFilter" class="px-4 py-2 text-sm border rounded-button">
                                <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>All</option>
                                <option value="planning" <?php echo $filter_status == 'planning' ? 'selected' : ''; ?>>Planning</option>
                                <option value="in_progress" <?php echo $filter_status == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                            <select name="sort" onchange="this.form.submit()" form="eventFilter" class="px-4 py-2 text-sm border rounded-button">
                                <option value="event_date_asc" <?php echo $sort == 'event_date_asc' ? 'selected' : ''; ?>>Date (Asc)</option>
                                <option value="event_date_desc" <?php echo $sort == 'event_date_desc' ? 'selected' : ''; ?>>Date (Desc)</option>
                                <option value="title_asc" <?php echo $sort == 'title_asc' ? 'selected' : ''; ?>>Title (A-Z)</option>
                                <option value="title_desc" <?php echo $sort == 'title_desc' ? 'selected' : ''; ?>>Title (Z-A)</option>
                            </select>
                            <a href="create_event.php" class="px-4 py-2 text-sm font-medium text-white bg-secondary rounded-button hover:bg-opacity-90">Create Event</a>
                        </div>
                    </div>
                    <form id="eventFilter" method="GET" action="" class="hidden">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                    <div class="space-y-4">
                        <?php if (empty($events)): ?>
                            <p class="text-gray-500 text-center py-4">No events found. Create one to get started!</p>
                        <?php else: ?>
                            <?php foreach ($events as $event): ?>
                                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <img class="h-12 w-12 rounded-lg object-cover" src="https://public.readdy.ai/ai/img_res/69a6388c76f84979f7383b6cfdf59b00.jpg" alt="Event Image">
                                        <div class="ml-4">
                                            <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($event['title']); ?></h3>
                                            <p class="text-sm text-gray-500"><?php echo date('F j, Y', strtotime($event['event_date'])) . ' • ' . htmlspecialchars($event['location'] ?: 'No location'); ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center mt-2 sm:mt-0">
                                        <?php
                                        $now = new DateTime();
                                        $event_date = new DateTime($event['event_date']);
                                        $status = $now < $event_date ? 'Planning' : ($now->format('Y-m-d') == $event_date->format('Y-m-d') ? 'In Progress' : 'Completed');
                                        $status_color = $status == 'Planning' ? 'primary' : ($status == 'In Progress' ? 'secondary' : 'gray-500');
                                        ?>
                                        <span class="px-3 py-1 text-xs font-medium bg-<?php echo $status_color; ?>/10 text-<?php echo $status_color; ?> rounded-full"><?php echo $status; ?></span>
                                        <a href="event_details.php?id=<?php echo $event['event_id']; ?>" class="ml-4 text-gray-400 hover:text-gray-500"><i class="ri-more-2-fill"></i></a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Budget Overview</h2>
                    <div id="budgetChart" class="chart"></div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-500">Total Budget</span>
                            <span class="font-medium">₹<?php echo number_format($total_budget, 2, '.', ','); ?></span>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-500">Spent</span>
                            <span class="font-medium">₹<?php echo number_format($spent_budget, 2, '.', ','); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Remaining</span>
                            <span class="font-medium text-secondary">₹<?php echo number_format($remaining_budget, 2, '.', ','); ?></span>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Recent Tasks</h2>
                    <div class="space-y-4">
                        <?php if (empty($tasks)): ?>
                            <p class="text-gray-500">No recent tasks.</p>
                        <?php else: ?>
                            <?php foreach ($tasks as $task): ?>
                                <div class="flex items-center">
                                    <input type="checkbox" class="h-4 w-4 text-primary rounded border-gray-300" <?php echo $task['completed'] ? 'checked' : ''; ?> disabled>
                                    <span class="ml-3 text-sm <?php echo $task['completed'] ? 'text-gray-500 line-through' : 'text-gray-900'; ?>">
                                        <?php echo htmlspecialchars($task['task_name']) . " (" . htmlspecialchars($task['event_title']) . ")"; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <a href="tasks.php" class="mt-4 block w-full px-4 py-2 text-sm font-medium text-primary border border-primary rounded-button hover:bg-primary/5 text-center">View All Tasks</a>
                </div>
            </div>
        </div>
    </main>

    <script>
        const eventData = {
            dates: <?php echo json_encode($event_chart_data['dates']); ?>,
            events: <?php echo json_encode($event_chart_data['events']); ?>,
            rsvps: <?php echo json_encode($event_chart_data['rsvps']); ?>
        };

        const eventChart = echarts.init(document.getElementById('eventChart'));
        eventChart.setOption({
            animation: false,
            tooltip: { trigger: 'axis', backgroundColor: 'rgba(255, 255, 255, 0.9)', textStyle: { color: '#1f2937' } },
            grid: { top: 10, right: 10, bottom: 20, left: 40 },
            xAxis: { type: 'category', data: eventData.dates, axisLine: { lineStyle: { color: '#e5e7eb' } } },
            yAxis: [
                { type: 'value', name: 'Events', axisLine: { show: false }, axisTick: { show: false }, splitLine: { lineStyle: { color: '#e5e7eb' } } },
                { type: 'value', name: 'RSVPs', axisLine: { show: false }, axisTick: { show: false }, splitLine: { show: false } }
            ],
            series: [
                { name: 'Events', type: 'line', smooth: true, data: eventData.events, symbolSize: 0, lineStyle: { width: 3 }, itemStyle: { color: '#1A2A44' }, areaStyle: { color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{ offset: 0, color: 'rgba(26, 42, 68, 0.2)' }, { offset: 1, color: 'rgba(26, 42, 68, 0)' }]) } },
                { name: 'RSVPs', type: 'line', smooth: true, yAxisIndex: 1, data: eventData.rsvps, symbolSize: 0, lineStyle: { width: 3 }, itemStyle: { color: '#FF6F61' }, areaStyle: { color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{ offset: 0, color: 'rgba(255, 111, 97, 0.2)' }, { offset: 1, color: 'rgba(255, 111, 97, 0)' }]) } }
            ]
        });

        const budgetChart = echarts.init(document.getElementById('budgetChart'));
        budgetChart.setOption({
            animation: false,
            series: [{
                type: 'pie',
                radius: ['60%', '80%'],
                avoidLabelOverlap: false,
                label: { show: false },
                labelLine: { show: false },
                data: [
                    { value: <?php echo $spent_budget; ?>, name: 'Spent', itemStyle: { color: '#1A2A44' } },
                    { value: <?php echo $remaining_budget; ?>, name: 'Remaining', itemStyle: { color: '#FF6F61' } }
                ]
            }]
        });

        window.addEventListener('resize', function() {
            eventChart.resize();
            budgetChart.resize();
        });

        function toggleMenu() {
            const menu = document.getElementById('nav-menu');
            menu.classList.toggle('hidden');
        }
    </script>
</body>
</html>