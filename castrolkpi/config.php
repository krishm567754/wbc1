<?php
// 1. FORCE ERROR REPORTING (Blank Page Fix)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// 2. DATABASE CREDENTIALS
$host = 'sql110.infinityfree.com';
$db   = 'if0_40787300_castrol';
$user = 'if0_40787300';
$pass = 'WXaaneTIHG';

// 3. ADMIN PASSWORD
define('ADMIN_PASS', 'admin123'); 

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("<h2 style='color:red'>Database Connection Failed</h2><p>" . $e->getMessage() . "</p>");
}
?>