<?php 
// 1. PERFORMANCE
ini_set('memory_limit', '1024M');
set_time_limit(600); 
error_reporting(E_ALL);
ini_set('display_errors', 0);

require 'config.php'; 

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header("Location: login.php"); exit; }
if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit; }

include 'header.php'; 

// --- STRICT AMERICAN DATE PARSER (MM/DD/YYYY) ---
function parse_smart_date($raw) {
    $raw = trim($raw);
    if ($raw === '' || $raw === null) return null;

    // 1. Excel Serial Number
    if (is_numeric($raw) && $raw > 20000 && $raw < 90000) {
        $unixDate = ($raw - 25569) * 86400;
        return gmdate("Y-m-d", $unixDate);
    }

    // 2. Remove Time
    $raw = explode(' ', $raw)[0];

    // 3. Normalize Separators (Convert - and . to /)
    // "1-22-2026" -> "1/22/2026"
    $clean = str_replace(['-', '.'], '/', $raw);

    // 4. *** FORCE AMERICAN FORMAT (Month/Day/Year) ***
    // Format: m/d/Y or n/j/Y (single digits allowed)
    $d = DateTime::createFromFormat('m/d/Y', $clean);
    
    // Check if valid
    if ($d && $d->format('m/d/Y') == $clean) {
        return $d->format('Y-m-d');
    }

    // Try Single Digit Month/Day (e.g. 1/7/2026)
    $d = DateTime::createFromFormat('n/j/Y', $clean);
    if ($d) {
        return $d->format('Y-m-d');
    }
    
    // Short Year Support (1/22/26)
    $d = DateTime::createFromFormat('m/d/y', $clean);
    if ($d) {
        return $d->format('Y-m-d');
    }

    // Fallback: Database Format (YYYY-MM-DD)
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
        return $raw;
    }

    return null; // Failed
}

// --- SYNC ENGINE ---
$syncMsg = "";
$debugLog = [];

if (isset($_POST['run_sync'])) {
    try {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $files = glob($uploadDir . '*.csv');
        
        if (empty($files)) { 
            $syncMsg = "<div class='bg-red-50 text-red-600 p-3 rounded font-bold'>No CSV files found in /uploads/</div>"; 
        } else {
            $pdo->beginTransaction();
            $pdo->exec("TRUNCATE TABLE sales_ledger");
            
            $totalRows = 0;
            $failedRows = 0;
            $batchSize = 1000;
            
            foreach ($files as $file) {
                if (($handle = fopen($file, "r")) !== FALSE) {
                    $header = fgetcsv($handle, 1000, ",");
                    if(isset($header[0])) $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
                    
                    // Columns
                    $colCode = -1; $colVol = -1; $colDate = -1; $colInv = -1; $colPName = -1; $colPCode = -1;
                    foreach ($header as $index => $colName) {
                        $c = trim(strtolower($colName));
                        if ($c == 'customer code') $colCode = $index;
                        if ($c == 'product volume') $colVol = $index;
                        if ($c == 'invoice date' || $c == 'billing date') $colDate = $index;
                        if ($c == 'invoice no' || $c == 'invoice number') $colInv = $index;
                        if ($c == 'product name' || $c == 'material description') $colPName = $index;
                        if ($c == 'product code' || $c == 'material code') $colPCode = $index;
                    }

                    if ($colCode !== -1 && $colVol !== -1 && $colDate !== -1) {
                        
                        $batchData = [];
                        $placeholders = [];
                        
                        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            if (!isset($row[$colCode])) continue;

                            $code = trim(strtoupper($row[$colCode]));
                            if ($code === '' && $row[$colCode] !== '0') continue;

                            $volStr = str_replace(',', '', $row[$colVol]);
                            $vol = floatval($volStr);
                            $rawDate = trim($row[$colDate]);
                            $invNo = ($colInv !== -1 && isset($row[$colInv])) ? trim($row[$colInv]) : "SYS";
                            $pName = ($colPName !== -1 && isset($row[$colPName])) ? trim($row[$colPName]) : "Product";
                            $pCode = ($colPCode !== -1 && isset($row[$colPCode])) ? trim($row[$colPCode]) : "000";

                            // USE AMERICAN PARSER
                            $finalDate = parse_smart_date($rawDate);
                            
                            // DEBUG: Capture failures specifically
                            if (!$finalDate && count($debugLog) < 10) {
                                $debugLog[] = ['raw' => $rawDate, 'conv' => 'FAILED', 'status' => 'error'];
                            } elseif ($finalDate && count($debugLog) < 10) {
                                $debugLog[] = ['raw' => $rawDate, 'conv' => $finalDate, 'status' => 'success'];
                            }

                            if ($finalDate) {
                                $batchData[] = $code;
                                $batchData[] = $finalDate;
                                $batchData[] = $invNo;
                                $batchData[] = $pCode;
                                $batchData[] = $pName;
                                $batchData[] = $vol;
                                $placeholders[] = "(?, ?, ?, ?, ?, ?)";
                                $totalRows++;

                                if (count($placeholders) >= $batchSize) {
                                    $sql = "INSERT INTO sales_ledger (customer_code, invoice_date, invoice_no, product_code, product_name, volume) VALUES " . implode(',', $placeholders);
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute($batchData);
                                    $batchData = []; $placeholders = [];
                                }
                            } else {
                                $failedRows++;
                            }
                        }
                        if (!empty($placeholders)) {
                            $sql = "INSERT INTO sales_ledger (customer_code, invoice_date, invoice_no, product_code, product_name, volume) VALUES " . implode(',', $placeholders);
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute($batchData);
                        }
                    }
                    fclose($handle);
                }
            }
            $pdo->commit();
            
            $syncMsg = "<div class='bg-green-100 text-green-800 p-4 rounded font-bold border border-green-200'>
                        <i class='fa-solid fa-check-circle'></i> Processed $totalRows rows. 
                        " . ($failedRows > 0 ? "<span class='text-red-600 block mt-1'>Warning: $failedRows dates failed. Check debugger below.</span>" : "") . "</div>";
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $syncMsg = "<div class='bg-red-50 text-red-600 p-3 rounded font-bold'>Error: " . $e->getMessage() . "</div>";
    }
}

