<?php session_start(); ?>

<script>
  const teacherName = "<?php echo $_SESSION['teacher_name'] ?? 'Teacher'; ?>";
</script>
  <style>
:root {
    --primary: #1e40af;
    --primary-light: #3b82f6;
    --primary-dark: #1e3a8a;
    --accent: #ef4444;
    --light-bg: #f5f7fb;
    --card-bg: #ffffff;
    --text: #1f2937;
    --gray: #6b7280;
    --shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
    --shadow-hover: 0 12px 32px rgba(0, 0, 0, 0.1);
}

.dashboard-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    padding:10px 0px;  
    min-height:170px; 
    margin-top: -51px;   
}

.dashboard-illustration{
    width:180px;
    flex-shrink:0;
    display:flex;
    justify-content:center;
    align-items:center;
}

.dashboard-illustration img{
    width:170px;
    height:auto;
    display:block;
}

.greeting {
font-size: 40px;
color: #05364d;
font-family: "Love Ya Like A Sister", cursive;
font-weight: 400;
}

#refresh-btn {
    border-radius: 30px;
    font-weight: 400;
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    color: #fff;
    border: none;
    transition: 0.3s;
    box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
    padding: 10px;
    width: 139px;
    font-size: 17px;
    flex-shrink:0;

}

/* hover RED */
#refresh-btn:hover{
  background: #2a5298;
  color:#fff;
  box-shadow:0 6px 18px rgba(232,6,60,0.4);
}

/* ================= CARDS ================= */

.stat-card {
  background: linear-gradient(135deg, #ffffff, #f8fbff);
  border-radius: 18px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  height: 100%;
  padding: 1.6rem;
  border: none;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: relative;
  overflow: hidden;
}

/* subtle top border accent */
.stat-card::before{
  content:"";
  position:absolute;
  top:0;
  left:0;
  width:100%;
  height:4px;
  background:var(--theme-red);
}

.stat-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
}

.stat-icon {
  width:70px;
  height:70px;
  display:flex;
  align-items:center;
  justify-content:center;
  margin:0 auto 10px;
  background:#f1f3f5;
  border-radius:50%;
  font-size:34px;
  color:var(--accent);
}

.stat-number {
  font-size: 50px;
  font-weight: 700;
  margin: 0;
  font-family: "Love Ya Like A Sister", cursive;
  color:var(--theme-red);
}

.stat-label {
  font-size: 18px;
  color: var(--primary);
  font-weight: 700;
}

.trend {
  font-size: 0.9rem;
}

/* ================= SECTION ================= */

.section-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 1rem;
  padding-left: 0.6rem;
  border-left: 4px solid var(--accent);
  margin-top: 47px;
}

/* ================= QUICK ACTIONS ================= */

.quick-actions-grid {
  display:grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap:16px;
  margin-bottom:2.5rem;
}

.quick-action {
  background:#fff;
  border:none;
  border-radius:14px;
  padding:1.2rem 1rem;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:8px;
  box-shadow:0 4px 14px rgba(0,0,0,0.06);
  transition:0.25s;
  cursor:pointer;
}

.quick-action i {
  font-size:26px;
  color:var(--accent);
  transition:0.25s;
}

.quick-action span {
  font-size:14px;
  font-weight:600;
  text-align:center;
}

