<?php

namespace SellNow\Controllers;

use PDO;

class ProductController
{
    private $twig;
    private PDO $db;

    private const UPLOAD_DIR = __DIR__ . '/../../public/uploads/';
    private const MAX_FILE_SIZE = 5_000_000; // 5MB

    public function __construct($twig, PDO $db)
    {
        $this->twig = $twig;
        $this->db = $db;
    }

    public function create(): void
    {
        $this->requireAuth();

        echo $this->twig->render('products/add.html.twig');
    }

    public function store(): void
    {
        $this->requireAuth();

        $title = trim($_POST['title'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);

        if ($title === '' || $price <= 0) {
            die('Invalid product data');
        }

        $slug = $this->generateSlug($title);

        $imagePath = $this->handleUpload('image', ['image/jpeg', 'image/png']);
        $filePath  = $this->handleUpload('product_file', null);

        $stmt = $this->db->prepare(
            'INSERT INTO products (user_id, title, slug, price, image_path, file_path)
             VALUES (:user, :title, :slug, :price, :image, :file)'
        );

        $stmt->execute([
            'user'  => $_SESSION['user_id'],
            'title' => $title,
            'slug'  => $slug,
            'price' => $price,
            'image' => $imagePath,
            'file'  => $filePath,
        ]);

        $this->redirect('/dashboard');
    }

    /**
     * Internal helper
    */
    private function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    private function generateSlug(string $title): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
        return trim($base, '-') . '-' . random_int(1000, 9999);
    }

    private function handleUpload(string $field, ?array $allowedTypes): ?string
    {
        if (
            !isset($_FILES[$field]) ||
            $_FILES[$field]['error'] !== UPLOAD_ERR_OK
        ) {
            return null;
        }

        if ($_FILES[$field]['size'] > self::MAX_FILE_SIZE) {
            die('File too large');
        }

        if ($allowedTypes !== null) {
            $mime = mime_content_type($_FILES[$field]['tmp_name']);
            if (!in_array($mime, $allowedTypes, true)) {
                die('Invalid file type');
            }
        }

        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0777, true);
        }

        $safeName = time() . '_' . basename($_FILES[$field]['name']);
        $target   = self::UPLOAD_DIR . $safeName;

        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
            die('Upload failed');
        }

        return 'uploads/' . $safeName;
    }

    private function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}
