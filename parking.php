<?php
// ============================================================
// parking.php - Parking Permit Module (30 Marks)
// ============================================================

define('PERMIT_STUDENT',      450.00);
define('PERMIT_STAFF',        750.00);
define('PERMIT_VISITOR',      100.00);
define('MAX_PARKING_CAPACITY', 50);
define('FINE_TEXTBOOK',   5.00);
define('FINE_JOURNAL',    3.00);
define('FINE_REFERENCE', 10.00);
define('MAX_FINE_LIMIT', 200.00);
define('LOAN_DAYS',       14);

require_once 'functions.php';

// ---------- DATA: Permit Applications ----------
// Format: [name, age, type]
$applications = [
    ['Tshego Zulu',    22, 'Student'],
    ['Esther Matthews',      17, 'Student'],   // Under 18 – should be denied
    ['Noah Bennett',      35, 'Staff'],
    ['Hana Sato',      45, 'Staff'],
    ['Sipho Dlamini',      19, 'Student'],
    ['Frank Williams',   16, 'Visitor'],   // Under 18 – should be denied
    ['Grace Sithole',    28, 'Visitor'],
    ['Emma Johnson',    31, 'Staff'],
    ['Aisha Khan',    24, 'Student'],
    ['James Mitchell',     52, 'Staff'],
    ['Nokubonga Mahlangu',   20, 'Student'],
    ['Lesego Skhosana',     18, 'Visitor'],
    ['Olivia Parker',       40, 'Staff'],
    ['Nomsa Zulu',       23, 'Student'],
    ['Thandiwe Nkosi',   29, 'Visitor'],
];

// ---------- PROCESS APPLICATIONS ----------
// hehe this part loops through all the applications one by one 🌸
$permits  = [];   // Associative: ['Student'=>['count'=>0,'revenue'=>0,'holders'=>[]], ...]
$messages = [];   // Log of outcomes

foreach ($applications as $app) {
    [$name, $age, $type] = $app;
    $messages[] = issuePermit($permits, $name, $age, $type);
}

