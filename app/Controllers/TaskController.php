<?php

namespace App\Controllers;

use App\Services\JiraService;

class TaskController
{
    private $jiraService;

    public function __construct()
    {
        $this->jiraService = new JiraService();
    }

    /*
    |--------------------------------------------------------------------------
    | STORE TASK (POST /create-task)
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $jira = new \App\Services\JiraService();
        $users = $jira->getAssignableUsers();
        $labels = $jira->getAllLabels();
        $priorities = $jira->getPriorities();

        require __DIR__ . '/../../public/views/task/create-task.php';
    }
    public function store()
    {
        header('Content-Type: application/json');

        try {

            /*
            |--------------------------------------------------------------------------
            | Validate input
            |--------------------------------------------------------------------------
            */

            $summary = trim($_POST['summary'] ?? '');
            $descriptionRaw = $_POST['description'] ?? '';
            $assignee = $_POST['assignee'] ?? null;
            $duedate = $_POST['duedate'] ?? null;
            $labels = $_POST['labels'] ?? [];
            $priority = $_POST['priority'] ?? null;

            if (empty($summary)) {
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'message' => 'Summary is required'
                ]);
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Handle description
            |--------------------------------------------------------------------------
            */

            $originUrl = $this->getOriginUrl();

            $descriptionFormatted = str_replace(
                'uploads/',
                $originUrl . '/uploads/',
                $descriptionRaw
            );

            // luôn convert HTML → ADF
            $description = $this->convertHtmlToAdf($descriptionFormatted);

            // Nếu không phải JSON hợp lệ → fallback text
            if (!$description) {
                $description = $descriptionRaw;
            }

            /*
            |--------------------------------------------------------------------------
            | Call Jira Service
            |--------------------------------------------------------------------------
            */

            $jira = new JiraService();

            $result = $jira->createTask([
                'summary' => $summary,
                'description' => $description,
                'assignee' => $assignee,
                'labels' => $labels,
                'priority' => $priority,
                'duedate' => $duedate
            ]);

            /*
            |--------------------------------------------------------------------------
            | Success Response
            |--------------------------------------------------------------------------
            */

            if (isset($result['key'])) {

                $issueKey = $result['key'];

                // =========================
                // UPLOAD FILE
                // =========================
                if (!empty($_FILES['attachments']['name'][0])) {

                    foreach ($_FILES['attachments']['tmp_name'] as $index => $tmpPath) {

                        $fileName = $_FILES['attachments']['name'][$index];

                        $jira->uploadAttachment($issueKey, $tmpPath, $fileName);
                    }
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Task created successfully',
                    'key' => $issueKey
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to create task',
                    'error' => $result
                ]);
            }

        } catch (\Exception $e) {

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET ORIGIN URL
    |--------------------------------------------------------------------------
    */
    private function getOriginUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            ? 'https'
            : 'http';

        $host = $_SERVER['HTTP_HOST'];

        return $protocol . '://' . $host;
    }

    public function board()
    {
        if (!isset($_SESSION['user'])) {
            header("Location: /login");
            exit;
        }

        $jira = new \App\Services\JiraService();
        $tasks = $jira->getTasks();

        require __DIR__ . '/../../public/views/task/board.php';
    }

    public function detail()
    {
        $users = $this->jiraService->getAssignableUsers();
        $allLabels = $this->jiraService->getAllLabels();

        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            exit('Task ID required');
        }

        $jira = new \App\Services\JiraService();

        $task = $jira->getTaskById($id);

        if (!empty($task['fields']['subtasks'])) {
            $subtaskKeys = [];
            foreach ($task['fields']['subtasks'] as $subtask) {
                if (!empty($subtask['key'])) {
                    $subtaskKeys[] = $subtask['key'];
                }
            }

            if (!empty($subtaskKeys)) {
                $subtaskDetails = $jira->getIssuesByKeys($subtaskKeys);
                $subtaskMap = [];

                foreach ($subtaskDetails as $subtaskDetail) {
                    if (!empty($subtaskDetail['key'])) {
                        $subtaskMap[$subtaskDetail['key']] = $subtaskDetail;
                    }
                }

                foreach ($task['fields']['subtasks'] as $index => $subtask) {
                    if (!empty($subtask['key']) && isset($subtaskMap[$subtask['key']])) {
                        $task['fields']['subtasks'][$index] = $subtaskMap[$subtask['key']];
                    }
                }
            }
        }

        require dirname(__DIR__, 2) . '/public/views/task/detail.php';
    }


    public function addComment()
    {
        header('Content-Type: application/json');

        $issueKey = $_POST['issueKey'] ?? '';
        $comment = trim($_POST['comment'] ?? '');

        if (!$issueKey || $comment === '') {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => 'Issue key and comment are required.'
            ]);
            return;
        }

        $result = $this->jiraService->addComment($issueKey, $comment);

        if (!empty($result['success'])) {
            echo json_encode($result);
            return;
        }

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'Unable to add comment.'
        ]);
    }

    public function createSubtask()
    {
        header('Content-Type: application/json');

        $parentKey = $_POST['parentKey'] ?? '';
        $summary = trim($_POST['summary'] ?? '');

        if (!$parentKey || $summary === '') {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => 'Parent issue key and summary are required.'
            ]);
            return;
        }

        $result = $this->jiraService->createSubtask($parentKey, $summary);

        if (!empty($result['success'])) {
            echo json_encode($result);
            return;
        }

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'Unable to create subtask.'
        ]);
    }

    public function addWorklog()
    {
        header('Content-Type: application/json');

        $issueKey = $_POST['issueKey'] ?? '';
        $timeSpent = trim($_POST['timeSpent'] ?? '');
        $remainingEstimate = trim($_POST['remainingEstimate'] ?? '');
        $comment = trim($_POST['comment'] ?? '');

        if (!$issueKey || !$timeSpent) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => 'Issue key and time spent are required.'
            ]);
            return;
        }

        $result = $this->jiraService->addWorklog($issueKey, $timeSpent, $remainingEstimate, $comment);

        if (!empty($result['success'])) {
            echo json_encode($result);
            return;
        }

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'Unable to log time.'
        ]);
    }

    public function updateDescription()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data) {
                echo json_encode([
                    "success" => false,
                    "error" => "Invalid JSON"
                ]);
                return;
            }

            $issueId = $data['issueId'] ?? null;
            $description = $data['description'] ?? '';

            if (!$issueId) {
                echo json_encode([
                    "success" => false,
                    "error" => "Missing issueId"
                ]);
                return;
            }

            $originUrl = $this->getOriginUrl();

            $description = str_replace(
                'src="uploads/',
                'src="' . $originUrl . '/uploads/',
                $description
            );

            $jiraService = new \App\Services\JiraService();

            $adf = $this->convertHtmlToAdf($description);

            $result = $jiraService->updateIssue($issueId, [
                "fields" => [
                    "description" => $adf
                ]
            ]);

            if (isset($result['error']) && $result['error']) {
                echo json_encode([
                    "success" => false,
                    "error" => $result
                ]);
                return;
            }

            echo json_encode([
                "success" => true
            ]);

        } catch (\Throwable $e) {
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
    }

    private function convertHtmlToAdf($html)
    {
        // Fix entity (tiếng Việt + &nbsp;)
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Load DOM
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);

        if (!$body) {
            return [
                'type' => 'doc',
                'version' => 1,
                'content' => []
            ];
        }

        $content = [];

        foreach ($body->childNodes as $node) {

            // =========================
            // PARAGRAPH + IMAGE
            // =========================
            if ($node->nodeName === 'p') {

                $inlineContent = [];

                foreach ($node->childNodes as $child) {

                    // TEXT / INLINE
                    if ($child->nodeType === XML_TEXT_NODE || $child->nodeType === XML_ELEMENT_NODE && $child->nodeName !== 'img') {

                        $parsed = $this->parseInlineNodes($child);

                        if (!empty($parsed)) {
                            $inlineContent = array_merge($inlineContent, $parsed);
                        }
                    }

                    // IMAGE
                    if ($child->nodeName === 'img') {

                        $src = $child->getAttribute('src');

                        if ($src) {
                            $content[] = [
                                'type' => 'mediaSingle',
                                'content' => [[
                                    'type' => 'media',
                                    'attrs' => [
                                        'type' => 'external',
                                        'url' => $src
                                    ]
                                ]]
                            ];
                        }
                    }
                }

                $inlineContent = $this->parseInlineNodes($node);

                // nếu có text thì mới add paragraph
                if (!empty($inlineContent)) {
                    $content[] = [
                        'type' => 'paragraph',
                        'content' => $inlineContent
                    ];
                }
            }

            // =========================
            // HEADING
            // =========================
            if (in_array($node->nodeName, ['h1','h2','h3','h4','h5','h6'])) {

                $level = (int) substr($node->nodeName, 1);

                $content[] = [
                    'type' => 'heading',
                    'attrs' => ['level' => $level],
                    'content' => $this->parseInlineNodes($node)
                ];
            }

            // =========================
            // BULLET LIST
            // =========================
            if ($node->nodeName === 'ul') {

                $items = [];

                foreach ($node->getElementsByTagName('li') as $li) {
                    $items[] = [
                        'type' => 'listItem',
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => $this->parseInlineNodes($li)
                        ]]
                    ];
                }

                $content[] = [
                    'type' => 'bulletList',
                    'content' => $items
                ];
            }

            // =========================
            // ORDERED LIST
            // =========================
            if ($node->nodeName === 'ol') {

                $items = [];

                foreach ($node->getElementsByTagName('li') as $li) {
                    $items[] = [
                        'type' => 'listItem',
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => $this->parseInlineNodes($li)
                        ]]
                    ];
                }

                $content[] = [
                    'type' => 'orderedList',
                    'content' => $items
                ];
            }
        }

        return [
            'type' => 'doc',
            'version' => 1,
            'content' => $content
        ];
    }

    private function parseInlineNodes($node)
    {
        $result = [];

        foreach ($node->childNodes as $child) {

            // TEXT NODE
            if ($child->nodeType === XML_TEXT_NODE) {

                $text = trim($child->nodeValue);

                if ($text !== '') {
                    $result[] = [
                        'type' => 'text',
                        'text' => $text
                    ];
                }
            }

            // ELEMENT NODE
            if ($child->nodeType === XML_ELEMENT_NODE) {

                $text = trim($child->textContent);

                if ($text === '') continue;

                $marks = [];

                switch ($child->nodeName) {
                    case 'strong':
                    case 'b':
                        $marks[] = ['type' => 'strong'];
                        break;

                    case 'em':
                    case 'i':
                        $marks[] = ['type' => 'em'];
                        break;

                    case 'a':
                        $marks[] = [
                            'type' => 'link',
                            'attrs' => [
                                'href' => $child->getAttribute('href')
                            ]
                        ];
                        break;
                }

                $nodeData = [
                    'type' => 'text',
                    'text' => $text
                ];

                if (!empty($marks)) {
                    $nodeData['marks'] = $marks;
                }

                $result[] = $nodeData;
            }
        }

        return $result;
    }

    public function uploadImage()
    {
        header('Content-Type: application/json');

        if (!isset($_FILES['file'])) {
            echo json_encode([
                'error' => 'No file uploaded'
            ]);
            return;
        }

        $file = $_FILES['file'];

        // validate type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode([
                'error' => 'Invalid file type'
            ]);
            return;
        }

        // tạo folder nếu chưa có
        $uploadDir = __DIR__ . '/../../public/uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // tạo tên file
        $fileName = time() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {

            // URL public
            $url = $this->getBaseUrl() . '/uploads/' . $fileName;

            echo json_encode([
                'location' => $url // 👈 TinyMCE cần field này
            ]);
        } else {
            echo json_encode([
                'error' => 'Upload failed'
            ]);
        }
    }

    private function getBaseUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)
            ? "https://"
            : "http://";

        return $protocol . $_SERVER['HTTP_HOST'];
    }

    public function getLabels()
    {
        $jira = new \App\Services\JiraService();

        $response = $jira->getLabels();
        $data = json_decode($response, true);

        return $data['values'] ?? [];
    }

    public function updateSummary()
    {
        header('Content-Type: application/json');

        try {

            $issueKey = $_POST['issueKey'] ?? null;
            $summary  = trim($_POST['summary'] ?? '');

            if (!$issueKey || empty($summary)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Missing data'
                ]);
                return;
            }

            $jira = new \App\Services\JiraService();

            $result = $jira->updateIssue($issueKey, [
                "fields" => [
                    "summary" => $summary
                ]
            ]);

            if (isset($result['error']) && $result['error']) {
                echo json_encode([
                    'success' => false,
                    'error' => $result
                ]);
                return;
            }

            // 🔥 CLEAR CACHE
            $cache = new \App\Services\CacheService();
            $cache->delete('board_data');

            echo json_encode([
                'success' => true
            ]);

        } catch (\Throwable $e) {

            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updateDueDate()
    {
        header('Content-Type: application/json');

        $issueKey = $_POST['issueKey'] ?? null;
        $duedate = $_POST['duedate'] ?? null;

        if ($duedate === '') {
            $duedate = null;
        }

        if (!$issueKey) {
            echo json_encode(['success' => false]);
            return;
        }

        $jira = new \App\Services\JiraService();
        $result = $jira->updateDueDate($issueKey, $duedate);

        echo json_encode([
            'success' => !isset($result['error'])
        ]);
    }

    public function delete()
    {
        header('Content-Type: application/json');

        $issueKey = $_POST['issueKey'] ?? null;

        if (!$issueKey) {
            echo json_encode(['success' => false]);
            return;
        }

        $jira = new \App\Services\JiraService();
        $archivedBy = [
            'name' => $_SESSION['user']['name'] ?? ($_SESSION['user']['username'] ?? 'Unknown'),
            'avatar' => $_SESSION['user']['avatar'] ?? '',
            'email' => $_SESSION['user']['email'] ?? '',
        ];
        $result = $jira->deleteTask($issueKey, $archivedBy);

        echo json_encode([
            'success' => !isset($result['error'])
        ]);
    }


    public function updatePriority()
    {
        header('Content-Type: application/json');

        $issueKey = $_POST['issueKey'] ?? '';
        $priorityName = $_POST['priority'] ?? '';

        if (!$issueKey || !$priorityName) {
            echo json_encode(['success' => false]);
            return;
        }

        try {
            $jira = new \App\Services\JiraService();

            $jira->updatePriority($issueKey, $priorityName);

            echo json_encode(['success' => true]);

        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }


    public function updateLabels()
    {
        header('Content-Type: application/json');

        $issueKey = $_POST['issueKey'] ?? '';
        $labels = $_POST['labels'] ?? [];

        if (!$issueKey) {
            echo json_encode(['success' => false]);
            return;
        }

        try {
            $jira = new \App\Services\JiraService();

            $jira->updateLabels($issueKey, $labels);

            echo json_encode([
                'success' => true,
                'labels' => $labels
            ]);

        } catch (\Throwable $e) {

            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

}