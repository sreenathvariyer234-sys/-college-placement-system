<?php
// config/db.php
$host = '127.0.0.1';
$dbname = 'college_placement';
$username = 'root'; // Default XAMPP/LAMPP user
$password = '';     // Default XAMPP/LAMPP password (may need adjustment)

date_default_timezone_set('Asia/Kolkata');

try {

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_TIMEOUT, 5); // 5 seconds timeout
} catch (PDOException $e) {
    error_log("Database Connection Failed: " . $e->getMessage());
    die("Service unavailable. Please try again later.");
}
?>
