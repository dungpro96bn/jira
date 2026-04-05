<?php

namespace App\Controllers;

use App\Services\JiraService;

class AttachmentController
{
    public function index()
    {
        require __DIR__ . '/../../public/views/attachments/index.php';
    }

    public function list()
    {
        header('Content-Type: application/json');

        try {
            $jira = new JiraService();
            echo json_encode([
                'success' => true,
                'attachments' => $jira->getAllAttachments(),
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

    public function delete()
    {
        header('Content-Type: application/json');

        $attachmentId = $_POST['attachmentId'] ?? '';

        if ($attachmentId === '') {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => 'Attachment id is required.',
            ]);
            exit;
        }

        try {
            $jira = new JiraService();
            $result = $jira->deleteAttachment($attachmentId);

            if (!empty($result['success'])) {
                echo json_encode($result);
                exit;
            }

            http_response_code(500);
            echo json_encode($result);
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

    public function proxy()
    {
        $id   = $_GET['id'] ?? null;
        $name = $_GET['name'] ?? ($id . '.file');
        $download = isset($_GET['download']) && (string) $_GET['download'] === '1';

        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            exit('Invalid attachment id');
        }

        $baseDir = __DIR__ . '/../../storage/cache/';
        $ttl = 60 * 60 * 24 * 2;
        $subDir = substr($id, 0, 2);
        $cacheDir = $baseDir . $subDir . '/';

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $ext = pathinfo($name, PATHINFO_EXTENSION) ?: 'bin';
        $cacheFile = $cacheDir . $id . '.' . $ext;
        $disposition = $download ? 'attachment' : 'inline';

        if (file_exists($cacheFile)) {
            if (time() - filemtime($cacheFile) > $ttl) {
                unlink($cacheFile);
            } else {
                $mime = mime_content_type($cacheFile);

                header('Content-Type: ' . $mime);
                header('Cache-Control: public, max-age=86400');
                header('Content-Length: ' . filesize($cacheFile));
                header("Content-Disposition: {$disposition}; filename*=UTF-8''" . rawurlencode($name));

                readfile($cacheFile);
                exit;
            }
        }

        $jira = new JiraService();
        $config = require __DIR__ . '/../Config/jira.php';
        $baseUrl = rtrim($config['base_url'], '/');
        $url = $baseUrl . '/rest/api/3/attachment/content/' . $id;

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $jira->getAuthString(),
            CURLOPT_HTTPHEADER => ['Accept: */*'],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $data = curl_exec($ch);

        if ($data === false) {
            http_response_code(500);
            echo 'CURL ERROR: ' . curl_error($ch);
            exit;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        curl_close($ch);

        if ($httpCode !== 200) {
            http_response_code($httpCode);
            echo 'HTTP ERROR: ' . $httpCode;
            exit;
        }

        if (strpos((string) $contentType, 'image') === 0) {
            file_put_contents($cacheFile, $data);
        }

        header('Content-Type: ' . $contentType);
        header('Cache-Control: public, max-age=86400');
        header("Content-Disposition: {$disposition}; filename*=UTF-8''" . rawurlencode($name));

        echo $data;
    }
}
