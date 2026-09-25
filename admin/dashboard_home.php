<!-- dashboard_home.php -->

<style>
  body {
    background: #f4f7fb;
    font-family: 'Poppins', 'Segoe UI', sans-serif;
  }

  /* Header */

  .dashboard-header {
    background: white;
    padding: 18px 22px;
    border-radius: 18px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, .05);
    flex-wrap: wrap;
    gap: 10px;
  }

  .dashboard-header h2 {
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    font-size: 22px;
  }

  /* Cards */

  .card {
    border: none;
    border-radius: 18px;
    background: white;
    box-shadow: 0 6px 18px rgba(0, 0, 0, .05);
    transition: .3s;
    position: relative;
    padding: 20px;
    overflow: hidden;
  }

  .card:hover {
    transform: translateY(-5px);
  }

  /* Gradient border */

  .card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 5px;
    border-radius: 18px 0 0 18px;
  }

  /* Colors */

  .card-blue::before {
    background: linear-gradient(180deg, #36d1dc, #5b86e5);
  }

  .card-green::before {
    background: linear-gradient(180deg, #11998e, #38ef7d);
  }

  .card-orange::before {
    background: linear-gradient(180deg, #f7971e, #ffd200);
  }

  .card-purple::before {
    background: linear-gradient(180deg, #834d9b, #d04ed6);
  }

  /* Card text */

  .card-title {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 8px;
  }

  .card-value {
    font-size: 26px;
    font-weight: 700;
    color: #111827;
  }

  /* Section */

  .section-card {
    border-radius: 18px;
    background: white;
    box-shadow: 0 6px 20px rgba(0, 0, 0, .05);
    padding: 20px;
    height: 100%;
  }

  .section-card h5 {
    font-weight: 600;
    margin-bottom: 15px;
  }

  /* Activity */

  .list-group-item {
    border: none;
    border-bottom: 1px solid #f1f1f1;
    font-size: 14px;
    padding: 12px 0;
  }

  /* Button */

  #refresh-btn {
    border-radius: 8px;
    padding: 6px 14px;
  }

  /* Spinner */

  .loading-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid #ddd;
    border-top: 2px solid #0d6efd;
    border-radius: 50%;
    animation: spin .6s linear infinite;
  }

  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
  }

  /* Section title (matches teacher dashboard) */

  .section-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1e40af;
    margin-bottom: 1rem;
    padding-left: 0.6rem;
    border-left: 4px solid #ef4444;
    margin-top: 50px;
  }

  /* Quick Actions (matches teacher dashboard) */

  .quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    margin-bottom: 2rem;
  }

  .quick-action {
    background: #fff;
    border: none;
    border-radius: 14px;
    padding: 1.2rem 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    transition: 0.25s;
    cursor: pointer;
    text-decoration: none;
  }

  .quick-action i {
    font-size: 26px;
    color: #ef4444;
    transition: 0.25s;
  }

  .quick-action span {
    font-size: 14px;
    font-weight: 600;
    text-align: center;
    color: #1f2937;
  }

  .quick-action:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    color: #fff;
  }

  .quick-action:hover i,
  .quick-action:hover span {
    color: #fff;
  }

    /* Teacher status list */

  .teacher-status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f1f1f1;
  }

  .teacher-status-item:last-child {
    border-bottom: none;
  }

  .ts-info {
    display: flex;
    flex-direction: column;
  }

  .ts-name {
    font-weight: 600;
    font-size: 14px;
    color: #1f2937;
  }

  .ts-sub {
    font-size: 12px;
    color: #6b7280;
  }

  .ts-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
  }

  .status-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 10px;
    border-radius: 20px;
  }

  .status-badge.online {
    background: #dcfce7;
    color: #15803d;
  }

  .status-badge.offline {
    background: #f3f4f6;
    color: #6b7280;
  }

  .ts-seen {
    font-size: 11px;
    color: #9ca3af;
  }

    .kpi-sub {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
    font-weight: 500;
  }

  /* Responsive */

  @media(max-width:992px) {

    .card-value {
      font-size: 22px;
    }

  }

  @media(max-width:768px) {

    .dashboard-header {
      flex-direction: column;
      align-items: flex-start;
    }

    .dashboard-header h2 {
      font-size: 20px;
    }

    .card {
      padding: 16px;
    }

    .card-value {
      font-size: 20px;
    }

    .quick-actions-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
    }

    .quick-action {
      padding: 1rem 0.6rem;
    }

    .quick-action i {
      font-size: 22px;
    }

    .quick-action span {
      font-size: 12.5px;
    }

  }

  @media(max-width:480px) {

    .card-title {
      font-size: 13px;
    }

    .card-value {
      font-size: 18px;
    }

    .section-card {
      padding: 15px;
    }

  }

  @media(max-width:300px) {

    .card {
      padding: 12px;
    }

    .card-value {
      font-size: 16px;
    }

    .dashboard-header h2 {
      font-size: 18px;
    }

  }
