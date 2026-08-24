<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');
$category = trim($_GET['category'] ?? 'National Guide'); $filter = $_GET['filter'] ?? 'all';
$where=[]; $types=''; $params=[];
if($category !== 'All Categories'){ $where[]='guide_category = ?'; $types.='s'; $params[]=$category; }
switch($filter){case 'active':$where[]="status = 'Active'";break;case 'ready':$where[]="status = 'Active' AND issue_date IS NOT NULL AND expiry_date IS NOT NULL";break;case 'missing_dates':$where[]='(issue_date IS NULL OR expiry_date IS NULL)';break;case 'expired':$where[]="(status = 'Expired' OR expiry_date < CURDATE())";break;}
$sql="SELECT id,officer_id,guide_category,full_name,COALESCE(NULLIF(nickname,''),SUBSTRING_INDEX(TRIM(full_name),' ',-1)) nickname,nic,COALESCE(NULLIF(languages,''),'English') languages,address,issue_date,expiry_date,status FROM officers".($where?' WHERE '.implode(' AND ',$where):'').' ORDER BY officer_id';
$stmt=$conn->prepare($sql); if(!$stmt){error_log('Unable to prepare the guide query: '.$conn->error.' SQL: '.$sql);http_response_code(500);echo json_encode(['success'=>false,'message'=>'Unable to prepare the guide query.']);exit;}
if($params)$stmt->bind_param($types,...$params); $stmt->execute(); $rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$total=(int)$conn->query('SELECT COUNT(*) count FROM officers')->fetch_assoc()['count'];
$ready=(int)$conn->query("SELECT COUNT(*) count FROM officers WHERE status='Active' AND issue_date IS NOT NULL AND expiry_date IS NOT NULL")->fetch_assoc()['count'];
echo json_encode(['success'=>true,'records'=>$rows,'total'=>$total,'ready'=>$ready]);