$summary = getParkingSummary($permits);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Parking Module – CampusMS</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin:0; padding:0; }
  body { font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; background:#f0f4f8; color:#2d3748; }

  .top-nav {
    background: linear-gradient(135deg,#60a5fa,#ff66c4);
    color:#fff; padding:0 2rem;
    display:flex; align-items:center; gap:1rem; height:64px;
    box-shadow:0 2px 8px rgba(0,0,0,.25);
  }
  .top-nav .brand { font-size:1.3rem; font-weight:700; flex:1; }
  .top-nav a { color:rgba(255,255,255,.85); text-decoration:none; padding:.4rem .9rem; border-radius:6px; font-size:.92rem; transition:background .2s; }
  .top-nav a:hover,.top-nav a.active { background:rgba(255,255,255,.18); color:#fff; }

  .page-hero { background:linear-gradient(135deg,#60a5fa,#ff66c4); color:#fff; padding:2.5rem 1.5rem; text-align:center; }
  .page-hero h1 { font-size:2rem; font-weight:800; }
  .page-hero p  { opacity:.85; margin-top:.4rem; }

  .container { max-width:1100px; margin:0 auto; padding:2rem 1.5rem; }

  /* Constants Banner */
  .constants-grid { display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:2rem; }
  .const-card { background:#fff; border-radius:10px; padding:1rem 1.4rem; box-shadow:0 2px 10px rgba(0,0,0,.08); flex:1; min-width:140px; text-align:center; }
  .const-card .const-value { font-size:1.5rem; font-weight:800; color:#ff66c4; }
  .const-card .const-label { font-size:.8rem; color:#64748b; margin-top:.2rem; }

  /* Cards */
  .card { background:#fff; border-radius:14px; box-shadow:0 4px 20px rgba(0,0,0,.09); margin-bottom:2rem; overflow:hidden; }
  .card-header { background:#ff66c4; color:#fff; padding:1rem 1.5rem; font-size:1.05rem; font-weight:700; display:flex; align-items:center; gap:.6rem; }
  .card-body { padding:1.5rem; }

  /* Log */
  .log-list { list-style:none; }
  .log-list li { padding:.55rem .9rem; border-radius:7px; margin-bottom:.4rem; font-size:.9rem; display:flex; align-items:center; gap:.6rem; }
  .log-issued { background:#ecfdf5; color:#065f46; border-left:4px solid #22c55e; }
  .log-denied { background:#fef2f2; color:#991b1b; border-left:4px solid #ef4444; }

  /* Data Table */
  .data-table { width:100%; border-collapse:collapse; font-size:.92rem; }
  .data-table thead { background:#eff6ff; }
  .data-table th { padding:.75rem 1rem; text-align:left; font-weight:700; color:#1e40af; border-bottom:2px solid #bfdbfe; }
  .data-table td { padding:.7rem 1rem; border-bottom:1px solid #e2e8f0; }
  .data-table tbody tr:last-child td { border-bottom:none; }
  .data-table tbody tr:hover { background:#f8fafc; }

  /* Summary Boxes */
  .summary-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1.2rem; margin-bottom:2rem; }
  .summary-box { background:#fff; border-radius:12px; padding:1.4rem 1.6rem; box-shadow:0 2px 12px rgba(0,0,0,.08); border-left:5px solid; }
  .summary-box.student { border-color:#ff66c4; }
  .summary-box.staff   { border-color:#8b5cf6; }
  .summary-box.visitor { border-color:#0891b2; }
  .summary-box.total   { border-color:#22c55e; }
  .summary-box .box-value { font-size:1.8rem; font-weight:800; color:#60a5fa; }
  .summary-box .box-label { font-size:.82rem; color:#64748b; margin-top:.2rem; }
  .summary-box .box-revenue { font-size:.95rem; font-weight:600; color:#475569; margin-top:.4rem; }

  .badge { display:inline-block; padding:.2rem .7rem; border-radius:20px; font-size:.78rem; font-weight:700; }
  .badge-student { background:#eff6ff; color:#ff66c4; }
  .badge-staff   { background:#f5f3ff; color:#8b5cf6; }
  .badge-visitor { background:#ecfeff; color:#0e7490; }

  .capacity-bar { background:#e2e8f0; border-radius:10px; height:18px; overflow:hidden; margin-top:.6rem; }
  .capacity-fill { background:linear-gradient(90deg,#ff66c4,#8b5cf6); height:100%; border-radius:10px; transition:width .6s ease; display:flex; align-items:center; padding-left:.5rem; color:#fff; font-size:.72rem; font-weight:700; }

  footer { text-align:center; padding:1.5rem; font-size:.85rem; color:#94a3b8; border-top:1px solid #e2e8f0; margin-top:2rem; }
</style>
</head>
<body>

<nav class="top-nav">
  <span class="brand">🎓 CampusMS</span>
  <a href="index.php">Home</a>
  <a href="parking.php" class="active">Parking</a>
  <a href="library.php">Library</a>
  <a href="performance.php">Performance</a>
</nav>

<div class="page-hero">
  <h1>🅿️ Parking Permit Module</h1>
  <p>Issue permits, enforce rules, and track revenue &mdash; 30 Marks</p>
</div>

<div class="container">

  <!-- Constants Display -->
  <div class="constants-grid">
    <div class="const-card">
      <div class="const-value">R<?= number_format(PERMIT_STUDENT, 0) ?></div>
      <div class="const-label">Student Permit</div>
    </div>
    <div class="const-card">
      <div class="const-value">R<?= number_format(PERMIT_STAFF, 0) ?></div>
      <div class="const-label">Staff Permit</div>
    </div>
    <div class="const-card">
      <div class="const-value">R<?= number_format(PERMIT_VISITOR, 0) ?></div>
      <div class="const-label">Visitor Permit</div>
    </div>
    <div class="const-card">
      <div class="const-value"><?= MAX_PARKING_CAPACITY ?></div>
      <div class="const-label">Max Capacity (bays)</div>
    </div>
    <div class="const-card">
      <div class="const-value">18</div>
      <div class="const-label">Minimum Age (years)</div>
    </div>
  </div>

  <!-- Capacity Bar -->
  <div class="card">
    <div class="card-header">📊 Parking Capacity Usage</div>
    <div class="card-body">
      <?php $used = $summary['total_permits']; $pct = round($used / MAX_PARKING_CAPACITY * 100); ?>
      <p style="margin-bottom:.5rem; font-size:.9rem; color:#475569;">
        <strong><?= $used ?></strong> of <strong><?= MAX_PARKING_CAPACITY ?></strong> bays used (<?= $pct ?>%)
      </p>
      <div class="capacity-bar">
        <div class="capacity-fill" style="width:<?= $pct ?>%;"><?= $pct ?>%</div>
      </div>
    </div>
  </div>

  <!-- Application Log -->
  <div class="card">
    <div class="card-header">📋 Permit Application Log</div>
    <div class="card-body">
      <ul class="log-list">
        <?php foreach ($messages as $msg): ?>
          <?php $class = str_starts_with($msg, 'ISSUED') ? 'log-issued' : 'log-denied'; ?>
          <?php $icon  = str_starts_with($msg, 'ISSUED') ? '✅' : '❌'; ?>
          <li class="<?= $class ?>"><?= $icon ?> <?= htmlspecialchars($msg) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- Summary by Category -->
  <div class="card">
    <div class="card-header">📈 Permit Summary by Category</div>
    <div class="card-body">
      <div class="summary-grid">
        <?php foreach (['Student' => 'student', 'Staff' => 'staff', 'Visitor' => 'visitor'] as $type => $cls): ?>
          <?php $d = $permits[$type] ?? ['count' => 0, 'revenue' => 0]; ?>
          <div class="summary-box <?= $cls ?>">
            <div class="box-value"><?= $d['count'] ?></div>
            <div class="box-label"><?= $type ?> Permits Issued</div>
            <div class="box-revenue">Revenue: <?= formatCurrency($d['revenue']) ?></div>
          </div>
        <?php endforeach; ?>
        <div class="summary-box total">
          <div class="box-value"><?= $summary['total_permits'] ?></div>
          <div class="box-label">Total Permits Issued</div>
          <div class="box-revenue">Total Revenue: <?= formatCurrency($summary['total_revenue']) ?></div>
        </div>
      </div>

      <!-- Detailed Table -->
      <table class="data-table">
        <thead>
          <tr>
            <th>Type</th>
            <th>Unit Price</th>
            <th>Count</th>
            <th>Revenue</th>
            <th>Permit Holders</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (['Student' => PERMIT_STUDENT, 'Staff' => PERMIT_STAFF, 'Visitor' => PERMIT_VISITOR] as $type => $price): ?>
            <?php $d = $permits[$type] ?? ['count' => 0, 'revenue' => 0.0, 'holders' => []]; ?>
            <tr>
              <td><span class="badge badge-<?= strtolower($type) ?>"><?= $type ?></span></td>
              <td><?= formatCurrency($price) ?></td>
              <td><strong><?= $d['count'] ?></strong></td>
              <td><?= formatCurrency($d['revenue']) ?></td>
              <td style="font-size:.85rem; color:#475569;"><?= implode(', ', $d['holders'] ?? []) ?: 'None' ?></td>
            </tr>
          <?php endforeach; ?>
          <tr style="font-weight:700; background:#f8fafc;">
            <td colspan="2">TOTALS</td>
            <td><?= $summary['total_permits'] ?></td>
            <td><?= formatCurrency($summary['total_revenue']) ?></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div><!-- /container -->

<footer>&copy; <?= date('Y') ?> Richfield Graduate Institute of Technology &mdash; Internet Programming 621</footer>
</body>
</html>
