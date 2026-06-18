<?php
// ============================================================
// library.php - Library Borrowing & Fine Module (40 Marks)
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

// ---------- DATA: Users (nested associative arrays) ----------
$users = [
    'U001' => ['name' => 'Reabetswe Mosenyi',  'outstanding_fine' => 0.0,   'borrowed_books' => []],
    'U002' => ['name' => 'Nkateko Nkuna',    'outstanding_fine' => 0.0,   'borrowed_books' => []],
    'U003' => ['name' => 'LEBOHANG MUNYAI',    'outstanding_fine' => 185.00, 'borrowed_books' => []], // Near limit
    'U004' => ['name' => 'KATLEGO MOSENYI',    'outstanding_fine' => 210.00, 'borrowed_books' => []], // Over limit – blocked
    'U005' => ['name' => 'THAPELO PHOSHOKO',    'outstanding_fine' => 0.0,   'borrowed_books' => []],
];

// ---------- PROCESS: Borrow Operations ----------
// still learning but this part helps me keep track of who borrowed which book 💖
$borrowLog = [];

$borrowLog[] = borrowBook($users, 'U001', 'Introduction to PHP', 'Textbook',       '2025-05-01');
$borrowLog[] = borrowBook($users, 'U001', 'Database Design Journal', 'Journal',    '2025-05-01');
$borrowLog[] = borrowBook($users, 'U002', 'Oxford English Dictionary', 'Reference Book', '2025-05-05');
$borrowLog[] = borrowBook($users, 'U002', 'Web Development Patterns', 'Textbook',  '2025-05-10');
$borrowLog[] = borrowBook($users, 'U003', 'Computer Networks', 'Textbook',         '2025-05-08');
$borrowLog[] = borrowBook($users, 'U004', 'Operating Systems', 'Reference Book',   '2025-05-03'); // BLOCKED
$borrowLog[] = borrowBook($users, 'U005', 'Algorithms & Data Structures', 'Textbook', '2025-05-12');
$borrowLog[] = borrowBook($users, 'U005', 'IEEE Journal Vol.12', 'Journal',        '2025-05-12');

// ---------- PROCESS: Return Operations ----------
$returnLog = [];

