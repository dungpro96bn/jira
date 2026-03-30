<?php

namespace App\Models;

use PDO;

class UserModel
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | GET ALL USERS
    |--------------------------------------------------------------------------
    */
    public function getAll()
    {
        return $this->db->query("
        SELECT id, username, email, role 
        FROM users 
        ORDER BY id ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    |--------------------------------------------------------------------------
    | FIND USER
    |--------------------------------------------------------------------------
    */
    public function findByUsername($username)
    {
        $stmt = $this->db->prepare("
            SELECT id, username, email, password, role
            FROM users
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->execute([$username]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("
            SELECT id, username, email, password, role
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK EXISTS
    |--------------------------------------------------------------------------
    */
    public function existsByUsername($username)
    {
        return $this->findByUsername($username) !== null;
    }

    public function existsByEmail($email)
    {
        return $this->findByEmail($email) !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE USER
    |--------------------------------------------------------------------------
    */
    public function create($username, $email, $password, $role = 'user')
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, role)
            VALUES (?, ?, ?, ?)
        ");

        return $stmt->execute([
            $username,
            $email,
            password_hash($password, PASSWORD_BCRYPT),
            $role
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE USER
    |--------------------------------------------------------------------------
    */
    public function update($id, $username, $email, $role)
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET username = ?, email = ?, role = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $username,
            $email,
            $role,
            $id
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PARTIAL (AJAX)
    |--------------------------------------------------------------------------
    */
    public function updatePartial($id, $data)
    {
        $fields = [];
        $values = [];

        if (!empty($data['username'])) {
            $fields[] = 'username = ?';
            $values[] = $data['username'];
        }

        if (!empty($data['email'])) {
            $fields[] = 'email = ?';
            $values[] = $data['email'];
        }

        if (!empty($data['role'])) {
            $fields[] = 'role = ?';
            $values[] = $data['role'];
        }

        if (empty($fields)) return false;

        $values[] = $id;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";

        return $this->db->prepare($sql)->execute($values);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE USER
    |--------------------------------------------------------------------------
    */
    public function delete($id)
    {
        return $this->db
            ->prepare("DELETE FROM users WHERE id = ?")
            ->execute([$id]);
    }

    /*
    |--------------------------------------------------------------------------
    | CHANGE PASSWORD
    |--------------------------------------------------------------------------
    */
    public function changePassword($id, $password)
    {
        return $this->db->prepare("
            UPDATE users 
            SET password = ?
            WHERE id = ?
        ")->execute([
            password_hash($password, PASSWORD_BCRYPT),
            $id
        ]);
    }
}