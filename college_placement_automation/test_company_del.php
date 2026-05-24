<?php
require 'config/db.php';
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create a dummy company
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES ('Test Company', 'testcomp@example.com', '123', 'company')");
    $stmt->execute();
    $company_id = $pdo->lastInsertId();
    echo "Created company $company_id\n";
    
    // Add a job for this company
    $stmt = $pdo->prepare("INSERT INTO jobs (title, company, company_id, description) VALUES ('Test Job', 'Test Company', ?, 'abc')");
    $stmt->execute([$company_id]);
    echo "Created job for company\n";
    
    // Now try to delete the company
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$company_id]);
    echo "Successfully deleted company $company_id\n";
    
} catch (Exception $e) {
    echo "ERROR CAUGHT: " . $e->getMessage();
}
