<?php
require_once 'config/database.php';

$message = '';
$status = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = 'Error uploading file.';
    } else {
        $filename = $file['tmp_name'];
        
        // Open the file for reading
        if (($handle = fopen($filename, "r")) !== FALSE) {
            
            // Get the first row, which contains the column headers
            $headers = fgetcsv($handle, 1000, ",");
            
            if ($headers) {
                // Remove BOM if present (sometimes Excel adds this)
                $headers[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $headers[0]);
                
                // Map header names to their index
                $headerMap = [];
                foreach ($headers as $index => $header) {
                    $headerMap[trim($header)] = $index;
                }
                
                // Check if required columns exist in the CSV
                $requiredColumns = ['NAME', 'qr', 'ADD', 'NIC', 'EMAIL', 'photo'];
                $hasAllColumns = true;
                foreach ($requiredColumns as $col) {
                    if (!isset($headerMap[$col])) {
                        $hasAllColumns = false;
                        $message = "Missing required column in CSV: " . htmlspecialchars($col);
                        break;
                    }
                }
                
                if ($hasAllColumns) {
                    $rowCount = 0;
                    
                    // Here is where you would prepare your SQL statement, for example:
                    // $stmt = $pdo->prepare("INSERT INTO inspectors (name, qr, address, nic, email, photo) VALUES (?, ?, ?, ?, ?, ?)");
                    
                    // Loop through the remaining rows
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        
                        // Extract data based on header positions
                        $name = $data[$headerMap['NAME']] ?? '';
                        $qr = $data[$headerMap['qr']] ?? '';
                        $address = $data[$headerMap['ADD']] ?? '';
                        $nic = $data[$headerMap['NIC']] ?? '';
                        $email = $data[$headerMap['EMAIL']] ?? '';
                        $photo = $data[$headerMap['photo']] ?? '';
                        
                        // Skip empty rows
                        if (empty($name) && empty($nic)) continue;
                        
                        /* 
                        // Once database is connected, uncomment this block to actually insert
                        try {
                            $stmt->execute([$name, $qr, $address, $nic, $email, $photo]);
                            $rowCount++;
                        } catch(PDOException $e) {
                            // Handle insertion error
                        }
                        */
                        
                        // For now, just count the rows to show it works
                        $rowCount++;
                    }
                    
                    $status = 'success';
                    $message = "Successfully processed " . $rowCount . " records from the CSV file!";
                }
            } else {
                $message = 'The CSV file appears to be empty.';
            }
            fclose($handle);
        } else {
            $message = 'Could not read the uploaded file.';
        }
    }
} else {
    $message = 'No file was uploaded.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Results - Inspection ID Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="header-left">
            <div class="logo-circle">
                <img src="img/logo.png" alt="SLTDA Logo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: contain; background-color: white;">
            </div>
            <div class="header-titles">
                <h1>INSPECTION ID MANAGEMENT SYSTEM</h1>
                <p>Official Administrative Portal</p>
            </div>
        </div>
        <div class="header-right">
            <a href="index.php" class="btn btn-outline-light"><i class="fas fa-arrow-left"></i> BACK TO DASHBOARD</a>
        </div>
    </header>

    <main class="main-content">
        <div class="search-card">
            <div class="search-header">
                <h2>Batch Import Results</h2>
                <p>Status of your CSV file upload.</p>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <?php if ($status === 'success'): ?>
                    <i class="fas fa-check-circle" style="font-size: 4rem; color: #16a34a; margin-bottom: 20px;"></i>
                    <h3 style="color: #16a34a; margin-bottom: 10px;">Import Successful!</h3>
                    <p style="font-size: 1.1rem;"><?php echo htmlspecialchars($message); ?></p>
                    <p style="margin-top: 15px; color: var(--text-muted); font-size: 0.9rem;">
                        (Note: The database connection in <code>config/database.php</code> is not fully configured yet, so this was a dry run to verify the file format.)
                    </p>
                <?php else: ?>
                    <i class="fas fa-times-circle" style="font-size: 4rem; color: #dc2626; margin-bottom: 20px;"></i>
                    <h3 style="color: #dc2626; margin-bottom: 10px;">Import Failed</h3>
                    <p style="font-size: 1.1rem; color: #dc2626;"><?php echo htmlspecialchars($message); ?></p>
                <?php endif; ?>
                
                <div style="margin-top: 40px;">
                    <a href="index.php" class="btn btn-primary" style="border-radius: 4px; display: inline-block; padding: 12px 30px;">Return to Dashboard</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
