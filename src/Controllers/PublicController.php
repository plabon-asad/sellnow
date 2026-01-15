<?php

namespace SellNow\Controllers;

use PDO;

class PublicController
{
    private $twig;
    private PDO $db;

    public function __construct($twig, PDO $db)
    {
        $this->twig = $twig;
        $this->db = $db;
    }

    public function profile(string $username): void
    {
        // Contract: username must be predictable
        $username = trim($username);
        if ($username === '') {
            $this->notFound();
        }

        // Single responsibility: fetch seller
        $stmt = $this->db->prepare(
            'SELECT id, username, Full_Name
             FROM users
             WHERE username = :username
             LIMIT 1'
        );
        $stmt->execute(['username' => $username]);

        $seller = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$seller) {
            $this->notFound();
        }

        // Explicit data contract: only public & active products
        $productsStmt = $this->db->prepare(
            'SELECT product_id, title, price, image_path
             FROM products
             WHERE user_id = :user
               AND is_active = 1'
        );
        $productsStmt->execute(['user' => $seller->id]);

        $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

        echo $this->twig->render('public/profile.html.twig', [
            'seller'   => $seller,
            'products' => $products,
        ]);
    }

    /**
     * Internal helper
    */
    private function notFound(): void
    {
        http_response_code(404);
        echo 'User not found';
        exit;
    }
}
