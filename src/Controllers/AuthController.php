<?php

namespace SellNow\Controllers;

use PDO;
use SellNow\Models\User;

class AuthController
{

    // Imperfect: Manual dependency injection via constructor every time
    private $twig;
    private PDO$db;
    private User $user;

    public function __construct($twig, PDO $db)
    {
        $this->twig = $twig;
        $this->db = $db;

        // Initialize User model
        $this->user = new User($db);
    }

    public function loginForm(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        echo $this->twig->render('auth/login.html.twig');
    }

    public function login(): void
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->redirect('/login?error=Missing credentials');
        }

        // Fetch user via user model
        $user = $this->user->findByEmail($email);

        // Password verification to user model
        if (!$user || !$this->user->verifyPassword($password, $user['password'])) {
            $this->redirect('/login?error=Invalid credentials');
        }

        $_SESSION['user_id']  = (int) $user['id'];
        $_SESSION['username'] = $user['username'];

        $this->redirect('/dashboard');
    }

    public function registerForm(): void
    {
        echo $this->twig->render('auth/register.html.twig');
    }

    public function register(): void
    {
        $email    = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $fullname = trim($_POST['fullname'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $username === '' || $password === '') {
            die('Fill all required fields');
        }
        
        // User create by model
        try {
            $this->user->create($email, $username, $fullname, $password);
        } catch (\Throwable $e) {
            die('Registration failed');
        }

        $this->redirect('/login?msg=Registered successfully');
    }

    public function dashboard(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        echo $this->twig->render('dashboard.html.twig', [
            'username' => $_SESSION['username'],
        ]);
    }


    /*
    * Internal helper
    */
    private function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    private function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    private function verifyPassword(string $input, string $stored): bool
    {
        // Supports legacy plaintext + modern hash
        if (password_get_info($stored)['algo'] === 0) {
            return hash_equals($stored, $input);
        }

        return password_verify($input, $stored);
    }
}
