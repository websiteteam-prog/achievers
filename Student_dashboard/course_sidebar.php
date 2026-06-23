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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($subject_name) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            height: 100vh;
            background: #fff;
            padding: 25px 20px;
            border-right: 1px solid #ddd;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .sidebar h2 {
            font-size: 22px;
            font-weight: 600;
            color: #1F669C;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Nav Links */
        .nav-link {
            display: block;
            padding: 10px 15px;
            margin-bottom: 10px;
            font-size: 16px;
            background: #f5f7fa;
            border-radius: 12px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            background: #1F669C;
            color: #fff;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: #1F669C;
            color: #fff;
            font-weight: 600;
        }

        /* Dropdown arrow for chapters */
        .nav-link.chapter-link::after {
            content: '▸';
            position: absolute;
            right: 15px;
            transition: transform 0.3s;
        }

        .nav-link.chapter-link.open::after {
            content: '▾';
        }

        /* Content */
        .content {
            flex-grow: 1;
            height: 100vh;
            background: #fff;
            padding: 0;
            margin: 0;
        }

        .content iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }

        /* Hamburger Button */
        .hamburger {
            display: none;
            position: absolute;
            top: 15px;
            left: 15px;
            font-size: 26px;
            color: #1F669C;
            background: #fff;
            border: none;
            cursor: pointer;
            z-index: 1100;
            border-radius: 8px;
            padding: 5px 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                overflow: auto;
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .hamburger {
                display: block;
            }

            .content {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <!-- Hamburger Button -->
    <button class="hamburger" id="hamburgerBtn">☰</button>

    <div class="sidebar" id="sidebar">
        <h2><?= htmlspecialchars($subject_name) ?></h2>
        <nav class="nav flex-column">
            <?php if (empty($chapters)): ?>
                <p class="text-muted text-center mt-3">No chapter assigned yet.</p>
            <?php endif; ?>
            <?php foreach ($chapters as $chapter_id => $chapter) { ?>
                <div class="chapter-item mb-2">
                    <a href="#"
                        class="nav-link chapter-link"
                        data-chapter-id="<?= $chapter_id ?>">
                        <?= htmlspecialchars($chapter['chapter_name']) ?>
                    </a>
                    <div class="topics-container"
                        id="topics-<?= $chapter_id ?>"
                        style="display:none; padding-left:20px;">
                        <?php foreach ($chapter['topics'] as $topic) { ?>
                            <a class="nav-link"
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

    <div class="content">
        <iframe name="contentFrame" src=""></iframe>
    </div>

    <script>
        // Chapter dropdown toggle
        document.querySelectorAll('.chapter-link').forEach(chapter => {
            chapter.addEventListener('click', function(e) {
                e.preventDefault();
                const chapterId = this.dataset.chapterId;
                const topicsDiv = document.getElementById(`topics-${chapterId}`);

                // Toggle display and arrow
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

        // Sidebar active link highlight
        const links = document.querySelectorAll('.nav-link');
        links.forEach(link => {
            link.addEventListener('click', () => {
                links.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            });
        });

        // Mobile Sidebar Toggle
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidebar = document.getElementById('sidebar');

        hamburgerBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    </script>

</body>

</html>