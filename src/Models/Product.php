<?php

namespace SellNow\Models;

use PDO;

class Product
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Create a new product
    public function create(
        int $userId,
        string $title,
        string $slug,
        float $price,
        string $imagePath = '',
        string $filePath = ''
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO products (user_id, title, slug, price, image_path, file_path)
             VALUES (:user_id, :title, :slug, :price, :image_path, :file_path)'
        );

        $stmt->execute([
            'user_id'    => $userId,
            'title'      => $title,
            'slug'       => $slug,
            'price'      => $price,
            'image_path'=> $imagePath,
            'file_path' => $filePath,
        ]);
    }

    // Get all products for a seller
    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM products WHERE user_id = :user_id AND is_active = 1'
        );

        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Find product by ID
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM products WHERE product_id = :id LIMIT 1'
        );

        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        return $product ?: null;
    }
}
