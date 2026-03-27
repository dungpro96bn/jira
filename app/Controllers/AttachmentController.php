<?php

namespace App\Controllers;

use App\Services\JiraService;

class AttachmentController
{

    public function proxy()
    {
        $id   = $_GET['id'] ?? null;
        $name = $_GET['name'] ?? ($id . '.file');

        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            exit('Invalid attachment id');
        }

        // =========================
        // CONFIG
        // =========================
        $baseDir = __DIR__ . '/../../storage/cache/';
        $ttl = 60 * 60 * 24 * 2; // 2 ngày

        // chia folder (tránh quá nhiều file)
        $subDir = substr($id, 0, 2);
        $cacheDir = $baseDir . $subDir . '/';

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        // lấy extension từ name
        $ext = pathinfo($name, PATHINFO_EXTENSION) ?: 'bin';
        $cacheFile = $cacheDir . $id . '.' . $ext;

        // =========================
        // CACHE HIT + TTL
        // =========================
        if (file_exists($cacheFile)) {

            // hết hạn → xóa
            if (time() - filemtime($cacheFile) > $ttl) {
                unlink($cacheFile);
            } else {
                $mime = mime_content_type($cacheFile);

                header("Content-Type: " . $mime);
                header("Cache-Control: public, max-age=86400");
                header("Content-Length: " . filesize($cacheFile));
                header("Content-Disposition: inline; filename*=UTF-8''" . rawurlencode($name));

                readfile($cacheFile);
                exit;
            }
        }

        // =========================
        // CALL JIRA
        // =========================
        $jira = new \App\Services\JiraService();

        $url = "https://dev-scvweb.atlassian.net/rest/api/3/attachment/content/" . $id;

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $jira->getAuthString(),
            CURLOPT_HTTPHEADER => ["Accept: */*"],
            CURLOPT_FOLLOWLOCATION => true,

            // ⚠️ local only
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
            echo "HTTP ERROR: " . $httpCode;
            exit;
        }

        // =========================
        // SAVE CACHE (chỉ cache image)
        // =========================
        if (str_starts_with($contentType, 'image')) {
            file_put_contents($cacheFile, $data);
        }

        // =========================
        // RESPONSE
        // =========================
        header("Content-Type: " . $contentType);
        header("Cache-Control: public, max-age=86400");
        header("Content-Disposition: inline; filename*=UTF-8''" . rawurlencode($name));

        echo $data;
    }

}