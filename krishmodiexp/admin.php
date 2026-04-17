<?php session_start(); if(!isset($_SESSION['admin'])) header("Location: login.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel | Shri Laxmi Auto Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        :root { --c-green: #00824D; }
        body { background: #f4f7f6; }
        .bg-castrol { background: var(--c-green); color: white; }
        .filter-card { height: 160px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff; border-radius: 8px; font-size: 0.75rem; }
        .card { border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.08); border-radius: 12px; margin-bottom: 15px; }
        label { font-weight: 700; color: #444; font-size: 0.8rem; margin-bottom: 4px; display: block; }
        .sel-all { font-size: 0.7rem; color: var(--c-green); cursor: pointer; text-decoration: underline; }
        .qtr-bg { background: #f0fdf4; border: 1px dashed var(--c-green); padding: 15px; border-radius: 10px; }
        .tier-sel-box { background: #fff; border: 1px solid #ced4da; border-radius: 5px; padding: 10px; min-height: 40px; }
        .scheme-card { border-left: 4px solid var(--c-green) !important; }
    </style>
</head>
<body class="pb-5">

<nav class="navbar bg-castrol mb-3 shadow py-2">
    <div class="container">
        <span class="navbar-brand fw-bold text-white mx-auto fs-6">SHRI LAXMI — ADMIN PANEL</span>
    </div>
</nav>

<div class="container">

    <!-- ── New Scheme Form ── -->
    <div class="card p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <label>Sample Excel (for filters)</label>
                <input type="file" id="xlsxIn" class="form-control form-control-sm" accept=".xlsx,.xls">
            </div>
            <div class="col-md-3">
                <label>Scheme Name</label>
                <input type="text" id="sName" class="form-control form-control-sm" placeholder="e.g. March 2026 Growth">
            </div>
            <div class="col-md-3">
                <label>Mode</label>
                <select id="sMode" class="form-select form-select-sm" onchange="toggleUI(this.value)">
                    <option value="standard">Standard (Cycle)</option>
                    <option value="growth">Growth & Slab (Monthly)</option>
                    <option value="quarterly">Quarterly Slab & Growth</option>
                    <option value="level_growth">Level Growth (L1, L2...)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label id="valLabel">Cycle Target (Ltrs)</label>
                <input type="number" id="mainVal" class="form-control form-control-sm" value="20">
            </div>
        </div>
    </div>

    <!-- ── Filters ── -->
    <div class="row g-2 mb-3">
        <div class="col-md-3">
            <label>DOC TYPE <span class="sel-all" onclick="toggleAll('d-chk')">(Toggle All)</span></label>
            <div id="docBox" class="filter-card"><small class="text-muted">Load Excel first</small></div>
        </div>
        <div class="col-md-3">
            <label>SECTOR <span class="sel-all" onclick="toggleAll('s-chk')">(Toggle All)</span></label>
            <div id="secBox" class="filter-card"><small class="text-muted">Load Excel first</small></div>
        </div>
        <div class="col-md-3">
            <label>BRAND <span class="sel-all" onclick="toggleAll('b-chk')">(Toggle All)</span></label>
            <div id="brdBox" class="filter-card"><small class="text-muted">Load Excel first</small></div>
        </div>
        <div class="col-md-3">
            <label>PRODUCT <span class="sel-all" onclick="toggleAll('p-chk')">(Toggle All)</span></label>
            <div id="prdBox" class="filter-card"><small class="text-muted">Select brands first</small></div>
        </div>
    </div>

    <!-- ── Scheme Config ── -->
    <div class="card p-3">
        <div class="row g-2">
            <div class="col-md-3">
                <label>Start Date</label>
                <input type="date" id="dateS" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label>End Date</label>
                <input type="date" id="dateE" class="form-control form-control-sm">
            </div>

            <!-- Standard only -->
            <div class="col-md-3 std-only">
                <label>Cycle Type</label>
                <select id="cType" class="form-select form-select-sm" onchange="document.getElementById('capDiv').style.display=this.value==='capped'?'block':'none'">
                    <option value="none">Fixed</option>
                    <option value="unlimited">Unlimited</option>
                    <option value="capped">Capped</option>
                </select>
            </div>
            <div class="col-md-3 std-only" id="capDiv" style="display:none;">
                <label>Max Cycles</label>
                <input type="number" id="maxC" class="form-control form-control-sm" value="1">
            </div>

            <!-- Growth / Level shared -->
            <div class="col-md-3 gro-only lvl-only" style="display:none;">
                <label>Min Target (Ltrs)</label>
                <input type="number" id="minT" class="form-control form-control-sm" value="250">
            </div>
            <div class="col-md-6 lvl-only" style="display:none;">
                <label>Level Slabs (e.g. 500, 1000, 2000)</label>
                <input type="text" id="lvlSlabs" class="form-control form-control-sm" placeholder="500, 1000, 2000">
            </div>
        </div>

        <!-- Growth extra options -->
        <div class="row g-2 mt-2 gro-only" style="display:none;">
            <div class="col-md-4">
                <label>Base Type</label>
                <select id="baseType" class="form-select form-select-sm">
                    <option value="default">Strict LY (same period last year)</option>
                    <option value="fallback">Fallback (if LY=0, use prev month)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Eligible if Base is 0?</label>
                <select id="noBase" class="form-select form-select-sm">
                    <option value="no">No</option>
                    <option value="yes">Yes</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Milestone %</label>
                <input type="number" id="msPer" class="form-control form-control-sm" value="40">
            </div>
            <div class="col-md-2">
                <label>Milestone Day</label>
                <input type="number" id="msDate" class="form-control form-control-sm" value="14">
            </div>
        </div>

        <!-- Quarterly extra options -->
        <div class="row g-2 mt-2 qtr-only qtr-bg" style="display:none;">
            <div class="col-md-4">
                <label class="text-success">Slabs (Comma Separated, Ltrs)</label>
                <input type="text" id="qSlabs" class="form-control form-control-sm" placeholder="750, 1500, 3000" oninput="generateTierChecks()">
            </div>
            <div class="col-md-8">
                <label>Growth Qualifier Applicable on Tiers:</label>
                <div id="tierSelection" class="tier-sel-box d-flex flex-wrap gap-3">
                    <small class="text-muted">Enter slabs first...</small>
                </div>
            </div>
            <div class="col-md-2 mt-2">
                <label>Qualifier %</label>
                <input type="number" id="qGro" class="form-control form-control-sm" value="15">
            </div>
            <div class="col-md-2 mt-2">
                <label>Base Type</label>
                <select id="qBaseType" class="form-select form-select-sm">
                    <option value="strict">Strict LY</option>
                    <option value="both">Fallback (LY=0 → use LM)</option>
                </select>
            </div>
            <div class="col-md-2 mt-2">
                <label>Annual Target %</label>
                <input type="number" id="qAnn" class="form-control form-control-sm" value="25">
            </div>
            <div class="col-md-3 mt-2">
                <label>Growth Mandatory?</label>
                <select id="qMand" class="form-select form-select-sm">
                    <option value="no">No</option>
                    <option value="yes">Yes</option>
                </select>
            </div>
            <div class="col-md-3 mt-2">
                <label>Eligible if Base = 0?</label>
                <select id="qElig" class="form-select form-select-sm">
                    <option value="no">No</option>
                    <option value="yes">Yes</option>
                </select>
            </div>
        </div>

        <!-- Customer codes -->
        <div class="mt-3">
            <label>Customer Codes (leave blank = all)</label>
            <textarea id="codes" class="form-control form-control-sm" rows="2" placeholder="CS01001, CS01002, CS01003 ..."></textarea>
        </div>
    </div>

    <div class="text-center mt-3">
        <button onclick="saveScheme()" class="btn btn-success btn-lg px-5 shadow fw-bold">
            <i class="bi bi-save me-2"></i>SAVE SCHEME
        </button>
    </div>

    <!-- ── Existing Schemes ── -->
    <div class="mt-4">
        <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size:0.75rem;">Saved Schemes</h6>
        <div id="sList" class="row g-2"></div>
    </div>
</div>

<script>
let excelData = [];

// ── Tier checkboxes for quarterly ──────────────────────────────────────────
function generateTierChecks() {
    const raw   = document.getElementById('qSlabs').value;
    const slabs = raw.split(',').map(n => n.trim()).filter(n => n !== '' && !isNaN(n)).map(Number);
    const box   = document.getElementById('tierSelection');
    if (!slabs.length) { box.innerHTML = '<small class="text-muted">Enter slabs first...</small>'; return; }

    box.innerHTML = slabs.map((s, i) => {
        const label = i < slabs.length - 1 ? `${s}–${slabs[i+1]}` : `${s}+`;
        return `<div class="form-check">
            <input class="form-check-input q-tier-chk" type="checkbox" value="${i}" id="qt${i}">
            <label class="form-check-label small" for="qt${i}">${label}</label>
        </div>`;
    }).join('');
}

// ── Show/hide mode-specific fields ─────────────────────────────────────────
function toggleUI(v) {
    const lbl = { standard: 'Cycle Target (Ltrs)', quarterly: 'Qualifier Growth %', growth: 'Growth %', level_growth: 'Growth %' };
    document.getElementById('valLabel').innerText = lbl[v] || 'Value';

    document.querySelectorAll('.std-only').forEach(e => e.style.display = v === 'standard'      ? '' : 'none');
    document.querySelectorAll('.gro-only').forEach(e => e.style.display = v === 'growth'        ? '' : 'none');
    document.querySelectorAll('.qtr-only').forEach(e => e.style.display = v === 'quarterly'     ? '' : 'none');
    document.querySelectorAll('.lvl-only').forEach(e => e.style.display = v === 'level_growth'  ? '' : 'none');

    // Min target shown for both growth and level_growth
    document.querySelectorAll('.gro-only.lvl-only').forEach(e => {
        e.style.display = (v === 'growth' || v === 'level_growth') ? '' : 'none';
    });
}

function toggleAll(cls) {
    const boxes = document.querySelectorAll('.' + cls);
    const allChecked = Array.from(boxes).every(b => b.checked);
    boxes.forEach(b => { b.checked = !allChecked; });
    if (cls === 'b-chk') updateProducts();
}

// ── Load Excel for filter population ──────────────────────────────────────
document.getElementById('xlsxIn').onchange = function(e) {
    const reader = new FileReader();
    reader.onload = ev => {
        const wb = XLSX.read(new Uint8Array(ev.target.result), { type: 'array' });
        excelData = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { defval: '', raw: false });
        fillFilter('docBox', 'Document Type',       'd-chk');
        fillFilter('secBox', 'Sub-sector Detail Name', 's-chk');
        fillFilter('brdBox', 'Product Brand Name',  'b-chk');
    };
    reader.readAsArrayBuffer(e.target.files[0]);
};

function fillFilter(boxId, colKey, cls) {
    const vals = [...new Set(excelData.map(r => (r[colKey] || '').toString().trim()))].filter(Boolean).sort();
    document.getElementById(boxId).innerHTML = vals.map(v =>
        `<div class="form-check small border-bottom py-1">
            <input class="form-check-input ${cls}" type="checkbox" value="${v}" onchange="${cls === 'b-chk' ? 'updateProducts()' : ''}">
            <label class="form-check-label">${v}</label>
        </div>`
    ).join('');
}

function updateProducts() {
    const selectedBrands = Array.from(document.querySelectorAll('.b-chk:checked')).map(c => c.value);
    const prods = [...new Set(
        excelData
            .filter(r => selectedBrands.includes((r['Product Brand Name'] || '').toString().trim()))
            .map(r => (r['Product Name'] || '').toString().trim())
    )].filter(Boolean).sort();
    document.getElementById('prdBox').innerHTML = prods.map(p =>
        `<div class="form-check small border-bottom py-1">
            <input class="form-check-input p-chk" type="checkbox" value="${p}">
            <label class="form-check-label">${p}</label>
        </div>`
    ).join('');
}

// ── Save Scheme ────────────────────────────────────────────────────────────
async function saveScheme() {
    const name = document.getElementById('sName').value.trim();
    if (!name) { alert('Enter a scheme name first.'); return; }

    const mode = document.getElementById('sMode').value;
    const dateS = document.getElementById('dateS').value;
    const dateE = document.getElementById('dateE').value;
    if (!dateS || !dateE) { alert('Set start and end dates.'); return; }
    if (dateS > dateE) { alert('End date must be after start date.'); return; }

    const scheme = {
        id:    Date.now(),
        name,  mode,
        start: dateS,
        end:   dateE,
        docs:  Array.from(document.querySelectorAll('.d-chk:checked')).map(c => c.value),
        secs:  Array.from(document.querySelectorAll('.s-chk:checked')).map(c => c.value),
        brds:  Array.from(document.querySelectorAll('.b-chk:checked')).map(c => c.value),
        prods: Array.from(document.querySelectorAll('.p-chk:checked')).map(c => c.value),
        codes: document.getElementById('codes').value.split(/[\n,]/).map(c => c.trim()).filter(Boolean)
    };

    if (mode === 'standard') {
        scheme.target = parseFloat(document.getElementById('mainVal').value) || 0;
        scheme.type   = document.getElementById('cType').value;
        scheme.max    = parseInt(document.getElementById('maxC').value) || 1;

    } else if (mode === 'growth') {
        scheme.growth     = parseFloat(document.getElementById('mainVal').value) || 0;
        scheme.min        = parseFloat(document.getElementById('minT').value) || 0;
        scheme.base_type  = document.getElementById('baseType').value;
        scheme.noBase     = document.getElementById('noBase').value;
        scheme.ms_per     = parseFloat(document.getElementById('msPer').value) || 0;
        scheme.ms_date    = parseInt(document.getElementById('msDate').value) || 0;

    } else if (mode === 'level_growth') {
        scheme.growth = parseFloat(document.getElementById('mainVal').value) || 0;
        scheme.min    = parseFloat(document.getElementById('minT').value) || 0;
        scheme.lvls   = document.getElementById('lvlSlabs').value
            .split(',').map(n => parseFloat(n.trim())).filter(x => !isNaN(x)).sort((a, b) => a - b);

    } else if (mode === 'quarterly') {
        scheme.qSlabs    = document.getElementById('qSlabs').value
            .split(',').map(n => parseFloat(n.trim())).filter(x => !isNaN(x));
        scheme.qGro      = parseFloat(document.getElementById('qGro').value) || 0;
        scheme.qBaseType = document.getElementById('qBaseType').value;
        scheme.qAnn      = parseFloat(document.getElementById('qAnn').value) || 0;
        scheme.qGroTiers = Array.from(document.querySelectorAll('.q-tier-chk:checked')).map(c => parseInt(c.value));
        scheme.qMand     = document.getElementById('qMand').value;
        scheme.qElig     = document.getElementById('qElig').value;
    }

    try {
        // Load existing schemes
        let existing = [];
        const res = await fetch('schemes_db.json?nc=' + Date.now());
        if (res.ok) {
            const txt = await res.text();
            if (txt.trim()) existing = JSON.parse(txt);
        }

        existing.push(scheme);

        // Save to server
        const saveRes = await fetch('save_scheme.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(existing)
        });

        if (!saveRes.ok) throw new Error('Server returned ' + saveRes.status);
        const saveJson = await saveRes.json();
        if (saveJson.status !== 'success') throw new Error(saveJson.msg || 'Unknown error');

        alert('✅ Scheme saved successfully!\n\nGo to dashboard and tap the refresh button to sync data.');
        clearForm();
        renderList();

    } catch(err) {
        alert('❌ Save failed: ' + err.message + '\n\nCheck that save_scheme.php exists on server and has write permission.');
    }
}

function clearForm() {
    document.getElementById('sName').value = '';
    document.getElementById('dateS').value = '';
    document.getElementById('dateE').value = '';
    document.getElementById('codes').value = '';
    document.querySelectorAll('.d-chk, .s-chk, .b-chk, .p-chk, .q-tier-chk').forEach(c => c.checked = false);
}

// ── Scheme List ────────────────────────────────────────────────────────────
async function renderList() {
    try {
        const res = await fetch('schemes_db.json?nc=' + Date.now());
        if (!res.ok) { document.getElementById('sList').innerHTML = '<p class="text-muted small">No schemes file found.</p>'; return; }
        const list = await res.json();
        if (!list.length) { document.getElementById('sList').innerHTML = '<p class="text-muted small">No schemes saved yet.</p>'; return; }

        const modeLabel = { standard: 'Cycle', growth: 'Growth', quarterly: 'Quarterly', level_growth: 'Level Growth' };

        document.getElementById('sList').innerHTML = list.map(s => `
            <div class="col-md-4">
                <div class="card p-2 border scheme-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold small">${s.name}</div>
                            <div class="text-muted" style="font-size:0.65rem;">${modeLabel[s.mode] || s.mode} &nbsp;|&nbsp; ${s.start || '—'} → ${s.end || '—'}</div>
                        </div>
                        <button class="btn btn-sm btn-outline-danger border-0 py-0 px-2" onclick="deleteScheme(${s.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>`
        ).join('');
    } catch(e) {
        document.getElementById('sList').innerHTML = '<p class="text-danger small">Could not load schemes list.</p>';
    }
}

async function deleteScheme(id) {
    if (!confirm('Delete this scheme?')) return;
    try {
        const res  = await fetch('schemes_db.json?nc=' + Date.now());
        const list = await res.json();
        const newList = list.filter(s => s.id !== id);
        const saveRes = await fetch('save_scheme.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(newList)
        });
        const json = await saveRes.json();
        if (json.status !== 'success') throw new Error(json.msg);
        renderList();
    } catch(e) {
        alert('Delete failed: ' + e.message);
    }
}

// Init
toggleUI('standard');
renderList();
</script>
</body>
</html>
