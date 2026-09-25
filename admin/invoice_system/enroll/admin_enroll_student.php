<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../../../db_config.php";
?>
<style>

.dashboard-card
 {
    min-height: 400px;
}

.enroll-section{
    background:transparent;
    padding:0 10px 30px;
}

.enroll-title{
    font-family:"Love Ya Like A Sister", cursive;
    font-size:36px;
    text-align:center;
    color:#05364d;

    margin:0 0 30px;
    padding-top:25px;
}

.enroll-form{
    max-width:900px;
    margin:0 auto;
    background:#fff;
    padding:35px 30px;
    border-radius:18px;
    box-shadow:0 10px 35px rgba(0,0,0,.08);
    margin-bottom:50px;
}

.form-row{
display:flex;
gap:20px;
margin-bottom:15px;
}

.form-group{
flex:1;
display:flex;
flex-direction:column;
}

.form-group label{
font-weight:600;
margin-bottom:6px;
font-size:14px;
}

.form-group input,
.form-group select,
.form-group textarea{
padding:12px 14px;
border-radius:10px;
border:1px solid #ddd;
font-size:14px;
width:100%;
}

.form-group textarea{
height:110px;
resize:none;
}

.submit-btn{
background:#e8063c;
color:#fff;
border:none;
padding:12px 35px;
border-radius:25px;
font-weight:600;
cursor:pointer;
margin-top:10px;
}

.submit-btn:hover{
background:#111;
}

.section-title{
font-size:18px;
margin:20px 0 10px;
border-bottom:2px solid #e8063c;
padding-bottom:5px;
}

.required{
color:red;
font-weight:bold;
margin-left:3px;
}

.terms-box{
background:#fff8e1;
padding:20px;
border-radius:12px;
margin:25px 0;
font-size:13px;
line-height:1.6;
}

.form-check{
margin-top:10px;
}

.form-check input{
margin-right:6px;
}

.subject-box {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border: 1px solid #ddd;
    border-radius: 20px;
    /* margin-bottom: 8px; */
    cursor: pointer;
    font-size: 13px;
    background: #fff;
    transition: all 0.2s ease;
    width: auto; 
}

.subject-box span {
    white-space: nowrap;
}

.subject-box input {
    cursor: pointer;
}

.subject-box:hover {
    background: #f0f4ff;
    border-color: #2a5298;
}

.subject-box input:checked + span {
    font-weight: 600;
    color: #2a5298;
}

.subject-box input:checked {
    accent-color: #2a5298;
}

#subject_container {
    display: flex;
    grid-template-columns: repeat(2, 1fr);
    flex-wrap: wrap;
    gap: 10px;
}
/* Chrome, Edge, Safari, Opera */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Firefox */
input[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
}
/* ================= MOBILE RESPONSIVE ================= */

@media (max-width:768px){

  .enroll-form{
    padding:20px 15px;
  }

  .enroll-title{
    font-size:30px;
  }
.enroll-section {
    background: #f7f9fc;
    padding: 0px 0px;
}
 
  .form-row{
    flex-direction:column;
    gap:12px;
  }

  .form-group{
    width:100%;
  }

  /* input size optimize */
  .form-group input,
  .form-group select,
  .form-group textarea{
    font-size:13px;
    padding:10px 12px;
  }

  .form-group textarea{
    height:90px;
  }

  /* section titles */
  .section-title{
    font-size:16px;
  }

  /* terms box compact */
  .terms-box{
    padding:15px;
    font-size:12px;
  }

  /* button full width */
  .submit-btn{
    width:100%;
    padding:12px;
  }

}

</style>


<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">

<div class="enroll-section m-0 p-0">

<h2 class="enroll-title">Student Enrollment</h2>

<form id="enrollForm" class="enroll-form">

<!-- Student Details -->

<h3 class="section-title">Student Details</h3>

<div class="form-row">

<div class="form-group">
<label>First Name  <span class="required">*</span></label>
<input type="text" name="first_name" placeholder="Enter student's first name" required>
</div>

<div class="form-group">
<label>Last Name  <span class="required">*</span></label>
<input type="text" name="last_name" placeholder="Enter student's last name" required>
</div>

</div>


<div class="form-row">

<div class="form-group">
<label>Date of Birth  <span class="required">*</span></label>
<input type="date" name="dob" required>
</div>

