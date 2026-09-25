<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['teacher_id'])) {
    http_response_code(403);
    echo "<p class='text-danger'>Not authorized.</p>";
    exit();
}

$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

/*
|--------------------------------------------------------------------------
| Filter: pending (default) vs reviewed
|--------------------------------------------------------------------------
*/
$filter = $_GET['filter'] ?? 'pending';

$where = ($filter === 'reviewed')
    ? "sd.is_drawing_correct IS NOT NULL"
    : "sd.is_drawing_correct IS NULL";

/*
|--------------------------------------------------------------------------
| Fetch drawings + question context + student name
|--------------------------------------------------------------------------
| NOTE: adjust `s.full_name` below if your students table uses a
| different column name for the student's display name.
*/
$sql = "SELECT
            sd.id,
            sd.student_id,
            sd.quiz_id,
            sd.question_id,
            sd.drawing_path,
            sd.is_drawing_correct,
            sd.teacher_note,
            sd.created_at,
            qq.question_text,
            qq.question_payload,
            qq.correct_answer,
            qq.unit,
            sa.student_answer,
            CONCAT(ei.first_name, ' ', ei.last_name) AS student_name
        FROM student_drawings sd
        JOIN quiz_questions qq ON qq.id = sd.question_id
        LEFT JOIN enrollment_inquiries ei ON ei.id = sd.student_id
        LEFT JOIN student_answers sa
         ON sa.student_id = sd.student_id
          AND sa.question_id = sd.question_id
          AND sa.quiz_id = sd.quiz_id
          AND sa.created_at = sd.created_at
        WHERE $where
        ORDER BY sd.created_at DESC
        LIMIT 100";

$result = $conn->query($sql);
$rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<style>
.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 22px;
}

.review-header h4 {
    margin: 0;
    font-weight: 700;
    color: #1e3c72;
}

.review-filter-pills a {
    display: inline-block;
    padding: 7px 18px;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    margin-left: 8px;
    border: 1px solid #d7deec;
    color: #2a5298;
    background: #fff;
    transition: 0.2s;
}

.review-filter-pills a.active,
.review-filter-pills a:hover {
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    color: #fff;
    border-color: transparent;
}

.review-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    padding: 22px;
    margin-bottom: 20px;
}

.review-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}

.review-student {
    font-weight: 700;
    color: #1e3c72;
    font-size: 15px;
}

.review-meta {
    font-size: 13px;
    color: #888;
}

