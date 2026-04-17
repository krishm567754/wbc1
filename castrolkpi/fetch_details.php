<?php
require 'config.php';

// Data return as JSON
header('Content-Type: application/json');

if (isset($_GET['code']) && isset($_GET['start']) && isset($_GET['end'])) {
    $code = $_GET['code'];
    $start = $_GET['start'];
    $end = $_GET['end'];

    // LOGIC UPDATE: 
    // 1. Group By product_code (Accurate Math)
    // 2. MAX(product_name) (Picks one name if multiple exist for same code)
    $sql = "SELECT 
                product_code, 
                MAX(product_name) as display_name, 
                SUM(volume) as total_vol 
            FROM sales_ledger 
            WHERE customer_code = ? 
            AND invoice_date BETWEEN ? AND ? 
            GROUP BY product_code 
            ORDER BY total_vol DESC";
            
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$code, $start, $end]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode([]);
}
?>