<div class="form-group">
<label>Enrollment Date  <span class="required">*</span></label>
<input type="date" name="enroll_date" required>
</div>

</div>


<div class="form-row">
<div class="form-group">
<label>Mode of Education</label>

<select name="mode_of_education">

<option value="">Select Mode</option>
<option>Offline</option>
<option>Online</option>

</select>

</div>
<div class="form-group">
<label>Grade  <span class="required">*</span></label>

<select name="grade" id="grade" required>

<option value="">Select Grade</option>

<option>Pre-School</option>
<option>Kindergarten</option>

<option>Grade 1</option>
<option>Grade 2</option>
<option>Grade 3</option>
<option>Grade 4</option>
<option>Grade 5</option>
<option>Grade 6</option>
<option>Grade 7</option>
<option>Grade 8</option>

<option>Grade 9</option>
<option>Grade 10</option>
<option>Grade 11</option>
<option>Grade 12</option>

</select>

</div>
<div class="form-group">
<label>Program</label>
<select name="program" id="program" required>
<!-- <select name="program" id="program" required readonly> -->
<option value="">Select Program</option>
<option value="Early Starters">Early Starters</option>
<option value="Elementary">Elementary</option>
<option value="Advanced Learners">Advanced Learners</option>
</select>
</div>


</div>
<div class="form-row" id="program_count_section" style="display:none;">
    <div class="form-group">
        <label>Number of Programs <span class="required">*</span></label>
        
        <select name="program_count" id="program_count" required>
            <option value="">Select Number of Programs</option>
        </select>
    </div>
</div>

<div class="form-row" id="subject_section" style="display:none;">
    <div class="form-group">
        <label>Select Subjects <span class="required">*</span></label>
        <div id="subject_container"></div>
    </div>
</div>

<!-- Guardian -->

<h3 class="section-title">Guardian Information</h3>

<div class="form-row">

<div class="form-group">
<label>Guardian Name  <span class="required">*</span></label>
<input type="text" name="guardian_name" placeholder="Enter guardian full name" required>
</div>

<div class="form-group">
<label>Guardian Email  <span class="required">*</span></label>
<input type="email" name="guardian_email" placeholder="guardian@email.com" required>
</div>

</div>


<div class="form-row">

<div class="form-group">
<label>Guardian Phone  <span class="required">*</span></label>
<input type="text" name="guardian_phone" placeholder="10 digit phone number" required pattern="[0-9]{10}" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
</div>

<div class="form-group">
<label>Payment Will Be Made By  <span class="required">*</span></label>

<select name="payment_by" required>

<option value="">Select</option>

<option>Guardian</option>
<option>Mother</option>
<option>Father</option>

</select>

</div>

</div>


<!-- Parents -->

<h3 class="section-title">Parent Information</h3>

<div class="form-row">

<div class="form-group">
<label>Mother Name</label>
<input type="text" name="mother_name" placeholder="Enter mother name">
</div>

<div class="form-group">
<label>Mother Email</label>
<input type="email" name="mother_email" placeholder="mother@email.com">
</div>

<div class="form-group">
<label>Mother Phone</label>
<input type="text" name="mother_phone" placeholder="Mother phone number" pattern="[0-9]{10}" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
</div>

</div>


<div class="form-row">

<div class="form-group">
<label>Father Name</label>
<input type="text" name="father_name" placeholder="Enter father name">
</div>

<div class="form-group">
<label>Father Email</label>
<input type="email" name="father_email" placeholder="father@email.com">
</div>

<div class="form-group">
<label>Father Phone</label>
<input type="text" name="father_phone" placeholder="Father phone number" pattern="[0-9]{10}" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
</div>

</div>


<!-- Emergency -->

<h3 class="section-title">Emergency Contact</h3>

<div class="form-row">

<div class="form-group">
<label>Emergency Contact Name</label>
<input type="text" name="emergency_name" placeholder="Emergency contact person">
</div>

<div class="form-group">
<label>Emergency Phone</label>
<input type="text" name="emergency_phone" placeholder="Emergency phone number" pattern="[0-9]{10}" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
</div>

</div>


<div class="form-row">

<div class="form-group">
<label>Authorized Pickup Name</label>
<input type="text" name="authorized_name" placeholder="Person allowed to pickup">
</div>

