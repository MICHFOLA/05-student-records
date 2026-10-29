<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    $pageTitle = 'Method not allowed';
    require __DIR__ . '/partials/header.php';
    ?>
    <section class="card error-state">
        <h2>Method not allowed</h2>
        <p>Student records can only be deleted by submitting the delete form.</p>
        <a class="button" href="index.php">Back to students</a>
    </section>
    <?php
    require __DIR__ . '/partials/footer.php';
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    $pageTitle = 'Request could not be verified';
    require __DIR__ . '/partials/header.php';
    ?>
    <section class="card error-state" role="alert">
        <h2>Request could not be verified</h2>
        <p>Your form session may have expired. Return to the student list and try again.</p>
        <a class="button" href="index.php">Back to students</a>
    </section>
    <?php
    require __DIR__ . '/partials/footer.php';
    exit;
}

$studentId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($studentId === false || $studentId === null || $studentId < 1) {
    http_response_code(400);
    $pageTitle = 'Invalid student record';
    require __DIR__ . '/partials/header.php';
    ?>
    <section class="card error-state">
        <h2>Invalid student record</h2>
        <p>Select a student from the list before deleting a record.</p>
        <a class="button" href="index.php">Back to students</a>
    </section>
    <?php
    require __DIR__ . '/partials/footer.php';
    exit;
}

try {
    $statement = db()->prepare('DELETE FROM students WHERE id = :id');
    $statement->execute(['id' => $studentId]);

    if ($statement->rowCount() > 0) {
        flash('success', 'Student deleted');
    } else {
        flash('error', 'That student record was not found or has already been deleted.');
    }

    redirect('index.php');
} catch (PDOException) {
    http_response_code(503);
    $pageTitle = 'Student records unavailable';
    require __DIR__ . '/partials/header.php';
    ?>
    <section class="card error-state" role="alert">
        <h2>Student records are temporarily unavailable</h2>
        <p>
            We couldn't update the student database. Please make sure MySQL is running
            in XAMPP, then try again.
        </p>
        <a class="button button-secondary" href="index.php">Back to students</a>
    </section>
    <?php
    require __DIR__ . '/partials/footer.php';
}
