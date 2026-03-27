<?php

namespace App\Controllers;

use App\Services\AuthService;

class AuthController
{
    /*
    |--------------------------------------------------------------------------
    | SHOW LOGIN PAGE (GET /login)
    |--------------------------------------------------------------------------
    */
    public function showLogin()
    {
        // Nếu đã login rồi thì không cho vào lại login
        if (isset($_SESSION['user'])) {
            header("Location: /board");
            exit;
        }

        require __DIR__ . '/../../public/views/auth/login.php';
    }

    /*
    |--------------------------------------------------------------------------
    | HANDLE LOGIN (POST /login)
    |--------------------------------------------------------------------------
    */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /login");
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($name) || empty($password)) {
            $error = "Name and password are required";
            require dirname(__DIR__, 2) . '/public/views/auth/login.php';
            return;
        }

        $auth = new AuthService();
        $result = $auth->attempt($name, $password);


        if ($result['success']) {

            // 🔐 Regenerate session
            session_regenerate_id(true);

            $user = $result['user'];

            $_SESSION['user'] = $result['user'];

            switch ($user['role']) {
                case 'admin':
                    header("Location: /board");
                    break;

                case 'editor':
                default:
                    header("Location: /create-task");
                    break;
            }

            exit;

        } else {

            $error = $result['message'];

            require dirname(__DIR__, 2) . '/public/views/auth/login.php';
            return;
        }
    }

    public function showRegister()
    {
        require __DIR__ . '/../../public/views/auth/register.php';
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT (GET /logout)
    |--------------------------------------------------------------------------
    */
    public function logout()
    {
        // Xóa session
        $_SESSION = [];

        // Hủy session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        header("Location: /login");
        exit;
    }


    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    // GET /register
    public function registerForm()
    {
        require dirname(__DIR__, 2) . '/public/views/auth/register.php';
    }

    // POST /register
    public function register()
    {
        $username = $_POST['username'] ?? '';
        $email    = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $referralCode = $_POST['referral_code'] ?? '';

        // load config
        $config = require __DIR__ . '/../Config/env.php';
        $validCode = $_ENV['REFERRAL_CODE'];

        // check referral code
        if ($referralCode !== $validCode) {
            $error = "Invalid referral code";
            require dirname(__DIR__, 2) . '/public/views/auth/register.php';
            return;
        }

        $result = $this->authService->register($username, $email, $password);

        if ($result['success']) {
            $success = "Register successful! Redirecting to login in 5 seconds...";
            require dirname(__DIR__, 2) . '/public/views/auth/register.php';
            return;
        }

        $error = $result['message'];
        require dirname(__DIR__, 2) . '/public/views/auth/register.php';
    }

}