<div class="form-group">
<label>Relation</label>
<input type="text" name="authorized_relation" placeholder="Relation with student">
</div>

</div>

<h3 class="section-title">Discount</h3>

<div class="form-row">
  <div class="form-group">
    <label>Discount Type</label>
    <select name="discount_type" id="discount_type">
      <option value="">No Discount</option>
      <option value="one_time">One Time Discount</option>
      <option value="sibling">Sibling Discount (Recurring)</option>
    </select>
  </div>

  <div class="form-group" id="discount_amount_box" style="display:none;">
    <label>Discount Amount (CAD $)</label>
    <input type="number" name="discount_amount" id="discount_amount" min="0" step="0.01">
  </div>
</div>

<div class="form-group">
  <label>Discount Description</label>
  <input type="text" name="discount_description" placeholder="Enter discount reason (optional)">
</div>
<!-- Extra Amount -->

<h3 class="section-title">Extra Amount</h3>

<div class="form-row">
  <div class="form-group">
    <label>Extra Amount Type</label>
    <select name="extra_type" id="extra_type">
      <option value="">No Extra Amount</option>
      <option value="one_time">One Time Extra</option>
      <option value="permanent">Permanent Extra (Recurring)</option>
    </select>
  </div>

  <div class="form-group" id="extra_amount_box" style="display:none;">
    <label>Extra Amount (CAD $)</label>
    <input type="number" name="extra_amount" id="extra_amount" min="0" step="0.01">
  </div>
</div>

<div class="form-group">
  <label>Extra Amount Description</label>
  <input type="text" name="extra_description" placeholder="Reason for extra charge (optional)">
</div>
<!-- Admin Fields -->

<h3 class="section-title">Payment Information</h3>

<div class="form-row">

<div class="form-group">
<label>Payment Type </label>

<select name="payment_type">

<option value="">Select Payment Type</option>

<option>Cash</option>
<option>E-Transfer</option>
<option>Debit Card</option>
<option>Credit Card</option>

</select>

</div>

</div>


<div class="form-group">
<label>Comments / Additional Message</label>
<textarea name="message" placeholder="Optional notes about student"></textarea>
</div>

<!-- Terms & Conditions -->

<?php include "../../../terms.php"; ?>

<div class="terms-box">
    <p><strong>Terms & Conditions:</strong></p>

    <?php echo $terms_content; ?>

    <div class="form-check">
        <input type="checkbox" name="terms_agreed" value="1" required>
        <label>
            <strong>I confirm that the guardian/parent agrees to the above Terms & Conditions.</strong>
        </label>
    </div>
</div>
<div style="text-align:center">

<button class="submit-btn" type="submit">
Enroll Student
</button>

</div>

</form>

</div>

<script>
document.querySelector(".enroll-form").addEventListener("submit", function(e){

    let paymentBy = document.querySelector("[name='payment_by']").value;

    let guardianEmail = document.querySelector("[name='guardian_email']").value.trim();

    let motherName = document.querySelector("[name='mother_name']").value.trim();
    let motherEmail = document.querySelector("[name='mother_email']").value.trim();
    let motherPhone = document.querySelector("[name='mother_phone']").value.trim();

    let fatherName = document.querySelector("[name='father_name']").value.trim();
    let fatherEmail = document.querySelector("[name='father_email']").value.trim();
    let fatherPhone = document.querySelector("[name='father_phone']").value.trim();

    if(paymentBy === "Guardian"){
        if(guardianEmail === ""){
            alert("Guardian email is required!");
            e.preventDefault();
        }
    }

    if(paymentBy === "Mother"){
        if(motherName === "" || motherEmail === "" || motherPhone === ""){
            alert("Mother name, email & phone are required!");
            e.preventDefault();
        }
    }

    if(paymentBy === "Father"){
        if(fatherName === "" || fatherEmail === "" || fatherPhone === ""){
            alert("Father name, email & phone are required!");
            e.preventDefault();
        }
    }

});

// For program, subject,no of program dropdown
var programSelect = document.getElementById("program");
var subjectContainer = document.getElementById("subject_container");
var subjectSection = document.getElementById("subject_section");
var programCountSelect = document.getElementById("program_count");
var programCountSection = document.getElementById("program_count_section");

