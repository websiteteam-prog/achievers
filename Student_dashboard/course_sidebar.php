<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../db_config.php";

if (!isset($_SESSION['student_email'])) {
    header("Location:student_login.php");
    exit();
}

$subject_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$student_id = $_SESSION['student_id'];

if (!$subject_id) {
    die("course sidebar Invalid subject ID");
}

// Only show chapters the teacher has actually assigned to this student
$sql =  "SELECT
            topics.id AS topic_id,
            subjects.subject_name,
            chapters.chapter_name,
            chapters.id AS chapter_id,
            topics.title,
            topics.file_path,
            topics.position
         FROM topics
         JOIN chapters ON topics.chapter_id = chapters.id
         JOIN assigned_chapters ac
              ON ac.chapter_title = chapters.chapter_name
             AND ac.subject_id = chapters.subject_id
             AND ac.student_id = ?
         JOIN subjects ON subjects.id = chapters.subject_id
         WHERE chapters.subject_id = ?
         ORDER BY chapters.id ASC, topics.position ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $student_id, $subject_id);
$stmt->execute();
$result = $stmt->get_result();

$chapters = [];

$name_stmt = $conn->prepare("SELECT subject_name FROM subjects WHERE id = ?");
$name_stmt->bind_param("i", $subject_id);
$name_stmt->execute();
$subject_name = $name_stmt->get_result()->fetch_assoc()['subject_name'] ?? 'Subject';

while ($row = $result->fetch_assoc()) {
    $chapter_id = $row['chapter_id'];
    $chapter_name = $row['chapter_name'];

    if (!isset($chapters[$chapter_id])) {
        $chapters[$chapter_id] = [
            'chapter_name' => $chapter_name,
            'topics' => []
        ];
    }

    $chapters[$chapter_id]['topics'][] = [
        'id' => $row['topic_id'],
        'title' => $row['title'],
        'position' => $row['position']
    ];
}

