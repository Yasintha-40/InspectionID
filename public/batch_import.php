<?php
require_once __DIR__ . '/../config/database.php';
$lastJobsResult = $conn->query('SELECT * FROM import_jobs ORDER BY id DESC LIMIT 5');
if ($lastJobsResult === false) {
    error_log('Unable to load import history: ' . $conn->error);
    $lastJobs = [];
} else {
    $lastJobs = $lastJobsResult->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Batch Import - Inspection ID System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="../css/batch-import.css">
</head>
<body>
  <header class="import-header">
    <div class="header-copy">
      <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back</a>
      <div><h1>BATCH IMPORT</h1><p>Validate and import officer records from Excel</p></div>
    </div>
    <div class="format-pill"><i class="fa-solid fa-file-excel"></i><span><strong>Excel format</strong> .xlsx · maximum 5 MB</span></div>
  </header>

  <main class="import-shell">
    <section class="stepper" aria-label="Import progress">
      <div class="step is-active" data-step="1"><span>1</span><div><strong>Upload</strong><small>Select workbook</small></div></div>
      <div class="step-line"></div>
      <div class="step" data-step="2"><span>2</span><div><strong>Validate</strong><small>Review records</small></div></div>
      <div class="step-line"></div>
      <div class="step" data-step="3"><span>3</span><div><strong>Import</strong><small>Save to database</small></div></div>
    </section>

    <section class="panel upload-panel" id="uploadPanel">
      <div class="panel-heading"><div><span class="eyebrow">Step 1</span><h2>Upload officer workbook</h2><p>The first worksheet must include Name, Address, NIC, and Email columns.</p></div></div>
      <label class="drop-zone" id="dropZone" for="workbookFile">
        <input id="workbookFile" type="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
        <span class="upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
        <strong>Drop your Excel file here</strong>
        <span>or click to browse from your computer</span>
        <em>Only .xlsx files up to 5 MB</em>
      </label>
      <div class="selected-file" id="selectedFile" hidden><i class="fa-solid fa-file-excel"></i><div><strong id="fileName"></strong><span id="fileSize"></span></div><button id="removeFile" type="button" aria-label="Remove selected file"><i class="fa-solid fa-xmark"></i></button></div>
      <div class="panel-actions"><a class="template-link" href="../database/New_formatted.xlsx" download><i class="fa-solid fa-download"></i> Download import template</a><button class="primary-button" id="validateButton" type="button" disabled>Validate workbook <i class="fa-solid fa-arrow-right"></i></button></div>
    </section>

    <section class="panel preview-panel" id="previewPanel" hidden>
      <div class="panel-heading preview-heading"><div><span class="eyebrow">Step 2</span><h2>Validation preview</h2><p id="previewDescription"></p></div><button class="secondary-button" id="chooseAnother" type="button"><i class="fa-solid fa-rotate-left"></i> Choose another file</button></div>
      <div class="summary-grid"><article><span>Total rows</span><strong id="totalRows">0</strong></article><article class="success"><span>Valid</span><strong id="validRows">0</strong></article><article class="warning"><span>Duplicates</span><strong id="duplicateRows">0</strong></article><article class="danger"><span>Errors</span><strong id="errorRows">0</strong></article></div>
      <div class="validation-alert" id="validationAlert"></div>
      <div class="preview-table-wrap"><table><thead><tr><th>Row</th><th>Status</th><th>Full name</th><th>NIC</th><th>Email</th><th>Address</th></tr></thead><tbody id="previewRows"></tbody></table></div>
      <div class="import-options"><div><h3>Duplicate records</h3><p>Choose how records with an existing NIC or email should be handled.</p></div><label><input type="radio" name="duplicatePolicy" value="skip" checked><span><strong>Skip existing</strong><small>Keep current database values</small></span></label><label><input type="radio" name="duplicatePolicy" value="update"><span><strong>Update existing</strong><small>Replace fields from this workbook</small></span></label></div>
      <div class="panel-actions"><button class="secondary-button" id="cancelPreview" type="button">Cancel</button><button class="primary-button" id="commitButton" type="button">Import valid records <i class="fa-solid fa-database"></i></button></div>
    </section>

    <section class="panel result-panel" id="resultPanel" hidden><div class="result-icon"><i class="fa-solid fa-circle-check"></i></div><h2>Import completed</h2><p id="resultMessage"></p><div class="summary-grid result-summary"><article class="success"><span>Inserted</span><strong id="insertedCount">0</strong></article><article><span>Updated</span><strong id="updatedCount">0</strong></article><article class="warning"><span>Skipped</span><strong id="skippedCount">0</strong></article></div><div class="panel-actions centered"><a class="secondary-button" href="dashboard.php">Return to dashboard</a><button class="primary-button" id="importAnother" type="button">Import another file</button></div></section>

    <section class="history-panel"><div class="history-heading"><div><span class="eyebrow">Audit trail</span><h2>Recent imports</h2></div></div><div class="history-list"><?php if (!$lastJobs): ?><p class="empty-history">No completed imports yet.</p><?php else: foreach ($lastJobs as $job): ?><article><span class="history-icon"><i class="fa-solid fa-file-excel"></i></span><div><strong><?= htmlspecialchars($job['original_filename']) ?></strong><small><?= date('d M Y, H:i', strtotime($job['created_at'])) ?> · <?= htmlspecialchars(ucfirst($job['duplicate_policy'])) ?> duplicates</small></div><div class="history-stats"><b><?= (int)$job['inserted_rows'] ?> inserted</b><span><?= (int)$job['updated_rows'] ?> updated · <?= (int)$job['skipped_rows'] ?> skipped</span></div></article><?php endforeach; endif; ?></div></section>
  </main>
  <div class="toast" id="toast" role="alert" aria-live="assertive"></div>
  <script src="../js/batch-import.js"></script>
</body>
</html>