// SAVE & DELETE
if (isset($_POST['save'])) {
    try {
        $code = trim(strtoupper($_POST['code']));
        $stmt = $pdo->prepare("INSERT INTO agreements (customer_name, customer_code, start_date, end_date, target_volume, incentive_per_liter) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['name'], $code, $_POST['start'], $_POST['end'], $_POST['target'], $_POST['incentive']]);
        $aid = $pdo->lastInsertId();
        $lines = explode("\n", $_POST['skus']);
        $sStmt = $pdo->prepare("INSERT INTO agreement_skus (agreement_id, product_name, discount_amount) VALUES (?, ?, ?)");
        foreach ($lines as $line) {
            $parts = explode(",", $line);
            if (count($parts) >= 2) $sStmt->execute([$aid, trim($parts[0]), floatval(trim($parts[1]))]);
        }
        echo "<script>alert('Saved!'); window.location='admin.php';</script>";
    } catch (Exception $e) { echo "<script>alert('Error: " . $e->getMessage() . "');</script>"; }
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM agreements WHERE id = ?")->execute([intval($_GET['delete'])]);
    echo "<script>window.location='admin.php';</script>";
}
?>

<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Admin Panel</h1>
        <a href="admin.php?logout=1" class="text-red-600 font-bold text-sm bg-red-50 px-3 py-1 rounded hover:bg-red-100">Logout</a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <div class="xl:col-span-1">
            <div class="glass-card rounded-xl p-6 shadow-lg border-t-4 border-slate-800">
                <h2 class="text-lg font-bold text-slate-800 mb-4">➕ New Agreement</h2>
                <form method="POST" class="space-y-4">
                    <input type="text" name="name" class="w-full border p-3 rounded" placeholder="Customer Name" required>
                    <input type="text" name="code" class="w-full border p-3 rounded" placeholder="Code (CS01699)" required>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="number" name="target" class="w-full border p-3 rounded" placeholder="Target" required>
                        <input type="number" step="0.01" name="incentive" class="w-full border bg-green-50 p-3 rounded font-bold text-green-700" placeholder="Incentive (₹)" required>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="text-xs font-bold text-slate-400">Start</label><input type="date" name="start" class="w-full border p-3 rounded" required></div>
                        <div><label class="text-xs font-bold text-slate-400">End</label><input type="date" name="end" class="w-full border p-3 rounded" required></div>
                    </div>
                    <textarea name="skus" rows="5" class="w-full border p-3 rounded text-sm" placeholder="Product, Discount"></textarea>
                    <button type="submit" name="save" class="w-full bg-slate-800 text-white font-bold py-3 rounded hover:bg-slate-900">Save</button>
                </form>
            </div>
        </div>

        <div class="xl:col-span-2 space-y-6">
            <div class="glass-card rounded-xl shadow-lg overflow-hidden border">
                <div class="px-6 py-4 bg-slate-50 border-b font-bold text-slate-700">Active Contracts</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-100 text-slate-500 text-xs uppercase">
                            <tr><th class="px-6 py-3">Customer</th><th class="px-6 py-3">Incentive</th><th class="px-6 py-3 text-right">Target</th><th class="px-6 py-3 text-right">Action</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php foreach ($pdo->query("SELECT * FROM agreements ORDER BY id DESC") as $row): ?>
                            <tr>
                                <td class="px-6 py-4 font-medium"><?= $row['customer_name'] ?></td>
                                <td class="px-6 py-4 font-bold text-green-600">₹<?= $row['incentive_per_liter'] ?></td>
                                <td class="px-6 py-4 text-right"><?= number_format($row['target_volume']) ?></td>
                                <td class="px-6 py-4 text-right"><a href="admin.php?delete=<?= $row['id'] ?>" class="text-red-500"><i class="fa-solid fa-trash"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card p-6 bg-slate-900 rounded-xl flex flex-col sm:flex-row justify-between items-center shadow-xl text-white">
                <div>
                    <h3 class="font-bold text-lg">American Date Sync (MM/DD/YYYY)</h3>
                    <p class="text-xs text-slate-400 mt-1">Accepts 1/22/2026 as Jan 22.</p>
                    <div class="mt-2"><?= $syncMsg ?></div>
                </div>
                <form method="POST"><button type="submit" name="run_sync" class="bg-blue-600 px-6 py-3 rounded font-bold hover:bg-blue-500">Run Sync</button></form>
            </div>

            <?php if (!empty($debugLog)): ?>
            <div class="glass-card rounded-xl p-6 border border-blue-200 bg-blue-50">
                <h3 class="font-bold text-blue-800 text-sm mb-3">🔍 Debugger: American Mode Result</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left bg-white rounded border">
                        <thead class="bg-slate-200">
                            <tr><th>Raw CSV</th><th>Converted (YYYY-MM-DD)</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($debugLog as $d): ?>
                            <tr>
                                <td class="px-4 py-2 font-mono border-b"><?= htmlspecialchars($d['raw']) ?></td>
                                <td class="px-4 py-2 font-bold border-b <?= $d['status']=='error'?'text-red-600':'text-green-600' ?>"><?= htmlspecialchars($d['conv']) ?></td>
                                <td class="px-4 py-2 border-b"><?= $d['status'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>