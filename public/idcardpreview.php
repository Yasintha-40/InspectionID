<!-- ID CARD PREVIEW SECTION -->
<div id="id-card-preview" class="id-preview-section" style="display: none; width: 100%; margin-top: 40px; padding-top: 40px; border-top: 1px solid #e2e8f0;">
    <div class="preview-header">
        <div>
            <h2>ID Card Preview</h2>
            <p>Review the generated card before printing.</p>
        </div>
        <button type="button" class="template-preview-close" aria-label="Close ID card preview" onclick="document.getElementById('id-card-preview').style.display='none';"><i class="fas fa-times"></i></button>
    </div>

    <div class="cards-container template-cards-container">
        <div class="id-card template-id-card front" aria-label="ID card front preview">
            <img class="id-template-art" src="../img/Untitled-1-Recovered.png" alt="">
            <img id="cardPhoto" class="template-card-photo" src="" alt="Officer photo" onerror="this.style.visibility='hidden'">
            <div class="template-front-details">
                <div id="cardName" class="template-card-name"></div>
                <div id="cardNic" class="template-card-nic"></div>
                <div id="cardOfficerId" class="template-card-officer-id"></div>
                <div class="template-card-dates">
                    <span>Issued: <b id="cardIssueDate"></b></span>
                    <span>Expires: <b id="cardExpiryDate"></b></span>
                </div>
            </div>
        </div>

        <div class="id-card template-id-card back" aria-label="ID card back preview">
            <img class="id-template-art" src="../img/Untitled-2-Recovered.png" alt="">
            <img id="cardQr" class="template-card-qr" src="" alt="Officer profile QR code" onerror="this.style.visibility='hidden'">
        </div>
    </div>

    <div class="template-preview-actions">
        <button type="button" class="btn btn-outline-dark template-download-button" onclick="downloadPDF()"><i class="fas fa-file-pdf"></i> Download PDF Print Standard</button>
    </div>

    <div class="pdf-settings">
        <h3>PDF Settings</h3>
        <table>
            <tr><td>Printer</td><td>Zebra ZC300 (Duplex CR80)</td></tr>
            <tr><td>Card Size</td><td>CR80</td></tr>
            <tr><td>Width</td><td>54.0 mm</td></tr>
            <tr><td>Height</td><td>85.6 mm</td></tr>
            <tr><td>Orientation</td><td>Portrait (Vertical)</td></tr>
            <tr><td>Print Type</td><td>Color (or Mono)</td></tr>
            <tr><td>Card Type</td><td>PVC, 30 mil (0.76 mm)</td></tr>
        </table>
    </div>
</div>