$total_topics = 0;
foreach ($chapters as $ch) {
    $total_topics += count($ch['topics']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($subject_name) ?> • Achiever's Castle</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --primary-dark: #1e3a8a;
            --brand-blue: #2f55a4;
            --accent: #ef4444;
            --accent-dark: #dc2626;
            --light-bg: #f5f7fb;
            --text: #1f2937;
            --gray: #6b7280;
            --border: #eef1f6;
            --shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            --sidebar-w: 300px;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            display: flex;
            background: var(--light-bg);
            color: var(--text);
            font-family: system-ui, -apple-system, sans-serif;
            overflow: hidden;
        }

        /* ============ SIDEBAR ============ */
        .course-sidebar {
            width: var(--sidebar-w);
            height: 100vh;
            background: #fff;
            border-right: 1px solid var(--border);
            box-shadow: 4px 0 24px rgba(15, 23, 42, 0.06);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            z-index: 1050;
            transition: transform 0.35s ease;
        }

        .sidebar-top {
            padding: 18px 18px 16px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-logo-wrap {
            text-align: center;
        }

        .sidebar-logo {
            width: 130px;
            height: auto;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            font-size: 13px;
            font-weight: 600;
            color: var(--gray);
            text-decoration: none;
            transition: 0.2s;
        }

        .back-link:hover {
            color: var(--primary);
        }

        .subject-card {
            margin-top: 12px;
            padding: 14px 16px;
            border-radius: 16px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
            box-shadow: 0 8px 20px rgba(30, 60, 114, 0.25);
        }

        .subject-card .label {
            font-size: 11px;
            letter-spacing: 1px;
            text-transform: uppercase;
            opacity: 0.75;
        }

        .subject-card h2 {
            font-family: "Love Ya Like A Sister", cursive;
            font-size: 26px;
            font-weight: 400;
            margin: 2px 0 6px;
            word-break: break-word;
        }

        .subject-card .stats {
            display: flex;
            gap: 14px;
            font-size: 12.5px;
            opacity: 0.9;
        }

        .sidebar-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 14px 14px 24px;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .sidebar-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .section-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--gray);
            padding: 0 6px 8px;
            border-left: 3px solid var(--accent);
            padding-left: 8px;
            margin: 2px 0 10px;
            line-height: 1;
        }

        /* Chapter */
        .chapter-item {
            margin-bottom: 8px;
        }

        .chapter-link {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            border: none;
            border-radius: 12px;
            background: transparent;
            color: #334155;
            font-size: 14.5px;
            font-weight: 600;
            text-align: left;
            cursor: pointer;
            transition: 0.25s;
        }

        .chapter-link:hover {
            background: #eef2ff;
            color: var(--primary);
        }

        .chapter-link.open {
            background: #eef2ff;
            color: var(--primary);
        }

        .chapter-num {
            flex-shrink: 0;
            width: 30px;
            height: 30px;
            border-radius: 9px;
            background: #fff1f2;
            color: var(--accent);
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.25s;
        }

        .chapter-link.open .chapter-num,
        .chapter-link:hover .chapter-num {
            background: var(--accent);
            color: #fff;
        }

        .chapter-title {
            flex: 1;
            min-width: 0;
            line-height: 1.3;
        }

        .chapter-arrow {
            flex-shrink: 0;
            font-size: 13px;
            color: var(--gray);
            transition: transform 0.3s;
        }

        .chapter-link.open .chapter-arrow {
            transform: rotate(180deg);
            color: var(--primary);
        }

        /* Topics */
        .topics-container {
            display: none;
            margin: 6px 0 4px 26px;
            padding-left: 14px;
            border-left: 2px dashed #dbe3f0;
        }

        .topics-container.show {
            display: block;
        }

        .topic-link {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 9px 12px;
            margin-bottom: 4px;
            border-radius: 10px;
            color: var(--gray);
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            line-height: 1.35;
            transition: 0.2s;
        }

        .topic-link i {
            margin-top: 1px;
            font-size: 14px;
            color: #94a3b8;
        }

        .topic-link:hover {
            background: #f1f5ff;
            color: var(--primary);
        }

        .topic-link:hover i {
            color: var(--primary-light);
        }

        .topic-link.active {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 6px 14px rgba(30, 60, 114, 0.25);
        }

        .topic-link.active i {
            color: #fff;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            color: var(--gray);
            padding: 36px 12px;
        }

        .empty-state i {
            font-size: 42px;
            display: block;
            margin-bottom: 10px;
            color: #cbd5e1;
        }

        /* ============ CONTENT ============ */
        .content {
            flex: 1;
            min-width: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .content-topbar {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 28px;
            background: #fff;
            border-bottom: 1px solid var(--border);
            min-height: 68px;
        }

        .menu-btn {
            display: none;
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            border: none;
            border-radius: 50%;
            background: var(--primary-light);
            color: #fff;
            font-size: 20px;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
        }

        .crumbs {
            min-width: 0;
            flex: 1;
        }

        .crumbs .path {
            font-size: 12.5px;
            color: var(--gray);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .crumbs .current {
            font-size: 17px;
            font-weight: 700;
            color: var(--primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .topbar-btn {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 50px;
            background: #eef2ff;
            color: var(--primary);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.25s;
        }

        .topbar-btn:hover {
            background: var(--primary-light);
            color: #fff;
        }

        .frame-wrap {
            position: relative;
            flex: 1;
            min-height: 0;
        }

        .frame-wrap iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
            background: var(--light-bg);
        }

        /* Welcome placeholder (shown until a topic is opened) */
        .welcome-panel {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            background: var(--light-bg);
            overflow-y: auto;
        }

        .welcome-panel.hidden {
            display: none;
        }

        .welcome-card {
            max-width: 620px;
            width: 100%;
            background: #fff;
            border-radius: 24px;
            padding: 40px 36px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        .welcome-card img {
            width: 190px;
            max-width: 60%;
            height: auto;
            margin-bottom: 14px;
        }

        .welcome-card h1 {
            font-family: "Love Ya Like A Sister", cursive;
            font-size: clamp(28px, 4vw, 40px);
            font-weight: 400;
            margin-bottom: 8px;
            background: linear-gradient(to right, #e02121, #2f55a4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }

        .welcome-card p {
            color: var(--gray);
            margin-bottom: 24px;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            text-align: center;
        }

        .step {
            background: var(--light-bg);
            border-radius: 16px;
            padding: 18px 10px;
        }

        .step i {
            font-size: 24px;
            color: var(--accent);
        }

        .step span {
            display: block;
            margin-top: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
        }

        .start-btn {
            margin-top: 26px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 30px;
            border: none;
            border-radius: 50px;
            background: #e02121;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 6px 16px rgba(224, 33, 33, 0.3);
            transition: 0.25s;
        }

        .start-btn:hover {
            background: var(--brand-blue);
            box-shadow: 0 8px 20px rgba(47, 85, 164, 0.35);
        }

        /* Loader */
        .frame-loader {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(245, 247, 251, 0.85);
            z-index: 5;
        }

        .frame-loader.show {
            display: flex;
        }

        .sidebar-backdrop {
            display: none;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 992px) {
            .course-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                transform: translateX(-100%);
            }

            .course-sidebar.show {
                transform: translateX(0);
            }

            .sidebar-backdrop.show {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.45);
                z-index: 1040;
            }

            .menu-btn {
                display: inline-flex;
            }

            .content-topbar {
                padding: 12px 16px;
            }
        }

        @media (max-width: 576px) {
            :root {
                --sidebar-w: 280px;
            }

            .topbar-btn span {
                display: none;
            }

            .topbar-btn {
                padding: 10px 12px;
            }

            .welcome-card {
                padding: 28px 18px;
            }

            .steps {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- ============ COURSE SIDEBAR ============ -->
    <aside class="course-sidebar" id="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo-wrap">
                <a href="student_dashboard.php">
                    <img src="../images/logo.png" alt="Achiever's Castle Logo" class="sidebar-logo">
                </a>
            </div>

            <a href="enrolled_subjects.php" class="back-link">
                <i class="bi bi-arrow-left"></i> Back to Enrolled Subjects
            </a>

            <div class="subject-card">
                <div class="label">Subject</div>
                <h2><?= htmlspecialchars($subject_name) ?></h2>
                <div class="stats">
                    <span><i class="bi bi-journal-bookmark me-1"></i><?= count($chapters) ?> Chapters</span>
                    <span><i class="bi bi-list-check me-1"></i><?= $total_topics ?> Topics</span>
                </div>
            </div>
        </div>

        <div class="sidebar-scroll">
            <div class="section-label">Chapters</div>

            <?php if (empty($chapters)): ?>
                <div class="empty-state">
                    <i class="bi bi-journal-x"></i>
                    No chapter assigned yet.
                </div>
            <?php endif; ?>

            <?php $chapter_no = 0; ?>
            <?php foreach ($chapters as $chapter_id => $chapter) { $chapter_no++; ?>
                <div class="chapter-item">
                    <button type="button"
                        class="chapter-link"
                        data-chapter-id="<?= $chapter_id ?>">
                        <span class="chapter-num"><?= $chapter_no ?></span>
                        <span class="chapter-title"><?= htmlspecialchars($chapter['chapter_name']) ?></span>
                        <i class="bi bi-chevron-down chapter-arrow"></i>
                    </button>

                    <div class="topics-container" id="topics-<?= $chapter_id ?>">
                        <?php foreach ($chapter['topics'] as $topic) { ?>
                            <a class="topic-link"
                                href="quiz.php?topic_id=<?= $topic['id'] ?>&id=<?= $subject_id ?>"
                                target="contentFrame"
                                data-chapter="<?= htmlspecialchars($chapter['chapter_name']) ?>"
                                data-title="<?= htmlspecialchars($topic['title']) ?>">
                                <i class="bi bi-play-circle"></i>
                                <span><?= htmlspecialchars($topic['position']) ?>. <?= htmlspecialchars($topic['title']) ?></span>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </aside>

    <!-- ============ CONTENT ============ -->
    <main class="content">
        <div class="content-topbar">
            <button class="menu-btn" id="hamburgerBtn" aria-label="Open chapters">
                <i class="bi bi-list"></i>
            </button>

            <div class="crumbs">
                <div class="path" id="crumbPath"><?= htmlspecialchars($subject_name) ?></div>
                <div class="current" id="crumbCurrent">Choose a topic to begin</div>
            </div>

            <a href="student_dashboard.php" class="topbar-btn">
                <i class="bi bi-house"></i><span>Dashboard</span>
            </a>
        </div>

        <div class="frame-wrap">
            <div class="welcome-panel" id="welcomePanel">
                <div class="welcome-card">
                    <img src="../images/welcome-illustration.png" alt="">
                    <h1>Let's learn <?= htmlspecialchars($subject_name) ?>!</h1>
                    <?php if (empty($chapters)): ?>
                        <p>Your teacher hasn't assigned any chapters yet. Please check back soon.</p>
                    <?php else: ?>
                        <p>Pick a chapter from the left, then open a topic to start practising.</p>

                        <div class="steps">
                            <div class="step"><i class="bi bi-journal-bookmark-fill"></i><span>Choose a chapter</span></div>
                            <div class="step"><i class="bi bi-play-circle-fill"></i><span>Open a topic</span></div>
                            <div class="step"><i class="bi bi-trophy-fill"></i><span>Answer &amp; get graded</span></div>
                        </div>

                        <button type="button" class="start-btn" id="startBtn">
                            <i class="bi bi-rocket-takeoff"></i> Start Learning
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="frame-loader" id="frameLoader">
                <div class="spinner-border text-primary" role="status"></div>
            </div>

            <iframe name="contentFrame" id="contentFrame" src="about:blank"></iframe>
        </div>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const welcomePanel = document.getElementById('welcomePanel');
        const frameLoader = document.getElementById('frameLoader');
        const frame = document.getElementById('contentFrame');
        const subjectName = <?= json_encode($subject_name) ?>;

        function openChapter(link) {
            const topicsDiv = document.getElementById('topics-' + link.dataset.chapterId);
            const isOpen = topicsDiv.classList.contains('show');

            // Only one chapter open at a time
            document.querySelectorAll('.topics-container').forEach(div => div.classList.remove('show'));
            document.querySelectorAll('.chapter-link').forEach(l => l.classList.remove('open'));

            if (!isOpen) {
                topicsDiv.classList.add('show');
                link.classList.add('open');
            }
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        }

        // Chapter dropdown toggle
        document.querySelectorAll('.chapter-link').forEach(link => {
            link.addEventListener('click', () => openChapter(link));
        });

        // Topic click: highlight, update breadcrumb, show loader
        const topicLinks = document.querySelectorAll('.topic-link');
        topicLinks.forEach(link => {
            link.addEventListener('click', () => {
                topicLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');

                document.getElementById('crumbPath').textContent = subjectName + '  ›  ' + link.dataset.chapter;
                document.getElementById('crumbCurrent').textContent = link.dataset.title;

                welcomePanel.classList.add('hidden');
                frameLoader.classList.add('show');
                closeSidebar();
            });
        });

        frame.addEventListener('load', () => frameLoader.classList.remove('show'));

        // "Start Learning" opens the first chapter (and the sidebar on mobile)
        document.getElementById('startBtn')?.addEventListener('click', () => {
            const first = document.querySelector('.chapter-link');
            if (first && !first.classList.contains('open')) openChapter(first);
            if (window.innerWidth <= 992) {
                sidebar.classList.add('show');
                backdrop.classList.add('show');
            }
        });

        // Mobile sidebar toggle
        document.getElementById('hamburgerBtn').addEventListener('click', () => {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
        });
        backdrop.addEventListener('click', closeSidebar);
    </script>

</body>

</html>
