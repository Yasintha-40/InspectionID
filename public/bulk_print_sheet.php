<?php
require_once __DIR__ . '/../config/database.php';
function fail_request($message, $code=400){http_response_code($code);die('<!doctype html><meta charset="utf-8"><style>body{font:16px Arial;padding:40px;color:#7f1d1d}</style><h2>Bulk print could not start</h2><p>'.htmlspecialchars($message).'</p>');}
$raw=trim($_POST['officer_ids']??''); $issue=$_POST['issue_date']??''; $expiry=$_POST['expiry_date']??'';
$ids=array_values(array_unique(array_filter(array_map('intval',explode(',',$raw)),fn($id)=>$id>0)));
if(!$ids||count($ids)>15)fail_request('Select between 1 and 15 records.');
$issueObj=DateTime::createFromFormat('Y-m-d',$issue); $expiryObj=DateTime::createFromFormat('Y-m-d',$expiry);
if(!$issueObj||!$expiryObj||$issueObj->format('Y-m-d')!==$issue||$expiryObj->format('Y-m-d')!==$expiry||$expiry<=$issue)fail_request('The selected issue and expiry dates are invalid.');
$placeholders=implode(',',array_fill(0,count($ids),'?')); $types=str_repeat('i',count($ids));
$stmt=$conn->prepare("SELECT * FROM officers WHERE id IN ($placeholders) ORDER BY officer_id"); $stmt->bind_param($types,...$ids); $stmt->execute(); $officers=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
if(count($officers)!==count($ids))fail_request('One or more selected records no longer exist.');
$conn->begin_transaction();
try {
    $update = $conn->prepare("UPDATE officers SET issue_date=?, expiry_date=?, row_version=row_version+1 WHERE id IN ($placeholders)");
    $updateTypes = 'ss' . $types;
    $updateParams = array_merge([$issue, $expiry], $ids);
    $update->bind_param($updateTypes, ...$updateParams);
    $update->execute();

    $idList = implode(',', $ids); // Retained for compatibility with existing reports.
    $count = count($ids);
    $log = $conn->prepare("INSERT INTO bulk_print_jobs(public_id,officer_ids,record_count,status,issue_date,expiry_date,completed_at) VALUES(UUID(),?,?,'completed',?,?,NOW(6))");
    $log->bind_param('siss', $idList, $count, $issue, $expiry);
    $log->execute();
    $jobId = $conn->insert_id;

    $item = $conn->prepare('INSERT INTO bulk_print_job_items(print_job_id,officer_id,sequence_no,printed_at) VALUES(?,?,?,NOW(6))');
    $issuance = $conn->prepare("INSERT INTO officer_card_issuances(officer_id,print_job_id,issue_date,expiry_date,card_status) VALUES(?,?,?,?,'issued')");
    foreach ($ids as $index => $officerId) {
        $sequence = $index + 1;
        $item->bind_param('iii', $jobId, $officerId, $sequence);
        $item->execute();
        $issuance->bind_param('iiss', $officerId, $jobId, $issue, $expiry);
        $issuance->execute();
    }

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    fail_request('The print job could not be saved.', 500);
}
function photo_url($officerId){return 'officer_photo.php?id='.rawurlencode((string)$officerId);}
function qr_url($officerId){return 'qr_image.php?id='.rawurlencode((string)$officerId);}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Bulk Print Job #<?= (int)$jobId ?></title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" crossorigin="anonymous"></script>
<style>
@page{size:54mm 85.6mm;margin:0}*{box-sizing:border-box}body{margin:0;background:#e8edf5;font-family:Arial,sans-serif;color:#16233d}.toolbar{position:sticky;top:0;z-index:10;padding:14px 20px;background:#173778;color:white;display:flex;justify-content:space-between;align-items:center;gap:18px;box-shadow:0 4px 16px rgba(15,23,42,.2)}.toolbar-copy{display:grid;gap:3px}.toolbar-copy strong{font-size:15px}.toolbar-copy small{color:#bcd0f2}.toolbar-actions{display:flex;gap:10px}.toolbar button{min-height:42px;border-radius:9px;padding:0 17px;font-weight:700;cursor:pointer}.pdf-button{border:1px solid rgba(255,255,255,.45);background:transparent;color:white}.print-button{border:0;background:#ffd500;color:#17305f}.toolbar button:disabled{opacity:.6;cursor:wait}.sheets{display:grid;grid-template-columns:repeat(auto-fit,300px);gap:34px;justify-content:center;padding:34px}.card-set{display:grid;gap:11px}.side-label{text-align:center;color:#52627d;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px}.card{width:300px;height:475.56px;border:0;border-radius:4px;background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.16);overflow:hidden;position:relative}.template-art{position:absolute;inset:0;width:100%;height:100%;object-fit:fill;z-index:1}.card-photo{position:absolute;left:27.9%;top:33%;width:44.2%;height:23.5%;object-fit:cover;z-index:2}.front-details{position:absolute;z-index:2;left:12%;right:12%;top:63.5%;height:34.5%;color:#000;text-align:center;font-family:Arial,Helvetica,sans-serif}.card-name{font-size:16px;font-weight:700;line-height:1.1;white-space:nowrap}.card-nic{font-size:16px;font-weight:700;margin-top:13px}.card-dates{position:absolute;top:80%;left:10%;right:17%;display:grid;gap:4px;margin:0;font-size:11px;font-weight:800}.card-dates div{display:grid;grid-template-columns:1fr 1fr;align-items:center;text-align:left}.card-dates b{text-align:right;font-size:11px;font-weight:800}.card-qr{position:absolute;z-index:2;left:31%;top:10%;width:38%;aspect-ratio:1;background:#fff;object-fit:contain;padding:3px}.status{min-height:18px;color:#dce8ff;font-size:11px;text-align:right}
@media(max-width:680px){.toolbar{align-items:flex-start;flex-direction:column}.toolbar-actions{width:100%;display:grid;grid-template-columns:1fr 1fr}.toolbar button{width:100%}.sheets{padding:20px 10px;gap:24px}.card{width:min(300px,calc(100vw - 28px));height:auto;aspect-ratio:300/475.56}}
@media print{body{background:#fff}.toolbar,.side-label{display:none!important}.sheets{display:block;padding:0}.card-set{display:block}.card{width:54mm;height:85.6mm;box-shadow:none;border-radius:0;margin:0;break-after:page;page-break-after:always}.card-name{font-size:8.16pt}.card-nic{font-size:8.16pt;margin-top:2.34mm}.card-dates{gap:.72mm;font-size:5.61pt}.card-dates b{font-size:5.61pt}}
</style></head><body>
<div class="toolbar"><div class="toolbar-copy"><strong>Print Job #<?= (int)$jobId ?> · <?= count($officers) ?> ID cards</strong><small>Approved CR80 template · front and back · duplex sequence</small></div><div><div class="toolbar-actions"><button class="pdf-button" id="downloadPdf" type="button" onclick="downloadBulkPDF()">Download PDF Print Standard</button><button class="print-button" type="button" onclick="window.print()">Print cards</button></div><div class="status" id="pdfStatus" role="status" aria-live="polite"></div></div></div>
<main class="sheets">
<?php foreach($officers as $o): ?>
<section class="card-set" aria-label="<?= htmlspecialchars($o['full_name']) ?> ID card">
  <span class="side-label"><?= htmlspecialchars($o['officer_id']) ?> · Front</span>
  <article class="card front" data-officer-id="<?= htmlspecialchars($o['officer_id']) ?>">
    <img class="template-art" src="../img/Untitled-1-Recovered.png" alt="">
    <img class="card-photo" src="<?= htmlspecialchars(photo_url($o['id'])) ?>" alt="<?= htmlspecialchars($o['full_name']) ?>" onerror="this.style.visibility='hidden'">
    <div class="front-details"><div class="card-name"><?= htmlspecialchars($o['full_name']) ?></div><div class="card-nic"><?= htmlspecialchars($o['nic'] ?: '') ?></div><div class="card-dates"><div><span>Issued Date</span><b><?= $issueObj->format('d.m.Y') ?></b></div><div><span>Expiry Date</span><b><?= $expiryObj->format('d.m.Y') ?></b></div></div></div>
  </article>
  <span class="side-label"><?= htmlspecialchars($o['officer_id']) ?> · Back</span>
  <article class="card back">
    <img class="template-art" src="../img/Untitled-2-Recovered.png" alt="">
    <img class="card-qr" src="<?= htmlspecialchars(qr_url($o['id'])) ?>" alt="<?= htmlspecialchars($o['full_name']) ?> QR code" onerror="this.style.visibility='hidden'">
  </article>
</section>
<?php endforeach; ?>
</main>
<script>
function fitCardNames(){document.querySelectorAll('.card-name').forEach(name=>{let size=16;name.style.fontSize=size+'px';while(name.scrollWidth>name.clientWidth&&size>9){size-=.5;name.style.fontSize=size+'px'}})}
async function waitForImages(){const images=[...document.images];await Promise.all(images.map(image=>image.complete?Promise.resolve():new Promise(resolve=>{image.addEventListener('load',resolve,{once:true});image.addEventListener('error',resolve,{once:true})})))}
async function downloadBulkPDF(){const button=document.getElementById('downloadPdf');const status=document.getElementById('pdfStatus');const cards=[...document.querySelectorAll('.card')];button.disabled=true;button.textContent='Generating PDF...';status.textContent=`Preparing 0 of ${cards.length} sides`;try{await waitForImages();const{jsPDF}=window.jspdf;const pdf=new jsPDF({orientation:'portrait',unit:'mm',format:[54,85.6],compress:true});for(let index=0;index<cards.length;index++){if(index>0)pdf.addPage([54,85.6],'portrait');status.textContent=`Preparing ${index+1} of ${cards.length} sides`;const canvas=await html2canvas(cards[index],{scale:3,useCORS:true,backgroundColor:'#ffffff'});pdf.addImage(canvas.toDataURL('image/jpeg',.95),'JPEG',0,0,54,85.6,undefined,'FAST')}pdf.save('Bulk_ID_Cards_Job_<?= (int)$jobId ?>.pdf');status.textContent='PDF downloaded successfully.'}catch(error){console.error(error);status.textContent='PDF generation failed. Please try again.'}finally{button.disabled=false;button.textContent='Download PDF Print Standard'}}
window.addEventListener('load',()=>{fitCardNames()});
window.addEventListener('beforeprint',()=>{document.querySelectorAll('.card-name').forEach(name=>{const pixels=parseFloat(name.style.fontSize)||16;name.dataset.screenFontSize=String(pixels);name.style.fontSize=(pixels*.18)+'mm'})});
window.addEventListener('afterprint',()=>{document.querySelectorAll('.card-name').forEach(name=>{name.style.fontSize=(Number(name.dataset.screenFontSize)||16)+'px'})});
</script></body></html>
