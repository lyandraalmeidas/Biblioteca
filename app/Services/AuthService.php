<?php

namespace App\Services;

use App\Repositories\UserRepository;

class AuthService
{
    private UserRepository $users;

    public function __construct(?UserRepository $users = null)
    {
        $this->users = $users ?: new UserRepository();
    }

    /**
     * Attempt login; returns array with user info or null.
     * On success, stores minimal user info in session.
     */
    public function attempt(string $email, string $password): ?array
    {
        $user = $this->users->findByEmail($email);
        if (!$user) return null;
        if (!password_verify($password, $user['password'])) return null;

        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ];
        return $_SESSION['user'];
    }
}
