<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Branch Calendar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="branch.css" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
</head>
<body>
  <?php include 'branch_dashboard_sidebar.php'; ?>

  <main class="main">
    <?php flash_render(); ?>
    <?php
      $CAL_CTX    = 'branch';
      $CAL_FETCH  = 'branch_fetch_events.php';
      $CAL_SAVE   = 'branch_save_event.php';
      $CAL_DELETE = 'branch_delete_event.php';
      $CAL_TITLE  = 'Branch Calendar';
      include __DIR__ . '/../calendar_widget.php';
    ?>
  </main>
</body>
</html>