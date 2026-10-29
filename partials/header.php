<?php

require_once __DIR__ . '/../src/helpers.php';

$pageTitle = $pageTitle ?? 'Student Records Manager';
$flashMessage = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | Student Records</title>
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="index.php">Student Records</a>
            <nav aria-label="Main navigation">
                <a class="nav-link" href="index.php">Students</a>
            </nav>
        </div>
    </header>
    <main class="container">
        <?php if ($flashMessage !== null): ?>
            <div class="alert alert-<?= e($flashMessage['type'] ?? 'info') ?>" role="status">
                <?= e($flashMessage['msg'] ?? '') ?>
            </div>
        <?php endif; ?>
