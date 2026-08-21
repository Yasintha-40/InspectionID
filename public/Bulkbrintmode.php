<?php
// public/Bulkbrintmode.php
// Placeholder for Bulk Print Mode implementation
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bulk Print Mode - Inspection ID System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="header-left">
            <div class="logo-circle">
                <img src="../img/logo.jpg" alt="SLTDA Logo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: contain; background-color: white;">
            </div>
            <div class="header-titles">
                <h1>INSPECTION ID MANAGEMENT SYSTEM</h1>
                <p>Bulk Print Mode</p>
            </div>
        </div>
        <div class="header-right">
            <a href="dashboard.php" class="btn btn-outline-light"><i class="fas fa-arrow-left"></i> BACK TO DASHBOARD</a>
        </div>
    </header>

    <main class="main-content" style="padding: 40px; text-align: center;">
        <i class="fas fa-print" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 20px;"></i>
        <h2>Bulk Print Mode</h2>
        <p style="color: #64748b; max-width: 600px; margin: 0 auto;">This section allows you to select multiple officers and send their generated ID cards to the printer in a single batch.</p>
        
        <div style="margin-top: 40px; padding: 30px; background: white; border-radius: 8px; border: 1px solid #e2e8f0; max-width: 800px; margin: 40px auto 0;">
            <p><strong>Coming Soon:</strong> The database integration for batch selecting profiles and pushing directly to the Zebra ZC300 will be deployed here.</p>
        </div>
    </main>
</body>
</html>
