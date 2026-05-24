<?php
require 'config/db.php';
try {
    $stmt = $pdo->query("SELECT * FROM users WHERE role='company' LIMIT 1");
    $c = $stmt->fetch();
    if ($c) {
        echo "Found company: " . $c['id'] . "\n";
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Let's first test if there's a constraint by deleting
        $stmt2 = $pdo->prepare("DELETE FROM users WHERE id=?");
        $stmt2->execute([$c['id']]);
        echo "Successfully Deleted!";
    } else {
        echo "No companies found in DB.";
    }
} catch (Exception $e) {
    echo "ERROR CAUGHT: " . $e->getMessage();
}
