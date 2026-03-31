<?php

namespace App\Controllers;

use App\Services\JiraService;

class BoardController
{
    private $jiraService;

    public function __construct()
    {
        $this->jiraService = new JiraService();
    }

    // /board
    public function index()
    {
        $jira = new \App\Services\JiraService();

        $issues = $jira->getAllIssues();

        $allLabels = [];

        foreach ($issues as $issue) {
            if (!empty($issue['fields']['labels'])) {
                foreach ($issue['fields']['labels'] as $label) {
                    $allLabels[$label] = true;
                }
            }
        }

        $allLabels = array_keys($allLabels);

        // đảm bảo biến tồn tại trong view
        extract([
            'issues' => $issues,
            'allLabels' => $allLabels
        ]);

        require dirname(__DIR__, 2) . '/public/views/board/index.php';
    }

    // /api/board
    public function list()
    {
        $jira = new \App\Services\JiraService();
        $issues = $this->jiraService->getBoardTasks();
        $users = $this->jiraService->getAssignableUsers();
//        $allLabels = $jira->allLabels();
        $allLabels = $this->jiraService->getAllLabels();

        require __DIR__ . '/../../public/views/board/partials/board-list.php';
    }

    public function move()
    {
        header('Content-Type: application/json');

        $issueKey = $_POST['issueKey'] ?? null;
        $transitionId = $_POST['transitionId'] ?? null;

        if (!$issueKey || !$transitionId) {
            echo json_encode(['success' => false]);
            return;
        }

        $jira = new JiraService();
        $result = $jira->transitionIssue($issueKey, $transitionId);

        echo json_encode([
            'success' => $result
        ]);
    }

    public function getTransitions()
    {
        header('Content-Type: application/json');

        $issueKey = $_GET['issueKey'] ?? null;

        if (!$issueKey) {
            echo json_encode([]);
            return;
        }

        $transitions = $this->jiraService->getTransitions($issueKey);

        echo json_encode($transitions);
    }

    public function assign()
    {
        header('Content-Type: application/json');

        $issueKey = $_POST['issueKey'] ?? null;
        $accountId = $_POST['accountId'] ?? null;

        if (!$issueKey) {
            echo json_encode(['success' => false]);
            return;
        }

        $result = $this->jiraService->assignIssue($issueKey, $accountId);

        echo json_encode(['success' => $result]);
    }

    // /api/board/summary
    public function summary()
    {
        $jira = new \App\Services\JiraService();

        $issues = $jira->getAllIssues();

        $summary = [];

        foreach ($issues as $issue) {
            $status = $issue['fields']['status']['name'];

            if (!isset($summary[$status])) {
                $summary[$status] = 0;
            }

            $summary[$status]++;
        }

        echo json_encode([
            'total' => count($issues),
            'status' => $summary
        ]);
    }

}