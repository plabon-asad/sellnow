<?php

namespace SellNow\Models;

use PDO;

class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Find a user by email
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, password FROM users WHERE email = :email LIMIT 1'
        );

        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    // Create a new user
    public function create(string $email, string $username, string $fullName, string $password): void 
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            'INSERT INTO users (email, username, Full_Name, password)
             VALUES (:email, :username, :fullname, :password)'
        );

        $stmt->execute([
            'email'    => $email,
            'username' => $username,
            'fullname' => $fullName,
            'password' => $hashedPassword,
        ]);
    }

    // Verify password (legacy + modern)
    public function verifyPassword(string $input, string $stored): bool
    {
        if (password_get_info($stored)['algo'] === 0) {
            return hash_equals($stored, $input);
        }

        return password_verify($input, $stored);
    }
}
