<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

$pageTitle = 'Student details';
$studentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$student = false;
$databaseUnavailable = false;

if ($studentId === false || $studentId === null || $studentId < 1) {
    http_response_code(400);
    $studentId = null;
} else {
    try {
        $statement = db()->prepare(
            'SELECT id, first_name, last_name, email, phone, course, enrolled_on, created_at, updated_at
             FROM students
             WHERE id = :id'
        );
        $statement->execute(['id' => $studentId]);
        $student = $statement->fetch();

        if ($student === false) {
            http_response_code(404);
        }
    } catch (PDOException) {
        http_response_code(503);
        $databaseUnavailable = true;
    }
}

require __DIR__ . '/partials/header.php';
?>
<section class="page-heading">
    <div>
        <h1>Student details</h1>
        <p class="page-description">View this student's record.</p>
    </div>
    <div class="page-actions">
        <?php if ($student !== false && $student !== null): ?>
            <a class="button" href="edit.php?id=<?= e($student['id']) ?>">Edit student</a>
        <?php endif; ?>
        <a class="button button-secondary" href="index.php">Back to students</a>
    </div>
</section>

<?php if ($databaseUnavailable): ?>
    <section class="card error-state" role="alert">
        <h2>Student records are temporarily unavailable</h2>
        <p>
            We couldn't reach the student database. Please make sure MySQL is running
            in XAMPP, then try again.
        </p>
        <a class="button button-secondary" href="show.php?id=<?= e($studentId ?? '') ?>">Try again</a>
    </section>
<?php elseif ($studentId === null): ?>
    <section class="card error-state">
        <h2>Invalid student record</h2>
        <p>Choose a student from the student list to view their details.</p>
        <a class="button" href="index.php">Back to students</a>
    </section>
<?php elseif ($student === false): ?>
    <section class="card error-state">
        <h2>Student not found</h2>
        <p>This student record may have been removed.</p>
        <a class="button" href="index.php">Back to students</a>
    </section>
<?php else: ?>
    <section class="card detail-card">
        <dl class="student-details">
            <div>
                <dt>Student ID</dt>
                <dd><?= e($student['id']) ?></dd>
            </div>
            <div>
                <dt>Name</dt>
                <dd><?= e($student['first_name'] . ' ' . $student['last_name']) ?></dd>
            </div>
            <div>
                <dt>Email</dt>
                <dd><a href="mailto:<?= e($student['email']) ?>"><?= e($student['email']) ?></a></dd>
            </div>
            <div>
                <dt>Phone</dt>
                <dd><?= $student['phone'] !== null && $student['phone'] !== '' ? e($student['phone']) : 'Not provided' ?></dd>
            </div>
            <div>
                <dt>Course</dt>
                <dd><?= e($student['course']) ?></dd>
            </div>
            <div>
                <dt>Enrolled on</dt>
                <dd><?= e((new DateTime($student['enrolled_on']))->format('d M Y')) ?></dd>
            </div>
            <div>
                <dt>Record created</dt>
                <dd><?= e((new DateTime($student['created_at']))->format('d M Y, H:i')) ?></dd>
            </div>
            <div>
                <dt>Last updated</dt>
                <dd><?= e((new DateTime($student['updated_at']))->format('d M Y, H:i')) ?></dd>
            </div>
        </dl>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
