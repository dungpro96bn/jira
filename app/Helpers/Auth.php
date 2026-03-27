<?php

namespace App\Helpers;

class Auth
{
    public static function user()
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check()
    {
        return isset($_SESSION['user']);
    }

    public static function logout()
    {
        unset($_SESSION['user']);
    }
}