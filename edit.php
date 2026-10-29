<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/StudentValidation.php';

$pageTitle = 'Edit student';
$errors = [];
$courseOptions = [];
$databaseUnavailable = false;
$studentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($studentId === false || $studentId === null || $studentId < 1) {
    http_response_code(400);
    $studentId = null;
}

$student = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'course' => '',
    'enrolled_on' => '',
];
$recordNotFound = false;

if ($studentId !== null) {
    try {
        $courseOptions = studentCourseOptions();
        $statement = db()->prepare(
            'SELECT id, first_name, last_name, email, phone, course, enrolled_on
             FROM students WHERE id = :id'
        );
        $statement->execute(['id' => $studentId]);
        $record = $statement->fetch();

        if ($record === false) {
            http_response_code(404);
            $recordNotFound = true;
        } else {
            $student = $record;

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                    $errors['csrf'] = 'Your form session expired. Please try again.';
                    foreach ($student as $field => $_) {
                        if (isset($_POST[$field]) && is_string($_POST[$field])) {
                            $student[$field] = trim($_POST[$field]);
                        }
                    }
                } else {
                    [$student, $errors] = validateStudent($_POST, $courseOptions, $studentId);

                    if ($errors === []) {
                        $update = db()->prepare(
                            'UPDATE students
                             SET first_name = :first_name,
                                 last_name = :last_name,
                                 email = :email,
                                 phone = :phone,
                                 course = :course,
                                 enrolled_on = :enrolled_on
                             WHERE id = :id'
                        );
                        $update->execute([
                            'first_name' => $student['first_name'],
                            'last_name' => $student['last_name'],
                            'email' => $student['email'],
                            'phone' => $student['phone'] === '' ? null : $student['phone'],
                            'course' => $student['course'],
                            'enrolled_on' => $student['enrolled_on'],
                            'id' => $studentId,
                        ]);
                        flash('success', 'Student updated');
                        redirect('index.php');
                    }
                }
            }
        }
    } catch (PDOException | RuntimeException $exception) {
        $databaseUnavailable = true;
        http_response_code(503);
    }
}

require __DIR__ . '/partials/header.php';
?>
<section class="page-heading">
    <div>
        <h1>Edit student</h1>
        <p class="page-description">Update the student's details below.</p>
    </div>
    <a class="button button-secondary" href="index.php">Back to students</a>
</section>

<?php if ($databaseUnavailable): ?>
    <section class="card error-state" role="alert">
        <h2>Student records are temporarily unavailable</h2>
        <p>
            We couldn't reach the student database. Please make sure MySQL is running
            in XAMPP, then try again.
        </p>
        <a class="button button-secondary" href="edit.php?id=<?= e($studentId ?? '') ?>">Try again</a>
    </section>
<?php elseif ($studentId === null): ?>
    <section class="card error-state">
        <h2>Invalid student record</h2>
        <p>Choose a student from the student list to edit their record.</p>
        <a class="button" href="index.php">Back to students</a>
    </section>
<?php elseif ($recordNotFound): ?>
    <section class="card error-state">
        <h2>Student not found</h2>
        <p>This student record may have been removed.</p>
        <a class="button" href="index.php">Back to students</a>
    </section>
<?php else: ?>
    <?php
    $submitLabel = 'Save changes';
    require __DIR__ . '/partials/student-form.php';
    ?>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
