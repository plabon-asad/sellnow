<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoload

use SellNow\Config\Database;

try {
    echo "--------------------------------------\n";
    echo "--- Starting Migration ---\n";
    echo "--------------------------------------\n";
    $db = Database::getInstance()->getConnection();

    $sqlPath = __DIR__ . '/../database/schema.sql';
    if (!file_exists($sqlPath)) {
        throw new Exception("Migration file not found.");
    }

    $sql = file_get_contents($sqlPath);

    // transaction begin
    $db->beginTransaction();
    $db->exec($sql);
    $db->commit();

    echo "✅ Migration completed successfully.\n";
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}