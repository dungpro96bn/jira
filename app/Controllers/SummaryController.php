<?php

namespace App\Controllers;

use App\Services\JiraService;

class SummaryController
{
    public function index()
    {
        require __DIR__ . '/../../public/views/summary/index.php';
    }

    public function getSummary()
    {
        header('Content-Type: application/json');

        $cacheFile = __DIR__ . '/../../storage/summary_cache.json';
        $cacheExpire = 300; // 5 phút

        // ✅ Nếu cache tồn tại và chưa hết hạn → dùng luôn
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheExpire) {
            echo file_get_contents($cacheFile);
            exit;
        }

        $jira = new JiraService();
        $issues = $jira->getAllIssues();

        $summary = [];

        foreach ($issues as $issue) {

            if (!isset($issue['fields']['summary'])) {
                continue;
            }

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

        // Tạo folder nếu chưa có
        if (!is_dir(dirname($cacheFile))) {
            mkdir(dirname($cacheFile), 0777, true);
        }

        // Lưu cache
        file_put_contents($cacheFile, json_encode($result));

        echo json_encode($result);
        exit;
    }

    public function clearCache()
    {
        header('Content-Type: application/json');

        $cacheFile = __DIR__ . '/../../storage/summary_cache.json';

        if (file_exists($cacheFile)) {
            unlink($cacheFile);

            echo json_encode([
                'success' => true,
                'message' => 'Cache cleared'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No cache file'
            ]);
        }

        exit;
    }


}