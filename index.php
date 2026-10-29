<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

$pageTitle = 'Students';
$databaseUnavailable = false;
$students = [];
$courseOptions = [];
$search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$selectedCourse = isset($_GET['course']) && is_string($_GET['course']) ? $_GET['course'] : '';
$requestedPage = filter_var($_GET['page'] ?? null, FILTER_VALIDATE_INT);
$currentPage = is_int($requestedPage) && $requestedPage > 0 ? $requestedPage : 1;
$perPage = 10;
$totalStudents = 0;
$totalPages = 1;
$startNumber = 0;
$endNumber = 0;
$paginationQuery = ['q' => $search, 'course' => $selectedCourse];

try {
    $connection = db();
    $courseOptions = studentCourseOptions();
    if (!in_array($selectedCourse, $courseOptions, true)) {
        $selectedCourse = '';
        $paginationQuery['course'] = '';
    }

    $conditions = [];
    $parameters = [];
    if ($search !== '') {
        $conditions[] = '(first_name LIKE :search_first OR last_name LIKE :search_last OR email LIKE :search_email)';
        $searchPattern = '%' . $search . '%';
        $parameters['search_first'] = $searchPattern;
        $parameters['search_last'] = $searchPattern;
        $parameters['search_email'] = $searchPattern;
    }
    if ($selectedCourse !== '') {
        $conditions[] = 'course = :course';
        $parameters['course'] = $selectedCourse;
    }
    $where = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);

    $countStatement = $connection->prepare('SELECT COUNT(*) FROM students' . $where);
    $countStatement->execute($parameters);
    $totalStudents = (int) $countStatement->fetchColumn();
    $totalPages = max(1, (int) ceil($totalStudents / $perPage));
    $currentPage = min($currentPage, $totalPages);
    $offset = ($currentPage - 1) * $perPage;

    $statement = $connection->prepare(
        'SELECT id, first_name, last_name, email, course, enrolled_on
         FROM students' . $where . '
         ORDER BY last_name ASC, first_name ASC, id ASC
         LIMIT :limit OFFSET :offset'
    );
    foreach ($parameters as $name => $value) {
        $statement->bindValue(':' . $name, $value, PDO::PARAM_STR);
    }
    $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->execute();
    $students = $statement->fetchAll();
    $startNumber = $totalStudents > 0 ? $offset + 1 : 0;
    $endNumber = min($offset + count($students), $totalStudents);
} catch (PDOException | RuntimeException) {
    http_response_code(503);
    $databaseUnavailable = true;
}

$csrfToken = csrfToken();
$paginationUrl = static function (int $page) use ($paginationQuery): string {
    $query = $paginationQuery;
    $query['page'] = $page;

    return 'index.php?' . http_build_query($query);
};
require __DIR__ . '/partials/header.php';
?>
<section class="page-heading">
    <div>
        <h1>Students</h1>
        <p class="page-description">Manage student records and enrollment details.</p>
    </div>
    <div class="page-actions">
        <a class="button button-secondary" href="seed.php">Seed test data</a>
        <a class="button" href="create.php">Add student</a>
    </div>
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
<?php else: ?>
    <form class="student-filters" method="get" action="index.php">
        <div class="form-field search-field">
            <label for="q">Search students</label>
            <input id="q" name="q" type="search" value="<?= e($search) ?>" placeholder="Name or email">
        </div>
        <div class="form-field">
            <label for="course">Course</label>
            <select id="course" name="course">
                <option value="">All courses</option>
                <?php foreach ($courseOptions as $course): ?>
                    <option value="<?= e($course) ?>" <?= $selectedCourse === $course ? 'selected' : '' ?>><?= e($course) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-actions">
            <button class="button" type="submit">Apply filters</button>
            <a class="button button-secondary" href="index.php">Clear</a>
        </div>
    </form>

    <?php if ($students === []): ?>
    <section class="card empty-state">
        <h2><?= $search !== '' || $selectedCourse !== '' ? 'No matching students' : 'No students yet' ?></h2>
        <p><?= $search !== '' || $selectedCourse !== '' ? 'Try adjusting your search or course filter.' : 'Add a student to start building your records.' ?></p>
        <?php if ($search === '' && $selectedCourse === ''): ?>
            <a class="button" href="create.php">Add student</a>
        <?php endif; ?>
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
                                    <a href="show.php?id=<?= e($student['id']) ?>">View</a>
                                    <a href="edit.php?id=<?= e($student['id']) ?>">Edit</a>
                                    <form method="post" action="delete.php" onsubmit="return confirm(<?= e(json_encode(
                                        'Delete ' . $student['first_name'] . ' ' . $student['last_name'] . '?',
                                        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
                                    )) ?>);">
                                        <input type="hidden" name="id" value="<?= e($student['id']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
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

    <div class="pagination-summary">
        Showing <?= e($startNumber) ?>–<?= e($endNumber) ?> of <?= e($totalStudents) ?> students
    </div>
    <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Student list pages">
            <?php if ($currentPage > 1): ?>
                <a class="pagination-link" href="<?= e($paginationUrl($currentPage - 1)) ?>" rel="prev">Previous</a>
            <?php else: ?>
                <span class="pagination-link is-disabled" aria-disabled="true">Previous</span>
            <?php endif; ?>

            <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                <?php if ($page === $currentPage): ?>
                    <span class="pagination-link is-current" aria-current="page"><?= e($page) ?></span>
                <?php else: ?>
                    <a class="pagination-link" href="<?= e($paginationUrl($page)) ?>"><?= e($page) ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a class="pagination-link" href="<?= e($paginationUrl($currentPage + 1)) ?>" rel="next">Next</a>
            <?php else: ?>
                <span class="pagination-link is-disabled" aria-disabled="true">Next</span>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
