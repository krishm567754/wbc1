<?php 
require 'config.php'; 
include 'header.php'; 
?>
<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Product Price Search</h1>
    
    <div class="glass-card p-6 mb-8 rounded-xl shadow-md border-t-4 border-castrol-600">
        <form method="GET" class="flex gap-4">
            <div class="relative w-full">
                <i class="fa-solid fa-search absolute left-4 top-3.5 text-slate-400"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" 
                       class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg text-lg outline-none focus:ring-2 focus:ring-castrol-500 transition" 
                       placeholder="Enter Product Name (e.g. GTX, Magnatec)..." required>
            </div>
            <button type="submit" class="bg-castrol-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-castrol-700 shadow-lg transition">
                Search
            </button>
        </form>
    </div>

    <?php if (!empty($_GET['q'])): ?>
        <?php
        $search = "%" . $_GET['q'] . "%";
        $sql = "SELECT a.customer_name, a.customer_code, a.incentive_per_liter, s.product_name, s.discount_amount 
                FROM agreements a 
                LEFT JOIN agreement_skus s ON a.id = s.agreement_id AND s.product_name LIKE ? 
                ORDER BY a.customer_name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$search]);
        $results = $stmt->fetchAll();
        ?>

        <div class="glass-card rounded-xl overflow-hidden shadow-lg border border-slate-200">
            <div class="px-6 py-4 border-b bg-white flex justify-between items-center">
                <span class="font-bold text-slate-700 text-lg">Results for "<span class="text-castrol-600"><?= htmlspecialchars($_GET['q']) ?></span>"</span>
                <span class="text-xs bg-slate-100 text-slate-500 px-2 py-1 rounded"><?= count($results) ?> Matches</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Customer</th>
                            <th class="px-6 py-3">Matched Product</th>
                            <th class="px-6 py-3 text-right">Base Disc.</th>
                            <th class="px-6 py-3 text-right bg-yellow-50 text-yellow-700 border-x border-yellow-100">Incentive</th>
                            <th class="px-6 py-3 text-right">Total Benefit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php foreach ($results as $r): 
                            $isMatch = !empty($r['product_name']);
                            $productName = $isMatch ? $r['product_name'] : "Standard (Incentive Only)";
                            $baseDisc = $isMatch ? $r['discount_amount'] : 0;
                            $incentive = $r['incentive_per_liter'];
                            $total = $baseDisc + $incentive;
                            $productClass = $isMatch ? "text-slate-800 font-bold" : "text-slate-400 italic";
                            $icon = $isMatch ? "<i class='fa-solid fa-tag text-blue-500 mr-1'></i>" : "";
                        ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900"><?= htmlspecialchars($r['customer_name']) ?></div>
                                <div class="text-xs text-slate-400 font-mono"><?= $r['customer_code'] ?></div>
                            </td>
                            <td class="px-6 py-4 <?= $productClass ?>">
                                <?= $icon . htmlspecialchars($productName) ?>
                            </td>
                            <td class="px-6 py-4 text-right text-slate-600">₹<?= number_format($baseDisc, 2) ?></td>
                            <td class="px-6 py-4 text-right font-bold text-brand-600 bg-yellow-50 border-x border-yellow-100">₹<?= number_format($incentive, 2) ?></td>
                            <td class="px-6 py-4 text-right">
                                <span class="bg-slate-800 text-white px-3 py-1 rounded-lg font-bold shadow-sm">
                                    ₹<?= number_format($total, 2) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if(count($results) == 0): ?>
                <div class="p-10 text-center text-slate-400"><p>No active agreements found.</p></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>