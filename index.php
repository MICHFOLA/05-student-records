<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

$pageTitle = 'Students';
$databaseUnavailable = false;
$students = [];

try {
    $statement = db()->query(
        'SELECT id, first_name, last_name, email, course, enrolled_on
         FROM students
         ORDER BY last_name ASC, first_name ASC'
    );
    $students = $statement->fetchAll();
} catch (PDOException) {
    http_response_code(503);
    $databaseUnavailable = true;
}

require __DIR__ . '/partials/header.php';
?>
<section class="page-heading">
    <div>
        <h1>Students</h1>
        <p class="page-description">Manage student records and enrollment details.</p>
    </div>
    <a class="button" href="create.php">Add student</a>
</section>

<?php if ($databaseUnavailable): ?>
    <section class="card error-state" role="alert">
        <h2>Student records are temporarily unavailable</h2>
        <p>
            We couldn't reach the student database. Please make sure MySQL is running
            in XAMPP, then try again.
        </p>
        <a class="button button-secondary" href="index.php">Try again</a>
    </section>
<?php elseif ($students === []): ?>
    <section class="card empty-state">
        <h2>No students yet</h2>
        <p>Add a student to start building your records.</p>
        <a class="button" href="create.php">Add student</a>
    </section>
<?php else: ?>
    <section class="card">
        <div class="table-wrap">
            <table class="student-table">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Course</th>
                        <th scope="col">Enrolled</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td class="student-name">
                                <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
                            </td>
                            <td><a href="mailto:<?= e($student['email']) ?>"><?= e($student['email']) ?></a></td>
                            <td><?= e($student['course']) ?></td>
                            <td>
                                <?= e((new DateTime($student['enrolled_on']))->format('d M Y')) ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="edit.php?id=<?= e($student['id']) ?>">Edit</a>
                                    <form method="post" action="delete.php" onsubmit="return confirm('Delete this student record?');">
                                        <input type="hidden" name="id" value="<?= e($student['id']) ?>">
                                        <button class="action-delete" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
