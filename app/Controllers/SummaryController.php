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

    public function dashboard()
    {
        require __DIR__ . '/../../public/views/dashboard/index.php';
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

    private function normalizeIssue(array $issue)
    {
        $fields = isset($issue['fields']) && is_array($issue['fields']) ? $issue['fields'] : [];
        $assignee = isset($fields['assignee']) && is_array($fields['assignee']) ? $fields['assignee'] : null;
        $priority = isset($fields['priority']) && is_array($fields['priority']) ? $fields['priority'] : null;
        $issuetype = isset($fields['issuetype']) && is_array($fields['issuetype']) ? $fields['issuetype'] : null;

        return [
            'key' => isset($issue['key']) ? $issue['key'] : '',
            'summary' => isset($fields['summary']) ? $fields['summary'] : 'Untitled issue',
            'status' => isset($fields['status']['name']) ? $fields['status']['name'] : 'Unknown',
            'assignee' => $assignee ? $assignee['displayName'] : 'Unassigned',
            'assigneeAvatar' => $assignee && !empty($assignee['avatarUrls']['48x48'])
                ? $assignee['avatarUrls']['48x48']
                : '/assets/images/default-avatar.jpg',
            'duedate' => !empty($fields['duedate']) ? $fields['duedate'] : null,
            'priority' => $priority && !empty($priority['name']) ? $priority['name'] : 'No Priority',
            'issueType' => $issuetype && !empty($issuetype['name']) ? $issuetype['name'] : 'Other',
            'created' => !empty($fields['created']) ? substr($fields['created'], 0, 10) : null,
            'labels' => !empty($fields['labels']) && is_array($fields['labels']) ? $fields['labels'] : [],
        ];
    }

    private function buildDashboardData(array $issues)
    {
        $status = [];
        $priority = [];
        $types = [];
        $workload = [];
        $normalizedIssues = [];
        $todayTs = strtotime(date('Y-m-d'));
        $overdueCount = 0;
        $doneCount = 0;
        $inProgressCount = 0;

        foreach ($issues as $issue) {
            $item = $this->normalizeIssue($issue);
            $normalizedIssues[] = $item;

            if (!isset($status[$item['status']])) {
                $status[$item['status']] = 0;
            }
            $status[$item['status']]++;

            if (!isset($priority[$item['priority']])) {
                $priority[$item['priority']] = 0;
            }
            $priority[$item['priority']]++;

            if (!isset($types[$item['issueType']])) {
                $types[$item['issueType']] = 0;
            }
            $types[$item['issueType']]++;

            if (!isset($workload[$item['assignee']])) {
                $workload[$item['assignee']] = [
                    'name' => $item['assignee'],
                    'avatar' => $item['assigneeAvatar'],
                    'count' => 0,
                ];
            }
            $workload[$item['assignee']]['count']++;

            if ($item['status'] === 'Done') {
                $doneCount++;
            }

            if ($item['status'] === 'In Progress') {
                $inProgressCount++;
            }

            if (!empty($item['duedate']) && $item['status'] !== 'Done') {
                $dueTs = strtotime($item['duedate']);
                if ($dueTs !== false && $dueTs < $todayTs) {
                    $overdueCount++;
                }
            }
        }

        usort($normalizedIssues, function ($a, $b) {
            $aDate = !empty($a['created']) ? strtotime($a['created']) : 0;
            $bDate = !empty($b['created']) ? strtotime($b['created']) : 0;
            return $bDate <=> $aDate;
        });

        $priorityOrder = ['Highest', 'High', 'Medium', 'Low', 'Lowest', 'No Priority'];
        uksort($priority, function ($a, $b) use ($priorityOrder) {
            $aIndex = array_search($a, $priorityOrder, true);
            $bIndex = array_search($b, $priorityOrder, true);
            $aIndex = $aIndex === false ? 999 : $aIndex;
            $bIndex = $bIndex === false ? 999 : $bIndex;
            return $aIndex <=> $bIndex;
        });

        $total = count($normalizedIssues);
        $workloadData = array_values($workload);
        usort($workloadData, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        foreach ($workloadData as &$member) {
            $member['percent'] = $total > 0 ? round(($member['count'] / $total) * 100) : 0;
        }
        unset($member);

        $typeData = [];
        foreach ($types as $name => $count) {
            $typeData[] = [
                'name' => $name,
                'count' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100) : 0,
            ];
        }
        usort($typeData, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return [
            'success' => true,
            'generated_at' => date('c'),
            'summary' => [
                'total' => $total,
                'overdue' => $overdueCount,
                'done' => $doneCount,
                'in_progress' => $inProgressCount,
                'completion_rate' => $total > 0 ? round(($doneCount / $total) * 100) : 0,
            ],
            'status' => $status,
            'priority' => [
                'labels' => array_keys($priority),
                'data' => array_values($priority),
            ],
            'workload' => $workloadData,
            'types' => $typeData,
            'issues' => $normalizedIssues,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD DATA
    |--------------------------------------------------------------------------
    */
    public function getDashboardData()
    {
        header('Content-Type: application/json');

        $cache = $this->getCache('dashboard');

        if ($cache) {
            echo json_encode($cache);
            exit;
        }

        try {
            $jira = new JiraService();
            $issues = $jira->getAllIssues();
            $result = $this->buildDashboardData($issues);
            $this->setCache('dashboard', $result);

            echo json_encode($result);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
            exit;
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

            usort($result, function ($a, $b) {
                return $b['percent'] <=> $a['percent'];
            });

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

            usort($result, function ($a, $b) {
                return $b['percent'] <=> $a['percent'];
            });

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
