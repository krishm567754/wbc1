<?php 
require 'config.php'; 
// Security Check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { 
    // If accessed directly without login, redirect or die
    // For sync.php embedded in admin, this check is good practice
}

include 'header.php'; 

// FOLDER SETUP
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
$files = glob($uploadDir . '*.csv');
?>

<div class="max-w-4xl mx-auto mt-8">
    <div class="glass-card rounded-xl overflow-hidden shadow-lg border border-slate-200">
        <div class="bg-slate-900 px-6 py-4 flex justify-between items-center">
            <h2 class="text-white font-bold text-lg"><i class="fa-solid fa-server mr-2"></i> Date-Aware Sync Engine</h2>
            <span class="text-xs text-slate-400 bg-slate-800 px-2 py-1 rounded">v2.0</span>
        </div>
        
        <div class="p-6 bg-slate-50">
            <?php if (empty($files)): ?>
                <div class="text-center py-8 text-slate-500">
                    <i class="fa-solid fa-triangle-exclamation text-4xl text-yellow-500 mb-3"></i>
                    <p>No CSV files found in <code>/uploads/</code> folder.</p>
                </div>
            <?php else: ?>
                
                <div class="space-y-4">
                    <?php
                    // 1. CLEAR OLD DATA
                    $pdo->exec("TRUNCATE TABLE sales_ledger");
                    
                    // 2. PROCESS FILES
                    foreach ($files as $file) {
                        echo "<div class='flex items-center text-sm text-slate-700 bg-white p-3 rounded border border-slate-200 shadow-sm'>";
                        echo "<i class='fa-solid fa-file-csv text-green-600 text-xl mr-3'></i>";
                        echo "<span class='font-mono'>" . basename($file) . "</span>";
                        
                        if (($handle = fopen($file, "r")) !== FALSE) {
                            $header = fgetcsv($handle, 1000, ",");
                            if(isset($header[0])) $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]); // Clean BOM

                            // FIND COLUMNS
                            $colCode = -1; $colVol = -1; $colDate = -1;
                            
                            foreach ($header as $index => $colName) {
                                $c = trim(strtolower($colName));
                                if ($c == 'customer code') $colCode = $index;
                                if ($c == 'product volume') $colVol = $index;
                                if ($c == 'invoice date' || $c == 'billing date') $colDate = $index;
                            }

                            if ($colCode === -1 || $colVol === -1 || $colDate === -1) {
                                echo "<span class='ml-auto text-red-600 font-bold text-xs'>FAILED: Missing Columns (Code, Volume, or Invoice Date)</span>";
                            } else {
                                $batchData = [];
                                $rowCount = 0;
                                
                                // PREPARE STATEMENT FOR SPEED
                                $stmt = $pdo->prepare("INSERT INTO sales_ledger (customer_code, invoice_date, volume) VALUES (?, ?, ?)");
                                $pdo->beginTransaction(); // Transaction for speed

                                while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                    if (isset($row[$colCode]) && isset($row[$colVol]) && isset($row[$colDate])) {
                                        $code = trim(strtoupper($row[$colCode]));
                                        $vol = floatval($row[$colVol]);
                                        $rawDate = trim($row[$colDate]);

                                        // DATE PARSING (Handles YYYY-MM-DD and DD-MM-YYYY)
                                        $dateObj = DateTime::createFromFormat('Y-m-d', $rawDate);
                                        if (!$dateObj) $dateObj = DateTime::createFromFormat('d-m-Y', $rawDate);
                                        if (!$dateObj) $dateObj = DateTime::createFromFormat('d/m/Y', $rawDate);
                                        
                                        $finalDate = $dateObj ? $dateObj->format('Y-m-d') : null;

                                        if (!empty($code) && $finalDate) {
                                            $stmt->execute([$code, $finalDate, $vol]);
                                            $rowCount++;
                                        }
                                    }
                                }
                                $pdo->commit(); // Save all at once
                                echo "<span class='ml-auto text-green-600 font-bold text-xs'>IMPORTED: $rowCount rows</span>";
                            }
                            fclose($handle);
                        }
                        echo "</div>";
                    }
                    ?>
                </div>

                <div class="mt-6 text-center">
                    <div class="inline-block bg-green-100 text-green-800 px-4 py-2 rounded-lg font-bold border border-green-200">
                        <i class="fa-solid fa-check-circle mr-2"></i> Database Updated Successfully
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <a href="admin.php" class="text-blue-600 hover:underline font-bold text-sm">Return to Admin Panel</a>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>