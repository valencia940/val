<?php
// ============================================================
// performance.php - Student Performance Analytics Module (30 Marks)
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

// ---------- DATA: Students with 6 marks each (nested arrays) ----------
// Some marks include invalid values to demonstrate graceful handling
$studentsRaw = [
    'Amahle Dube'    => [78, 82, 91, 74, 88, 95],
    'Brendan Fourie' => [55, 62, 'N/A', 70, 48, 66],   // one invalid mark
    'Cynthia Khumalo'=> [41, 38, 45, 52, 'absent', 39], // one invalid mark
    'Dylan Smit'     => [90, 88, 96, 92, 85, 94],
    'Esther Molefe'  => [65, 71, 58, 'NaN', 74, 69],    // one invalid mark
    'Farai Ndlovu'   => [50, 55, 62, 48, 57, 'TBA'],    // one invalid mark
];

// ---------- PROCESS: Build student performance records ----------
// okay so here i wanted to calculate averages and skip weird marks 😭✨
$students = [];

foreach ($studentsRaw as $name => $marks) {
    $validMarks   = [];
    $invalidMarks = [];

    foreach ($marks as $mark) {
        if (validateMark($mark)) {
            $validMarks[] = (float)$mark;
        } else {
            $invalidMarks[] = $mark;
        }
    }

    $average = calculateAverage($validMarks);
    $result  = assignResult($average);

    $students[$name] = [
        'marks'         => $marks,
        'valid_marks'   => $validMarks,
        'invalid_marks' => $invalidMarks,
        'average'       => $average,
        'result'        => $result,
        'highest_mark'  => !empty($validMarks) ? max($validMarks) : 0,
        'lowest_mark'   => !empty($validMarks) ? min($validMarks) : 0,
    ];
}

