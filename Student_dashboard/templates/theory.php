<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
IMPORTANT:
Theory ke liye hum quiz_questions table ka use kar rahe hain
→ question_text = heading
→ file_path = image
*/

// ✅ Correct Base Path (IMPORTANT)
$base_url = "/creativetheka.in/Student_dashboard/templates/images/";

// ✅ DB se data (quiz_questions se)
$image_name = $q['file_path'] ?? '';
$title = $q['question_text'] ?? 'Theory';

// ✅ Final Image Path
$image_path = (!empty($image_name)) ? $base_url . trim($image_name) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Theory</title>

<style>

/* Background */
body {
    margin: 0;
    padding: 0;
    background: #f3f4f7;
    font-family: 'Poppins', sans-serif;
}

/* Container */
.theory-container {
    max-width: 1000px;
    margin: 40px auto;
    padding: 20px;
}

/* Card */
.theory-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    text-align: center;
}

/* Heading */
.theory-title {
    font-size: 26px;
    font-weight: 600;
    color: #1F669C;
    margin-bottom: 25px;
}

/* Image */
.theory-image {
    width: 100%;
    max-width: 100%;
    height: auto;
    object-fit: contain;
    border-radius: 12px;
}

/* Debug box */
.debug-box {
    background: #fff3cd;
    padding: 10px;
    border-radius: 8px;
    margin-top: 15px;
    font-size: 13px;
    color: #856404;
    text-align: left;
}

/* Mobile */
@media(max-width:768px){
    .theory-container { padding: 10px; }
    .theory-card { padding: 20px; }
    .theory-title { font-size: 20px; }
}

</style>
</head>

<body>

<div class="theory-container">
    <div class="theory-card">

        <!-- ✅ Heading (NOW CORRECT) -->
        <div class="theory-title">
            <?= htmlspecialchars($title) ?>
        </div>

        <!-- ✅ IMAGE -->
        <?php if (!empty($image_path)) { ?>

            <img 
                src="<?= htmlspecialchars($image_path) ?>" 
                alt="theory-image"
                class="theory-image"
            >

        <?php } else { ?>

            <p style="color:red; font-weight:600;">
                ❌ Image not set in DB
            </p>

        <?php } ?>

        <!-- ✅ DEBUG -->
        <div class="debug-box">
            <strong>DEBUG:</strong><br>
            Question Text: <?= htmlspecialchars($title) ?><br>
            DB Image: <?= htmlspecialchars($image_name) ?><br>
            Final Path: <?= htmlspecialchars($image_path) ?>
        </div>

    </div>
</div>

</body>
</html>