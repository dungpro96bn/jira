<?php

namespace App\Controllers;

use App\Services\JiraService;

class SummaryController
{
    private $cacheDir;
    private $ttl = 300; // 5 phút

    public function __construct()
    {
        $this->cacheDir = __DIR__ . '/../../storage/cache/';

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        require __DIR__ . '/../../public/views/summary/index.php';
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER CACHE
    |--------------------------------------------------------------------------
    */
    private function getCache($key)
    {
        $file = $this->cacheDir . $key . '.json';

        if (file_exists($file) && (time() - filemtime($file)) < $this->ttl) {
            return json_decode(file_get_contents($file), true);
        }

        return null;
    }

    private function setCache($key, $data)
    {
        $file = $this->cacheDir . $key . '.json';
        file_put_contents($file, json_encode($data));
    }

    private function clearCacheFile($key)
    {
        $file = $this->cacheDir . $key . '.json';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS SUMMARY
    |--------------------------------------------------------------------------
    */
    public function getSummary()
    {
        header('Content-Type: application/json');

        $cache = $this->getCache('summary');

        if ($cache) {
            echo json_encode($cache);
            exit;
        }

        $jira = new JiraService();
        $issues = $jira->getAllIssues();

        $summary = [];

        foreach ($issues as $issue) {

            $status = $issue['fields']['status']['name'] ?? 'Unknown';

            if (!isset($summary[$status])) {
                $summary[$status] = 0;
            }

            $summary[$status]++;
        }

        $result = [
            'total' => count($issues),
            'status' => $summary
        ];

        $this->setCache('summary', $result);

        echo json_encode($result);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | PRIORITY
    |--------------------------------------------------------------------------
    */
    public function getPriority()
    {
        header('Content-Type: application/json');

        $cache = $this->getCache('priority');

        if ($cache) {
            echo json_encode($cache);
            exit;
        }

        try {
            $jira = new JiraService();
            $issues = $jira->getAllIssues();

            $priorityStats = [];

            foreach ($issues as $issue) {

                $priority = $issue['fields']['priority']['name'] ?? 'No Priority';

                if (!isset($priorityStats[$priority])) {
                    $priorityStats[$priority] = 0;
                }

                $priorityStats[$priority]++;
            }

            $order = ['Highest', 'High', 'Medium', 'Low', 'Lowest'];

            uksort($priorityStats, function ($a, $b) use ($order) {
                return (array_search($a, $order) ?? 999)
                    <=> (array_search($b, $order) ?? 999);
            });

            $result = [
                'success' => true,
                'labels' => array_keys($priorityStats),
                'data'   => array_values($priorityStats)
            ];

            $this->setCache('priority', $result);

            echo json_encode($result);
            exit;

        } catch (\Throwable $e) {

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TEAM WORKLOAD
    |--------------------------------------------------------------------------
    */
    public function getWorkload()
    {
        header('Content-Type: application/json');

        $cache = $this->getCache('workload');

        if ($cache) {
            echo json_encode($cache);
            exit;
        }

        try {
            $jira = new JiraService();
            $issues = $jira->getAllIssues();

            $workload = [];
            $total = count($issues);

            foreach ($issues as $issue) {

                $assignee = $issue['fields']['assignee'] ?? null;

                if ($assignee) {
                    $name = $assignee['displayName'];
                    $avatar = $assignee['avatarUrls']['48x48'];
                } else {
                    $name = 'Unassigned';
                    $avatar = '/assets/images/default-avatar.jpg';
                }

                if (!isset($workload[$name])) {
                    $workload[$name] = [
                        'count' => 0,
                        'avatar' => $avatar
                    ];
                }

                $workload[$name]['count']++;
            }

            $result = [];

            foreach ($workload as $name => $data) {

                $percent = $total > 0 ? round(($data['count'] / $total) * 100) : 0;

                $result[] = [
                    'name' => $name,
                    'count' => $data['count'],
                    'percent' => $percent,
                    'avatar' => $data['avatar']
                ];
            }

            usort($result, fn($a, $b) => $b['percent'] <=> $a['percent']);

            $final = [
                'success' => true,
                'data' => $result
            ];

            $this->setCache('workload', $final);

            echo json_encode($final);
            exit;

        } catch (\Throwable $e) {

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TYPES OF WORK
    |--------------------------------------------------------------------------
    */
    public function getTypesTask()
    {
        header('Content-Type: application/json');

        $cache = $this->getCache('types');

        if ($cache) {
            echo json_encode($cache);
            exit;
        }

        try {
            $jira = new JiraService();
            $issues = $jira->getAllIssues();

            $types = [];
            $total = count($issues);

            foreach ($issues as $issue) {

                $type = $issue['fields']['issuetype']['name'] ?? 'Other';

                if (!isset($types[$type])) {
                    $types[$type] = 0;
                }

                $types[$type]++;
            }

            $result = [];

            foreach ($types as $name => $count) {

                $percent = $total > 0 ? round(($count / $total) * 100) : 0;

                $result[] = [
                    'name' => $name,
                    'count' => $count,
                    'percent' => $percent
                ];
            }

            usort($result, fn($a, $b) => $b['percent'] <=> $a['percent']);

            $final = [
                'success' => true,
                'data' => $result
            ];

            $this->setCache('types', $final);

            echo json_encode($final);
            exit;

        } catch (\Throwable $e) {

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CLEAR ALL CACHE
    |--------------------------------------------------------------------------
    */
    public function clearCache()
    {
        header('Content-Type: application/json');

        $files = glob($this->cacheDir . '*.json');

        foreach ($files as $file) {
            unlink($file);
        }

        echo json_encode([
            'success' => true,
            'message' => 'All cache cleared'
        ]);

        exit;
    }
}