<?php
require 'config/db.php';
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES ('Browser Test Company 2', 'browser_test2@example.com', '123', 'company')");
    $stmt->execute();
    echo "Created Browser Test Company 2";
} catch (Exception $e) {
    echo "ERROR CAUGHT: " . $e->getMessage();
}
