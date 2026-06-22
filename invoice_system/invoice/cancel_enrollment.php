<?php
include "../../db_config.php";

$id = $_GET['id'];

mysqli_query($conn,"
UPDATE enrollment_inquiries 
SET status='Cancelled' 
WHERE id='$id'
");

echo "<script>
alert('Enrollment Cancelled');
window.history.back();
</script>";