<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/StudentValidation.php';

$pageTitle = 'Add student';
$student = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'course' => '',
    'enrolled_on' => '',
];
$errors = [];
$courseOptions = [];
$databaseUnavailable = false;

try {
    $courseOptions = studentCourseOptions();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $errors['csrf'] = 'Your form session expired. Please try again.';
            foreach ($student as $field => $_) {
                if (isset($_POST[$field]) && is_string($_POST[$field])) {
                    $student[$field] = trim($_POST[$field]);
                }
            }
        } else {
            [$student, $errors] = validateStudent($_POST, $courseOptions);

            if ($errors === []) {
                $statement = db()->prepare(
                    'INSERT INTO students (first_name, last_name, email, phone, course, enrolled_on)
                     VALUES (:first_name, :last_name, :email, :phone, :course, :enrolled_on)'
                );
                $statement->execute([
                    'first_name' => $student['first_name'],
                    'last_name' => $student['last_name'],
                    'email' => $student['email'],
                    'phone' => $student['phone'] === '' ? null : $student['phone'],
                    'course' => $student['course'],
                    'enrolled_on' => $student['enrolled_on'],
                ]);

                flash('success', 'Student added');
                redirect('index.php');
            }
        }
    }
} catch (PDOException | RuntimeException $exception) {
    $databaseUnavailable = true;
    http_response_code(503);
}

require __DIR__ . '/partials/header.php';
?>
<section class="page-heading">
    <div>
        <h1>Add student</h1>
        <p class="page-description">Enter the student's details below.</p>
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
        <a class="button button-secondary" href="create.php">Try again</a>
    </section>
<?php else: ?>
    <?php
    $submitLabel = 'Add student';
    require __DIR__ . '/partials/student-form.php';
    ?>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
