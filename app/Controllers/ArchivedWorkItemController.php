<?php

namespace App\Controllers;

use App\Services\JiraService;

class ArchivedWorkItemController
{
    public function index()
    {
        require __DIR__ . '/../../public/views/archived-work-items/index.php';
    }

    public function list()
    {
        header('Content-Type: application/json');

        try {
            $jira = new JiraService();
            $items = $jira->getArchivedWorkItemsStore();

            echo json_encode([
                'success' => true,
                'items' => $items,
            ]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
            exit;
        }
    }
}
