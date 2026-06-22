<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

session_start();

include '../db_config.php';
include 'branch_dashboard_sidebar.php';

$branch_id = $_SESSION['branch_id'];

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] != 'branch_admin'){
    header("Location: ../login.php");
    exit();
}

$stmt = $conn->prepare("SELECT event_name, description, date 
                        FROM events 
                        WHERE (date BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()) 
                           OR (date > CURDATE() AND branch_id = ?)");
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => $row['event_name'],
        'start' => $row['date'],
        'description' => $row['description']
    ];
}
?>

<!DOCTYPE HTML>
<html lang="en">
  <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
     <title>Events in the branch</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
     <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
     <link rel="stylesheet" href="branch.css"/>

     <!-- FullCalendar CSS -->
     <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet"/>
     <style>
        #calendar {
            max-width: 1000px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .fc-h-event {
    background-color: green;
    border: green;
    display: block;
}
     </style>
  </head>
  
  <body>
      <section class="main">
          <div class="info">
              <div id="calendar"></div>
          </div>
      </section>

      <!-- FullCalendar JS -->
      <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                themeSystem: 'bootstrap5',
                events: <?php echo json_encode($events); ?>,
                eventDidMount: function(info) {
                    if (info.event.extendedProps.description) {
                        new bootstrap.Tooltip(info.el, {
                            title: info.event.extendedProps.description,
                            placement: 'top',
                            trigger: 'hover',
                            container: 'body'
                        });
                    }
                }
            });

            calendar.render();
        });
      </script>
  </body>
</html>
