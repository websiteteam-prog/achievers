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
    <title><?= htmlspecialchars($subject_name) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #2f55a4;
            --primary-dark: #1e3c72;
            --accent: #e02121;
            --bg: #f5f7fb;
            --text: #1e293b;
            --muted: #64748b;
            --border: #e8edf5;
            --sidebar-w: 300px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
        }

        body {
            display: flex;
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        /* ================= SIDEBAR ================= */
        .sidebar {
            width: var(--sidebar-w);
            height: 100%;
            flex-shrink: 0;
            background: #fff;
            border-right: 1px solid var(--border);
            box-shadow: 4px 0 24px rgba(47, 85, 164, 0.06);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: transform 0.3s ease;
            z-index: 1050;
        }

        /* Coloured header */
        .sidebar-header {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 24px 20px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-bottom: 4px solid var(--accent);
            color: #fff;
            overflow: hidden;
            flex-shrink: 0;
        }

        .sidebar-header::before,
        .sidebar-header::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar-header::before {
            width: 120px;
            height: 120px;
            top: -50px;
            right: -30px;
        }

        .sidebar-header::after {
            width: 70px;
            height: 70px;
            bottom: -35px;
            left: 40%;
        }

        .sidebar-header h2 {
            position: relative;
            z-index: 1;
            min-width: 0;
            font-family: "Love Ya Like A Sister", cursive;
            font-size: 28px;
            font-weight: 400;
            margin: 0;
            overflow-wrap: break-word;
        }

        .header-art {
            position: relative;
            z-index: 1;
            width: 72px;
            flex-shrink: 0;
            animation: floaty 3s ease-in-out infinite;
        }

        /* close button – only on mobile */
        .close-btn {
            display: none;
            position: relative;
            z-index: 1;
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            font-size: 18px;
            align-items: center;
            justify-content: center;
        }

        .sidebar-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 16px 14px 20px;
        }

        .sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            color: #9ca3af;
            padding: 30px 10px;
        }

        .empty-state i {
            font-size: 38px;
            display: block;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        /* Chapter card */
        .chapter-item {
            margin-bottom: 10px;
        }

        .chapter-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            color: var(--text);
            font-size: 14.5px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
            transition: all 0.25s ease;
        }

        .chapter-link:hover {
            background: #f3f6fc;
            border-color: #d5def0;
            color: var(--primary);
            transform: translateX(4px);
            box-shadow: 0 6px 16px rgba(47, 85, 164, 0.1);
        }

        .chapter-link.open {
            background: #eef2fb;
            border-color: #c9d6ef;
            border-left: 4px solid var(--accent);
            color: var(--primary);
        }

        .chapter-title {
            flex: 1;
            min-width: 0;
            line-height: 1.35;
        }

        .chapter-arrow {
            font-size: 14px;
            color: var(--muted);
            transition: transform 0.3s ease;
        }

        .chapter-link.open .chapter-arrow {
            transform: rotate(90deg);
            color: var(--accent);
        }

        /* Coloured number badges */
        .num {
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            box-shadow: 0 0 0 5px var(--ring);
        }

        .c1 { background: #2f55a4; --ring: #e3eaf7; }
        .c2 { background: #ec4899; --ring: #fce7f3; }
        .c3 { background: #f97316; --ring: #ffedd5; }
        .c4 { background: #22c55e; --ring: #dcfce7; }
        .c5 { background: #a855f7; --ring: #f3e8ff; }
        .c6 { background: #06b6d4; --ring: #cffafe; }
        .c7 { background: #eab308; --ring: #fef9c3; }
        .c8 { background: #e02121; --ring: #fde3e3; }

        /* Topics */
        .topics-container {
            margin: 8px 0 4px 30px;
            padding-left: 12px;
            border-left: 2px dashed #c9d6ef;
            animation: slideDown 0.3s ease;
        }

        .topic-link {
            display: flex;
            gap: 8px;
            padding: 8px 12px;
            margin-bottom: 4px;
            border-radius: 10px;
            color: var(--muted);
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .topic-link:hover {
            background: #f3f6fc;
            color: var(--primary);
        }

        .topic-link.active {
            background: var(--accent);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 6px 14px rgba(224, 33, 33, 0.3);
        }

        /* ================= CONTENT ================= */
        .content {
            flex: 1;
            min-width: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        /* top bar – only on mobile / tablet */
        .mobile-bar {
            display: none;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-bottom: 3px solid var(--accent);
            color: #fff;
            flex-shrink: 0;
        }

        .hamburger {
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            border: none;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            font-size: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mobile-title {
            min-width: 0;
        }

        .mobile-title .subject {
            font-family: "Love Ya Like A Sister", cursive;
            font-size: 22px;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mobile-title .topic {
            font-size: 12.5px;
            opacity: 0.85;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .frame-area {
            position: relative;
            flex: 1;
            min-height: 0;
        }

        .frame-area iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }

        /* Welcome screen (until a topic is chosen) */
        .welcome {
            position: absolute;
            inset: 0;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow-y: auto;
            scrollbar-width: none;
            background:
                radial-gradient(circle at 15% 85%, #dfe7f7 0, transparent 35%),
                radial-gradient(circle at 90% 90%, #fbdcdc 0, transparent 30%),
                linear-gradient(180deg, #fafbfe, #f5f7fb);
            transition: opacity 0.4s ease;
        }

        .welcome::-webkit-scrollbar {
            display: none;
        }

        .welcome.hide {
            opacity: 0;
            pointer-events: none;
        }

        .welcome-inner {
            max-width: 640px;
            width: 100%;
            margin: auto;
            text-align: center;
            animation: fadeUp 0.6s ease;
        }

        .welcome-svg {
            width: 100%;
            max-width: 420px;
            max-height: 38vh;
            height: auto;
        }

        .welcome-svg * {
            transform-box: fill-box;
        }

        .welcome h1 {
            margin-top: 6px;
            font-family: "Love Ya Like A Sister", cursive;
            font-size: clamp(26px, 3.8vw, 44px);
            font-weight: 400;
            background: linear-gradient(to right, #e02121, #2f55a4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }

        .welcome p {
            margin: 10px auto 0;
            max-width: 480px;
            color: var(--muted);
            font-size: 16px;
        }

        .progress-pill {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-top: 18px;
        }

        .progress-pill span {
            height: 6px;
            width: 8px;
            border-radius: 6px;
            background: var(--primary);
            animation: pill 1.6s ease-in-out infinite;
        }

        .progress-pill span:nth-child(2) { animation-delay: .2s; }
        .progress-pill span:nth-child(3) { animation-delay: .4s; background: var(--accent); }

        .stats {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-top: 26px;
            flex-wrap: wrap;
        }

        .stat {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            padding: 12px 20px;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(47, 85, 164, 0.08);
            text-align: left;
        }

        .stat i {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .stat .blue { background: #e3eaf7; color: var(--primary); }
        .stat .red  { background: #fde3e3; color: var(--accent); }

        .stat small {
            display: block;
            color: var(--muted);
            font-size: 12px;
        }

        .stat b {
            font-size: 20px;
            color: #0f172a;
        }

        /* backdrop behind sidebar on mobile */
        .backdrop {
            display: none;
        }

        /* SVG animations */
        .books  { animation: floaty 3s ease-in-out infinite; transform-origin: center; }
        .plane  { animation: fly 3.5s ease-in-out infinite; }
        .trail  { stroke-dasharray: 6 6; animation: dash 1.2s linear infinite; }
        .plant  { animation: sway 3s ease-in-out infinite; transform-origin: bottom center; }
        .twinkle { animation: twinkle 2s ease-in-out infinite; transform-origin: center; }
        .t2 { animation-delay: .5s; }
        .t3 { animation-delay: 1s; }
        .t4 { animation-delay: 1.5s; }

        @keyframes floaty  { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        @keyframes fly     { 0%,100% { transform: translate(0,0) rotate(0); } 50% { transform: translate(10px,-10px) rotate(-4deg); } }
        @keyframes dash    { to { stroke-dashoffset: -24; } }
        @keyframes sway    { 0%,100% { transform: rotate(-3deg); } 50% { transform: rotate(3deg); } }
        @keyframes twinkle { 0%,100% { transform: scale(.6); opacity: .4; } 50% { transform: scale(1.1); opacity: 1; } }
        @keyframes pill    { 0%,100% { width: 8px; } 50% { width: 34px; } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeUp  { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

        /* ================= RESPONSIVE ================= */

        /* small laptops */
        @media (max-width: 1200px) {
            :root {
                --sidebar-w: 270px;
            }

            .sidebar-header h2 {
                font-size: 24px;
            }

            .header-art {
                width: 60px;
            }
        }

        /* tablets & phones – sidebar becomes a slide-in drawer */
        @media (max-width: 992px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: min(300px, 85vw);
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
                box-shadow: 10px 0 40px rgba(0, 0, 0, 0.2);
            }

            .backdrop.show {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.45);
                z-index: 1040;
            }

            .close-btn {
                display: flex;
            }

            .header-art {
                display: none;
            }

            .mobile-bar {
                display: flex;
            }
        }

        /* phones */
        @media (max-width: 576px) {
            .welcome {
                padding: 18px 14px;
            }

            .welcome-svg {
                max-height: 28vh;
            }

            .welcome p {
                font-size: 14px;
            }

            .stats {
                gap: 10px;
                margin-top: 20px;
            }

            .stat {
                padding: 10px 14px;
                flex: 1 1 130px;
            }

            .stat i {
                width: 36px;
                height: 36px;
                font-size: 16px;
            }

            .stat b {
                font-size: 18px;
            }
        }
    </style>
</head>

<body>

    <div class="backdrop" id="backdrop"></div>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2><?= htmlspecialchars($subject_name) ?></h2>

            <!-- small book art -->
            <svg class="header-art" viewBox="0 0 80 64" xmlns="http://www.w3.org/2000/svg">
                <path d="M40 58 L6 52 L6 20 L40 26 Z" fill="#fff"/>
                <path d="M40 58 L74 52 L74 20 L40 26 Z" fill="#e3eaf7"/>
                <path d="M40 26 L40 58" stroke="#c9d6ef" stroke-width="2"/>
                <line x1="12" y1="30" x2="34" y2="34" stroke="#c9d6ef" stroke-width="2"/>
                <line x1="12" y1="37" x2="34" y2="41" stroke="#c9d6ef" stroke-width="2"/>
                <line x1="46" y1="34" x2="68" y2="30" stroke="#9fb3dc" stroke-width="2"/>
                <line x1="46" y1="41" x2="68" y2="37" stroke="#9fb3dc" stroke-width="2"/>
                <text x="10" y="16" fill="#4ade80" font-size="14" font-weight="700">+</text>
                <text x="30" y="12" fill="#facc15" font-size="14" font-weight="700">+</text>
                <polygon points="62,2 74,22 50,22" fill="#e02121"/>
            </svg>

            <button class="close-btn" id="closeBtn" aria-label="Close menu"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="sidebar-scroll">
            <nav class="nav flex-column">
                <?php if (empty($chapters)): ?>
                    <div class="empty-state">
                        <i class="bi bi-journal-x"></i>
                        No chapter assigned yet.
                    </div>
                <?php endif; ?>

                <?php $n = 0; ?>
                <?php foreach ($chapters as $chapter_id => $chapter) { $n++; ?>
                    <div class="chapter-item">
                        <a href="#"
                            class="chapter-link"
                            data-chapter-id="<?= $chapter_id ?>">
                            <span class="num c<?= (($n - 1) % 8) + 1 ?>"><?= $n ?></span>
                            <span class="chapter-title"><?= htmlspecialchars($chapter['chapter_name']) ?></span>
                            <i class="bi bi-chevron-right chapter-arrow"></i>
                        </a>
                        <div class="topics-container"
                            id="topics-<?= $chapter_id ?>"
                            style="display:none;">
                            <?php foreach ($chapter['topics'] as $topic) { ?>
                                <a class="topic-link"
                                    href="quiz.php?topic_id=<?= $topic['id'] ?>&id=<?= $subject_id ?>"
                                    target="contentFrame">
                                    <?= htmlspecialchars($topic['position']) ?>. <?= htmlspecialchars($topic['title']) ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </nav>
        </div>
    </div>

    <div class="content">

        <!-- Mobile / tablet top bar -->
        <div class="mobile-bar">
            <button class="hamburger" id="hamburgerBtn" aria-label="Open chapters"><i class="bi bi-list"></i></button>
            <div class="mobile-title">
                <div class="subject"><?= htmlspecialchars($subject_name) ?></div>
                <div class="topic" id="mobileTopic">Choose a chapter to begin</div>
            </div>
        </div>

        <div class="frame-area">

            <!-- Welcome animation – shown until a topic is opened -->
            <div class="welcome" id="welcome">
                <div class="welcome-inner">
                    <svg class="welcome-svg" viewBox="0 0 420 260" xmlns="http://www.w3.org/2000/svg">
                        <!-- cloud blob -->
                        <path d="M70 220 Q30 220 40 180 Q45 140 95 145 Q100 70 180 75 Q230 30 290 80 Q360 80 365 140 Q400 150 390 190 Q385 222 345 222 Z" fill="#e9eef8"/>

                        <!-- paper plane + dashed trail -->
                        <path class="trail" d="M175 120 Q200 60 260 80 T340 50" fill="none" stroke="#e02121" stroke-width="2"/>
                        <g class="plane">
                            <polygon points="330,40 385,25 355,70" fill="#2f55a4"/>
                            <polygon points="330,40 355,70 350,50" fill="#1e3c72"/>
                        </g>

                        <!-- stars / sparkles -->
                        <polygon class="twinkle"    points="140,70 144,80 155,80 146,86 149,96 140,90 131,96 134,86 125,80 136,80" fill="#facc15"/>
                        <polygon class="twinkle t2" points="350,100 353,108 361,108 355,113 357,121 350,116 343,121 345,113 339,108 347,108" fill="#2f55a4"/>
                        <circle  class="twinkle t3" cx="95"  cy="120" r="4" fill="#e02121"/>
                        <circle  class="twinkle t4" cx="330" cy="170" r="4" fill="#e02121"/>
                        <circle  class="twinkle t2" cx="200" cy="50"  r="3" fill="#2f55a4"/>
                        <circle  class="twinkle"    cx="365" cy="140" r="3" fill="#9fb3dc"/>

                        <!-- plant -->
                        <g class="plant">
                            <path d="M125 215 Q120 165 100 150 Q130 160 128 200" fill="#22c55e"/>
                            <path d="M128 215 Q135 160 160 145 Q140 175 132 210" fill="#16a34a"/>
                            <path d="M126 215 Q110 185 88 182 Q112 178 128 205" fill="#4ade80"/>
                        </g>
                        <path d="M108 210 L148 210 L142 240 L114 240 Z" fill="#f1f5f9" stroke="#cbd5e1"/>

                        <!-- stack of books -->
                        <g class="books">
                            <rect x="170" y="200" width="160" height="34" rx="8" fill="#2f55a4"/>
                            <rect x="178" y="206" width="148" height="22" rx="5" fill="#fff"/>
                            <rect x="178" y="210" width="148" height="2" fill="#e2e8f0"/>
                            <rect x="178" y="218" width="148" height="2" fill="#e2e8f0"/>

                            <rect x="182" y="168" width="150" height="32" rx="8" fill="#e02121"/>
                            <rect x="182" y="174" width="140" height="20" rx="5" fill="#fff"/>
                            <rect x="182" y="178" width="140" height="2" fill="#e2e8f0"/>
                            <rect x="182" y="186" width="140" height="2" fill="#e2e8f0"/>

                            <rect x="176" y="138" width="146" height="30" rx="8" fill="#1e3c72"/>
                            <rect x="186" y="144" width="136" height="18" rx="5" fill="#fff"/>
                            <rect x="186" y="148" width="136" height="2" fill="#e2e8f0"/>
                            <rect x="186" y="155" width="136" height="2" fill="#e2e8f0"/>
                            <rect x="200" y="138" width="8" height="30" fill="#e02121"/>
                        </g>
                    </svg>

                    <?php if (empty($chapters)): ?>
                        <h1>No chapters assigned yet</h1>
                        <p>Your teacher will assign chapters soon. Please check back later!</p>
                    <?php else: ?>
                        <h1>Ready to Start Learning?</h1>
                        <p>Select a chapter from the sidebar to view lessons, practice questions and track your progress.</p>
                    <?php endif; ?>

                    <div class="progress-pill"><span></span><span></span><span></span></div>

                    <div class="stats">
                        <div class="stat">
                            <i class="bi bi-journal-bookmark-fill blue"></i>
                            <div><small>Chapters</small><b><?= count($chapters) ?></b></div>
                        </div>
                        <div class="stat">
                            <i class="bi bi-list-check red"></i>
                            <div><small>Topics</small><b><?= $total_topics ?></b></div>
                        </div>
                    </div>
                </div>
            </div>

            <iframe name="contentFrame" src=""></iframe>
        </div>
    </div>

    <script>
        const sidebar  = document.getElementById('sidebar');
        const backdrop = document.getElementById('backdrop');
        const welcome  = document.getElementById('welcome');

        function openSidebar() {
            sidebar.classList.add('show');
            backdrop.classList.add('show');
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        }

        // Chapter dropdown toggle
        document.querySelectorAll('.chapter-link').forEach(chapter => {
            chapter.addEventListener('click', function(e) {
                e.preventDefault();
                const topicsDiv = document.getElementById(`topics-${this.dataset.chapterId}`);

                if (topicsDiv.style.display === 'block') {
                    topicsDiv.style.display = 'none';
                    this.classList.remove('open');
                } else {
                    // Hide others
                    document.querySelectorAll('.topics-container').forEach(div => div.style.display = 'none');
                    document.querySelectorAll('.chapter-link').forEach(link => link.classList.remove('open'));

                    topicsDiv.style.display = 'block';
                    this.classList.add('open');
                }
            });
        });

        // Topic click: highlight, hide welcome screen, close drawer on mobile
        const topicLinks = document.querySelectorAll('.topic-link');
        topicLinks.forEach(link => {
            link.addEventListener('click', () => {
                topicLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
                welcome.classList.add('hide');
                document.getElementById('mobileTopic').textContent = link.textContent.trim();
                closeSidebar();
            });
        });

        // Mobile drawer
        document.getElementById('hamburgerBtn').addEventListener('click', openSidebar);
        document.getElementById('closeBtn').addEventListener('click', closeSidebar);
        backdrop.addEventListener('click', closeSidebar);
    </script>

</body>

</html>