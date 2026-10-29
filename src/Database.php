<?php

function db(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $config = require __DIR__ . '/../config/config.php';
    $settings = $config['db'];
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $settings['host'],
        $settings['port'],
        $settings['database']
    );

    $connection = new PDO($dsn, $settings['username'], $settings['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}

function studentCourseOptions(): array
{
    $column = db()->query("SHOW COLUMNS FROM students LIKE 'course'")->fetch();

    if ($column === false || !preg_match('/^enum\((.*)\)$/i', $column['Type'], $matches)) {
        throw new RuntimeException('The students.course column must be an ENUM.');
    }

    $options = str_getcsv($matches[1], ',', "'", '\\');
    if ($options === [] || $options === false) {
        throw new RuntimeException('The students.course ENUM has no options.');
    }

    return $options;
}
