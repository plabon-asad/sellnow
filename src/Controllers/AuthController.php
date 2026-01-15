<?php

namespace SellNow\Controllers;

use PDO;

class AuthController
{

    // Imperfect: Manual dependency injection via constructor every time
    private $twig;
    private PDO$db;

    public function __construct($twig, PDO $db)
    {
        $this->twig = $twig;
        $this->db = $db;
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

        $stmt = $this->db->prepare(
            'SELECT id, username, password FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Legacy-compatible password check
        if (!$user || !$this->verifyPassword($password, $user['password'])) {
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

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            'INSERT INTO users (email, username, Full_Name, password)
             VALUES (:email, :username, :fullname, :password)'
        );

        try {
            $stmt->execute([
                'email'    => $email,
                'username' => $username,
                'fullname' => $fullname,
                'password' => $hashedPassword,
            ]);
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
