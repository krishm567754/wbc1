<?php
require 'config.php';
header('Content-Type: application/json');

if (isset($_GET['ccode']) && isset($_GET['pcode']) && isset($_GET['start']) && isset($_GET['end'])) {
    
    // Fetch Invoices for specific Product & Customer in Date Range
    $sql = "SELECT invoice_no, invoice_date, volume 
            FROM sales_ledger 
            WHERE customer_code = ? 
            AND product_code = ? 
            AND invoice_date BETWEEN ? AND ? 
            ORDER BY invoice_date DESC";
            
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_GET['ccode'], $_GET['pcode'], $_GET['start'], $_GET['end']]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode([]);
}
?>