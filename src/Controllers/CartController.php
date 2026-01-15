<?php

namespace SellNow\Controllers;

use PDO;

class CartController
{
    private $twig;
    private PDO $db;

    public function __construct($twig, PDO $db)
    {
        $this->twig = $twig;
        $this->db = $db;
    }

    public function index(): void
    {
        $cart  = $_SESSION['cart'] ?? [];
        $total = 0.0;

        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        echo $this->twig->render('cart/index.html.twig', [
            'cart'  => array_values($cart),
            'total' => $total,
        ]);
    }

    public function add(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity  = max(1, (int) ($_POST['quantity'] ?? 1));

        if ($productId <= 0) {
            $this->json(['status' => 'error', 'message' => 'Invalid product']);
        }

        $stmt = $this->db->prepare(
            'SELECT product_id, title, price FROM products WHERE product_id = :id AND is_active = 1'
        );
        $stmt->execute(['id' => $productId]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $this->json(['status' => 'error', 'message' => 'Product not found']);
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Key cart by product_id to avoid duplicates
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'product_id' => (int) $product['product_id'],
                'title'      => $product['title'],
                'price'      => (float) $product['price'],
                'quantity'   => $quantity,
            ];
        }

        $this->json([
            'status' => 'success',
            'count'  => count($_SESSION['cart']),
        ]);
    }

    public function clear(): void
    {
        unset($_SESSION['cart']);
        $this->redirect('/cart');
    }

    /**
     * Internal helper
    */
    private function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    private function json(array $payload): void
    {
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}
