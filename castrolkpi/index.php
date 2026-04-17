<?php 
require 'config.php'; 
include 'header.php'; 

// MASTER QUERY
$sql = "
    SELECT 
        a.id, a.customer_name, a.customer_code, a.start_date, a.end_date, a.target_volume, a.incentive_per_liter,
        COALESCE(SUM(sl.volume), 0) as achieved
    FROM agreements a
    LEFT JOIN sales_ledger sl 
        ON a.customer_code = sl.customer_code 
        AND sl.invoice_date BETWEEN a.start_date AND a.end_date
    GROUP BY a.id, a.customer_name, a.customer_code, a.start_date, a.end_date, a.target_volume, a.incentive_per_liter
    ORDER BY a.customer_name ASC
";
$rows = $pdo->query($sql)->fetchAll();

// KPI TOTALS
$totalTarget = 0; $totalAchieved = 0; $activeCount = count($rows);
foreach($rows as $r) { 
    $totalTarget += $r['target_volume']; 
    $totalAchieved += $r['achieved']; 
}
$comp = ($totalTarget > 0) ? ($totalAchieved / $totalTarget) * 100 : 0;
?>

<div class="max-w-7xl mx-auto relative">
    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Customers</p>
            <h3 class="text-3xl font-bold text-slate-800"><?= $activeCount ?></h3>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Target</p>
            <h3 class="text-3xl font-bold text-slate-800"><?= number_format($totalTarget/1000, 1) ?>k</h3>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Achieved</p>
            <h3 class="text-3xl font-bold text-brand-600"><?= number_format($totalAchieved) ?></h3>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Global Status</p>
            <h3 class="text-3xl font-bold text-castrol-600"><?= number_format($comp, 1) ?>%</h3>
        </div>
    </div>

    <div class="glass-card rounded-xl overflow-hidden shadow-lg border border-slate-100">
        <div class="px-6 py-5 border-b border-slate-100 bg-white font-bold text-lg text-slate-800 flex justify-between items-center">
            <span>Agreement Performance Ledger</span>
            <span class="text-xs font-normal text-slate-400 bg-slate-50 px-2 py-1 rounded">QTD View</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Period</th>
                        <th class="px-6 py-4 text-center">Monthly Avg<br><span class="text-[10px] normal-case opacity-70">(Current / Required)</span></th>
                        <th class="px-6 py-4 text-right">Target</th>
                        <th class="px-6 py-4 text-center bg-yellow-50 text-yellow-800 border-x border-yellow-100">Inc.</th>
                        <th class="px-6 py-4 text-right">Achieved</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach ($rows as $row) { 
                        $achieved = $row['achieved'];
                        $target = $row['target_volume'];
                        $percent = ($target > 0) ? ($achieved / $target) * 100 : 0;
                        $isMet = $achieved >= $target;
                        
                        // Date Objects
                        $startObj = new DateTime($row['start_date']);
                        $endObj = new DateTime($row['end_date']);
                        $nowObj = new DateTime();
                        
                        // Display Dates
                        $start = $startObj->format('d M y');
                        $end = $endObj->format('d M y');
                        
                        // --- MONTHLY CALCULATION LOGIC ---
                        
                        // 1. Total Months (Agreement Duration)
                        $diffTotal = $startObj->diff($endObj);
                        $totalMonths = ($diffTotal->y * 12) + $diffTotal->m + ($diffTotal->d / 30);
                        if($totalMonths < 1) $totalMonths = 1; // Avoid divide by zero

                        // 2. Elapsed Months (Aaj tak kitne mahine hue)
                        if ($nowObj < $startObj) {
                            $elapsedMonths = 0.1; // Just started
                        } elseif ($nowObj > $endObj) {
                            $elapsedMonths = $totalMonths; // Finished
                        } else {
                            $diffElapsed = $startObj->diff($nowObj);
                            $elapsedMonths = ($diffElapsed->y * 12) + $diffElapsed->m + ($diffElapsed->d / 30);
                        }
                        if($elapsedMonths < 0.5) $elapsedMonths = 0.5; // Avoid spike in first 15 days

                        // 3. The Formulas
                        $reqMonthly = $target / $totalMonths;    // e.g. 7200 / 24 = 300
                        $curMonthly = $achieved / $elapsedMonths; // e.g. 6000 / 8 = 750
                        
                        // 4. Color Logic (Is he doing enough?)
                        $isSafeSpeed = $curMonthly >= $reqMonthly;
                        $speedColor = $isSafeSpeed ? "text-green-600" : "text-red-500";
                        
                        // Red Row Logic (Expiry)
                        $diffLeft = $nowObj->diff($endObj);
                        $daysLeft = $diffLeft->days;
                        $isExpired = $nowObj > $endObj;
                        
                        $rowClass = "hover:bg-slate-50";
                        if (($isExpired || ($daysLeft <= 30 && !$isExpired)) && !$isMet) { 
                            $rowClass = "bg-red-50 hover:bg-red-100 border-l-4 border-red-500"; 
                        }
                    ?>
                    <tr class="<?= $rowClass ?> transition duration-150">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 text-base"><?= htmlspecialchars($row['customer_name']) ?></div>
                            <div class="text-xs text-slate-400 font-mono mt-0.5"><?= $row['customer_code'] ?></div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center text-slate-600 font-medium text-xs">
                                <span><?= $start ?></span> <i class="fa-solid fa-arrow-right mx-1 text-slate-300"></i> <span><?= $end ?></span>
                            </div>
                            <?php if($isExpired && !$isMet): ?>
                                <div class="text-[10px] text-red-600 font-bold mt-1">EXPIRED</div>
                            <?php endif; ?>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-sm font-bold <?= $speedColor ?>">
                                    <?= number_format($curMonthly, 0) ?>
                                </span>
                                <span class="text-[10px] text-slate-400 border-t border-slate-200 mt-0.5 pt-0.5 px-2">
                                    Req: <?= number_format($reqMonthly, 0) ?>
                                </span>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-right font-medium text-slate-600"><?= number_format($target) ?></td>
                        
                        <td class="px-6 py-4 text-center"><span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-800 rounded font-bold border border-yellow-200 shadow-sm text-xs">₹<?= $row['incentive_per_liter'] ?></span></td>

                        <td class="px-6 py-4 text-right">
                            <button onclick="openModal('<?= $row['customer_code'] ?>', '<?= htmlspecialchars($row['customer_name']) ?>', '<?= $row['start_date'] ?>', '<?= $row['end_date'] ?>')" 
                                    class="font-bold text-blue-600 hover:text-blue-800 hover:underline text-base focus:outline-none transition">
                                <?= number_format($achieved, 2) ?>
                            </button>
                            <div class="w-24 bg-slate-200 rounded-full h-1.5 ml-auto mt-2 overflow-hidden">
                                <div class="bg-gradient-to-r from-brand-500 to-brand-400 h-1.5 rounded-full" style="width: <?= min($percent, 100) ?>%"></div>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <?php if($isMet): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800 border border-green-200">Met</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-white text-slate-500 border border-slate-200 shadow-sm">Pending</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="productModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden transform transition-all scale-95" id="modalContent">
            <div class="bg-slate-900 p-6 flex justify-between items-center text-white">
                <div>
                    <h3 class="text-xl font-bold" id="modalCustomerName">Customer Details</h3>
                    <p class="text-xs text-slate-400 mt-1">Breakdown by Product <span class="text-slate-500 mx-1">|</span> Click row for invoices</p>
                </div>
                <button onclick="closeModal()" class="text-slate-400 hover:text-white transition p-2"><i class="fa-solid fa-xmark text-2xl"></i></button>
            </div>
            <div class="p-0 max-h-[70vh] overflow-y-auto bg-slate-50">
                <div id="modalLoading" class="text-center py-12"><i class="fa-solid fa-circle-notch fa-spin text-4xl text-blue-600"></i><p class="text-slate-500 mt-4 font-medium">Loading...</p></div>
                <table class="w-full text-sm text-left hidden" id="modalTable">
                    <thead class="bg-white text-slate-500 uppercase text-xs sticky top-0 shadow-sm z-10">
                        <tr><th class="px-6 py-3 border-b">Code</th><th class="px-6 py-3 border-b">Product Name</th><th class="px-6 py-3 text-right border-b">Volume</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white" id="modalTableBody"></tbody>
                    <tfoot class="bg-slate-100 font-bold text-slate-800 border-t border-slate-200 sticky bottom-0"><tr><td colspan="2" class="px-6 py-3 text-right text-xs uppercase text-slate-500">Total</td><td class="px-6 py-3 text-right text-base text-brand-600" id="modalTotal">0.00</td></tr></tfoot>
                </table>
                <div id="modalEmpty" class="hidden text-center py-12 text-slate-400"><p>No data.</p></div>
            </div>
        </div>
    </div>

</div>

<script>
let currentCCode = ''; let currentStart = ''; let currentEnd = '';
function openModal(code, name, start, end) {
    currentCCode = code; currentStart = start; currentEnd = end;
    document.getElementById('productModal').classList.remove('hidden');
    document.getElementById('modalCustomerName').innerText = name;
    document.getElementById('modalLoading').classList.remove('hidden');
    document.getElementById('modalTable').classList.add('hidden');
    document.getElementById('modalEmpty').classList.add('hidden');
    document.getElementById('modalTableBody').innerHTML = '';

    fetch(`fetch_details.php?code=${code}&start=${start}&end=${end}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalLoading').classList.add('hidden');
            if (data.length > 0) {
                document.getElementById('modalTable').classList.remove('hidden');
                let html = ''; let total = 0;
                data.forEach((row) => {
                    let vol = parseFloat(row.total_vol); total += vol;
                    let pCodeSafe = row.product_code.replace(/[^a-zA-Z0-9]/g, ''); 
                    html += `<tr class="hover:bg-blue-50 cursor-pointer transition group" onclick="toggleInvoices('${pCodeSafe}', '${row.product_code}')">
                                <td class="px-6 py-3 font-mono text-slate-500 text-xs">${row.product_code}</td>
                                <td class="px-6 py-3 font-medium text-slate-700 group-hover:text-blue-700"><i class="fa-solid fa-chevron-right text-[10px] mr-2 text-slate-300 group-hover:text-blue-500" id="icon_${pCodeSafe}"></i>${row.display_name}</td>
                                <td class="px-6 py-3 text-right font-bold text-slate-800">${vol.toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                             </tr>
                             <tr id="details_${pCodeSafe}" class="hidden bg-slate-50"><td colspan="3" class="p-0 border-b border-slate-200 shadow-inner"><div class="px-10 py-4">
                                <div id="loader_${pCodeSafe}" class="text-xs text-slate-400 py-2">Loading...</div>
                                <table class="w-full text-xs bg-white rounded-lg border border-slate-200 hidden overflow-hidden" id="table_${pCodeSafe}"><thead class="bg-slate-100 text-slate-500 font-semibold"><tr><th class="px-4 py-2 text-left">Inv No</th><th class="px-4 py-2 text-left">Date</th><th class="px-4 py-2 text-right">Qty</th></tr></thead><tbody id="tbody_${pCodeSafe}" class="divide-y divide-slate-100 text-slate-600"></tbody></table>
                             </div></td></tr>`;
                });
                document.getElementById('modalTableBody').innerHTML = html;
                document.getElementById('modalTotal').innerText = total.toLocaleString('en-IN', {minimumFractionDigits: 2});
            } else { document.getElementById('modalEmpty').classList.remove('hidden'); }
        });
}
function toggleInvoices(safeId, realPCode) {
    const detailsRow = document.getElementById(`details_${safeId}`);
    const icon = document.getElementById(`icon_${safeId}`);
    const table = document.getElementById(`table_${safeId}`);
    const loader = document.getElementById(`loader_${safeId}`);
    const tbody = document.getElementById(`tbody_${safeId}`);
    if (!detailsRow.classList.contains('hidden')) { detailsRow.classList.add('hidden'); icon.classList.remove('rotate-90', 'text-blue-600'); return; }
    detailsRow.classList.remove('hidden'); icon.classList.add('rotate-90', 'text-blue-600');
    if (tbody.innerHTML.trim() !== '') return;
    fetch(`fetch_invoices.php?ccode=${currentCCode}&pcode=${realPCode}&start=${currentStart}&end=${currentEnd}`)
        .then(res => res.json()).then(invoices => {
            loader.classList.add('hidden'); table.classList.remove('hidden');
            let html = '';
            if(invoices.length > 0) { invoices.forEach(inv => {
                let d = new Date(inv.invoice_date); let dateStr = d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                html += `<tr class="hover:bg-slate-50"><td class="px-4 py-2 font-mono text-slate-500">${inv.invoice_no}</td><td class="px-4 py-2">${dateStr}</td><td class="px-4 py-2 text-right font-bold text-slate-800">${parseFloat(inv.volume).toLocaleString()}</td></tr>`;
            }); } else { html = `<tr><td colspan="3" class="px-4 py-2 text-center text-slate-400">No details</td></tr>`; }
            tbody.innerHTML = html;
        });
}
function closeModal() { document.getElementById('productModal').classList.add('hidden'); }
document.getElementById('productModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
</script>
</body>
</html>