.quick-action:hover {
  transform:translateY(-4px);
  box-shadow:0 10px 24px rgba(0,0,0,0.12);
  background:linear-gradient(135deg, #1e3c72, #2a5298);
  color:#fff;
}

.quick-action:hover i {
  color:#fff;
}

.quick-action.disabled {
  opacity:0.55;
  cursor:not-allowed;
}

.quick-action.disabled:hover {
  transform:none;
  background:#fff;
  color:inherit;
  box-shadow:0 4px 14px rgba(0,0,0,0.06);
}

.quick-action.disabled:hover i {
  color:var(--accent);
}

/* ================= CARDS (BOTTOM) ================= */

.card{
  border-radius:18px !important;
  border:none;
  box-shadow:0 8px 25px rgba(0,0,0,0.08);
}

/* ================= LIST ================= */

.activity-list .list-group-item {
  border: none;
  padding: 1rem 1.2rem;
  margin-bottom: 8px;
  background: #f9fbff;
  border-radius: 12px;
  transition:0.2s;
}

.activity-list .list-group-item:hover{
  background:#eef4ff;
}

/* ================= BUTTONS ================= */

.btn{
  border-radius:25px;
}

.text-muted {
    --bs-text-opacity: 1;
    color: var(--bs-secondary-color) !important;
    margin-top: -14px !important;
}

.welcome-text{
    flex:1;
    display:flex;
    justify-content:center;
    align-items:center;
    min-width:0;
    margin-top:22px;   /* thoda upar */
}

.welcome-title{
    margin:0;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    transform:translateY(-10px);
    font-family:"Love Ya Like A Sister", cursive;
    font-size:clamp(28px,2.7vw,42px);

    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.welcome-gradient{
    background:linear-gradient(90deg,#e02121,#5a3d9a);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    white-space:nowrap;
}

.teacher-name{
    color:#3b82f6;
    white-space:nowrap;
    flex-shrink:0;
}

/* ================= LOADER ================= */

.loading-spinner {
  width: 1.4rem;
  height: 1.4rem;
  border: 3px solid rgba(30,60,114,0.15);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ================= STUDENTS PER BATCH ================= */
.batch-summary{
  display:flex; gap:16px; flex-wrap:wrap; margin-bottom:22px;
}
.batch-summary-item{
  flex:1; min-width:150px; color:#fff; border-radius:14px;
  padding:16px 20px; display:flex; flex-direction:column;
  background:linear-gradient(135deg,#1e3c72,#2a5298);
  box-shadow:0 6px 18px rgba(30,60,114,0.25);
}
.batch-summary-item:nth-child(2){
  background:linear-gradient(135deg,#e8063c,#c40530);
  box-shadow:0 6px 18px rgba(232,6,60,0.25);
}
.batch-summary-item .bs-number{
  font-size:34px; font-weight:700; line-height:1;
  font-family:"Love Ya Like A Sister", cursive;
}
.batch-summary-item .bs-label{ font-size:14px; opacity:.9; margin-top:6px; }

.batch-grid{
  display:grid; grid-template-columns:repeat(auto-fill, minmax(220px,1fr)); gap:14px;
}
.batch-item{
  display:flex; align-items:center; justify-content:space-between; gap:12px;
  background:#f8fbff; border:1px solid #eef2f9; border-radius:14px;
  padding:14px 16px; transition:.2s ease;
}
.batch-item:hover{ transform:translateY(-3px); box-shadow:0 10px 22px rgba(0,0,0,.08); background:#fff; }
.batch-name{ margin:0; font-weight:700; color:#1e3c72; font-size:15px; }
.batch-meta{ color:#6b7280; font-size:12.5px; }
.batch-count{
  min-width:44px; height:44px; border-radius:50%; flex-shrink:0;
  background:linear-gradient(135deg,#1e3c72,#2a5298); color:#fff;
  font-weight:700; font-size:16px; display:flex; align-items:center; justify-content:center;
  box-shadow:0 4px 12px rgba(30,60,114,.3);
}

/* ================= MOBILE ================= */

@media (max-width:768px){

  .greeting{
    font-size:1.4rem;
  }
.dashboard-illustration img{display:none;}
  .dashboard-header{
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    padding: 0px 0px 20px;
    min-height: 0px;
    margin-top: 0px;
  }
.welcome-text{margin-top:0px;}
.text-muted{margin-top:0px!important;}
  #refresh-btn{
    /* width: auto;
    padding:10px 18px;
    font-size:14px;
    border-radius:20px;
    align-self:flex-start; */
    display:none;
  }

.stat-card{
    padding:18px 12px;
  }

  .stat-number{
   font-size:32px;
  }

  .stat-icon{
    width:55px;
    height:55px;
    font-size:22px;
  }

  .section-title{
    font-size:1.1rem;
  }

  .card-body{
    padding:1rem !important;
  }

  .quick-actions-grid{
    grid-template-columns: repeat(2, 1fr);
    gap:10px;
  }

  .quick-action{
    padding:1rem 0.6rem;
  }

  .quick-action i{
    font-size:22px;
  }

  .quick-action span{
    font-size:12.5px;
  }

/* container spacing */
  #recent-activity .activity-list{
    display:flex;
    flex-direction:column;
    gap:12px;
  }

  /* each activity item */
  #recent-activity .list-group-item{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:10px;

    padding:14px;
    border-radius:14px;
    background:#f8fafc;

    box-shadow:0 4px 12px rgba(0,0,0,0.05);
  }

  /* text left */
  #recent-activity .list-group-item span{
    font-size:14px;
    line-height:1.4;
    color:#333;
  }

  /* time right */
  #recent-activity .list-group-item small{
    font-size:12px;
    color:#888;
    white-space:nowrap;
  }

  /* FIX wrapping issue */
  #recent-activity .d-flex{
    width:100%;
    flex-direction:column;
    gap:6px;
  }

  /* button styling */
  #activity-footer button{
    width:100%;
    border-radius:12px;
    font-size:14px;
  }
}
  </style>
<!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">
  <div class="dashboard-header">

      <div class="dashboard-illustration">
          <img src="images/welcome-illustration.png" alt="Welcome">
      </div>

      <div class="welcome-text">
        <h1 class="welcome-title">
            <span class="welcome-gradient">Welcome back,</span>
            <span class="teacher-name">
                <?= htmlspecialchars($_SESSION['teacher_name'] ?? 'Teacher') ?>
            </span>
        </h1>
    </div>
      <button id="refresh-btn" class="btn btn-outline-primary btn-sm px-4">
          <span id="refresh-text">Refresh</span>
          <span id="refresh-spinner" class="loading-spinner d-none ms-2"></span>
      </button>

  </div>

  <div class="row g-4 mb-5">
    <!-- My Students -->
    <div class="col-md-3 ">
      <div class="stat-card text-center">
        <div>
        <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
        <p class="stat-number" id="my-students">0</p>
        <p class="stat-label">My Students</p>
        </div>
        <small class="trend text-muted" id="students-trend"></small>
      </div>
    </div>

    <!-- My Subjects -->
    <div class="col-md-3 ">
      <div class="stat-card text-center">
        <div>
        <div class="stat-icon"><i class="bi bi-clipboard-check-fill"></i></div>
        <p class="stat-number" id="my-subjects-count">0</p>
        <p class="stat-label">My Subjects</p>
        </div>
        <small class="trend text-muted" id="subjects-trend"></small>
      </div>
    </div>

    <!-- Student Documents -->
    <div class="col-md-3 ">
      <div class="stat-card text-center">
        <div>
        <div class="stat-icon"><i class="bi bi-file-earmark-check-fill"></i></div>
        <p class="stat-number" id="pending-documents">0</p>
        <p class="stat-label">Pending Docs</p>
        </div>
        <small class="trend text-muted" id="documents-trend"></small>
      </div>
    </div>

    <!-- Assessments -->
    <div class="col-md-3 ">
      <div class="stat-card text-center">
        <div>
        <div class="stat-icon"><i class="bi bi-bar-chart-fill"></i></div>
        <p class="stat-number" id="teacher-assessments">0</p>
        <p class="stat-label">Assessments</p>
        </div>
        <small class="trend text-muted" id="teacher-assessments-trend"></small>
      </div>
    </div>
  </div>

  <!-- Students Per Batch -->
<h5 class="section-title">Students Per Batch</h5>
<div class="card mb-5" style="border-radius:16px; box-shadow:0 6px 20px rgba(0,0,0,0.08);">
  <div class="card-body p-4" id="students-per-batch">
    <div class="text-center py-5"><span class="loading-spinner"></span></div>
  </div>
</div>

  <!-- Quick Actions -->
  <h5 class="section-title">Quick Actions</h5>
  <div class="quick-actions-grid">
    <button type="button" class="quick-action" data-target="my_students.php">
      <i class="bi bi-people-fill"></i>
      <span>Manage Students</span>
    </button>
    <button type="button" class="quick-action" data-target="suggest_course_changes.php">
      <i class="bi bi-lightbulb"></i>
      <span>Suggest Course Change</span>
    </button>
    <button type="button" class="quick-action" data-target="calendar.php">
      <i class="bi bi-calendar-event"></i>
      <span>My Calendar</span>
    </button>
    <button type="button" class="quick-action" data-target="send_email_updates.php">
      <i class="bi bi-envelope-paper"></i>
      <span>Email Students &amp; Parents</span>
    </button>
    <button type="button" class="quick-action" data-target="attendance.php">
      <i class="bi bi-clipboard-check"></i>
      <span>Record Attendance</span>
    </button>
    <button type="button" class="quick-action" data-target="assign_chapter.php">
      <i class="bi bi-book"></i>
      <span>Assign Chapters</span>
    </button>
    <!-- Student Documents -->
    <!-- <button class="quick-action" data-target="my_students.php">
        <i class="bi bi-folder2-open"></i>
        <span>Student Documents</span>
    </button> -->

    <!-- Assign Assessment -->
    <button class="quick-action" data-target="teacher_question_pages/assign_assessment.php">
        <i class="bi bi-file-earmark-plus"></i>
        <span>Assign Assessment</span>
    </button>

    <!-- Manage Assessments -->
    <button class="quick-action" data-target="teacher_question_pages/manage_assessments.php">
        <i class="bi bi-folder-check"></i>
        <span>Manage Assessments</span>
    </button>

    <!-- Manage Questions -->
    <button class="quick-action" data-target="teacher_question_pages/manage_questions.php">
        <i class="bi bi-patch-question"></i>
        <span>Manage Questions</span>
    </button>
  </div>

  <div class="row g-4">
    <!-- My Subjects -->
    <div class="col-lg-6">
      <h5 class="section-title">My Subjects</h5>
      <div class="card" style="border-radius:16px; box-shadow:0 6px 20px rgba(0,0,0,0.08);">
        <div class="card-body p-4" id="teacher-subjects">
          <div class="text-center py-5"><span class="loading-spinner"></span></div>
        </div>
      </div>
    </div>


  <!-- My Recent Activity -->
  <div class="col-lg-6">
    <h5 class="section-title">My Recent Activity</h5>
    <div class="card" style="border-radius:16px; box-shadow:0 8px 25px rgba(0,0,0,0.08);">
      <div class="card-body p-4" id="recent-activity">
        <div class="text-center py-5"><span class="loading-spinner"></span></div>
      </div>
      <div class="card-footer bg-transparent border-0 text-center pb-3" id="activity-footer" style="display:none;">
        <button id="read-more-btn" class="btn btn-outline-primary btn-sm px-4 me-2">View All Activity</button>
        <button id="show-less-btn" class="btn btn-outline-secondary btn-sm px-4" style="display:none;">Show Less</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {

  const BASE_API = './api/';

  const API_ENDPOINTS = {
    myStudents: BASE_API + 'get_my_students_count.php',
    subjectsCount: BASE_API + 'get_my_subjects_count.php',
    pendingDocuments: BASE_API + 'get_pending_documents.php',
    teacherAssessments: BASE_API + 'get_teacher_assessments.php',
    teacherSubjects: BASE_API + 'get_teacher_subjects.php',
    recentActivity: BASE_API + 'get_teacher_recent_activity.php',
    studentsPerBatch: BASE_API + 'get_students_per_batch.php'
  };

  const elements = {
    greeting: $('#greeting'),
    students: $('#my-students'), studentsTrend: $('#students-trend'),
   subjectsCount: $('#my-subjects-count'),
  subjectsTrend: $('#subjects-trend'),

  documents: $('#pending-documents'),
  documentsTrend: $('#documents-trend'),

  assessments: $('#teacher-assessments'),
  assessmentsTrend: $('#teacher-assessments-trend'),
    subjects: $('#teacher-subjects'),
    activity: $('#recent-activity'),
    batches: $('#students-per-batch'),
    footer: $('#activity-footer'),
    readMoreBtn: $('#read-more-btn'),
    showLessBtn: $('#show-less-btn'),
    refreshBtn: $('#refresh-btn'),
    refreshText: $('#refresh-text'),
    refreshSpinner: $('#refresh-spinner')
  };

  function showLoading(el) {
    el.html('<div class="text-center py-5"><span class="loading-spinner"></span></div>');
  }

  function handleError(el) {
    el.html('<div class="text-center py-5 text-danger small">Failed to load</div>');
  }

  async function fetchData(endpoint, element, successCallback) {
    showLoading(element);
    try {
      const res = await $.ajax({ url: endpoint, dataType: 'json', timeout: 10000 });
      if (res && res.success) {
        successCallback(res);
      } else {
        handleError(element);
      }
    } catch (e) {
      handleError(element);
    }
  }

  function renderActivity(container, items) {
    let html = '<div class="list-group activity-list">';
    if (!items || items.length === 0) {
      html += '<div class="list-group-item text-muted text-center py-4">No recent activity</div>';
    } else {
      items.forEach(item => {
        html += `<div class="list-group-item">
                   <div class="d-flex justify-content-between">
                     <span>${item.description}</span>
                     <small class="text-muted">${item.time_ago}</small>
                   </div>
                 </div>`;
      });
    }
    html += '</div>';
    container.html(html);
  }

    function renderBatches(container, data) {
    const batches = data.batches || [];

    let html = `
      <div class="batch-summary">
        <div class="batch-summary-item">
          <span class="bs-number">${data.total_students || 0}</span>
          <span class="bs-label">Total Students</span>
        </div>
        <div class="batch-summary-item">
          <span class="bs-number">${data.total_branches || 0}</span>
          <span class="bs-label">Total Branches</span>
        </div>
      </div>`;

    if (batches.length === 0) {
      html += `<div class="text-center py-4 text-muted">No batches found</div>`;
    } else {
      html += '<div class="batch-grid">';
      batches.forEach(b => {
        const type = (b.course_type || '').replaceAll('_', ' ');
        html += `
          <div class="batch-item">
            <div class="batch-info">
              <h6 class="batch-name">${b.subject_name}</h6>
              <small class="batch-meta">Grade ${b.grade}${type ? ' · ' + type : ''}</small>
            </div>
            <span class="batch-count">${b.student_count}</span>
          </div>`;
      });
      html += '</div>';
    }

    container.html(html);
  }

  function loadRecentActivity() {
    return fetchData(API_ENDPOINTS.recentActivity, elements.activity, res => {
      renderActivity(elements.activity, res.data);

      elements.readMoreBtn.show();
      elements.showLessBtn.hide();
      elements.footer.toggle(!!(res.data && res.data.length > 0));
    });
  }

  async function loadDashboard() {
    elements.refreshText.text('Refreshing...');
    elements.refreshSpinner.removeClass('d-none');

    try {
      await Promise.all([
        fetchData(API_ENDPOINTS.myStudents, elements.students, res => {
          elements.students.text(res.data.count || 0);
          elements.studentsTrend.html(res.data.trend || '');
        }),

        fetchData(API_ENDPOINTS.subjectsCount, elements.subjectsCount, res => {
          elements.subjectsCount.text(res.data.count || 0);
          elements.subjectsTrend.text(res.data.trend);
        }),

        fetchData(API_ENDPOINTS.pendingDocuments, elements.documents, res => {
          elements.documents.text(res.data.count || 0);
          elements.documentsTrend.text(res.data.trend);
        }),

        fetchData(API_ENDPOINTS.teacherAssessments, elements.assessments, res => {
          elements.assessments.text(res.data.count || 0);
          elements.assessmentsTrend.text(res.data.trend);
        }),

        fetchData(API_ENDPOINTS.studentsPerBatch, elements.batches, res => {
          renderBatches(elements.batches, res.data);
        }),

        fetchData(API_ENDPOINTS.teacherSubjects, elements.subjects, res => {
          let html = '';

          if (!res.data || res.data.length === 0) {
            html = `<div class="text-center py-5 text-muted">No subjects assigned</div>`;
          } else {
            html = '<div class="list-group activity-list">';
            res.data.forEach(s => {
              html += `<div class="list-group-item">
                          <div class="d-flex justify-content-between align-items-center">
                            <div>
                              <h6 class="mb-1 fw-bold text-primary">${s.subject_name}</h6>
                              <small class="text-muted">
                                Grade : <b>${s.grade}</b> &nbsp; | &nbsp; ${s.course_type.replaceAll('_',' ')}
                              </small>
                            </div>
                            <div>
                              <span class="badge bg-primary rounded-pill">${s.total_students} Students</span>
                            </div>
                          </div>
                        </div>`;
            });
            html += '</div>';
          }

          elements.subjects.html(html);
        }),

        loadRecentActivity()
      ]);
    } finally {
      elements.refreshText.text('Refresh');
      elements.refreshSpinner.addClass('d-none');
    }
  }

  // View All Activity
  elements.readMoreBtn.on('click', function() {
    $(this).prop('disabled', true).html('Loading...');

    $.ajax({
      url: API_ENDPOINTS.recentActivity + '?limit=all',
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data) {
          renderActivity(elements.activity, res.data);
          elements.readMoreBtn.hide();
          elements.showLessBtn.show();
        }
      },
      complete: function() {
        elements.readMoreBtn.prop('disabled', false).html('View All Activity');
      }
    });
  });

  // Show Less Button - only resets the activity panel, not the whole dashboard
  elements.showLessBtn.on('click', function() {
    loadRecentActivity();
  });

  // Quick Actions - reuse the sidebar's existing SPA navigation (data-page/.menu-link)
  $('.quick-action:not(.disabled)').on('click', function() {
    const page = $(this).data('target');
    $('.menu-link[data-page="' + page + '"]').trigger('click');
  });

  loadDashboard();
  elements.refreshBtn.on('click', loadDashboard);
});
</script>