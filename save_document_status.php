<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();

include "db_config.php";

if(!isset($_SESSION['teacher_id'])){
    exit("Unauthorized Access");
}

$teacher_id = (int)$_SESSION['teacher_id'];

$document_id = (int)($_POST['document_id'] ?? 0);

$status = trim($_POST['status'] ?? '');

$remark = trim($_POST['remark'] ?? '');


/*
|--------------------------------------------------------------------------
| Validate Status
|--------------------------------------------------------------------------
*/

$allowedStatus = [
    "Pending",
    "Approved",
    "Rejected"
];

if(!in_array($status,$allowedStatus,true)){
    exit("Invalid Status");
}


/*
|--------------------------------------------------------------------------
| Verify Teacher Owns This Document
|--------------------------------------------------------------------------
*/

$sql = "

SELECT
    sd.id

FROM student_documents sd

INNER JOIN teacher_subjects ts
ON ts.subject_id = sd.subject_id

WHERE

sd.id = ?

AND ts.teacher_id = ?

LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("ii",$document_id,$teacher_id);

$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows==0){

    exit("Permission Denied");

}

$stmt->close();


/*
|--------------------------------------------------------------------------
| Update Document
|--------------------------------------------------------------------------
*/

$sql = "

UPDATE student_documents

SET

status=?,

remark=?,

action_by=?,

action_role='Teacher',

action_date=NOW()

WHERE id=?

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(

"ssii",

$status,

$remark,

$teacher_id,

$document_id

);

if($stmt->execute()){

    echo "Document updated successfully.";

}else{

    echo "Unable to update document.";

}

$stmt->close();

$conn->close();

?>