programSelect.addEventListener("change", function(){

    let program = this.value;

    // RESET
    subjectContainer.innerHTML = "";
    subjectSection.style.display = "none"; 
    programCountSelect.innerHTML = '<option value="">Select Number of Programs</option>';

     if(program === ""){
        programCountSection.style.display = "none"; // hide
        return;
    }

    // SHOW program count
    programCountSection.style.display = "block";

    if(program === "Early Starters"){
        programCountSelect.innerHTML += `<option value="all">All Programs</option>`;
    }
    else if(program === "Elementary" || program === "Advanced Learners"){
        programCountSelect.innerHTML += `
            <option value="1">One Program</option>
            <option value="2">Two Programs</option>
            <option value="all">Three / All Programs</option>
        `;
    }

});
programCountSelect.addEventListener("change", function(){

    let program = programSelect.value;

    if(program === ""){
        subjectContainer.innerHTML = "<p style='color:red;'>Select program first</p>";
        return;
    }

    subjectSection.style.display = "block"; 
    subjectContainer.innerHTML = "Loading...";

    let grade = document.getElementById("grade").value;

    fetch(
        "invoice_system/enroll/get_subjects.php?program=" +
        encodeURIComponent(program) +
        "&grade=" +
        encodeURIComponent(grade)
    )
    .then(res => res.json())
    .then(data => {

        subjectContainer.innerHTML = "";

        if(data.length === 0){
            subjectContainer.innerHTML = "<p style='color:red;'>No subjects found</p>";
            return;
        }

        data.forEach(sub => {
           subjectContainer.innerHTML += `
            <label class="subject-box">
                <input type="checkbox" name="subjects[]" value="${sub.subject_name}">
                <span>${sub.subject_name}</span>
            </label>
        `;
        });

    });

});
subjectContainer.addEventListener("change", function(){

  let selectedValue = programCountSelect.value;
    let checked = document.querySelectorAll("input[name='subjects[]']:checked");

    if(selectedValue === "all"){
        return;
    }

    let max = parseInt(selectedValue);

    if(checked.length > max){
        alert("You can select only " + max + " subjects");
        checked[checked.length - 1].checked = false;
    }

});

$("#enrollForm").submit(function(e){
    e.preventDefault();

    let btn = $(".submit-btn");
    btn.prop("disabled", true).text("Processing...");

    let formData = $(this).serialize();

    $.ajax({
        url: "invoice_system/enroll/save_admin_enrollment.php",
        type: "POST",
        data: formData,

        success: function(res){
            $("#page-content").html(res);
        },

        error: function(){
            alert("Enrollment failed");
            btn.prop("disabled", false).text("Enroll Student");
        }
    });
});

// for discount

document.getElementById("discount_type").addEventListener("change", function(){
    let type = this.value;
    let box = document.getElementById("discount_amount_box");

    if(type === "one_time" || type === "sibling"){
        box.style.display = "block";
    } else {
        box.style.display = "none";
    }
});

// extra amount show/hide
document.getElementById("extra_type").addEventListener("change", function(){
    let type = this.value;
    let box = document.getElementById("extra_amount_box");

    if(type === "one_time" || type === "permanent"){
        box.style.display = "block";
    } else {
        box.style.display = "none";
        document.getElementById("extra_amount").value = "";
    }
});

// grade dependency

document.getElementById("grade").addEventListener("change", function(){

    let grade = this.value;
    let programSelect = document.getElementById("program");

    // reset program
    programSelect.value = "";

    if(grade === "") return;

    if(
        grade === "Pre-School" || 
        grade === "Kindergarten" ||
        grade === "Grade 1" || 
        grade === "Grade 2"
    ){
        programSelect.value = "Early Starters";
    }
    else if(
        ["Grade 3","Grade 4","Grade 5","Grade 6","Grade 7","Grade 8"].includes(grade)
    ){
        programSelect.value = "Elementary";
    }
    else if(
        ["Grade 9","Grade 10","Grade 11","Grade 12"].includes(grade)
    ){
        programSelect.value = "Advanced Learners";
    }

    // trigger program change manually (VERY IMPORTANT)
    programSelect.dispatchEvent(new Event('change'));
});
// disable program
// document.getElementById("program").setAttribute("disabled", true);

</script>