.review-status-badge {
    padding: 5px 14px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.badge-pending { background: #fff3cd; color: #8a6400; }
.badge-correct { background: #d4edda; color: #157347; }
.badge-incorrect { background: #f8d7da; color: #b02a37; }

.review-body {
    display: flex;
    gap: 22px;
    flex-wrap: wrap;
}

.review-question-text {
    flex: 1;
    min-width: 220px;
    font-size: 15px;
    color: #333;
    line-height: 1.55;
}

.review-expected {
    margin-top: 10px;
    font-size: 14px;
    color: #2a5298;
    font-weight: 600;
}

.review-student-answer {
    margin-top: 6px;
    font-size: 14px;
    color: #444;
}

.review-drawing-box {
    width: 260px;
    height: 140px;
    flex-shrink: 0;
    border: 1px solid #e2e2e2;
    border-radius: 10px;
    overflow: hidden;
    background: #fafafa;
}

.review-drawing-box img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.review-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    justify-content: center;
    min-width: 140px;
}

.review-actions button {
    border: none;
    border-radius: 30px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
}

.btn-mark-correct {
    background: #198754;
    color: #fff;
}
.btn-mark-correct:hover { background: #146c43; }

.btn-mark-incorrect {
    background: #dc3545;
    color: #fff;
}
.btn-mark-incorrect:hover { background: #b02a37; }

.btn-mark-undo {
    background: #e9ecef;
    color: #444;
}
.btn-mark-undo:hover { background: #dde1e5; }

.review-empty {
    text-align: center;
    color: #9ca3af;
    padding: 50px 10px;
}

.review-empty i {
    font-size: 40px;
    display: block;
    margin-bottom: 10px;
    opacity: 0.5;
}
</style>

<div class="review-header">
    <h4><i class="bi bi-easel2"></i> Diagram Review</h4>

    <div class="review-filter-pills">
        <a href="#"
           class="drawing-filter-link <?= $filter === 'pending' ? 'active' : '' ?>"
           data-filter="pending">Pending</a>
        <a href="#"
           class="drawing-filter-link <?= $filter === 'reviewed' ? 'active' : '' ?>"
           data-filter="reviewed">Reviewed</a>
    </div>
</div>

<div id="review-list">

<?php if (empty($rows)): ?>

    <div class="review-empty">
        <i class="bi bi-check2-circle"></i>
        <?= $filter === 'pending'
            ? 'No drawings waiting for review.'
            : 'No reviewed drawings yet.' ?>
    </div>

<?php else: foreach ($rows as $row):

    $payload = json_decode((string)($row['question_payload'] ?? '{}'), true);
    $payload = is_array($payload) ? $payload : [];

    $expected_bits = [];
    if (!empty($payload['shape']))  $expected_bits[] = 'Shape: ' . $payload['shape'];
    if (!empty($payload['length'])) $expected_bits[] = 'Length: ' . $payload['length'];
    if (!empty($payload['width']))  $expected_bits[] = 'Width: '  . $payload['width'];
    if (!empty($payload['side']))   $expected_bits[] = 'Side: '   . $payload['side'];

    $status = $row['is_drawing_correct'];
    $status_class = $status === null ? 'badge-pending' : ($status ? 'badge-correct' : 'badge-incorrect');
    $status_label = $status === null ? 'Pending' : ($status ? 'Correct' : 'Incorrect');
?>

    <div class="review-card" data-row-id="<?= (int)$row['id'] ?>">

        <div class="review-card-top">
            <div>
                <div class="review-student">
                    <?= $h($row['student_name'] ?? ('Student #' . $row['student_id'])) ?>
                </div>
                <div class="review-meta">
                    Submitted <?= $h($row['created_at']) ?>
                </div>
            </div>

            <span class="review-status-badge <?= $status_class ?>" data-status-label>
                <?= $status_label ?>
            </span>
        </div>

        <div class="review-body">

            <div class="review-drawing-box">
                <img src="<?= $h('/Student_dashboard/' . ltrim($row['drawing_path'], '/')) ?>" alt="Student drawing">
            </div>

            <div class="review-question-text">
                <?= nl2br($h($row['question_text'])) ?>

                <?php if (!empty($expected_bits)): ?>
                    <div class="review-expected">
                        Expected: <?= $h(implode(' · ', $expected_bits)) ?>
                        <?= !empty($row['correct_answer']) ? ' → ' . $h($row['correct_answer']) . ' ' . $h($row['unit'] ?? '') : '' ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($row['student_answer'])): ?>
                    <div class="review-student-answer">
                        Student's numeric answer: <strong><?= $h($row['student_answer']) ?></strong>
                    </div>
                <?php endif; ?>
            </div>

            <div class="review-actions">
                <?php if ($status === null): ?>
                    <button class="btn-mark-correct" data-action="1" data-id="<?= (int)$row['id'] ?>">
                        <i class="bi bi-check-lg"></i> Mark Correct
                    </button>
                    <button class="btn-mark-incorrect" data-action="0" data-id="<?= (int)$row['id'] ?>">
                        <i class="bi bi-x-lg"></i> Mark Incorrect
                    </button>
                <?php else: ?>
                    <button class="btn-mark-undo" data-action="reset" data-id="<?= (int)$row['id'] ?>">
                        <i class="bi bi-arrow-counterclockwise"></i> Undo
                    </button>
                <?php endif; ?>
            </div>

        </div>

    </div>

<?php endforeach; endif; ?>

</div>

<script>
(function () {

    // Filter pill switching (re-fetches this same fragment with ?filter=)
    document.querySelectorAll('.drawing-filter-link').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            var f = this.dataset.filter;
            $("#content-area").html("<p>Loading...</p>");
            $.get('review_drawings.php?filter=' + f, function (data) {
                $("#content-area").html(data);
            });
        });
    });

    // Mark correct / incorrect / undo
    document.getElementById('review-list').addEventListener('click', function (e) {

        var btn = e.target.closest('button[data-action]');
        if (!btn) return;

        var id = btn.dataset.id;
        var action = btn.dataset.action;
        var card = btn.closest('.review-card');

        btn.disabled = true;

        fetch('api/update_drawing_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(id) + '&action=' + encodeURIComponent(action)
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                // Simplest reliable UX: just remove the card from view,
                // since it no longer belongs in the current filter list.
                card.style.transition = 'opacity 0.25s ease';
                card.style.opacity = '0';
                setTimeout(function () { card.remove(); }, 250);
            } else {
                alert(data.message || 'Something went wrong.');
                btn.disabled = false;
            }
        })
        .catch(function () {
            alert('Network error. Please try again.');
            btn.disabled = false;
        });
    });

})();
</script>