</style>


<div class="dashboard-header d-flex justify-content-between align-items-center mb-4">
  <h2>Dashboard Overview</h2>

  <button id="refresh-btn" class="btn btn-outline-secondary btn-sm">
    <span id="refresh-text">Refresh</span>
    <span id="refresh-spinner" class="loading-spinner d-none"></span>
  </button>
</div><!-- Top Cards -->
<div class="row g-4 mb-4">

  <div class="col-md-4">
    <div class="card card-blue">
      <div class="card-title">Total Teachers</div>
      <div class="card-value" id="total-teachers"><span class="loading-spinner"></span></div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card card-green">
      <div class="card-title">Total Students</div>
      <div class="card-value" id="total-students"><span class="loading-spinner"></span></div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card card-purple">
      <div class="card-title">Online Teachers</div>
      <div class="card-value" id="online-teachers"><span class="loading-spinner"></span></div>
    </div>
  </div>

</div>


<!-- Sales Overview KPIs -->
<h5 class="section-title">Sales Overview</h5>
<div class="row g-4 mb-4">

  <div class="col-md-3">
    <div class="card card-green">
      <div class="card-title">This Month's Sales</div>
      <div class="card-value" id="sales-month"><span class="loading-spinner"></span></div>
      <div class="kpi-sub" id="sales-month-sub"></div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card card-blue">
      <div class="card-title">Total Sales (Lifetime)</div>
      <div class="card-value" id="sales-total"><span class="loading-spinner"></span></div>
      <div class="kpi-sub" id="sales-total-sub"></div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card card-orange">
      <div class="card-title">Outstanding (Pending)</div>
      <div class="card-value" id="sales-pending"><span class="loading-spinner"></span></div>
      <div class="kpi-sub" id="sales-pending-sub"></div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card card-purple">
      <div class="card-title">New Invoices (This Month)</div>
      <div class="card-value" id="sales-new"><span class="loading-spinner"></span></div>
      <div class="kpi-sub" id="sales-new-sub"></div>
    </div>
  </div>

</div>
<!-- Quick Actions -->
<h5 class="section-title">Quick Actions</h5>
<div class="quick-actions-grid">

  <button type="button" class="quick-action" data-target="manage_teachers.php">
    <i class="bi bi-person-badge"></i>
    <span>Manage Teachers</span>
  </button>

  <button type="button" class="quick-action" data-target="manage_students.php">
    <i class="bi bi-people"></i>
    <span>Manage Students</span>
  </button>

  <button type="button" class="quick-action" data-target="manage_branches.php">
    <i class="bi bi-diagram-3"></i>
    <span>Manage Branches</span>
  </button>

  <button type="button" class="quick-action" data-target="manage_courses.php">
    <i class="bi bi-book"></i>
    <span>Manage Courses</span>
  </button>

  <button type="button" class="quick-action" data-target="admin_events_calendar.php">
    <i class="bi bi-calendar-event"></i>
    <span>Events Calendar</span>
  </button>

  <button type="button" class="quick-action" data-target="admin_attendance.php">
    <i class="bi bi-calendar-check"></i>
    <span>Attendance</span>
  </button>

  <button type="button" class="quick-action" data-target="invoice_system/enroll/admin_enroll_student.php">
    <i class="bi bi-person-plus"></i>
    <span>Enroll Student</span>
  </button>

  <button type="button" class="quick-action" data-target="invoice_system/payments/payment_list.php">
    <i class="bi bi-cash-coin"></i>
    <span>Payments</span>
  </button>

</div>


<!-- Bottom Section -->
<div class="row g-4">

  <div class="col-md-6">
    <div class="section-card">
      <h5 class="mb-3">Teacher Status</h5>
      <div id="login-status"><span class="loading-spinner"></span></div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="section-card">
      <h5 class="mb-3">Recent Activity</h5>
      <div id="recent-activity">
        <div class="text-center py-3"><span class="loading-spinner"></span></div>
      </div>
    </div>
  </div>

</div>

