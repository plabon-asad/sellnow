<?php

namespace SellNow\Controllers;

use PDO;
use SellNow\Services\PaymentService;

class CheckoutController
{
    private $twig;
    private PDO $db;
    private PaymentService $paymentService;

    // private const ALLOWED_PROVIDERS = ['Stripe', 'PayPal', 'Razorpay'];

    public function __construct($twig, PDO $db)
    {
        $this->twig = $twig;
        $this->db = $db;
        $this->paymentService = new PaymentService();
    }

    public function index(): void
    {
        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            $this->redirect('/cart');
        }

        echo $this->twig->render('checkout/index.html.twig', [
            'total'     => $this->calculateTotal($cart),
            'providers' => $this->paymentService->getAvailableProviders(),
        ]);
    }

    public function process(): void
    {
        $provider = $_POST['provider'] ?? '';

        if (!$this->paymentService->validateProvider($provider)) {
            die('Invalid payment provider');
        }

        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            $this->redirect('/cart');
        }

        // Never trust client totals
        $_SESSION['checkout'] = [
            'provider' => $provider,
            'total'    => $this->calculateTotal($cart),
        ];

        $this->redirect('/payment');
    }

    public function payment(): void
    {
        if (empty($_SESSION['checkout']) || empty($_SESSION['cart'])) {
            $this->redirect('/cart');
        }

        echo $this->twig->render('checkout/payment.html.twig', [
            'provider' => $_SESSION['checkout']['provider'],
            'total'    => $_SESSION['checkout']['total'],
        ]);
    }

    public function success(): void
    {
        if (empty($_SESSION['checkout'])) {
            $this->redirect('/cart');
        }

        $provider = $_SESSION['checkout']['provider'];
        $total    = $_SESSION['checkout']['total'];
        $userId   = $_SESSION['user_id'] ?? null;

        $this->paymentService->logTransaction($provider, $userId, $total);

        unset($_SESSION['cart'], $_SESSION['checkout']);

        echo $this->twig->render('layouts/base.html.twig', [
            'content' => "<h1>Thank you!</h1>
                          <p>Payment via {$provider} completed.</p>
                          <a href='/dashboard' class='btn btn-primary'>Dashboard</a>",
        ]);
    }

    /**
     * Internal helper
    */
    private function calculateTotal(array $cart): float
    {
        $total = 0.0;

        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return round($total, 2);
    }

    private function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}