// ---------- CLASS STATISTICS ----------
$topStudent  = findTopStudent($students);
$classStats  = generateClassStats($students);
$totalPass   = count(array_filter($students, fn($s) => $s['result'] === 'Pass'));
$totalFail   = count(array_filter($students, fn($s) => $s['result'] === 'Fail'));
$totalDist   = count(array_filter($students, fn($s) => $s['result'] === 'Distinction'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Performance Module – CampusMS</title>
<style>
  *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
  body { font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; background:#f0f4f8; color:#2d3748; }

  .top-nav {
    background:linear-gradient(135deg,#065f46,#22c55e);
    color:#fff; padding:0 2rem;
    display:flex; align-items:center; gap:1rem; height:64px;
    box-shadow:0 2px 8px rgba(0,0,0,.25);
  }
  .top-nav .brand { font-size:1.3rem; font-weight:700; flex:1; }
  .top-nav a { color:rgba(255,255,255,.85); text-decoration:none; padding:.4rem .9rem; border-radius:6px; font-size:.92rem; transition:background .2s; }
  .top-nav a:hover,.top-nav a.active { background:rgba(255,255,255,.18); color:#fff; }

  .page-hero { background:linear-gradient(135deg,#065f46,#22c55e); color:#fff; padding:2.5rem 1.5rem; text-align:center; }
  .page-hero h1 { font-size:2rem; font-weight:800; }
  .page-hero p  { opacity:.85; margin-top:.4rem; }

  .container { max-width:1100px; margin:0 auto; padding:2rem 1.5rem; }

  /* Stat boxes */
  .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:1rem; margin-bottom:2rem; }
  .stat-box { background:#fff; border-radius:12px; padding:1.2rem 1.4rem; box-shadow:0 2px 12px rgba(0,0,0,.08); text-align:center; border-top:4px solid; }
  .stat-box.green  { border-color:#22c55e; }
  .stat-box.blue   { border-color:#ff66c4; }
  .stat-box.yellow { border-color:#facc15; }
  .stat-box.red    { border-color:#dc2626; }
  .stat-box.purple { border-color:#8b5cf6; }
  .stat-box .val { font-size:2rem; font-weight:800; color:#60a5fa; }
  .stat-box .lbl { font-size:.8rem; color:#64748b; margin-top:.2rem; }

  /* Cards */
  .card { background:#fff; border-radius:14px; box-shadow:0 4px 20px rgba(0,0,0,.09); margin-bottom:2rem; overflow:hidden; }
  .card-header { background:#22c55e; color:#fff; padding:1rem 1.5rem; font-size:1.05rem; font-weight:700; display:flex; align-items:center; gap:.6rem; }
  .card-body { padding:1.5rem; }

  /* Student card grid */
  .student-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:1.4rem; }
  .student-card { border:1px solid #e2e8f0; border-radius:12px; padding:1.2rem 1.4rem; position:relative; transition:box-shadow .2s; }
  .student-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.1); }
  .student-card.top { border-color:#facc15; background:#fffbeb; }
  .top-badge { position:absolute; top:.7rem; right:.7rem; background:#facc15; color:#fff; font-size:.72rem; font-weight:700; padding:.2rem .6rem; border-radius:20px; }
  .student-name { font-weight:700; font-size:1rem; margin-bottom:.6rem; }

  .marks-display { display:flex; flex-wrap:wrap; gap:.4rem; margin-bottom:.8rem; }
  .mark-chip { padding:.2rem .55rem; border-radius:6px; font-size:.8rem; font-weight:600; }
  .mark-valid   { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
  .mark-invalid { background:#fef2f2; color:#991b1b; border:1px solid #fca5a5; text-decoration:line-through; }

  .avg-bar { background:#e2e8f0; border-radius:10px; height:10px; overflow:hidden; margin:.5rem 0; }
  .avg-fill { height:100%; border-radius:10px; }
  .fill-distinction { background:linear-gradient(90deg,#facc15,#f59e0b); }
  .fill-pass        { background:linear-gradient(90deg,#22c55e,#34d399); }
  .fill-fail        { background:linear-gradient(90deg,#dc2626,#f87171); }

  .badge { display:inline-block; padding:.25rem .8rem; border-radius:20px; font-size:.8rem; font-weight:700; }
  .badge-distinction { background:#fef3c7; color:#92400e; }
  .badge-pass        { background:#ecfdf5; color:#065f46; }
  .badge-fail        { background:#fef2f2; color:#991b1b; }

  /* Data table */
  .data-table { width:100%; border-collapse:collapse; font-size:.9rem; }
  .data-table thead { background:#ecfdf5; }
  .data-table th { padding:.75rem 1rem; text-align:left; font-weight:700; color:#065f46; border-bottom:2px solid #a7f3d0; }
  .data-table td { padding:.7rem 1rem; border-bottom:1px solid #e2e8f0; }
  .data-table tbody tr:last-child td { border-bottom:none; }
  .data-table tbody tr:hover { background:#f0fdf4; }

  /* Class stats */
  .class-stats { display:flex; gap:1.5rem; flex-wrap:wrap; }
  .cs-item { flex:1; min-width:160px; background:#f0fdf4; border-radius:10px; padding:1rem 1.2rem; border-left:4px solid #22c55e; }
  .cs-item .cs-val { font-size:1.6rem; font-weight:800; color:#065f46; }
  .cs-item .cs-lbl { font-size:.82rem; color:#64748b; margin-top:.2rem; }

  .top-student-banner {
    background:linear-gradient(135deg,#facc15,#f59e0b);
    color:#fff; border-radius:12px; padding:1.4rem 1.8rem;
    display:flex; align-items:center; gap:1rem; margin-bottom:2rem;
  }
  .top-student-banner .trophy { font-size:2.5rem; }
  .top-student-banner h3 { font-size:1.2rem; font-weight:800; }
  .top-student-banner p  { opacity:.9; font-size:.95rem; }

  .invalid-note { font-size:.8rem; color:#94a3b8; margin-top:.3rem; }

  footer { text-align:center; padding:1.5rem; font-size:.85rem; color:#94a3b8; border-top:1px solid #e2e8f0; margin-top:2rem; }
</style>
</head>
<body>

<nav class="top-nav">
  <span class="brand">🎓 CampusMS</span>
  <a href="index.php">Home</a>
  <a href="parking.php">Parking</a>
  <a href="library.php">Library</a>
  <a href="performance.php" class="active">Performance</a>
</nav>

<div class="page-hero">
  <h1>📊 Student Performance Analytics</h1>
  <p>Averages, grading, class statistics and validation &mdash; 30 Marks</p>
</div>

<div class="container">

  <!-- Overview Stats -->
  <div class="stats-grid">
    <div class="stat-box green">
      <div class="val"><?= count($students) ?></div>
      <div class="lbl">Total Students</div>
    </div>
    <div class="stat-box yellow">
      <div class="val"><?= $totalDist ?></div>
      <div class="lbl">Distinctions</div>
    </div>
    <div class="stat-box blue">
      <div class="val"><?= $totalPass ?></div>
      <div class="lbl">Passes</div>
    </div>
    <div class="stat-box red">
      <div class="val"><?= $totalFail ?></div>
      <div class="lbl">Fails</div>
    </div>
    <div class="stat-box purple">
      <div class="val"><?= number_format($classStats['class_average'], 1) ?>%</div>
      <div class="lbl">Class Average</div>
    </div>
  </div>

  <!-- Top Student Banner -->
  <?php if ($topStudent): ?>
  <div class="top-student-banner">
    <div class="trophy">🏆</div>
    <div>
      <h3>Top Performing Student: <?= htmlspecialchars($topStudent) ?></h3>
      <p>Average: <?= number_format($students[$topStudent]['average'], 2) ?>% &mdash;
         Result: <?= $students[$topStudent]['result'] ?>
         (<?= count($students[$topStudent]['valid_marks']) ?> valid marks counted)</p>
    </div>
  </div>
  <?php endif; ?>

  <!-- Student Cards -->
  <div class="card">
    <div class="card-header">🎓 Individual Student Results</div>
    <div class="card-body">
      <div class="student-grid">
        <?php foreach ($students as $name => $data): ?>
          <?php $isTop = ($name === $topStudent); ?>
          <?php $resultClass = strtolower($data['result']); ?>
          <div class="student-card <?= $isTop ? 'top' : '' ?>">
            <?php if ($isTop): ?>
              <span class="top-badge">🏆 Top Student</span>
            <?php endif; ?>

            <div class="student-name"><?= htmlspecialchars($name) ?></div>

            <div class="marks-display">
              <?php foreach ($data['marks'] as $mark): ?>
                <?php $valid = validateMark($mark); ?>
                <span class="mark-chip <?= $valid ? 'mark-valid' : 'mark-invalid' ?>">
                  <?= htmlspecialchars((string)$mark) ?>
                </span>
              <?php endforeach; ?>
            </div>

            <?php if (!empty($data['invalid_marks'])): ?>
              <p class="invalid-note">
                ⚠️ Invalid mark(s) skipped: <strong><?= implode(', ', $data['invalid_marks']) ?></strong>
                &nbsp;(<?= count($data['valid_marks']) ?>/<?= count($data['marks']) ?> marks used)
              </p>
            <?php endif; ?>

            <div class="avg-bar" title="Average: <?= number_format($data['average'],1) ?>%">
              <div class="avg-fill fill-<?= $resultClass ?>" style="width:<?= min(100, $data['average']) ?>%;"></div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:.4rem;">
              <span style="font-size:.88rem; color:#475569;">
                Average: <strong><?= number_format($data['average'], 2) ?>%</strong>
              </span>
              <span class="badge badge-<?= $resultClass ?>"><?= $data['result'] ?></span>
            </div>

            <div style="font-size:.82rem; color:#94a3b8; margin-top:.4rem;">
              High: <?= $data['highest_mark'] ?>% &nbsp;|&nbsp; Low: <?= $data['lowest_mark'] ?>%
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Summary Table -->
  <div class="card">
    <div class="card-header">📋 Class Summary Table</div>
    <div class="card-body">
      <table class="data-table">
        <thead>
          <tr>
            <th>Student</th>
            <th>Valid Marks</th>
            <th>Invalid</th>
            <th>Average</th>
            <th>Highest</th>
            <th>Lowest</th>
            <th>Result</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $name => $data): ?>
            <tr>
              <td>
                <?= htmlspecialchars($name) ?>
                <?php if ($name === $topStudent): ?> <span style="font-size:.75rem;">🏆</span><?php endif; ?>
              </td>
              <td><?= implode(', ', $data['valid_marks']) ?></td>
              <td>
                <?php if (!empty($data['invalid_marks'])): ?>
                  <span class="badge badge-fail"><?= implode(', ', $data['invalid_marks']) ?></span>
                <?php else: ?>
                  <span style="color:#94a3b8; font-size:.85rem;">None</span>
                <?php endif; ?>
              </td>
              <td><strong><?= number_format($data['average'], 2) ?>%</strong></td>
              <td><?= $data['highest_mark'] ?>%</td>
              <td><?= $data['lowest_mark'] ?>%</td>
              <td>
                <span class="badge badge-<?= strtolower($data['result']) ?>">
                  <?= $data['result'] ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Class Statistics -->
  <div class="card">
    <div class="card-header">📈 Class Statistics – generateClassStats()</div>
    <div class="card-body">
      <div class="class-stats">
        <div class="cs-item">
          <div class="cs-val"><?= number_format($classStats['highest'], 2) ?>%</div>
          <div class="cs-lbl">Highest Student Average</div>
          <?php foreach ($students as $n => $d): if (abs($d['average'] - $classStats['highest']) < 0.01) echo "<div style='font-size:.8rem;color:#475569;margin-top:.3rem;'>$n</div>"; endforeach; ?>
        </div>
        <div class="cs-item">
          <div class="cs-val"><?= number_format($classStats['lowest'], 2) ?>%</div>
          <div class="cs-lbl">Lowest Student Average</div>
          <?php foreach ($students as $n => $d): if (abs($d['average'] - $classStats['lowest']) < 0.01) echo "<div style='font-size:.8rem;color:#475569;margin-top:.3rem;'>$n</div>"; endforeach; ?>
        </div>
        <div class="cs-item">
          <div class="cs-val"><?= number_format($classStats['class_average'], 2) ?>%</div>
          <div class="cs-lbl">Overall Class Average</div>
          <div style="font-size:.8rem;color:#475569;margin-top:.3rem;">
            Result: <strong><?= assignResult($classStats['class_average']) ?></strong>
          </div>
        </div>
      </div>

      <!-- Grading Scale -->
      <div style="margin-top:1.5rem; padding-top:1.5rem; border-top:1px solid #e2e8f0;">
        <p style="font-size:.9rem; font-weight:700; margin-bottom:.8rem;">Grading Scale – assignResult()</p>
        <div style="display:flex; gap:.8rem; flex-wrap:wrap;">
          <span class="badge badge-distinction">Distinction: ≥ 75%</span>
          <span class="badge badge-pass">Pass: 50% – 74%</span>
          <span class="badge badge-fail">Fail: &lt; 50%</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Invalid Mark Handling -->
  <div class="card">
    <div class="card-header">⚠️ Invalid Mark Handling – validateMark()</div>
    <div class="card-body">
      <p style="font-size:.9rem; color:#475569; margin-bottom:1rem;">
        The system detects and skips non-numeric values and marks outside the 0–100 range.
        Only valid marks are included in average calculations.
      </p>
      <table class="data-table">
        <thead><tr><th>Student</th><th>Invalid Values Detected</th><th>Marks Used</th><th>Impact</th></tr></thead>
        <tbody>
          <?php foreach ($students as $name => $data): ?>
            <?php if (!empty($data['invalid_marks'])): ?>
              <tr>
                <td><?= htmlspecialchars($name) ?></td>
                <td>
                  <?php foreach ($data['invalid_marks'] as $inv): ?>
                    <span class="badge badge-fail"><?= htmlspecialchars((string)$inv) ?></span>
                  <?php endforeach; ?>
                </td>
                <td><?= count($data['valid_marks']) ?> of <?= count($data['marks']) ?></td>
                <td style="font-size:.85rem; color:#64748b;">
                  Average calculated on <?= count($data['valid_marks']) ?> marks only
                </td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<footer>&copy; <?= date('Y') ?> Richfield Graduate Institute of Technology &mdash; Internet Programming 621</footer>
</body>
</html>
