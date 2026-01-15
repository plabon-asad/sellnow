<?php

namespace SellNow\Services;

class PaymentService
{
    private const ALLOWED_PROVIDERS = ['Stripe', 'PayPal', 'Razorpay'];

    public function validateProvider(string $provider): bool
    {
        return in_array($provider, self::ALLOWED_PROVIDERS, true);
    }

    public function getAvailableProviders(): array
    {
        return self::ALLOWED_PROVIDERS;
    }

    public function logTransaction(string $provider, ?int $userId, float $total): void {
        $logFile = __DIR__ . '/../../storage/logs/transactions.log';

        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }

        $line = sprintf(
            "%s - Order processed via %s - User: %s - Total: %.2f\n",
            date('Y-m-d H:i:s'),
            $provider,
            $userId ?? 'Guest',
            $total
        );

        file_put_contents($logFile, $line, FILE_APPEND);
    }
}