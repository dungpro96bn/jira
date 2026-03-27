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
        require dirname(__DIR__, 2) . '/public/views/board/index.php';
    }

    // /api/board
    public function list()
    {
        $issues = $this->jiraService->getBoardTasks();
        $users = $this->jiraService->getAssignableUsers();

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

}