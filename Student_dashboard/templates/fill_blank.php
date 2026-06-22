<style>
.container-fluid {
    margin-left: 50px;
    margin-bottom: 20px;
    padding: 18px 22px; 
    border-radius: 14px; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.08); 
    background-color: #ffffff; 
    transition: all 0.3s ease;
    width: 90%;
    margin-top:10px;
}

.container-fluid:hover {
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    transform: translateY(-3px);
}

.container-fluid h6 {
    font-weight: 600;
    margin-bottom: 12px; 
    color: #333; 
}

/* Number line image */
.number-line {
    width: 100%;
    max-width: 600px;
    margin: 10px auto;
    display: block;
}

/* Input styling */
.quiz-input {
    width: 100%;
    padding: 10px 0;
    font-size: 16px;
    border: none;
    border-bottom: 2px solid #ccc;
    background: transparent;
    outline: none;
    transition: all 0.3s;
}

.quiz-input:focus {
    border-bottom-color: #007bff;
}

/* Answer label */
.answer-label {
    color: purple;
    font-weight: 600;
    margin-top: 10px;
    display: block;
}

/* Mobile fix */
@media(max-width:768px){
    .container-fluid{
        margin-left: 0;
        width: 95%;
    }
}
</style>

<div class="container-fluid">

    <h6><?= $char.'. '. htmlspecialchars($q['question_text']) ?></h6>
    <?php $char++; ?>

    <!-- Number Line Image (Optional) -->
    <?php if(!empty($q['question_image'])){ ?>
        <img src="templates/images/<?= $q['question_image'] ?>" class="number-line">
    <?php } ?>

    <!-- Input -->
    <label class="answer-label">Answer:</label>
    <input 
        type="text" 
        class="quiz-input" 
        name="answer[<?= $q['id'] ?>]" 
        placeholder="Enter your answer"
    /> 

</div>