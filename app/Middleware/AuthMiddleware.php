<?php

namespace App\Middleware;

class AuthMiddleware
{
    public static function check()
    {
        if (!isset($_SESSION['user'])) {
            header("Location: /login");
            exit;
        }
    }

    public static function checkAccess()
    {
        if (!isset($_SESSION['user'])) {
            header("Location: /login");
            exit;
        }

        $role = $_SESSION['user']['role'] ?? 'user';

        // user chưa được duyệt
        if ($role === 'user') {
            header("Location: /waiting-approval");
            exit;
        }
    }

}