<script>
  $(document).ready(function() {
    const API_ENDPOINTS = {
      teachers: '../api/Get_teacher_count.php',
      students: '../api/Get_student_count.php',
      sales: '../api/Get_cource_sell.php',
      online: '../api/online_teachers.php',
      logins: '../api/teacher_logins.php',
      activity: '../api/recent_activity.php'
    };

    const elements = {
      teachers: $('#total-teachers'),
      students: $('#total-students'),
      online: $('#online-teachers'),
      logins: $('#login-status'),
      activity: $('#recent-activity'),
      refreshBtn: $('#refresh-btn'),
      refreshText: $('#refresh-text'),
      refreshSpinner: $('#refresh-spinner')
    };

    function formatNumber(num) {
      return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function formatCurrency(amount) {
      return '$' + formatNumber(amount);
    }

    function showLoading(element) {
      element.html('<span class="loading-spinner"></span>');
    }

    function handleError(element, error) {
      console.error('API Error:', error);
      element.html('<span class="text-danger">Failed to load</span>');
    }

    async function fetchData(endpoint, element, formatter = null) {
      showLoading(element);
      try {
        const response = await $.ajax({
          url: endpoint,
          dataType: 'json',
          timeout: 5000
        });

        if (response && response.success) {
          element.html(formatter ? formatter(response.data) : response.data);
        } else {
          handleError(element, response?.message || 'Invalid response');
        }
      } catch (error) {
        handleError(element, error);
      }
    }

    async function fetchRecentActivity() {
      showLoading(elements.activity);
      try {
        const response = await $.ajax({
          url: API_ENDPOINTS.activity,
          dataType: 'json',
          timeout: 5000
        });

        if (response && response.success) {
          let html = '';
          if (response.data.length > 0) {
            html = '<div class="list-group">';
            response.data.forEach(item => {
              html += `
              <div class="list-group-item">
                <div class="d-flex justify-content-between">
                  <span>${item.description}</span>
                  <small class="text-muted">${new Date(item.timestamp).toLocaleString()}</small>
                </div>
              </div>
            `;
            });
            html += '</div>';
          } else {
            html = '<p class="text-muted">No recent activity</p>';
          }
          elements.activity.html(html);
        } else {
          handleError(elements.activity, response?.message || 'Invalid response');
        }
      } catch (error) {
        handleError(elements.activity, error);
      }
    }

        async function fetchSalesKpis() {
      const ids = ['sales-month', 'sales-total', 'sales-pending', 'sales-new'];
      ids.forEach(id => $('#' + id).html('<span class="loading-spinner"></span>'));
      try {
        const response = await $.ajax({
          url: API_ENDPOINTS.sales,
          dataType: 'json',
          timeout: 5000
        });

        if (response && response.success) {
          const d = response.data;

          $('#sales-month').text(formatCurrency(d.month_revenue));
          $('#sales-month-sub').text(formatNumber(d.month_count) + ' payment(s) received');

          $('#sales-total').text(formatCurrency(d.total_revenue));
          $('#sales-total-sub').text(formatNumber(d.total_count) + ' payments all-time');

          $('#sales-pending').text(formatCurrency(d.pending_amount));
          $('#sales-pending-sub').text(formatNumber(d.pending_count) + ' invoice(s) unpaid');

          $('#sales-new').text(formatNumber(d.new_invoices_month));
          $('#sales-new-sub').text('billed this month');
        } else {
          ids.forEach(id => $('#' + id).html('<span class="text-danger">Failed</span>'));
        }
      } catch (error) {
        console.error('Sales KPI Error:', error);
        ids.forEach(id => $('#' + id).html('<span class="text-danger">Failed</span>'));
      }
    }

    async function loadDashboard() {
      elements.refreshText.text('Refreshing...');
      elements.refreshSpinner.removeClass('d-none');

      try {
        await Promise.all([
          fetchData(API_ENDPOINTS.teachers, elements.teachers, formatNumber),
          fetchData(API_ENDPOINTS.students, elements.students, formatNumber),
          fetchSalesKpis(),
          fetchData(API_ENDPOINTS.online, elements.online, formatNumber),
                    fetchData(API_ENDPOINTS.logins, elements.logins, data => {
            if (!data || data.length === 0) {
              return '<p class="text-muted mb-0">No teachers found</p>';
            }
            let html = '';
            data.forEach(t => {
              const badge = t.online
                ? '<span class="status-badge online">Online</span>'
                : '<span class="status-badge offline">Offline</span>';
              const seen = t.last_seen
                ? new Date(t.last_seen.replace(' ', 'T')).toLocaleString()
                : 'Never logged in';
              html += `
                <div class="teacher-status-item">
                  <div class="ts-info">
                    <span class="ts-name">${t.name}</span>
                    ${t.subject ? `<span class="ts-sub">${t.subject}</span>` : ''}
                  </div>
                  <div class="ts-right">
                    ${badge}
                    <small class="ts-seen">${t.online ? 'Active now' : seen}</small>
                  </div>
                </div>`;
            });
            return html;
          }),
          fetchRecentActivity()
        ]);
      } finally {
        elements.refreshText.text('Refresh');
        elements.refreshSpinner.addClass('d-none');
      }
    }

    // Initial load
    loadDashboard();

    // Refresh on click
    elements.refreshBtn.on('click', loadDashboard);

    // Quick Actions - reuse sidebar's SPA navigation (data-page/.menu-link)
    $('.quick-action').on('click', function() {
      const page = $(this).data('target');
      $('.menu-link[data-page="' + page + '"]').trigger('click');
    });
  });
</script>