<?php
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bulk Print Mode - Inspection ID System</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"><link rel="stylesheet" href="../css/bulk-print.css">
</head><body>
<header class="bulk-hero">
 <div class="hero-title-group"><a class="back-button" href="dashboard.php"><span class="back-icon"><i class="fa-solid fa-arrow-left"></i></span> Back</a><div><h1>BULK PRINT MODE</h1><p>Select up to <strong>15</strong> guide records to send to printer</p></div></div>
 <div class="hero-filters">
  <label>Filter Type<select id="typeFilter"><option value="all">All Data</option><option value="active">Active</option><option value="ready">Ready to Print</option><option value="missing_dates">Missing Dates</option><option value="expired">Expired</option></select></label>
  <button id="loadGuides" class="load-button" type="button">Load Guides</button>
 </div>
</header>
<main class="page-shell">
 <section class="metric-grid" aria-label="Bulk print statistics"><article class="metric-card"><span>Total Records</span><strong id="totalRecords">0</strong></article><article class="metric-card"><span>Selected</span><strong><b id="selectedMetric">0</b> / 15</strong></article><article class="metric-card"><span>Ready to Print</span><strong id="readyRecords">0</strong></article></section>
 <section class="records-card">
  <div class="action-bar"><div class="selection-summary"><span>Selection Summary</span><p><strong id="summaryCount">0</strong> / 15 records selected</p></div><div class="date-field"><label for="issueDate">Issued Date</label><input id="issueDate" type="date"></div><div class="date-field"><label for="expiryDate">Expiry Date</label><input id="expiryDate" type="date"></div><button id="printSelected" class="print-button" type="button" disabled><i class="fa-solid fa-print"></i> Print Selected (Max 15)</button></div>
  <div id="statusMessage" class="status-message" role="status" aria-live="polite"></div>
  <div class="table-scroll"><table><thead><tr><th class="select-column"><button id="selectAll" type="button">Select</button></th><th>Guide ID</th><th>Full Name</th><th>Nick Name</th><th>NIC Number</th><th>Languages</th><th>Address</th></tr></thead><tbody id="guideRows"><tr><td colspan="7" class="table-state"><i class="fa-solid fa-circle-notch fa-spin"></i> Loading guides...</td></tr></tbody></table></div>
 </section>
</main>
<form id="printForm" action="bulk_print_sheet.php" method="post" target="_blank"><input type="hidden" name="officer_ids" id="printIds"><input type="hidden" name="issue_date" id="printIssueDate"><input type="hidden" name="expiry_date" id="printExpiryDate"></form>
<script src="../js/bulk-print.js"></script></body></html>
