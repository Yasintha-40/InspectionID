<!-- ID CARD PREVIEW SECTION -->
<div id="id-card-preview" class="id-preview-section" style="display: none; width: 100%; margin-top: 40px; padding-top: 40px; border-top: 1px solid #e2e8f0;">
    <div class="preview-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2>ID Card Preview</h2>
            <p style="color: #64748b;">Review the generated card before printing.</p>
        </div>
        <button type="button" class="btn-close" style="background: white; border: 1px solid #cbd5e1; padding: 10px; border-radius: 4px; cursor: pointer;" onclick="document.getElementById('id-card-preview').style.display='none';"><i class="fas fa-times"></i></button>
    </div>
    
    <div class="cards-container" style="display: flex; gap: 30px; justify-content: center; background: #f8fafc; padding: 40px; border-radius: 8px; border: 1px solid #e2e8f0;">
        
        <!-- Front Card -->
        <div class="id-card front" style="background: white; border-radius: 12px; width: 300px; height: 480px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); border: 1px solid #e2e8f0; position: relative; overflow: hidden; display: flex; flex-direction: column;">
            
            <!-- Blue Curved Arcs -->
            <div style="position: absolute; width: 400px; height: 400px; border: 18px solid #5784C5; border-radius: 50%; left: -50px; top: -330px; pointer-events: none;"></div>
            <div style="position: absolute; width: 400px; height: 400px; border: 18px solid #5784C5; border-radius: 50%; left: -50px; bottom: -330px; pointer-events: none;"></div>
            
            <div class="card-content text-center" style="position: relative; z-index: 10; padding-top: 45px; height: 100%; display: flex; flex-direction: column;">
                
                <div style="display: flex; justify-content: center; gap: 15px; margin-bottom: 8px;">
                    <img src="../img/logo.jpg" alt="Logo 1" style="width: 45px; height: 45px; object-fit: contain;">
                    <!-- Placeholder for second logo -->
                    <img src="../img/logo.jpg" alt="Logo 2" style="width: 45px; height: 45px; object-fit: contain;">
                </div>
                
                <h4 style="font-size: 14px; font-weight: 700; margin: 5px 0 15px 0; color: #1e293b; line-height: 1.2;">
                    SRI LANKA TOURISM<br>DEVELOPMENT AUTHORITY
                </h4>
                
                <div class="photo-frame" style="width: 130px; height: 130px; margin: 0 auto 15px auto; border: 1px solid #cbd5e1; padding: 2px;">
                    <img id="cardPhoto" src="" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src=''">
                </div>
                
                <h3 id="cardDesignation" style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 5px 0;">Inspection Officer</h3>
                <p id="cardName" style="font-size: 15px; color: #334155; font-weight: 500; margin: 0 0 5px 0;"></p>
                <p id="cardNic" style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0 0 15px 0;"></p>
                
                <div style="margin-top: auto; padding-bottom: 30px; display: flex; flex-direction: column; align-items: center; gap: 2px;">
                    <div style="display: flex; width: 200px; justify-content: space-between; font-size: 12px; font-weight: 600; color: #1e293b;">
                        <span>Issued Date</span>
                        <span id="cardIssueDate"></span>
                    </div>
                    <div style="display: flex; width: 200px; justify-content: space-between; font-size: 12px; font-weight: 600; color: #1e293b;">
                        <span>Expiry Date</span>
                        <span id="cardExpiryDate"></span>
                    </div>
                </div>
                
            </div>
        </div>

        <!-- Back Card -->
        <div class="id-card back" style="background: white; border-radius: 12px; width: 300px; height: 480px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); border: 1px solid #e2e8f0; position: relative; overflow: hidden; display: flex; flex-direction: column;">
            
            <!-- Blue Curved Arcs -->
            <div style="position: absolute; width: 400px; height: 400px; border: 18px solid #5784C5; border-radius: 50%; left: -50px; top: -330px; pointer-events: none;"></div>
            <div style="position: absolute; width: 400px; height: 400px; border: 18px solid #5784C5; border-radius: 50%; left: -50px; bottom: -330px; pointer-events: none;"></div>
            
            <div class="card-content text-center" style="position: relative; z-index: 10; padding: 45px 20px 20px 20px; height: 100%; display: flex; flex-direction: column;">
                
                <!-- QR Code centered at top -->
                <div style="display: flex; justify-content: center; margin-bottom: 20px;">
                    <img id="cardQr" src="" alt="QR" style="width: 100px; height: 100px;" onerror="this.src=''">
                </div>
                
                <!-- Signatures placeholder -->
                <div style="display: flex; justify-content: space-between; align-items: flex-end; padding: 0 10px; margin-bottom: 20px; height: 60px;">
                    <!-- Placeholder signature 1 -->
                    <div style="width: 80px; height: 40px; border-bottom: 1px dashed #94a3b8; position: relative;">
                        <!-- You can insert img tag here for sig 1 later -->
                    </div>
                    <!-- Placeholder signature 2 -->
                    <div style="width: 80px; height: 40px; border-bottom: 1px dashed #94a3b8; position: relative;">
                        <!-- You can insert img tag here for sig 2 later -->
                    </div>
                </div>
                
                <h4 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 15px 0;">Director General</h4>
                
                <p style="font-size: 11px; font-weight: 600; color: #334155; margin: 0 0 5px 0;">Sri Lanka Tourism Development Authority</p>
                <p style="font-size: 18px; font-weight: 800; color: #1e293b; margin: 0 0 5px 0;">Hotline : 1912</p>
                <p style="font-size: 11px; font-weight: 600; color: #334155; margin: 0 0 15px 0;">Tourism Information and Complaint</p>
                
                <p style="font-size: 11px; color: #475569; margin: 0 0 15px 0;">If found please contact or Hand over to:</p>
                
                <div style="margin-top: auto; padding-bottom: 25px;">
                    <p style="font-size: 10px; font-weight: 700; color: #1e293b; margin: 0 0 2px 0;">Sri Lanka Tourism Development Authority</p>
                    <p style="font-size: 10px; color: #1e293b; margin: 0 0 2px 0;">No:80, Galle Road, Colombo 03</p>
                    <p style="font-size: 10px; color: #1e293b; margin: 0;">Tel: 011-24286800 Ext: 405</p>
                </div>
            </div>
        </div>
        
    </div>

    <div style="text-align: left; margin-top: 20px;">
        <button type="button" class="btn btn-outline-dark" style="background: white; border: 1px solid #cbd5e1; color: #000; border-radius: 20px;" onclick="downloadPDF()"><i class="fas fa-file-pdf"></i> Download PDF Print Standard</button>
    </div>
    
    <div class="pdf-settings" style="margin-top: 40px; background: white; padding: 30px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h3 style="margin-bottom: 20px; font-size: 16px;">PDF Settings</h3>
        <table style="width: 100%; font-size: 12px;">
            <tr><td style="padding: 8px 0; width: 200px; font-weight: 500;">Printer</td><td style="color: #475569;">Zebra ZC300 (Duplex CR80)</td></tr>
            <tr><td style="padding: 8px 0; font-weight: 500;">Card Size</td><td style="color: #475569;">CR80</td></tr>
            <tr><td style="padding: 8px 0; font-weight: 500;">Width</td><td style="color: #475569;">85.6 mm</td></tr>
            <tr><td style="padding: 8px 0; font-weight: 500;">Height</td><td style="color: #475569;">54.0 mm</td></tr>
            <tr><td style="padding: 8px 0; font-weight: 500;">Orientation</td><td style="color: #475569;">Portrait (Vertical)</td></tr>
            <tr><td style="padding: 8px 0; font-weight: 500;">Print Type</td><td style="color: #475569;">Color (or Mono)</td></tr>
            <tr><td style="padding: 8px 0; font-weight: 500;">Card Type</td><td style="color: #475569;">PVC</td></tr>
            <tr><td style="padding: 8px 0; font-weight: 500;">Thickness</td><td style="color: #475569;">30 mil (0.76 mm)</td></tr>
            <tr><td style="padding: 8px 0; font-weight: 500;">Driver</td><td style="color: #475569;">CardStudio ZC300 Direct-to-Card</td></tr>
        </table>
    </div>
</div>
