<?php
namespace App;

class Task
{
    private $storageFile;

    public function __construct(string $storageFile)
    {
        $this->storageFile = $storageFile;
        $dir = dirname($storageFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        if (!file_exists($this->storageFile)) {
            file_put_contents($this->storageFile, json_encode([]));
        }
    }

    public function all(): array
    {
        $json = @file_get_contents($this->storageFile);
        $arr = json_decode($json, true);
        return is_array($arr) ? $arr : [];
    }

    public function add(string $title, ?string $dueAt = null): array
    {
        $tasks = $this->all();
        $task = [
            'id' => time() . rand(100,999),
            'title' => $title,
            // due_at is expected as a local datetime string in 'Y-m-d H:i:s' (no TZ)
            'due_at' => $dueAt,
            // store created_at as plain local datetime without timezone offset for consistency
            'created_at' => date('Y-m-d H:i:s')
        ];
        $tasks[] = $task;
        file_put_contents($this->storageFile, json_encode($tasks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $task;
    }

    public function delete($id): bool
    {
        $tasks = $this->all();
        $before = count($tasks);
        $tasks = array_values(array_filter($tasks, function($t) use ($id) { return (string)$t['id'] !== (string)$id; }));
        file_put_contents($this->storageFile, json_encode($tasks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return count($tasks) < $before;
    }
}
