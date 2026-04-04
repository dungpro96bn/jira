<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Services\AuthService;

class UserController
{
    protected $userModel;

    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();

        $db = require __DIR__ . '/../Config/database.php';
        $this->userModel = new UserModel($db);
    }

    /*
    |--------------------------------------------------------------------------
    | View
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $users = $this->userModel->getAll();
        require __DIR__ . '/../../public/views/users/index.php';
    }

    /*
    |--------------------------------------------------------------------------
    | Create User
    |--------------------------------------------------------------------------
    */
    public function store()
    {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'user';

        if (!$username || !$email || !$password) {
            header('Location: /users');
            exit;
        }

        $this->userModel->create([
            'username' => $username,
            'email'    => $email,
            'password' => $password,
            'role'     => $role
        ]);

        header('Location: /users');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Update (role / info)
    |--------------------------------------------------------------------------
    */
    public function update()
    {
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Missing ID']);
            exit;
        }

        $data = [
            'username' => $_POST['username'] ?? null,
            'email'    => $_POST['email'] ?? null,
            'role'     => $_POST['role'] ?? null,
        ];

        $result = $this->userModel->updatePartial($id, $data);

        echo json_encode([
            'success' => $result ? true : false
        ]);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete User
    |--------------------------------------------------------------------------
    */
    public function delete()
    {
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Missing ID']);
            exit;
        }

        $result = $this->userModel->delete($id);

        echo json_encode([
            'success' => $result ? true : false
        ]);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Change Password
    |--------------------------------------------------------------------------
    */
    public function changePassword()
    {
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? null;
        $password = $_POST['password'] ?? '';

        if (!$id || !$password) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing data'
            ]);
            exit;
        }

        $result = $this->userModel->changePassword($id, $password);

        echo json_encode([
            'success' => $result ? true : false
        ]);
        exit;
    }

    public function userNew(){

        require __DIR__ . '/../../public/views/users/user-new.php';
    }

    public function registerUserNew()
    {
        $username = $_POST['username'] ?? '';
        $email    = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? '';
        $password = $_POST['password'] ?? '';
        $referralCode = $_POST['referral_code'] ?? '';

        // load config
        $config = require __DIR__ . '/../Config/env.php';
        $validCode = $_ENV['REFERRAL_CODE'];

        // check referral code
        if ($referralCode !== $validCode) {
            $error = "Invalid referral code";
            require __DIR__ . '/../../public/views/users/user-new.php';
            return;
        }

        $result = $this->authService->registerNew($username, $email, $role, $password);

        if ($result['success']) {
            $success = "Register successful!";
            require __DIR__ . '/../../public/views/users/user-new.php';
            return;
        }

        $error = $result['message'];
        require __DIR__ . '/../../public/views/users/user-new.php';
    }

}