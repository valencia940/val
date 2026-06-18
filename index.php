<?php
// ============================================================
// index.php - Campus Management System Main Menu
// ============================================================

// ---------- GLOBAL CONSTANTS ----------

// Parking
define('PERMIT_STUDENT',      450.00);
define('PERMIT_STAFF',        750.00);
define('PERMIT_VISITOR',      100.00);
define('MAX_PARKING_CAPACITY', 50);

// Library fines (per day late)
define('FINE_TEXTBOOK',   5.00);
define('FINE_JOURNAL',    3.00);
define('FINE_REFERENCE', 10.00);
define('MAX_FINE_LIMIT', 200.00);
define('LOAN_DAYS',       14);

require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Campus Management System</title>
<style>
  /* ---- Reset & Base ---- */
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f4f8;
    color: #2d3748;
    min-height: 100vh;
  }

  /* ---- Top Nav ---- */
  .top-nav {
    background: linear-gradient(135deg, #60a5fa 0%, #ff66c4 100%);
    color: #fff;
    padding: 0 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    height: 64px;
    box-shadow: 0 2px 8px rgba(0,0,0,.25);
  }
  .top-nav .brand { font-size: 1.3rem; font-weight: 700; letter-spacing: .5px; flex: 1; }
  .top-nav a {
    color: rgba(255,255,255,.85);
    text-decoration: none;
    padding: .4rem .9rem;
    border-radius: 6px;
    font-size: .92rem;
    transition: background .2s;
  }
  .top-nav a:hover, .top-nav a.active { background: rgba(255,255,255,.18); color: #fff; }

  /* ---- Hero ---- */
  .hero {
    background: linear-gradient(135deg, #60a5fa 0%, #ff66c4 100%);
    color: #fff;
    text-align: center;
    padding: 3.5rem 1rem 5rem;
  }
  .hero h1 { font-size: 2.4rem; font-weight: 800; margin-bottom: .6rem; }
  .hero p  { font-size: 1.1rem; opacity: .88; }

  /* ---- Card Grid ---- */
  .card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.8rem;
    max-width: 1100px;
    margin: -2.5rem auto 3rem;
    padding: 0 1.5rem;
  }
  .card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 20px rgba(0,0,0,.1);
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
  }
  .card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(0,0,0,.15); }
  .card-accent { height: 6px; }
  .card-parking  .card-accent { background: #ff66c4; }
  .card-library  .card-accent { background: #8b5cf6; }
  .card-performance .card-accent { background: #22c55e; }
  .card-body { padding: 1.6rem 1.8rem 2rem; }
  .card-icon { font-size: 2.6rem; margin-bottom: .8rem; }
  .card h3 { font-size: 1.2rem; font-weight: 700; margin-bottom: .5rem; }
  .card p  { font-size: .93rem; color: #64748b; line-height: 1.55; margin-bottom: 1.4rem; }
  .card-marks {
    display: inline-block;
    background: #eff6ff;
    color: #ff66c4;
    font-size: .8rem;
    font-weight: 600;
    padding: .25rem .7rem;
    border-radius: 20px;
    margin-bottom: 1rem;
  }
  .card-parking  .card-marks { background: #eff6ff; color: #ff66c4; }
  .card-library  .card-marks { background: #f5f3ff; color: #8b5cf6; }
  .card-performance .card-marks { background: #ecfdf5; color: #22c55e; }

  .btn {
    display: inline-block;
    padding: .65rem 1.4rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: .95rem;
    transition: opacity .2s, transform .15s;
  }
  .btn:hover { opacity: .9; transform: scale(1.02); }
  .btn-blue   { background: #ff66c4; color: #fff; }
  .btn-purple { background: #8b5cf6; color: #fff; }
  .btn-green  { background: #22c55e; color: #fff; }

  /* ---- Stats Bar ---- */
  .stats-bar {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
    max-width: 1100px;
    margin: 0 auto 2.5rem;
    padding: 1.4rem 2rem;
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
    justify-content: space-around;
  }
  .stat-item { text-align: center; }
  .stat-item .stat-value { font-size: 1.8rem; font-weight: 800; color: #60a5fa; }
  .stat-item .stat-label { font-size: .82rem; color: #64748b; margin-top: .2rem; }

  /* ---- Footer ---- */
  footer {
    text-align: center;
    padding: 1.5rem;
    font-size: .85rem;
    color: #94a3b8;
    border-top: 1px solid #e2e8f0;
    margin-top: 2rem;
  }
</style>
</head>
<body>

<nav class="top-nav">
  <span class="brand">🎓 CampusMS</span>
  <a href="index.php" class="active">Home</a>
  <a href="parking.php">Parking</a>
  <a href="library.php">Library</a>
  <a href="performance.php">Performance</a>
</nav>

<div class="hero">
  <h1>Campus Management System</h1>
  <p>Richfield Graduate Institute of Technology &mdash; Integrated Campus Modules</p>
</div>

<div class="card-grid">

  <div class="card card-parking">
    <div class="card-accent"></div>
    <div class="card-body">
      <div class="card-icon">🅿️</div>
      <span class="card-marks">30 Marks</span>
      <h3>Parking Permit Module</h3>
      <p>Issue and manage parking permits for Students, Staff, and Visitors. Enforces age restrictions and capacity limits with full revenue reporting.</p>
      <a href="parking.php" class="btn btn-blue">Open Module →</a>
    </div>
  </div>

  <div class="card card-library">
    <div class="card-accent"></div>
    <div class="card-body">
      <div class="card-icon">📚</div>
      <span class="card-marks">40 Marks</span>
      <h3>Library Borrowing Module</h3>
      <p>Track book loans across Textbooks, Journals, and Reference Books. Automatically calculates late-return fines and enforces borrowing restrictions.</p>
      <a href="library.php" class="btn btn-purple">Open Module →</a>
    </div>
  </div>

  <div class="card card-performance">
    <div class="card-accent"></div>
    <div class="card-body">
      <div class="card-icon">📊</div>
      <span class="card-marks">30 Marks</span>
      <h3>Student Performance Module</h3>
      <p>Analyse student marks across six assessments. Calculates averages, assigns Pass/Fail/Distinction results, and generates class-wide statistics.</p>
      <a href="performance.php" class="btn btn-green">Open Module →</a>
    </div>
  </div>

</div>

<div class="stats-bar" style="padding: 0 1.5rem; max-width:1100px; margin: 0 auto 3rem;">
  <div class="stat-item">
    <div class="stat-value">3</div>
    <div class="stat-label">Integrated Modules</div>
  </div>
  <div class="stat-item">
    <div class="stat-value">100</div>
    <div class="stat-label">Total Marks</div>
  </div>
  <div class="stat-item">
    <div class="stat-value">R<?= number_format(PERMIT_STUDENT + PERMIT_STAFF + PERMIT_VISITOR, 0) ?></div>
    <div class="stat-label">Permit Price Range</div>
  </div>
  <div class="stat-item">
    <div class="stat-value"><?= MAX_PARKING_CAPACITY ?></div>
    <div class="stat-label">Max Parking Bays</div>
  </div>
  <div class="stat-item">
    <div class="stat-value">R<?= number_format(MAX_FINE_LIMIT, 0) ?></div>
    <div class="stat-label">Max Library Fine</div>
  </div>
</div>

<footer>
  &copy; <?= date('Y') ?> Richfield Graduate Institute of Technology &mdash; Internet Programming 621 Assignment
</footer>

</body>
</html>
