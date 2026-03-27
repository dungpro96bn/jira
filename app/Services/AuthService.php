<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /*
    |--------------------------------------------------------------------------
    | ATTEMPT LOGIN
    |--------------------------------------------------------------------------
    */
    public function attempt($username, $password)
    {
        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }

        if (!password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }

        unset($user['password']);

        return [
            'success' => true,
            'user' => $user
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    */
    public function register($username, $email, $password)
    {
        if (empty($username) || empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'All fields are required'
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Invalid email format'
            ];
        }

        //CHECK PASSWORD >= 6
        if (strlen($password) < 6) {
            return [
                'success' => false,
                'message' => 'Password must be at least 6 characters'
            ];
        }

        if ($this->userModel->existsByUsername($username)) {
            return [
                'success' => false,
                'message' => 'Username already exists'
            ];
        }

        if ($this->userModel->existsByEmail($email)) {
            return [
                'success' => false,
                'message' => 'Email already exists'
            ];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->userModel->create($username, $email, $hashedPassword);

        return [
            'success' => true
        ];
    }
}