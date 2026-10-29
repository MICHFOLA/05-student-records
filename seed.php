<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

$pageTitle = 'Seed test data';
$databaseUnavailable = false;
$seeded = false;
$alreadySeeded = false;

if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    try {
        $connection = db();
        $existing = $connection->prepare(
            "SELECT COUNT(*) FROM students WHERE email LIKE :email_pattern"
        );
        $existing->execute(['email_pattern' => 'student.seed.%@example.test']);

        if ((int) $existing->fetchColumn() > 0) {
            $alreadySeeded = true;
        } else {
            $firstNames = [
                'Ada', 'Aisha', 'Amaka', 'Babatunde', 'Chiamaka',
                'Chinedu', 'Damilola', 'Emeka', 'Fatima', 'Ifeoma',
                'Ibrahim', 'Kelechi', 'Ngozi', 'Oluwaseun', 'Tari',
            ];
            $lastNames = [
                'Adebayo', 'Adeyemi', 'Bello', 'Chukwu', 'Eze',
                'Ibrahim', 'Okafor', 'Okonkwo', 'Olawale', 'Yusuf',
            ];
            $courses = studentCourseOptions();
            $insert = $connection->prepare(
                'INSERT INTO students (first_name, last_name, email, phone, course, enrolled_on)
                 VALUES (:first_name, :last_name, :email, :phone, :course, :enrolled_on)'
            );
            $today = new DateTimeImmutable('today');

            $connection->beginTransaction();
            for ($index = 0; $index < 50; $index++) {
                $firstName = $firstNames[$index % count($firstNames)];
                $lastName = $lastNames[($index * 3 + intdiv($index, count($firstNames))) % count($lastNames)];
                $insert->execute([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => sprintf('student.seed.%02d@example.test', $index + 1),
                    'phone' => sprintf('070%08d', $index + 10000000),
                    'course' => $courses[$index % count($courses)],
                    'enrolled_on' => $today->modify('-' . (30 + (($index * 37) % 1700)) . ' days')->format('Y-m-d'),
                ]);
            }
            $connection->commit();
            $seeded = true;
        }
    } catch (PDOException | RuntimeException $exception) {
        if (isset($connection) && $connection->inTransaction()) {
            $connection->rollBack();
        }
        http_response_code(503);
        $databaseUnavailable = true;
    }
}

require __DIR__ . '/partials/header.php';
?>
<section class="page-heading">
    <div>
        <h1>Seed test data</h1>
        <p class="page-description">Add 50 realistic, fictional Nigerian student records for testing.</p>
    </div>
    <a class="button button-secondary" href="index.php">Back to students</a>
</section>

<?php if ($databaseUnavailable): ?>
    <section class="card error-state" role="alert">
        <h2>Student records are temporarily unavailable</h2>
        <p>
            We couldn't write to the student database. Please make sure MySQL is running
            in XAMPP and the database is ready.
        </p>
        <a class="button button-secondary" href="seed.php">Try again</a>
    </section>
<?php elseif ($seeded): ?>
    <section class="card empty-state">
        <h2>50 test students added</h2>
        <p>The seed records were added successfully. You can now try search, filters, and pagination.</p>
        <a class="button" href="index.php">View students</a>
    </section>
<?php elseif ($alreadySeeded): ?>
    <section class="card empty-state">
        <h2>Test data is already present</h2>
        <p>The 50-record seed set was already added, so no additional records were inserted.</p>
        <a class="button" href="index.php">View students</a>
    </section>
<?php else: ?>
    <section class="card empty-state">
        <h2>Ready to add 50 test students?</h2>
        <p>This inserts fictional records with unique <code>example.test</code> emails. The seed set is only added once.</p>
        <form method="get" action="seed.php">
            <input type="hidden" name="confirm" value="yes">
            <button class="button" type="submit">Add 50 test students</button>
        </form>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