// U001 returns PHP book late (+20 days = 6 days late → R5 × 6 = R30)
$returnLog[] = returnBook($users, 'U001', 'Introduction to PHP',       '2025-05-21', LOAN_DAYS);
// U001 returns Journal on time (10 days)
$returnLog[] = returnBook($users, 'U001', 'Database Design Journal',   '2025-05-11', LOAN_DAYS);
// U002 returns Reference book very late (+30 days = 16 days late → R10 × 16 = R160)
$returnLog[] = returnBook($users, 'U002', 'Oxford English Dictionary', '2025-06-05', LOAN_DAYS);
// U002 returns Textbook on time
$returnLog[] = returnBook($users, 'U002', 'Web Development Patterns',  '2025-05-22', LOAN_DAYS);
// U003 returns Textbook late (+5 days → R5 × 5 = R25, pushing total to R210 > R200)
$returnLog[] = returnBook($users, 'U003', 'Computer Networks',         '2025-05-27', LOAN_DAYS);
// U005 returns both on time
$returnLog[] = returnBook($users, 'U005', 'Algorithms & Data Structures', '2025-05-20', LOAN_DAYS);
$returnLog[] = returnBook($users, 'U005', 'IEEE Journal Vol.12',          '2025-05-24', LOAN_DAYS);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Library Module – CampusMS</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin:0; padding:0; }
  body { font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; background:#f0f4f8; color:#2d3748; }

  .top-nav {
    background:linear-gradient(135deg,#60a5fa,#8b5cf6);
    color:#fff; padding:0 2rem;
    display:flex; align-items:center; gap:1rem; height:64px;
    box-shadow:0 2px 8px rgba(0,0,0,.25);
  }
  .top-nav .brand { font-size:1.3rem; font-weight:700; flex:1; }
  .top-nav a { color:rgba(255,255,255,.85); text-decoration:none; padding:.4rem .9rem; border-radius:6px; font-size:.92rem; transition:background .2s; }
  .top-nav a:hover,.top-nav a.active { background:rgba(255,255,255,.18); color:#fff; }

  .page-hero { background:linear-gradient(135deg,#4c1d95,#8b5cf6); color:#fff; padding:2.5rem 1.5rem; text-align:center; }
  .page-hero h1 { font-size:2rem; font-weight:800; }
  .page-hero p  { opacity:.85; margin-top:.4rem; }

  .container { max-width:1100px; margin:0 auto; padding:2rem 1.5rem; }

  .constants-grid { display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:2rem; }
  .const-card { background:#fff; border-radius:10px; padding:1rem 1.4rem; box-shadow:0 2px 10px rgba(0,0,0,.08); flex:1; min-width:140px; text-align:center; }
  .const-card .const-value { font-size:1.5rem; font-weight:800; color:#8b5cf6; }
  .const-card .const-label { font-size:.8rem; color:#64748b; margin-top:.2rem; }

  .card { background:#fff; border-radius:14px; box-shadow:0 4px 20px rgba(0,0,0,.09); margin-bottom:2rem; overflow:hidden; }
  .card-header { background:#8b5cf6; color:#fff; padding:1rem 1.5rem; font-size:1.05rem; font-weight:700; display:flex; align-items:center; gap:.6rem; }
  .card-body { padding:1.5rem; }

  .log-list { list-style:none; }
  .log-list li { padding:.55rem .9rem; border-radius:7px; margin-bottom:.4rem; font-size:.88rem; display:flex; align-items:flex-start; gap:.6rem; }
  .log-success { background:#ecfdf5; color:#065f46; border-left:4px solid #22c55e; }
  .log-denied  { background:#fef2f2; color:#991b1b; border-left:4px solid #ef4444; }
  .log-return  { background:#eff6ff; color:#1e40af; border-left:4px solid #3b82f6; }
  .log-late    { background:#fffbeb; color:#92400e; border-left:4px solid #f59e0b; }

  .data-table { width:100%; border-collapse:collapse; font-size:.9rem; }
  .data-table thead { background:#f5f3ff; }
  .data-table th { padding:.75rem 1rem; text-align:left; font-weight:700; color:#6d28d9; border-bottom:2px solid #ddd6fe; }
  .data-table td { padding:.7rem 1rem; border-bottom:1px solid #e2e8f0; vertical-align:top; }
  .data-table tbody tr:last-child td { border-bottom:none; }
  .data-table tbody tr:hover { background:#faf5ff; }

  .text-danger { color:#ef4444; font-weight:700; }
  .text-warn   { color:#f59e0b; font-weight:600; }
  .text-ok     { color:#22c55e; font-weight:600; }

  .badge { display:inline-block; padding:.2rem .65rem; border-radius:20px; font-size:.76rem; font-weight:700; }
  .badge-textbook   { background:#eff6ff; color:#ff66c4; }
  .badge-journal    { background:#ecfdf5; color:#22c55e; }
  .badge-reference  { background:#fef3c7; color:#92400e; }
  .badge-blocked    { background:#fef2f2; color:#dc2626; }
  .badge-ok         { background:#ecfdf5; color:#065f46; }

  .fine-bar { background:#e2e8f0; border-radius:10px; height:12px; overflow:hidden; margin-top:.3rem; min-width:80px; display:inline-block; width:120px; vertical-align:middle; }
  .fine-fill { height:100%; border-radius:10px; }
  .fine-ok     { background:#22c55e; }
  .fine-warn   { background:#f59e0b; }
  .fine-danger { background:#ef4444; }

  footer { text-align:center; padding:1.5rem; font-size:.85rem; color:#94a3b8; border-top:1px solid #e2e8f0; margin-top:2rem; }
</style>
</head>
<body>

<nav class="top-nav">
  <span class="brand">🎓 CampusMS</span>
  <a href="index.php">Home</a>
  <a href="parking.php">Parking</a>
  <a href="library.php" class="active">Library</a>
  <a href="performance.php">Performance</a>
</nav>

<div class="page-hero">
  <h1>📚 Library Borrowing &amp; Fine Module</h1>
  <p>Track loans, calculate fines, enforce borrowing limits &mdash; 40 Marks</p>
</div>

<div class="container">

  <!-- Constants -->
  <div class="constants-grid">
    <div class="const-card">
      <div class="const-value">R<?= number_format(FINE_TEXTBOOK, 0) ?>/day</div>
      <div class="const-label">Textbook Fine Rate</div>
    </div>
    <div class="const-card">
      <div class="const-value">R<?= number_format(FINE_JOURNAL, 0) ?>/day</div>
      <div class="const-label">Journal Fine Rate</div>
    </div>
    <div class="const-card">
      <div class="const-value">R<?= number_format(FINE_REFERENCE, 0) ?>/day</div>
      <div class="const-label">Reference Book Rate</div>
    </div>
    <div class="const-card">
      <div class="const-value">R<?= number_format(MAX_FINE_LIMIT, 0) ?></div>
      <div class="const-label">Max Fine Before Block</div>
    </div>
    <div class="const-card">
      <div class="const-value"><?= LOAN_DAYS ?> days</div>
      <div class="const-label">Standard Loan Period</div>
    </div>
  </div>

  <!-- Borrow Log -->
  <div class="card">
    <div class="card-header">📥 Borrowing Log</div>
    <div class="card-body">
      <ul class="log-list">
        <?php foreach ($borrowLog as $msg): ?>
          <?php
          if (str_starts_with($msg,'SUCCESS'))  $cls = 'log-success', $icon = '✅';
          elseif (str_starts_with($msg,'DENIED')) $cls = 'log-denied',  $icon = '🚫';
          else                                   $cls = 'log-denied',  $icon = '⚠️';
          ?>
          <li class="<?= $cls ?>"><?= $icon ?> <?= htmlspecialchars($msg) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- Return Log -->
  <div class="card">
    <div class="card-header">📤 Return Log</div>
    <div class="card-body">
      <ul class="log-list">
        <?php foreach ($returnLog as $msg): ?>
          <?php
          if (str_contains($msg,'Fine:'))       $cls = 'log-late',    $icon = '⚠️';
          elseif (str_starts_with($msg,'RETURNED')) $cls = 'log-return',  $icon = '✅';
          else                                  $cls = 'log-denied',  $icon = '❌';
          ?>
          <li class="<?= $cls ?>"><?= $icon ?> <?= htmlspecialchars($msg) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- User Summary (via printUserSummary function) -->
  <div class="card">
    <div class="card-header">👤 User Summary – printUserSummary()</div>
    <div class="card-body">
      <?php printUserSummary($users); ?>
    </div>
  </div>

  <!-- Detailed Book Records -->
  <div class="card">
    <div class="card-header">📖 Detailed Borrowing Records</div>
    <div class="card-body">
      <table class="data-table">
        <thead>
          <tr>
            <th>User</th><th>Book</th><th>Category</th>
            <th>Borrow Date</th><th>Return Date</th><th>Fine</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $uid => $user): ?>
            <?php foreach ($user['borrowed_books'] as $book): ?>
              <?php
              $catClass = match($book['category']) {
                  'Textbook'       => 'badge-textbook',
                  'Journal'        => 'badge-journal',
                  'Reference Book' => 'badge-reference',
                  default          => '',
              };
              $fineDisplay = $book['fine'] > 0
                  ? '<span class="text-danger">' . formatCurrency($book['fine']) . '</span>'
                  : '<span class="text-ok">R0.00</span>';
              $returned = $book['return_date'] ?? '<em style="color:#94a3b8">Still on loan</em>';
              ?>
              <tr>
                <td><?= htmlspecialchars($user['name']) ?><br><small style="color:#94a3b8">$uid</small></td>
                <td><?= htmlspecialchars($book['title']) ?></td>
                <td><span class="badge <?= $catClass ?>"><?= $book['category'] ?></span></td>
                <td><?= $book['borrow_date'] ?></td>
                <td><?= is_string($returned) && !str_contains($returned,'em') ? $returned : $returned ?></td>
                <td><?= $fineDisplay ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Fine Calculations Explained -->
  <div class="card">
    <div class="card-header">🧮 Fine Calculation – calculateFine()</div>
    <div class="card-body">
      <p style="font-size:.9rem; color:#475569; margin-bottom:1rem;">
        Fine = <code>rate_per_day × days_late</code> &nbsp;|&nbsp;
        Days late = days held − <?= LOAN_DAYS ?>-day loan period (if positive)
      </p>
      <table class="data-table">
        <thead>
          <tr><th>Category</th><th>Rate/Day</th><th>Example: 10 Days Late</th></tr>
        </thead>
        <tbody>
          <tr><td>Textbook</td>      <td>R<?= FINE_TEXTBOOK ?></td>  <td><?= formatCurrency(calculateFine('Textbook', 10)) ?></td></tr>
          <tr><td>Journal</td>       <td>R<?= FINE_JOURNAL ?></td>   <td><?= formatCurrency(calculateFine('Journal', 10)) ?></td></tr>
          <tr><td>Reference Book</td><td>R<?= FINE_REFERENCE ?></td> <td><?= formatCurrency(calculateFine('Reference Book', 10)) ?></td></tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<footer>&copy; <?= date('Y') ?> Richfield Graduate Institute of Technology &mdash; Internet Programming 621</footer>
</body>
</html>
