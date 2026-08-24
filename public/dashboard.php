<?php
// public/dashboard.php
// The dashboard is now a static shell that fetches data via AJAX
$result = false;
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
            <a href="add_officer.php" id="addOfficerButton" class="btn btn-add-officer">
                <i class="fas fa-user-plus"></i> ADD OFFICER
            </a>
            <a href="Bulkbrintmode.php" class="btn btn-warning"><i class="fas fa-print"></i> BULK PRINT MODE</a>
            <a href="batch_import.php" class="btn btn-outline-light">
                <i class="fas fa-file-excel"></i> BATCH IMPORT
            </a>
            <a href="settings.php" class="btn btn-settings" aria-label="System settings" title="System settings">
                <i class="fas fa-gear" aria-hidden="true"></i>
                <span>SETTINGS</span>
            </a>
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
                        <input type="text" name="inspection_id" id="inspection_id" class="form-control" placeholder="e.g. 199881900934 or 560091907V" required aria-describedby="inspectionIdError" autocomplete="off">
                        <button type="submit" class="btn btn-primary">FIND</button>
                    </div>
                    <small class="field-error" id="inspectionIdError" aria-live="polite"></small>
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
                    <label class="profile-photo profile-photo-upload" id="profilePhotoUpload" for="photoUploadInput" tabindex="0" role="button" aria-label="Change officer photo">
                        <img id="dispPhoto" src="" alt="Officer profile photo" onerror="this.style.display='none'; document.getElementById('dispPhotoFallback').style.display='inline-block';">
                        <i class="fas fa-user" id="dispPhotoFallback" style="display: none; font-size: 5rem; color: white;" aria-hidden="true"></i>
                        <span class="photo-upload-overlay"><i class="fas fa-camera" aria-hidden="true"></i><b>Change photo</b><small>JPG, PNG or WebP · max 5 MB</small></span>
                        <span class="photo-upload-progress" id="photoUploadProgress" hidden><i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Uploading...</span>
                    </label>
                    <input id="photoUploadInput" class="visually-hidden" type="file" accept="image/jpeg,image/png,image/webp">
                    <div id="photoUploadMessage" class="photo-upload-message" role="status" aria-live="polite"></div>
                    <h3 class="profile-name-short" id="dispShortName"></h3>
                    <span class="status-badge printed" id="dispStatus">PRINTED</span>
                    <button class="btn btn-outline-dark btn-block mt-3"><i class="fas fa-qrcode"></i> ONLINE PROFILE</button>
                </div>
                
                <div class="profile-details">
                    <form id="updateForm">
                        <input type="hidden" id="officerId">
                        
                        <div class="details-grid">
                            <div class="detail-item">
                                <label for="dispName">FULL NAME</label>
                                <input type="text" id="dispName" name="full_name" class="form-control editable-field" maxlength="255" required>
                            </div>
                            <div class="detail-item">
                                <label for="dispNicValue">NIC NUMBER</label>
                                <input type="text" id="dispNicValue" name="nic" class="form-control editable-field" maxlength="30" required>
                            </div>
                            <div class="detail-item">
                                <label for="dispNickname">NICKNAME</label>
                                <input type="text" id="dispNickname" name="nickname" class="form-control editable-field" maxlength="100">
                            </div>
                            <div class="detail-item">
                                <label for="dispEmail">EMAIL</label>
                                <input type="email" id="dispEmail" name="email" class="form-control editable-field" maxlength="150">
                            </div>
                            <div class="detail-item">
                                <label for="dispPhone">PHONE</label>
                                <input type="tel" id="dispPhone" name="phone" class="form-control editable-field" maxlength="30">
                            </div>
                            <div class="detail-item full-width">
                                <label for="dispDesignation">DESIGNATION</label>
                                <input type="text" id="dispDesignation" name="designation" class="form-control editable-field" maxlength="100">
                            </div>
                            <div class="detail-item">
                                <label for="dispStatusSelect">STATUS</label>
                                <select id="dispStatusSelect" name="status" class="form-control editable-field">
                                    <option>Active</option><option>Inactive</option><option>Expired</option><option>Suspended</option>
                                </select>
                            </div>
                            <div class="detail-item full-width">
                                <label for="dispAddress">REGISTERED ADDRESS</label>
                                <textarea id="dispAddress" name="address" class="form-control editable-field" rows="2"></textarea>
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
                            <button type="submit" id="saveOfficerButton" class="btn btn-primary" style="border-radius: 20px; padding: 10px 30px;"><i class="fas fa-save"></i> SAVE CHANGES &amp; GENERATE ID CARD</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- ID Card Preview included directly inside the results panel -->
            <?php include 'idcardpreview.php'; ?>
        </div>
        
    </main>

    <div class="notification-region" id="notificationRegion" aria-live="polite" aria-atomic="false"></div>

    <script>
        const notificationRegion = document.getElementById('notificationRegion');

        function notify(type, title, message, duration = 4500) {
            const icons = { success: 'fa-circle-check', error: 'fa-circle-exclamation', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
            const notification = document.createElement('div');
            notification.className = `system-notification ${type}`;
            notification.setAttribute('role', type === 'error' ? 'alert' : 'status');

            const icon = document.createElement('i');
            icon.className = `fas ${icons[type] || icons.info} notification-icon`;
            icon.setAttribute('aria-hidden', 'true');
            const copy = document.createElement('div');
            const heading = document.createElement('strong');
            heading.textContent = title;
            const body = document.createElement('p');
            body.textContent = message;
            copy.append(heading, body);
            const close = document.createElement('button');
            close.type = 'button';
            close.className = 'notification-close';
            close.setAttribute('aria-label', 'Dismiss notification');
            close.innerHTML = '<i class="fas fa-times" aria-hidden="true"></i>';
            const progress = document.createElement('span');
            progress.className = 'notification-timer';
            progress.style.animationDuration = `${duration}ms`;
            notification.append(icon, copy, close, progress);
            notificationRegion.append(notification);

            requestAnimationFrame(() => notification.classList.add('is-visible'));
            const dismiss = () => {
                notification.classList.remove('is-visible');
                window.setTimeout(() => notification.remove(), 220);
            };
            close.addEventListener('click', dismiss);
            window.setTimeout(dismiss, duration);
        }

        function setFieldError(input, message) {
            const error = document.getElementById(input.getAttribute('aria-describedby'));
            input.classList.toggle('is-invalid', Boolean(message));
            input.setAttribute('aria-invalid', message ? 'true' : 'false');
            if (error) error.textContent = message;
        }

        function isValidNic(value) {
            return /^(?:\d{9}[VX]|\d{12})$/i.test(value.replace(/[\s-]+/g, ''));
        }

        function closeResults() {
            document.getElementById('resultsPanel').style.display = 'none';
            document.getElementById('id-card-preview').style.display = 'none';
            document.querySelector('.main-content').classList.remove('has-results');
        }

        function extractFilename(path) {
            if (!path) return '';
            return path.split('\\').pop().split('/').pop();
        }

        const photoUploadInput = document.getElementById('photoUploadInput');
        const profilePhotoUpload = document.getElementById('profilePhotoUpload');
        const photoUploadProgress = document.getElementById('photoUploadProgress');
        const photoUploadMessage = document.getElementById('photoUploadMessage');

        profilePhotoUpload.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                photoUploadInput.click();
            }
        });

        photoUploadInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (!file) return;

            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!allowedTypes.includes(file.type) || file.size > 5 * 1024 * 1024) {
                photoUploadMessage.className = 'photo-upload-message error';
                photoUploadMessage.textContent = 'Choose a JPG, PNG or WebP image up to 5 MB.';
                notify('error', 'Photo not accepted', 'Choose a JPG, PNG or WebP image up to 5 MB.');
                this.value = '';
                return;
            }

            const officerId = document.getElementById('officerId').value;
            if (!officerId) {
                photoUploadMessage.className = 'photo-upload-message error';
                photoUploadMessage.textContent = 'Search for an officer before changing the photo.';
                notify('warning', 'No officer selected', 'Search for an officer before changing the photo.');
                this.value = '';
                return;
            }

            const currentPhoto = document.getElementById('dispPhoto');
            const previousSrc = currentPhoto.src;
            const previewUrl = URL.createObjectURL(file);
            currentPhoto.src = previewUrl;
            currentPhoto.style.display = 'block';
            document.getElementById('dispPhotoFallback').style.display = 'none';
            profilePhotoUpload.classList.add('is-uploading');
            photoUploadProgress.hidden = false;
            photoUploadMessage.textContent = '';

            const formData = new FormData();
            formData.append('id', officerId);
            formData.append('image', file);

            fetch('update_photo.php', { method: 'POST', body: formData })
                .then(response => response.json().then(data => ({ response, data })))
                .then(({ response, data }) => {
                    if (!response.ok || !data.success) throw new Error(data.message || 'Photo upload failed.');
                    const savedPhotoUrl = `${data.photo_url}${data.photo_url.includes('?') ? '&' : '?'}v=${Date.now()}`;
                    currentPhoto.src = savedPhotoUrl;
                    const cardPhoto = document.getElementById('cardPhoto');
                    cardPhoto.src = savedPhotoUrl;
                    cardPhoto.style.visibility = 'visible';
                    photoUploadMessage.className = 'photo-upload-message success';
                    photoUploadMessage.textContent = 'Photo updated successfully.';
                    notify('success', 'Photo updated', 'The officer profile and ID-card preview now use the new photo.');
                })
                .catch(error => {
                    currentPhoto.src = previousSrc;
                    photoUploadMessage.className = 'photo-upload-message error';
                    photoUploadMessage.textContent = error.message || 'Unable to update the photo.';
                    notify('error', 'Photo update failed', error.message || 'Unable to update the photo.');
                })
                .finally(() => {
                    URL.revokeObjectURL(previewUrl);
                    profilePhotoUpload.classList.remove('is-uploading');
                    photoUploadProgress.hidden = true;
                    photoUploadInput.value = '';
                });
        });

        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const nicInput = document.getElementById('inspection_id');
            const nic = nicInput.value.trim().toUpperCase();
            const messages = document.getElementById('search-results-messages');
            messages.innerHTML = '';
            if (!isValidNic(nic)) {
                setFieldError(nicInput, 'Enter a 12-digit NIC or an old NIC with 9 digits followed by V or X.');
                nicInput.focus();
                notify('warning', 'Check the NIC number', 'The NIC format is incomplete or invalid.');
                return;
            }
            setFieldError(nicInput, '');
            nicInput.value = nic.replace(/[\s-]+/g, '');
            
            fetch(`get_officer.php?nic=${encodeURIComponent(nic)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const o = data.officer;
                        
                        document.querySelector('.main-content').classList.add('has-results');
                        document.getElementById('resultsPanel').style.display = 'block';
                        
                        document.getElementById('officerId').value = o.id;
                        document.getElementById('hdrNic').innerText = o.NIC;
                        document.getElementById('dispName').value = o.NAME;
                        
                        const nameParts = o.NAME.split(' ');
                        document.getElementById('dispShortName').innerText = nameParts[nameParts.length - 1];
                        
                        document.getElementById('dispNicValue').value = o.NIC;
                        document.getElementById('dispNickname').value = o.nickname;
                        document.getElementById('dispEmail').value = o.email;
                        document.getElementById('dispPhone').value = o.phone;
                        document.getElementById('dispDesignation').value = o.designation;
                        document.getElementById('dispStatusSelect').value = o.status || 'Active';
                        document.getElementById('dispAddress').value = o.ADD;
                        
                        document.getElementById('dispIssueDate').value = o.issue_date;
                        document.getElementById('dispExpiryDate').value = o.expiry_date;
                        document.getElementById('dispStatus').innerText = o.status || 'Active';
                        
                        const photoEl = document.getElementById('dispPhoto');
                        photoEl.src = o.photo_url;
                        photoEl.style.display = 'block';
                        document.getElementById('dispPhotoFallback').style.display = 'none';
                        photoUploadMessage.textContent = '';
                        photoUploadMessage.className = 'photo-upload-message';
                        notify('success', 'Officer found', `${o.NAME} is ready to review and update.`, 3200);
                        
                        // Automatically show and populate the ID Card Preview
                        document.getElementById('id-card-preview').style.display = 'block';
                        document.getElementById('cardName').innerText = nameParts[nameParts.length - 1];
                        document.getElementById('cardNic').innerText = o.NIC;
                        document.getElementById('cardOfficerId').innerText = o.officer_id;
                        
                        // Format dates to DD.MM.YYYY
                        let issueD = o.issue_date || '';
                        let expiryD = o.expiry_date || '';
                        if (issueD) issueD = issueD.split('-').reverse().join('.');
                        if (expiryD) expiryD = expiryD.split('-').reverse().join('.');
                        
                        document.getElementById('cardIssueDate').innerText = issueD;
                        document.getElementById('cardExpiryDate').innerText = expiryD;
                        const cardPhoto = document.getElementById('cardPhoto');
                        cardPhoto.style.visibility = 'visible';
                        cardPhoto.src = o.photo_url;
                        
                        const cardQr = document.getElementById('cardQr');
                        if (o.qr_url) {
                            cardQr.style.visibility = 'visible';
                            cardQr.src = `${o.qr_url}&v=${Date.now()}`;
                        } else {
                            cardQr.removeAttribute('src');
                            cardQr.style.visibility = 'hidden';
                        }
                        
                    } else {
                        closeResults();
                        messages.innerHTML = `<div class="alert alert-danger" style="margin-top: 15px;">${data.message}</div>`;
                        notify('error', 'No officer found', data.message || 'No matching officer record was found.');
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    closeResults();
                    messages.innerHTML = `<div class="alert alert-danger" style="margin-top: 15px;">Network or server error.</div>`;
                    notify('error', 'Connection problem', 'The officer lookup could not reach the server. Please try again.');
                });
        });

        document.getElementById('inspection_id').addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) setFieldError(this, '');
        });

        document.getElementById('updateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const officerId = document.getElementById('officerId').value;
            const issueDate = document.getElementById('dispIssueDate').value;
            const expiryDate = document.getElementById('dispExpiryDate').value;

            const fullName = document.getElementById('dispName');
            const nicField = document.getElementById('dispNicValue');
            const emailField = document.getElementById('dispEmail');
            if (!fullName.value.trim()) {
                fullName.focus();
                notify('warning', 'Full name required', 'Enter the officer’s full name before saving.');
                return;
            }
            if (!isValidNic(nicField.value)) {
                nicField.focus();
                notify('warning', 'Invalid NIC number', 'Use a 12-digit NIC or 9 digits followed by V or X.');
                return;
            }
            if (emailField.value && !emailField.checkValidity()) {
                emailField.focus();
                notify('warning', 'Invalid email address', 'Enter a complete email address or leave the field empty.');
                return;
            }
            if ((issueDate || expiryDate) && (!issueDate || !expiryDate || expiryDate <= issueDate)) {
                document.getElementById(!issueDate ? 'dispIssueDate' : 'dispExpiryDate').focus();
                notify('warning', 'Check the card dates', 'Provide both dates and make the expiry date later than registration.');
                return;
            }

            const formData = new FormData(this);
            formData.append('id', officerId);
            formData.append('issue_date', issueDate);
            formData.append('expiry_date', expiryDate);

            const saveButton = document.getElementById('saveOfficerButton');
            const originalButtonHtml = saveButton.innerHTML;
            saveButton.disabled = true;
            saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> SAVING...';

            fetch('update_officer.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const updatedName = document.getElementById('dispName').value.trim();
                    const updatedNic = document.getElementById('dispNicValue').value.trim();
                    const nameParts = updatedName.split(/\s+/);
                    const shortName = nameParts[nameParts.length - 1];
                    document.getElementById('hdrNic').innerText = updatedNic;
                    document.getElementById('dispShortName').innerText = shortName;
                    document.getElementById('inspection_id').value = updatedNic;
                    
                    // Show preview
                    document.getElementById('id-card-preview').style.display = 'block';
                    
                    document.getElementById('cardName').innerText = shortName;
                    document.getElementById('cardNic').innerText = updatedNic;
                    
                    document.getElementById('cardIssueDate').innerText = issueDate.split('-').reverse().join('.');
                    document.getElementById('cardExpiryDate').innerText = expiryDate.split('-').reverse().join('.');
                    
                    document.getElementById('cardPhoto').style.visibility = 'visible';
                    document.getElementById('cardPhoto').src = document.getElementById('dispPhoto').src;
                    
                    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> GENERATING QR...';
                    const qrData = new URLSearchParams({ id: officerId });
                    return fetch('qr-generator.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                        body: qrData
                    })
                    .then(response => response.json().then(result => ({ response, result })))
                    .then(({ response, result }) => {
                        if (!response.ok || !result.success) throw new Error(result.message || 'QR generation failed.');
                        const cardQr = document.getElementById('cardQr');
                        cardQr.style.visibility = 'visible';
                        cardQr.src = `${result.qr_url}&v=${Date.now()}`;
                        document.getElementById('id-card-preview').scrollIntoView({ behavior: 'smooth' });
                        notify('success', 'Officer saved', `Changes were saved and ${result.file_name} was generated.`, 5500);
                    });
                } else {
                    throw new Error(data.message || 'The officer record could not be updated.');
                }
            })
            .catch(err => {
                console.error('Update error:', err);
                notify('error', 'Save failed', err.message || 'A network error occurred while saving or generating the QR code.', 6000);
            })
            .finally(() => {
                saveButton.disabled = false;
                saveButton.innerHTML = originalButtonHtml;
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
