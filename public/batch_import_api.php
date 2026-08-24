<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

function respond($data, $status = 200) { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
function clean_nic($value) { return strtoupper(preg_replace('/[\s-]+/', '', trim((string)$value))); }
function valid_email($value) { return $value === '' || filter_var($value, FILTER_VALIDATE_EMAIL); }
function staging_dir() { $dir = __DIR__ . '/../database/import_staging'; if (!is_dir($dir)) mkdir($dir, 0755, true); return $dir; }

foreach (glob(staging_dir() . '/*') ?: [] as $stagedFile) {
    if (is_file($stagedFile) && filemtime($stagedFile) < time() - 3600) @unlink($stagedFile);
}


$action = $_POST['action'] ?? '';
if ($action === 'preview') {
    if (!isset($_FILES['workbook']) || $_FILES['workbook']['error'] !== UPLOAD_ERR_OK) respond(['success'=>false,'message'=>'The workbook upload failed.'], 422);
    $file = $_FILES['workbook'];
    if ($file['size'] > 5 * 1024 * 1024) respond(['success'=>false,'message'=>'The workbook must be smaller than 5 MB.'], 422);
    if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'xlsx') respond(['success'=>false,'message'=>'Only .xlsx workbooks are supported.'], 422);
    $token = bin2hex(random_bytes(20)); $xlsxPath = staging_dir() . '/' . $token . '.xlsx';
    if (!move_uploaded_file($file['tmp_name'], $xlsxPath)) respond(['success'=>false,'message'=>'The workbook could not be staged.'], 500);
    $script = escapeshellarg(__DIR__ . '/../scripts/parse_xlsx.py'); $input = escapeshellarg($xlsxPath);
    $output = []; $exitCode = 0; exec("python $script $input 2>&1", $output, $exitCode); $parsed = json_decode(implode("\n", $output), true);
    if ($exitCode !== 0 || !$parsed || isset($parsed['error'])) { @unlink($xlsxPath); respond(['success'=>false,'message'=>$parsed['error'] ?? 'The workbook could not be read.'], 422); }
    $seen = []; $records = []; $counts = ['total'=>0,'valid'=>0,'duplicates'=>0,'errors'=>0];
    foreach ($parsed['records'] as $record) {
        $counts['total']++; $record['nic'] = clean_nic($record['nic']); $errors = [];
        if (trim($record['full_name']) === '') $errors[] = 'Name is required';
        if ($record['nic'] === '') $errors[] = 'NIC is required';
        if (!valid_email(trim($record['email']))) $errors[] = 'Invalid email';
        $key = $record['nic']; if ($key && isset($seen[$key])) $errors[] = 'Duplicate NIC in workbook'; $seen[$key] = true;
        $duplicate = false;
        if (!$errors) { $stmt=$conn->prepare("SELECT id FROM officers WHERE nic_normalized=? OR (email<>'' AND LOWER(email)=LOWER(?)) LIMIT 1"); $email=trim($record['email']); $stmt->bind_param('ss',$key,$email); $stmt->execute(); $duplicate=(bool)$stmt->get_result()->fetch_assoc(); }
        if ($errors) { $status='error'; $counts['errors']++; } elseif ($duplicate) { $status='duplicate'; $counts['duplicates']++; } else { $status='valid'; $counts['valid']++; }
        $record['status']=$status; $record['errors']=$errors; $records[]=$record;
    }
    file_put_contents(staging_dir().'/'.$token.'.json', json_encode(['filename'=>basename($file['name']),'records'=>$records], JSON_UNESCAPED_UNICODE), LOCK_EX);
    respond(['success'=>true,'token'=>$token,'sheet'=>$parsed['sheet'],'counts'=>$counts,'records'=>$records]);
}

if ($action === 'commit') {
    $token = preg_replace('/[^a-f0-9]/', '', $_POST['token'] ?? ''); $policy = $_POST['duplicate_policy'] ?? 'skip';
    if (strlen($token)!==40 || !in_array($policy,['skip','update'],true)) respond(['success'=>false,'message'=>'The staged import request is invalid.'],422);
    $jsonPath=staging_dir().'/'.$token.'.json'; $xlsxPath=staging_dir().'/'.$token.'.xlsx';
    if (!is_file($jsonPath) || filemtime($jsonPath) < time()-3600) respond(['success'=>false,'message'=>'This import preview has expired. Upload the workbook again.'],410);
    $stage=json_decode(file_get_contents($jsonPath),true); $inserted=$updated=$skipped=$failed=0;
    $conn->begin_transaction();
    try {
        $next=(int)$conn->query("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(officer_id,'-',-1) AS UNSIGNED)),0)+1 next_id FROM officers")->fetch_assoc()['next_id'];
        foreach($stage['records'] as $record) {
            if($record['status']==='error'){ $failed++; continue; }
            $nic=clean_nic($record['nic']); $email=trim($record['email']);
            $find=$conn->prepare("SELECT id FROM officers WHERE nic_normalized=? OR (email<>'' AND LOWER(email)=LOWER(?)) LIMIT 1"); $find->bind_param('ss',$nic,$email); $find->execute(); $existing=$find->get_result()->fetch_assoc();
            $name=trim($record['full_name']);$address=trim($record['address']);$photo=trim($record['photo']);$qr=trim($record['qr_code']);$category=trim($record['guide_category'])?:'National Guide';$nickname=trim($record['nickname']);$languages=trim($record['languages'])?:'English';
            if($existing){ if($policy==='skip'){ $skipped++; continue; } $stmt=$conn->prepare('UPDATE officers SET full_name=?,address=?,nic=?,email=?,photo=?,qr_code=?,guide_category=?,nickname=?,languages=? WHERE id=?');$stmt->bind_param('sssssssssi',$name,$address,$nic,$email,$photo,$qr,$category,$nickname,$languages,$existing['id']);$stmt->execute();$updated++; }
            else { do{$officerId='INS-'.str_pad((string)$next++,4,'0',STR_PAD_LEFT);$check=$conn->prepare('SELECT id FROM officers WHERE officer_id=?');$check->bind_param('s',$officerId);$check->execute();}while($check->get_result()->num_rows);$stmt=$conn->prepare('INSERT INTO officers(officer_id,guide_category,full_name,nickname,languages,address,nic,email,photo,qr_code) VALUES(?,?,?,?,?,?,?,?,?,?)');$stmt->bind_param('ssssssssss',$officerId,$category,$name,$nickname,$languages,$address,$nic,$email,$photo,$qr);$stmt->execute();$inserted++; }
        }
        $total=count($stage['records']);$job=$conn->prepare("INSERT INTO import_jobs(public_id,original_filename,duplicate_policy,status,total_rows,inserted_rows,updated_rows,skipped_rows,failed_rows,started_at,completed_at) VALUES(UUID(),?,?,'completed',?,?,?,?,?,?,NOW(6),NOW(6))");$job->bind_param('ssiiiii',$stage['filename'],$policy,$total,$inserted,$updated,$skipped,$failed);$job->execute();$jobId=$conn->insert_id;$conn->commit();@unlink($jsonPath);@unlink($xlsxPath);
        respond(['success'=>true,'job_id'=>$jobId,'inserted'=>$inserted,'updated'=>$updated,'skipped'=>$skipped,'failed'=>$failed]);
    } catch(Throwable $e) { $conn->rollback(); respond(['success'=>false,'message'=>'No records were saved because the database import failed.'],500); }
}
respond(['success'=>false,'message'=>'Unsupported import action.'],400);
