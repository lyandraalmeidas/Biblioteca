<?php
require_once __DIR__ . '/../app/Task.php';
use App\Task;

$tasksFile = __DIR__ . '/../storage/tasks_test.json';
@unlink($tasksFile);
$repo = new Task($tasksFile);

$title = 'unit test ' . time();
$dueAtRaw = date('Y-m-d H:i'); // simulate datetime-local without seconds
$dueAtRaw = str_replace(' ', 'T', $dueAtRaw); // make it YYYY-MM-DDTHH:MM

// Simulate the parsing logic from tarefas.php
$dueAtClean = str_replace('T', ' ', $dueAtRaw);
if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $dueAtClean)) {
    $dueAtClean .= ':00';
}
$ts = strtotime($dueAtClean);
$dueAt = $ts !== false ? date('Y-m-d H:i:s', $ts) : null;

$task = $repo->add($title, $dueAt);

echo "Added task:\n";
print_r($task);

echo "File contents:\n";
echo file_get_contents($tasksFile);
