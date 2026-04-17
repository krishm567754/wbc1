<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Shri Laxmi Auto Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        :root { --c-green: #00824D; --c-gold: #f9da00; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; padding-bottom: 80px; }
        .bg-castrol { background: var(--c-green); color: white; border-bottom: 5px solid var(--c-gold); padding: 15px; }
        .exec-card { border-radius: 15px; background: white; border-left: 6px solid var(--c-green); box-shadow: 0 4px 10px rgba(0,0,0,0.05); cursor: pointer; border: none; transition: 0.2s; }
        .exec-card:active { transform: scale(0.97); }
        .status-pill { font-size: 0.62rem; padding: 4px 10px; border-radius: 20px; font-weight: 700; text-transform: uppercase; display: inline-block; white-space: nowrap; }
        .badge-red    { background: #fee2e2; color: #b91c1c; }
        .badge-green  { background: #dcfce7; color: #15803d; }
        .badge-blue   { background: #e0f2fe; color: #0369a1; }
        .badge-orange { background: #fff7ed; color: #c2410c; }
        .badge-gray   { background: #f3f4f6; color: #6b7280; }
        .info-box { font-size: 0.63rem; color: #555; background: #f8f9fa; padding: 6px 8px; border-radius: 8px; border: 1px solid #eee; margin-top: 4px; line-height: 1.6; }
        .refresh-btn { position: fixed; bottom: 20px; right: 20px; width: 58px; height: 58px; border-radius: 50%; box-shadow: 0 5px 20px rgba(0,0,0,0.3); z-index: 1000; border: none; display: flex; align-items: center; justify-content: center; }
        .spin { animation: rotate 0.9s linear infinite; }
        @keyframes rotate { 100% { transform: rotate(360deg); } }
        .sticky-thead th { position: sticky; top: 0; background: #f8f9fa; z-index: 2; font-size: 0.7rem; padding: 8px 6px; border-bottom: 2px solid #dee2e6; }
        td { font-size: 0.72rem; vertical-align: middle; }
        .shop-name { font-weight: 700; text-transform: uppercase; font-size: 0.65rem; color: #1e293b; }
        .ach-val { font-weight: 800; color: #1e293b; font-size: 0.8rem; }
        .bal-val { font-weight: 700; font-size: 0.72rem; }
        .toast-msg { position: fixed; bottom: 90px; left: 50%; transform: translateX(-50%); background: #1e293b; color: white; padding: 8px 20px; border-radius: 20px; font-size: 0.75rem; z-index: 9999; display: none; }
        .exec-name { font-weight: 700; font-size: 0.82rem; color: #1e293b; }
        .exec-sub  { font-size: 0.65rem; color: #64748b; margin-top: 2px; }
    </style>
</head>
<body>

<nav class="bg-castrol shadow-sm mb-4">
    <div class="container py-1">
        <div class="fw-bold h5 mb-0 text-white text-uppercase">Shri Laxmi Auto Store</div>
        <div id="syncStatus" style="font-size:0.6rem; color:rgba(255,255,255,0.75)">Ready</div>
    </div>
</nav>

<div class="container">
    <div class="card p-3 mb-4 border-0 shadow-sm rounded-4">
        <label class="small fw-bold text-success text-uppercase mb-1" style="font-size:0.65rem;">Active Scheme</label>
        <select id="schemeSelect" class="form-select border-0 bg-light fw-bold shadow-none" onchange="switchScheme(this.value)"></select>
    </div>

    <div id="mainGrid" class="row g-2">
        <div class="text-center py-5">
            <div class="spinner-border text-success"></div>
            <p class="mt-2 text-muted fw-bold small">Loading data...</p>
        </div>
    </div>

    <div id="detailView" style="display:none;">
        <div class="d-flex justify-content-between align-items-center mb-3 px-1">
            <div>
                <h6 id="exTitle" class="mb-0 fw-bold text-success"></h6>
                <div id="exSubtitle" class="text-muted" style="font-size:0.65rem;"></div>
            </div>
            <button class="btn btn-dark rounded-pill px-4 btn-sm" onclick="goBack()">← BACK</button>
        </div>
        <div class="bg-white rounded-4 shadow-sm border-0 overflow-hidden">
            <div class="p-2 border-bottom bg-light">
                <input type="text" id="shopSearch" class="form-control border-0 bg-transparent shadow-none" placeholder="🔍 Search shop..." onkeyup="doSearch()">
            </div>
            <div class="table-responsive" style="max-height: 72vh;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="sticky-thead text-center" id="tHead"></thead>
                    <tbody id="mBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<button class="btn btn-success refresh-btn" onclick="forceSync()" title="Sync Data">
    <i class="bi bi-arrow-clockwise fs-2" id="syncIcon"></i>
</button>
<div class="toast-msg" id="toastMsg"></div>

<script>
let masterSchemes = [], processedData = {}, curGroup = {}, curSchemeId = null;
const _fileCache = new Map();

function clean(v) {
    if (v === null || v === undefined) return '';
    return v.toString().replace(/[^a-z0-9]/gi, '').toUpperCase().trim();
}

function getCol(row, target) {
    const ct = clean(target);
    const k  = Object.keys(row).find(key => clean(key) === ct);
    return k !== undefined ? row[k] : null;
}

// ─── MATCH FUNCTION ───────────────────────────────────────────────────────────
// All filters use EXACT match after clean() on both sides.
// Admin saves exact values from Excel dropdowns; data has same exact values.
// Same function is used for sales rows AND base map rows → always consistent.
// This means base volume and sales volume are filtered identically → correct targets.
function buildMatchFn(scheme) {
    const fDocs  = (scheme.docs  || []).map(clean);  // Document Type
    const fSecs  = (scheme.secs  || []).map(clean);  // Sub-sector Detail Name
    const fBrds  = (scheme.brds  || []).map(clean);  // Product Brand Name
    const fProds = (scheme.prods || []).map(clean);  // Product Name

    return function(row) {
        const d   = clean(getCol(row, 'Document Type'));
        const sec = clean(getCol(row, 'Sub-sector Detail Name'));
        const b   = clean(getCol(row, 'Product Brand Name'));
        const p   = clean(getCol(row, 'Product Name'));

        // Document Type — exact match (if filter set)
        if (fDocs.length > 0 && !fDocs.includes(d)) return false;

        // Sub-sector Detail Name — exact match (if filter set)
        if (fSecs.length > 0 && !fSecs.includes(sec)) return false;

        // If no brand/product filter set → pass everything remaining
        if (fBrds.length === 0 && fProds.length === 0) return true;

        // Product filter takes priority when set (more specific than brand)
        if (fProds.length > 0) return fProds.includes(p);

        // Brand filter — exact match on Product Brand Name
        return fBrds.includes(b);
    };
}

function showToast(msg, ms = 2500) {
    const t = document.getElementById('toastMsg');
    t.innerText = msg; t.style.display = 'block';
    setTimeout(() => { t.style.display = 'none'; }, ms);
}
function setSyncStatus(msg) { document.getElementById('syncStatus').innerText = msg; }

async function loadRows(path) {
    if (_fileCache.has(path)) return _fileCache.get(path);
    try {
        const res = await fetch(`${path}?v=${Date.now()}`, { cache: 'no-store' });
        if (!res.ok) { _fileCache.set(path, null); return null; }
        const ab  = await res.arrayBuffer();
        const wb  = XLSX.read(ab, { type: 'array', cellDates: true });
        const data = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { defval: null });
        _fileCache.set(path, data);
        return data;
    } catch (e) { _fileCache.set(path, null); return null; }
}

function monthPath(ym) { return `monthly_data/${ym.replace('-', '_')}.xlsx`; }

function schemeMonths(start, end) {
    const months = [];
    if(!start || !end) return months;
    let [sy, sm] = start.split('-').map(Number);
    const [ey, em] = end.split('-').map(Number);
    while (sy < ey || (sy === ey && sm <= em)) {
        months.push(`${sy}-${String(sm).padStart(2, '0')}`);
        if (++sm > 12) { sm = 1; sy++; }
    }
    return months;
}

async function buildBaseMap(ymList, matchFn) {
    const map = new Map();
    for (const ym of ymList) {
        const rows = await loadRows(monthPath(ym));
        if (!rows) continue;
        rows.forEach(row => {
            const code = clean(getCol(row, 'Customer Code'));
            if (!code || !matchFn(row)) return;
            const vol = parseFloat(getCol(row, 'Product Volume')) || 0;
            map.set(code, (map.get(code) || 0) + vol);
        });
    }
    return map;
}

// ─── ENGINE ───────────────────────────────────────────────────────────────────
async function engine(scheme, salesRows) {
    const matchFn   = buildMatchFn(scheme);
    const fCodes    = (scheme.codes || []).map(clean);
    const curMonths = schemeMonths(scheme.start, scheme.end);

    const lyMonths = curMonths.map(ym => {
        const [y, m] = ym.split('-').map(Number);
        return `${y-1}-${String(m).padStart(2, '0')}`;
    });
    const pmMonths = curMonths.map(ym => {
        let [y, m] = ym.split('-').map(Number);
        m -= curMonths.length; while (m < 1) { m += 12; y--; }
        return `${y}-${String(m).padStart(2, '0')}`;
    });

    let bMapLY = await buildBaseMap(lyMonths, matchFn);
    let bMapPM = await buildBaseMap(pmMonths, matchFn);

    if (bMapLY.size === 0 && bMapPM.size === 0) {
        const lyData = await loadRows('base_ly.xlsx');
        if(lyData) lyData.forEach(r => { const c = clean(getCol(r, 'Customer Code')); if(c && matchFn(r)) bMapLY.set(c, (bMapLY.get(c)||0)+(parseFloat(getCol(r,'Product Volume'))||0)); });
        const lmData = await loadRows('base_lm.xlsx');
        if(lmData) lmData.forEach(r => { const c = clean(getCol(r, 'Customer Code')); if(c && matchFn(r)) bMapPM.set(c, (bMapPM.get(c)||0)+(parseFloat(getCol(r,'Product Volume'))||0)); });
    }

    let bMapAnn = new Map();
    let annPct = parseFloat(scheme.qAnn) || parseFloat(scheme.annual_target) || parseFloat(scheme.annual_pct) || 0;
    if (annPct > 0 && scheme.start) {
        const startYear = parseInt(scheme.start.split('-')[0]);
        const annYear = startYear - 1;
        const annMonths = Array.from({length: 12}, (_, i) => `${annYear}-${String(i+1).padStart(2, '0')}`);
        let loadedAnnFiles = false;
        for (const ym of annMonths) {
            const data = await loadRows(monthPath(ym));
            if(data) {
                loadedAnnFiles = true;
                data.forEach(r => {
                    const c = clean(getCol(r, 'Customer Code'));
                    if(c && matchFn(r)) bMapAnn.set(c, (bMapAnn.get(c)||0)+(parseFloat(getCol(r,'Product Volume'))||0));
                });
            }
        }
        if (!loadedAnnFiles) {
            const lyData = await loadRows('base_ly.xlsx');
            if(lyData) lyData.forEach(r => { const c = clean(getCol(r, 'Customer Code')); if(c && matchFn(r)) bMapAnn.set(c, (bMapAnn.get(c)||0)+(parseFloat(getCol(r,'Product Volume'))||0)); });
        }
    }

    const [ss, sm] = scheme.start.split('-').map(Number);
    const [es, em] = scheme.end.split('-').map(Number);
    const dateStart = new Date(ss, sm - 1, 1);
    const dateEnd   = new Date(es, em, 0);
    dateEnd.setHours(23, 59, 59, 999);

    const grouped = {};

    salesRows.forEach(row => {
        const rawD = getCol(row, 'Invoice Date');
        if (!rawD) return;
        const d  = rawD instanceof Date ? rawD : new Date(rawD);
        if (isNaN(d.getTime())) return;
        const ds = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        if (ds < dateStart || ds > dateEnd) return;

        if (!matchFn(row)) return;
        const code = clean(getCol(row, 'Customer Code'));
        if (!code) return;
        if (fCodes.length && !fCodes.includes(code)) return;

        const shop = (getCol(row, 'Customer Name') || 'Unknown').toString().trim();
        const exec = (getCol(row, 'Sales Executive Name') || 'Other').toString().trim();
        const vol  = parseFloat(getCol(row, 'Product Volume')) || 0;

        if (!grouped[exec]) grouped[exec] = {};

        if (!grouped[exec][code]) {
            const lyVol   = bMapLY.get(code) || 0;
            const pmVol   = bMapPM.get(code) || 0;
            const annBase = bMapAnn.get(code) || 0;
            let annTgt = 0;
            if (annPct > 0) annTgt = annPct <= 100 ? annBase * (annPct / 100) : annPct;

            if (scheme.mode === 'standard') {
                grouped[exec][code] = {
                    name: shop, ach: 0, mode: 's', target: scheme.target || 0, annTgt
                };
            } else if (scheme.mode === 'growth') {
                const fallback = scheme.base_type === 'fallback';
                let base = lyVol, baseLabel = 'LY';
                if (fallback && lyVol === 0) { base = pmVol; baseLabel = 'PM'; }
                const growthPct = parseFloat(scheme.growth) || 0;
                let target = base + (base * growthPct / 100);
                if (scheme.min && target < scheme.min) target = scheme.min;
                grouped[exec][code] = {
                    name: shop, ach: 0, mode: 'g', base, baseLabel, target, growthPct,
                    ms_per: parseFloat(scheme.ms_per) || 0,
                    ms_date: parseInt(scheme.ms_date) || 14,
                    end_date: scheme.end, annTgt
                };
            } else if (scheme.mode === 'quarterly') {
                const fallback = scheme.qBaseType === 'both';
                let base = lyVol, baseLabel = 'LY';
                if (fallback && lyVol === 0) { base = pmVol; baseLabel = 'PM'; }
                grouped[exec][code] = {
                    name: shop, ach: 0, mode: 'q', base, baseLabel,
                    slabs: (scheme.qSlabs || []).slice().sort((a, b) => a - b),
                    qGro: parseFloat(scheme.qGro) || 0,
                    qGroTiers: scheme.qGroTiers || [],
                    end_date: scheme.end, annTgt
                };
            }
        }
        grouped[exec][code].ach += vol;
    });

    return grouped;
}

async function init() {
    setSyncStatus('Loading...');
    try {
        const sRes = await fetch('schemes_db.json?v=' + Date.now());
        if (!sRes.ok) throw new Error('schemes_db.json not found');
        masterSchemes = await sRes.json();
        const cRes = await fetch('processed_cache.json?v=' + Date.now());
        if (cRes.ok) {
            const cached = await cRes.json();
            if (Object.keys(cached).length > 0) {
                processedData = cached;
                renderUI();
                setSyncStatus('From cache · tap 🔄 to refresh');
                return;
            }
        }
        await forceSync();
    } catch (e) { setSyncStatus('❌ ' + e.message); }
}

async function forceSync() {
    const icon = document.getElementById('syncIcon');
    icon.classList.add('spin');
    setSyncStatus('Syncing...');
    _fileCache.clear();
    try {
        if (!masterSchemes.length) {
            const sRes = await fetch('schemes_db.json?v=' + Date.now());
            masterSchemes = await sRes.json();
        }
        processedData = {};
        for (const scheme of masterSchemes) {
            if (!scheme.start || !scheme.end) { processedData[scheme.id] = {}; continue; }
            const allMonths = schemeMonths(scheme.start, scheme.end);
            let salesRows = [];
            for (const ym of allMonths) {
                const rows = await loadRows(monthPath(ym));
                if (rows) salesRows = salesRows.concat(rows);
            }
            if (salesRows.length === 0) salesRows = await loadRows('sales_data.xlsx') || [];
            processedData[scheme.id] = await engine(scheme, salesRows);
        }
        await fetch('save_cache.php', { method: 'POST', body: JSON.stringify(processedData) });
        renderUI();
        setSyncStatus('✅ Synced');
    } catch (e) { setSyncStatus('❌ Error'); }
    finally { icon.classList.remove('spin'); }
}

function renderUI() {
    const sel = document.getElementById('schemeSelect');
    sel.innerHTML = masterSchemes.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
    if (curSchemeId && masterSchemes.find(s => s.id == curSchemeId)) sel.value = curSchemeId;
    renderGrid(sel.value);
}

function switchScheme(id) { curSchemeId = id; renderGrid(id); }

function renderGrid(id) {
    curSchemeId = id;
    curGroup    = processedData[id] || {};
    const grid  = document.getElementById('mainGrid');
    if (!Object.keys(curGroup).length) {
        grid.innerHTML = `<div class="col-12 text-center py-5 text-muted small">No data. Tap 🔄 to sync.</div>`;
        return;
    }
    grid.innerHTML = '';
    Object.keys(curGroup).sort().forEach(exec => {
        const shops = Object.values(curGroup[exec]);
        grid.innerHTML += `
        <div class="col-6 col-md-4">
            <div class="card exec-card p-3 h-100 shadow-sm" onclick="showDetail('${exec.replace(/'/g, "\\'")}','${id}')">
                <div class="exec-name text-truncate">${exec}</div>
                <div class="exec-sub">${shops.length} shops</div>
            </div>
        </div>`;
    });
}

function showDetail(exec, id) {
    document.getElementById('mainGrid').style.display = 'none';
    document.getElementById('detailView').style.display = 'block';
    document.getElementById('shopSearch').value = '';

    const scheme = masterSchemes.find(s => s.id == id);
    document.getElementById('exTitle').innerText    = exec;
    document.getElementById('exSubtitle').innerText = scheme ? `${scheme.name}` : '';

    const mode     = scheme ? scheme.mode : 'standard';
    const msPer    = scheme ? (parseFloat(scheme.ms_per) || 0) : 0;
    const qGro     = scheme ? (parseFloat(scheme.qGro) || 0) : 0;
    const annTgtVal = scheme ? (parseFloat(scheme.qAnn) || parseFloat(scheme.annual_target) || parseFloat(scheme.annual_pct) || 0) : 0;

    const hasTierBal = mode === 'quarterly';
    const hasGroBal  = mode === 'quarterly' && qGro > 0;
    const hasMsBal   = mode === 'growth' && msPer > 0;
    const hasAnnBal  = annTgtVal > 0;

    let tHeadStr = `<tr><th class="text-start ps-3">Shop</th><th class="text-center">Ach</th>`;
    if (hasTierBal) tHeadStr += `<th class="text-center">Tier Bal</th>`;
    if (hasGroBal)  tHeadStr += `<th class="text-center">Gro Bal</th>`;
    if (hasMsBal)   tHeadStr += `<th class="text-center">MS Ach</th>`;
    if (hasAnnBal)  tHeadStr += `<th class="text-center">Ann Bal</th>`;
    tHeadStr += `<th class="text-center">Status</th></tr>`;
    document.getElementById('tHead').innerHTML = tHeadStr;

    const items = Object.values(curGroup[exec] || {}).sort((a, b) => b.ach - a.ach);
    document.getElementById('mBody').innerHTML = items.map(item => buildRow(item, hasTierBal, hasGroBal, hasMsBal, hasAnnBal)).join('');
}

function buildRow(item, hasTierBal, hasGroBal, hasMsBal, hasAnnBal) {
    let sc = 'badge-red', st = '', info = '';
    let balCells = '';
    const todayFull = new Date();

    let isEnded = false;
    if (item.end_date) {
        const [ey, em] = item.end_date.split('-').map(Number);
        const endDate = new Date(ey, em, 0);
        endDate.setHours(23, 59, 59, 999);
        isEnded = todayFull > endDate;
    }

    let annBalCell = '';
    if (hasAnnBal) {
        const annRem = Math.max(0, (item.annTgt || 0) - item.ach);
        annBalCell = `<td class="text-center bal-val ${annRem > 0 ? 'text-danger' : 'text-success'}">${annRem > 0 ? annRem.toFixed(1) + 'L' : '✓'}</td>`;
    }
    let annBalStr = '';
    if (item.annTgt > 0) {
        const rem = Math.max(0, item.annTgt - item.ach);
        annBalStr = ` &nbsp;·&nbsp; <span style="color:#b91c1c;font-weight:700;">Ann Bal: ${rem.toFixed(1)}L</span>`;
    }

    // ── STANDARD ─────────────────────────────────────────────────────────────
    if (item.mode === 's') {
        const tgt = item.target;
        info = `<div class="info-box">Target: ${tgt}L${annBalStr}</div>`;
        if (tgt <= 0) { sc = 'badge-gray'; st = 'NO TARGET'; }
        else {
            const cycles = Math.floor(item.ach / tgt);
            const rem    = item.ach % tgt;
            const need   = (tgt - rem).toFixed(1);
            if (item.ach < tgt) { sc = 'badge-red'; st = `NEED ${need}L`; }
            else { st = rem > 0 ? `${cycles}× · NEED ${need}L` : `${cycles}× DONE`; sc = rem === 0 ? 'badge-green' : 'badge-blue'; }
        }
        balCells = annBalCell;
    }

    // ── GROWTH (Monthly) ─────────────────────────────────────────────────────
    else if (item.mode === 'g') {
        const base   = item.base   || 0;
        const tgt    = item.target || 0;
        const groAch = base > 0 ? ((item.ach - base) / base * 100) : 0;
        info = `<div class="info-box">Base(${item.baseLabel}): ${base.toFixed(1)}L &nbsp;·&nbsp; Tgt: ${tgt.toFixed(1)}L &nbsp;·&nbsp; Gro: ${groAch.toFixed(1)}%</div>`;

        if (hasMsBal) {
            const msTgt  = tgt * (item.ms_per || 0) / 100;
            const msBal  = msTgt - item.ach;
            const msDate = item.ms_date || 14;
            // Build full deadline date from scheme end month + ms day number
            const epParts = (item.end_date || '').split('-').map(Number);
            const msDeadline = epParts.length === 2
                ? new Date(epParts[0], epParts[1] - 1, msDate)
                : new Date(todayFull.getFullYear(), todayFull.getMonth(), msDate);
            const isMsPast = todayFull > msDeadline;

            if (item.ach >= msTgt) {
                balCells += `<td class="text-center fw-bold text-success" style="font-size:0.65rem;">ACHIEVED</td>`;
            } else if (isMsPast) {
                balCells += `<td class="text-center fw-bold text-danger" style="font-size:0.6rem;">NOT ELIG.</td>`;
            } else {
                balCells += `<td class="text-center text-primary fw-bold">${msBal.toFixed(1)}L</td>`;
            }
        }
        // Status always shows target balance — independent of MS
        if (item.ach >= tgt) { sc = 'badge-green'; st = 'DONE ✓'; }
        else { sc = 'badge-red'; st = `NEED ${(tgt - item.ach).toFixed(1)}L`; }
        balCells += annBalCell;
    }

    // ── QUARTERLY ─────────────────────────────────────────────────────────────
    else if (item.mode === 'q') {
        const slabs   = item.slabs || [];
        const base    = item.base  || 0;
        const qGro    = item.qGro  || 0;
        const groAch  = base > 0 ? ((item.ach - base) / base * 100) : 0;
        const qGroTgt = base + (base * qGro / 100);

        // volumeTier: slabs crossed by volume alone (for display columns)
        // actualTier: slabs fully qualified (vol + growth) — for status badge
        let volumeTier = 0;
        for (let i = 0; i < slabs.length; i++) { if (item.ach >= slabs[i]) volumeTier = i + 1; }

        let actualTier = 0;
        for (let i = 0; i < slabs.length; i++) {
            const reqGro  = item.qGroTiers && item.qGroTiers.includes(i);
            const meetsGro = !reqGro || item.ach >= qGroTgt;
            if (item.ach >= slabs[i] && meetsGro) actualTier = i + 1;
            else break;
        }

        const maxTier = slabs.length;

        // Tier balance: uses volumeTier (pure volume position)
        const nextSlabVol = slabs[volumeTier];
        const needVol = nextSlabVol ? Math.max(0, nextSlabVol - item.ach) : 0;

        // Growth eligibility: uses volumeTier (which interval shop is in by volume)
        const reqGroForNext = item.qGroTiers && item.qGroTiers.includes(volumeTier);
        const needGro = reqGroForNext ? Math.max(0, qGroTgt - item.ach) : 0;

        // Status totalNeed: based on actualTier's next slab
        const nextSlab = slabs[actualTier];
        const needVolForStatus = nextSlab ? Math.max(0, nextSlab - item.ach) : 0;
        const totalNeed = Math.max(needVolForStatus, needGro);

        let qBalCells = '';

        // Tier Balance column — show next tier label + volume needed
        if (hasTierBal) {
            if (volumeTier === maxTier) {
                qBalCells += `<td class="text-center text-success fw-bold" style="font-size:0.65rem;">DONE</td>`;
            } else {
                qBalCells += `<td class="text-center text-primary fw-bold">T${volumeTier + 1}: ${needVol.toFixed(1)}L</td>`;
            }
        }

        // Growth Balance column — eligible only for intervals in qGroTiers
        if (hasGroBal) {
            if (volumeTier >= maxTier || !reqGroForNext) {
                qBalCells += `<td class="text-center text-muted fw-bold" style="font-size:0.6rem;">NOT ELIG.</td>`;
            } else {
                const gDiff = qGroTgt - item.ach;
                if (gDiff <= 0) {
                    qBalCells += `<td class="text-center text-success fw-bold">✓</td>`;
                } else if (isEnded) {
                    qBalCells += `<td class="text-center text-danger fw-bold" style="font-size:0.6rem;">MISSED</td>`;
                } else {
                    qBalCells += `<td class="text-center text-primary fw-bold">${gDiff.toFixed(1)}L</td>`;
                }
            }
        }

        balCells = qBalCells + annBalCell;

        const tierParts = slabs.map((s, i) => {
            const reqGro  = item.qGroTiers.includes(i);
            const crossed = item.ach >= s && (!reqGro || item.ach >= qGroTgt);
            const isTarget = i === volumeTier;
            const groMark = reqGro ? '⚡' : '';
            const style   = crossed ? 'color:#94a3b8;text-decoration:line-through;' : (isTarget ? 'color:#0369a1;font-weight:700;' : '');
            return `<span style="${style}">T${i + 1}:${s}L${groMark}</span>`;
        }).join('&nbsp; ');

        info = `<div class="info-box">Base(${item.baseLabel}): ${base.toFixed(1)}L &nbsp;·&nbsp; Gro: ${groAch.toFixed(1)}%${qGro ? ` (req ${qGro}%)` : ''}${annBalStr}<br>${tierParts}</div>`;

        if (actualTier === maxTier) {
            sc = 'badge-green'; st = `T${maxTier} DONE ✓`;
        } else if (isEnded && totalNeed > 0) {
            sc = 'badge-red'; st = actualTier > 0 ? `T${actualTier} · MISSED` : 'MISSED';
        } else {
            sc = actualTier > 0 ? 'badge-blue' : 'badge-red';
            st = actualTier > 0 ? `T${actualTier} DONE · NEED ${totalNeed.toFixed(1)}L` : `NEED ${totalNeed.toFixed(1)}L`;
        }
    }

    return `<tr>
        <td class="p-2 ps-3"><div class="fw-bold text-uppercase shop-name">${item.name}</div>${info}</td>
        <td class="text-center ach-val">${item.ach.toFixed(1)}</td>
        ${balCells}
        <td class="text-center"><span class="status-pill ${sc}">${st}</span></td>
    </tr>`;
}

function goBack() { document.getElementById('mainGrid').style.display = ''; document.getElementById('detailView').style.display = 'none'; }
function doSearch() {
    const q = document.getElementById('shopSearch').value.toUpperCase();
    const trs = document.getElementById('mBody').getElementsByTagName('tr');
    for (const r of trs) r.style.display = r.innerText.toUpperCase().includes(q) ? '' : 'none';
}
init();
</script>
</body>
</html>
