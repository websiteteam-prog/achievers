<?php
session_start();
include "../db_config.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = (int)$_SESSION['student_id'];

$upload_type = trim($_POST['upload_type'] ?? '');
$title       = trim($_POST['title'] ?? '');

$subject_id = !empty($_POST['subject_id'])
    ? (int)$_POST['subject_id']
    : NULL;

function redirect_with_error($code)
{
    header("Location: student_documents.php?error=".$code);
    exit();
}

/*----------------------------------------------------------
VALIDATE UPLOAD TYPE
----------------------------------------------------------*/

$allowed_types = [
    "personal",
    "assignment",
    "homework",
    "project"
];

if(!in_array($upload_type,$allowed_types,true)){
    redirect_with_error("type");
}

/*----------------------------------------------------------
TITLE REQUIRED
----------------------------------------------------------*/

if($title==""){
    redirect_with_error("upload");
}

/*----------------------------------------------------------
SUBJECT REQUIRED ONLY FOR ASSIGNMENT/HOMEWORK/PROJECT
----------------------------------------------------------*/

if($upload_type!="personal"){

    if(empty($subject_id)){
        redirect_with_error("subject");
    }

    $sql="SELECT id
          FROM student_subjects
          WHERE student_id=?
          AND subject_id=?";

    $stmt=$conn->prepare($sql);
    $stmt->bind_param("ii",$student_id,$subject_id);
    $stmt->execute();

    if($stmt->get_result()->num_rows==0){
        redirect_with_error("subject");
    }

    $stmt->close();

}

/*----------------------------------------------------------
CHECK FILE
----------------------------------------------------------*/

if(
    !isset($_FILES["document"]) ||
    $_FILES["document"]["error"]!=UPLOAD_ERR_OK
){
    redirect_with_error("upload");
}

$file=$_FILES["document"];

/*----------------------------------------------------------
FILE SIZE
----------------------------------------------------------*/

$max_size = 40 * 1024 * 1024; // 40 MB

if($file["size"]>$max_size){
    redirect_with_error("size");
}

/*----------------------------------------------------------
FILE TYPE
----------------------------------------------------------*/

$allowed_extensions=[
    "pdf",
    "doc",
    "docx",
    "jpg",
    "jpeg",
    "png"
];

$extension=strtolower(
    pathinfo($file["name"],PATHINFO_EXTENSION)
);

if(!in_array($extension,$allowed_extensions)){
    redirect_with_error("type");
}

/*----------------------------------------------------------
UPLOAD DIRECTORY
----------------------------------------------------------*/

$upload_dir=__DIR__."/../uploads/student_documents/";

if(!is_dir($upload_dir)){
    mkdir($upload_dir,0755,true);
}

/*----------------------------------------------------------
GENERATE SAFE FILE NAME
----------------------------------------------------------*/

$new_name=time()."_".$student_id."_".preg_replace(
    '/[^A-Za-z0-9._-]/',
    '_',
    basename($file["name"])
);

$target_path=$upload_dir.$new_name;

if(!move_uploaded_file($file["tmp_name"],$target_path)){
    redirect_with_error("upload");
}

/*----------------------------------------------------------
SAVE TO DATABASE
----------------------------------------------------------*/

$file_path="uploads/student_documents/".$new_name;

$file_name=basename($file["name"]);

$sql="INSERT INTO student_documents
(
student_id,
subject_id,
title,
upload_type,
file_name,
file_path
)
VALUES
(
?,
?,
?,
?,
?,
?
)";

$stmt=$conn->prepare($sql);

$stmt->bind_param(
    "iissss",
    $student_id,
    $subject_id,
    $title,
    $upload_type,
    $file_name,
    $file_path
);

if(!$stmt->execute()){

    unlink($target_path);

    die("Database Error : ".$stmt->error);

}

$stmt->close();

/*----------------------------------------------------------
SUCCESS
----------------------------------------------------------*/

header("Location: student_documents.php?uploaded=1");
exit();