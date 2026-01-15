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
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Raw SQL, no Model
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user && $password == $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: /dashboard");
            exit;
        } else {
            header("Location: /login?error=Invalid credentials");
            exit;
        }
    }

    public function registerForm()
    {
        echo $this->twig->render('auth/register.html.twig');
    }

    public function register()
    {
        if (empty($_POST['email']) || empty($_POST['password']))
            die("Fill all fields");

        // Raw SQL
        $sql = "INSERT INTO users (email, username, Full_Name, password) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute([
                $_POST['email'],
                $_POST['username'],
                $_POST['fullname'],
                $_POST['password']
            ]);
        } catch (\Exception $e) {
            die("Error registering: " . $e->getMessage());
        }

        header("Location: /login?msg=Registered successfully");
        exit;
    }

    public function dashboard()
    {
        if (!isset($_SESSION['user_id']))
            header("Location: /login");

        echo $this->twig->render('dashboard.html.twig', [
            'username' => $_SESSION['username']
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
