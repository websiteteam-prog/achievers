<?php
$payload = json_decode($q['question_payload'] ?? '', true) ?: [];
$isPieChart = ($payload['type'] ?? '') === 'pie_chart';
$allow_html = !empty($payload['allow_html']);

if (!isset($GLOBALS['pie_image_shown'])) {
    $GLOBALS['pie_image_shown'] = false;
}
?>
<style>
/* Container spacing and styling */
.fill-blank-card{

margin:10px auto;

padding:15px 20px;

border-radius:12px;

box-shadow:0 2px 6px rgba(0,0,0,.1);

background:#fff;

}

/* Hover effect for subtle lift */
.fill-blank-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

/* Question text */
.fill-blank-card h6{
    font-weight:600;
    margin-bottom:10px;
    margin-top:0;
    color:#333;
}

/* Input field design (bottom border full width) */
.quiz-input {
    width: 100%; /* Full container width */
    padding: 8px 0; /* Top & bottom padding */
    font-size: 16px;
    border: none;
    border-bottom: 2px solid #ccc; /* Full-width bottom border */
    outline: none;
    background-color: transparent; 
    transition: border-color 0.3s;
}

/* Input focus effect */
.quiz-input:focus {
    border-bottom-color: #007bff; 
}

/* ✅ image styling */
.question-img {
    text-align: center;
    margin-bottom: 15px;
}
.question-img img {
    max-width: 100%;
    border-radius: 10px;
}

.pie-chart-banner {
    width: 100%;
    text-align: center;
    margin-bottom: 25px;
}

.pie-chart-banner img {
    max-width: 600px;
    width: 100%;
    height: auto;
    border-radius: 0;
    box-shadow: none;
}
</style>
<!-- ✅ IMAGE OUTSIDE CARD -->
<?php if ($isPieChart && !$GLOBALS['pie_image_shown'] && !empty($q['question_image'])): ?>
    
    <div class="pie-chart-banner">
        <img src="<?= htmlspecialchars($q['question_image']) ?>">
    </div>

    <?php $GLOBALS['pie_image_shown'] = true; ?>

    <?php endif; ?>
    
    <!-- ✅ CARD START -->
    <div class="fill-blank-card">
    
        <?php
    
    $questionText = '';
    
    if (!empty($q['question_text'])) {
    
        $questionText = $q['question_text'];
    
    } elseif (!empty($payload['expression'])) {
    
        $questionText = $payload['expression'];
    
    } elseif (!empty($payload['question'])) {
    
        $questionText = $payload['question'];
    
    }
    
    ?>
    <h6>
    <?= $char.'. '. ($allow_html ? $questionText : htmlspecialchars($questionText)) ?>
    </h6>
    <?php $char++; ?>

    <input type="text"
        class="quiz-input"
        name="answer[<?= $q['id'] ?>]"
        placeholder="Type your answer here"/>

</div>

<script>
document.addEventListener("DOMContentLoaded", function(){

    document.querySelectorAll(".quiz-input").forEach(function(input){

        input.addEventListener("blur", function(){

            let val = input.value;

            // Remove extra spaces around commas
            val = val.split(",")
                     .map(v => v.trim())
                     .filter(Boolean)
                     .join(",");

            input.value = val;

        });

    });

});
</script>