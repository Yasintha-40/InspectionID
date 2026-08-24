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
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Bulk Print Job #<?= (int)$jobId ?></title><style>
@page{size:54mm 85.6mm;margin:0}*{box-sizing:border-box}body{margin:0;background:#e8edf5;font-family:Arial,sans-serif;color:#16233d}.toolbar{position:sticky;top:0;z-index:5;padding:14px 20px;background:#173778;color:white;display:flex;justify-content:space-between;align-items:center}.toolbar button{border:0;border-radius:8px;background:#ffd500;padding:10px 18px;font-weight:700;cursor:pointer}.sheets{display:grid;grid-template-columns:repeat(auto-fit,54mm);gap:14mm;justify-content:center;padding:20px}.card{width:54mm;height:85.6mm;background:white;position:relative;overflow:hidden;border-radius:3mm;box-shadow:0 2px 12px #aab4c5;page-break-after:always;text-align:center;padding:7mm 5mm}.card:before,.card:after{content:'';position:absolute;left:-12mm;width:78mm;height:22mm;background:#2b61a4;border-radius:50%;z-index:0}.card:before{top:-14mm}.card:after{bottom:-14mm}.content{position:relative;z-index:1}.logos{display:flex;justify-content:center;gap:3mm}.logos img{width:10mm;height:10mm;object-fit:contain}.authority{font-size:7pt;font-weight:bold;line-height:1.25;margin:2mm 0}.photo{width:25mm;height:28mm;object-fit:cover;border:1px solid #b7c0cc;background:#eef2f7}.role{font-size:9pt;font-weight:bold;margin:2.5mm 0 1mm}.name{font-size:8pt;min-height:9mm}.nic{font-size:8pt;font-weight:bold;margin:1mm}.dates{font-size:6.5pt;text-align:left;margin:3mm auto;width:37mm}.dates div{display:flex;justify-content:space-between;margin:1mm 0}.id{font-size:7pt;font-weight:bold;color:#284c8e}.empty-photo{width:25mm;height:28mm;margin:auto;background:#e8edf5;display:grid;place-items:center;font-size:24pt;color:#9aa7b8}@media print{body{background:white}.toolbar{display:none}.sheets{display:block;padding:0}.card{box-shadow:none;border-radius:0;margin:0}}
</style></head><body><div class="toolbar"><span>Print Job #<?= (int)$jobId ?> · <?= count($officers) ?> cards saved</span><button onclick="window.print()">Print cards</button></div><main class="sheets">
<?php foreach($officers as $o): ?><article class="card"><div class="content"><div class="logos"><img src="../img/logo.jpg" alt="SLTDA"><img src="../img/logo.jpg" alt="Sri Lanka Tourism"></div><div class="authority">SRI LANKA TOURISM<br>DEVELOPMENT AUTHORITY</div><img class="photo" src="<?= htmlspecialchars(photo_url($o['id'])) ?>" alt="<?= htmlspecialchars($o['full_name']) ?>" onerror="this.style.visibility='hidden'"><div class="role"><?= htmlspecialchars($o['designation']?:'Inspection Officer') ?></div><div class="name"><?= htmlspecialchars($o['full_name']) ?></div><div class="nic"><?= htmlspecialchars($o['nic']?:'NIC unavailable') ?></div><div class="id"><?= htmlspecialchars($o['officer_id']) ?></div><div class="dates"><div><b>Issued Date</b><span><?= $issueObj->format('d.m.Y') ?></span></div><div><b>Expiry Date</b><span><?= $expiryObj->format('d.m.Y') ?></span></div></div></div></article><?php endforeach; ?>
</main></body></html>
