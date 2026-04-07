<?php

namespace App\Helpers;

class Role
{
    public static function check($roles = [])
    {
        if (!isset($_SESSION['user'])) {
            header("Location: /login");
            exit;
        }

        $userRole = $_SESSION['user']['role'] ?? null;

        if (!in_array($userRole, (array)$roles)) {
            http_response_code(403);
            header("Location: /forbidden");
            exit;
        }
    }
}