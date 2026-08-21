<?php
// public/dashboard.php
// The dashboard is now a static shell that fetches data via AJAX
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspection ID Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" crossorigin="anonymous"></script>
</head>
<body>

    <header class="main-header">
        <div class="header-left">
            <div class="logo-circle">
                <img src="../img/logo.jpg" alt="SLTDA Logo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: contain; background-color: white;">
            </div>
            <div class="header-titles">
                <h1>INSPECTION ID MANAGEMENT SYSTEM</h1>
                <p>Official Administrative Portal</p>
            </div>
        </div>
        <div class="header-right">
            <a href="Bulkbrintmode.php" class="btn btn-warning"><i class="fas fa-print"></i> BULK PRINT MODE</a>
            <form action="import.php" method="POST" enctype="multipart/form-data" id="importForm" style="display: inline;">
                <input type="file" name="csv_file" id="csv_file" accept=".csv" style="display: none;" onchange="document.getElementById('importForm').submit();">
                <button type="button" class="btn btn-outline-light" onclick="document.getElementById('csv_file').click();">
                    <i class="fas fa-file-excel"></i> BATCH IMPORT (CSV)
                </button>
            </form>
        </div>
    </header>

    <main class="main-content <?php echo $result ? 'has-results' : ''; ?>">
        
        <!-- LEFT COLUMN: Search Form -->
        <div class="search-card">
            <div class="search-header">
                <h2>Record Search</h2>
            </div>

            <form id="searchForm" class="search-form">
                <div class="form-group" style="display: none;">
                    <input type="hidden" name="category" value="national">
                </div>

                <div class="form-group">
                    <label for="inspection_id">NATIONAL IDENTITY CARD (NIC) NUMBER</label>
                    <div class="input-group">
                        <input type="text" name="inspection_id" id="inspection_id" class="form-control" placeholder="e.g. 199881900934 or 560091907V" required>
                        <button type="submit" class="btn btn-primary">FIND</button>
                    </div>
                </div>
            </form>
            
            <div id="search-results-messages"></div>
        </div>

        <!-- RIGHT COLUMN: Search Results Panel -->
        <div class="results-panel" id="resultsPanel" style="display: none;">
            <div class="results-header">
                <span class="result-id" id="hdrNic"></span>
                <button type="button" class="btn-close-results" onclick="closeResults()"><i class="fas fa-times"></i> CLOSE</button>
            </div>
            
            <div class="results-body">
                <div class="profile-sidebar">
                    <div class="profile-photo">
                        <img id="dispPhoto" src="" alt="Profile Photo" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                        <i class="fas fa-user" id="dispPhotoFallback" style="display: none; font-size: 5rem; color: white;"></i>
                    </div>
                    <h3 class="profile-name-short" id="dispShortName"></h3>
                    <span class="status-badge printed" id="dispStatus">PRINTED</span>
                    <button class="btn btn-outline-dark btn-block mt-3"><i class="fas fa-qrcode"></i> ONLINE PROFILE</button>
                </div>
                
                <div class="profile-details">
                    <form id="updateForm">
                        <input type="hidden" id="officerId">
                        
                        <div class="details-grid">
                            <div class="detail-item">
                                <label>FULL NAME</label>
                                <p id="dispName"></p>
                            </div>
                            <div class="detail-item">
                                <label>NIC NUMBER</label>
                                <p id="dispNicValue"></p>
                            </div>
                            <div class="detail-item">
                                <label>CATEGORY</label>
                                <p id="dispCategory"></p>
                            </div>
                            <div class="detail-item">
                                <label>PROVINCE</label>
                                <p id="dispProvince"></p>
                            </div>
                            <div class="detail-item full-width">
                                <label>DESIGNATION</label>
                                <p id="dispDesignation"></p>
                            </div>
                            <div class="detail-item full-width">
                                <label>REGISTERED ADDRESS</label>
                                <p id="dispAddress"></p>
                            </div>
                        </div>
                        
                        <div class="dates-grid mt-4">
                            <div class="form-group">
                                <label>REGISTRATION DATE</label>
                                <div class="input-with-icon">
                                    <input type="date" id="dispIssueDate" class="form-control" style="border: 1px solid #e2e8f0; border-radius: 4px;">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>EXPIRY DATE</label>
                                <div class="input-with-icon">
                                    <input type="date" id="dispExpiryDate" class="form-control" style="border: 1px solid #e2e8f0; border-radius: 4px;">
                                </div>
                            </div>
                        </div>
                        
                        <div class="actions-row mt-4" style="text-align: right;">
                            <button type="submit" class="btn btn-primary" style="border-radius: 20px; padding: 10px 30px;"><i class="fas fa-print"></i> GENERATE ID CARD</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- ID Card Preview included directly inside the results panel -->
            <?php include 'idcardpreview.php'; ?>
        </div>
        
    </main>

    <script>
        function closeResults() {
            document.getElementById('resultsPanel').style.display = 'none';
            document.getElementById('id-card-preview').style.display = 'none';
            document.querySelector('.main-content').classList.remove('has-results');
        }

        function extractFilename(path) {
            if (!path) return '';
            return path.split('\\').pop().split('/').pop();
        }

        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const nic = document.getElementById('inspection_id').value;
            const messages = document.getElementById('search-results-messages');
            messages.innerHTML = '';
            
            fetch(`get_officer.php?nic=${encodeURIComponent(nic)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const o = data.officer;
                        
                        document.querySelector('.main-content').classList.add('has-results');
                        document.getElementById('resultsPanel').style.display = 'block';
                        
                        document.getElementById('officerId').value = o.id;
                        document.getElementById('hdrNic').innerText = o.NIC;
                        document.getElementById('dispName').innerText = o.NAME;
                        
                        const nameParts = o.NAME.split(' ');
                        document.getElementById('dispShortName').innerText = nameParts[nameParts.length - 1];
                        
                        document.getElementById('dispNicValue').innerText = o.NIC;
                        document.getElementById('dispCategory').innerText = o.category;
                        document.getElementById('dispProvince').innerText = o.province;
                        document.getElementById('dispDesignation').innerText = o.designation;
                        document.getElementById('dispAddress').innerText = o.ADD;
                        
                        document.getElementById('dispIssueDate').value = o.issue_date;
                        document.getElementById('dispExpiryDate').value = o.expiry_date;
                        document.getElementById('dispStatus').innerText = o.status || 'Active';
                        
                        const photoFile = extractFilename(o.photo);
                        const photoEl = document.getElementById('dispPhoto');
                        if (photoFile) {
                            photoEl.src = `../photos/${photoFile}`;
                            photoEl.style.display = 'block';
                            document.getElementById('dispPhotoFallback').style.display = 'none';
                        } else {
                            photoEl.style.display = 'none';
                            document.getElementById('dispPhotoFallback').style.display = 'inline-block';
                        }
                        
                        // Automatically show and populate the ID Card Preview
                        document.getElementById('id-card-preview').style.display = 'block';
                        document.getElementById('cardName').innerText = nameParts[nameParts.length - 1];
                        document.getElementById('cardNic').innerText = o.NIC;
                        
                        // Format dates to DD.MM.YYYY
                        let issueD = o.issue_date || '';
                        let expiryD = o.expiry_date || '';
                        if (issueD) issueD = issueD.split('-').reverse().join('.');
                        if (expiryD) expiryD = expiryD.split('-').reverse().join('.');
                        
                        document.getElementById('cardIssueDate').innerText = issueD;
                        document.getElementById('cardExpiryDate').innerText = expiryD;
                        document.getElementById('cardPhoto').src = photoFile ? `../photos/${photoFile}` : '';
                        
                        // Populate QR Code
                        const qrProfileUrl = 'https://sltda.gov.lk/inspection?nic=' + encodeURIComponent(o.NIC);
                        document.getElementById('cardQr').src = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=5&data=${encodeURIComponent(qrProfileUrl)}`;
                        
                    } else {
                        closeResults();
                        messages.innerHTML = `<div class="alert alert-danger" style="margin-top: 15px;">${data.message}</div>`;
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    closeResults();
                    messages.innerHTML = `<div class="alert alert-danger" style="margin-top: 15px;">Network or server error.</div>`;
                });
        });

        document.getElementById('updateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const officerId = document.getElementById('officerId').value;
            const issueDate = document.getElementById('dispIssueDate').value;
            const expiryDate = document.getElementById('dispExpiryDate').value;

            const formData = new FormData();
            formData.append('id', officerId);
            formData.append('issue_date', issueDate);
            formData.append('expiry_date', expiryDate);

            fetch('update_officer.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Record updated and ID generated!');
                    document.getElementById('dispStatus').innerText = 'Printed';
                    
                    // Show preview
                    document.getElementById('id-card-preview').style.display = 'block';
                    
                    document.getElementById('cardName').innerText = document.getElementById('dispShortName').innerText;
                    document.getElementById('cardNic').innerText = document.getElementById('dispNicValue').innerText;
                    
                    document.getElementById('cardIssueDate').innerText = issueDate.split('-').reverse().join('.');
                    document.getElementById('cardExpiryDate').innerText = expiryDate.split('-').reverse().join('.');
                    
                    document.getElementById('cardPhoto').src = document.getElementById('dispPhoto').src;
                    
                    // Populate QR Code
                    const qrProfileUrl2 = 'https://sltda.gov.lk/inspection?nic=' + encodeURIComponent(document.getElementById('dispNicValue').innerText);
                    document.getElementById('cardQr').src = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=5&data=${encodeURIComponent(qrProfileUrl2)}`;
                    
                    // Scroll to preview
                    document.getElementById('id-card-preview').scrollIntoView({ behavior: 'smooth' });
                } else {
                    alert('Error updating record: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Update error:', err);
                alert('A network error occurred while updating.');
            });
        });
        
        function downloadPDF() {
            const frontCard = document.querySelector('.id-card.front');
            const backCard = document.querySelector('.id-card.back');
            const { jsPDF } = window.jspdf;
            
            // Show loading state
            const btn = document.querySelector('.btn-outline-dark .fa-file-pdf').parentNode;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            btn.disabled = true;
            
            // PDF Settings (CR80: 54x85.6 mm)
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: [54.0, 85.6],
                compress: true
            });
            
            html2canvas(frontCard, { scale: 3, useCORS: true, backgroundColor: null }).then(frontCanvas => {
                const frontImg = frontCanvas.toDataURL('image/png');
                pdf.addImage(frontImg, 'PNG', 0, 0, 54.0, 85.6);
                
                html2canvas(backCard, { scale: 3, useCORS: true, backgroundColor: null }).then(backCanvas => {
                    const backImg = backCanvas.toDataURL('image/png');
                    pdf.addPage();
                    pdf.addImage(backImg, 'PNG', 0, 0, 54.0, 85.6);
                    
                    const nic = document.getElementById('cardNic').innerText || 'card';
                    pdf.save(`ID_${nic}.pdf`);
                    
                    // Reset button
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            });
        }
    </script>
</body>
</html>
